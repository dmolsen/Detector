<?php

class Detector {
	
	public static  $ua;
	public static  $accept;
	
	public static  $foundIn; // this is just for the demo. won't ever really be needed i don't think
	
	private static $uaHash;
	private static $uaFeaturesCore_js      = "modernizr/modernizr.pre21.js"; // pre should support values being passed from tests
	private static $uaFeaturesCoreExtra_js = "modernizr/extra/";
	
	private static $isMobile    = false;
	private static $isTablet    = false;
	private static $isComputer  = false;
	private static $isSpider    = false;
	private static $deviceOSGeneral;
	private static $deviceOSSpecific;
	private static $majorVersion = 0;
	private static $minorVersion = 0;
	
	public function build() {
		
		// populate some standard variables
		self::$ua     = $_SERVER["HTTP_USER_AGENT"];
		self::$uaHash = md5(self::$ua);
	    self::$accept = $_SERVER["HTTP_ACCEPT"];
		
		if (session_start() && isset($_SESSION) && isset($_SESSION[self::$uaHash])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "session";
			
			// update session info from a cookie that has the extra info
			return $_SESSION[$uaHash];
			
		} else if (isset($_COOKIE) && isset($_COOKIE[self::$uaHash])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "cookie";
			
			// grab the features from the cookie and create an object with them (self::_ang)
			$uaFeatures = self::_ang($_COOKIE[self::$uaHash]);
			
			// classify the user agent string so we can learn more what device this really is. more for readability than anything
			self::classifyUA();
			
			// open the JSON template file that will be populated
			if ($uaJSONTemplate = @file_get_contents(__DIR__."/../user-agents/ua.template.json")) {
				$jsonTemplate = json_decode($uaJSONTemplate);
			} 
			
			$jsonTemplate->ua               = self::$ua;
			$jsonTemplate->uaHash           = self::$uaHash;
			$jsonTemplate->deviceOSGeneral  = self::$deviceOSGeneral;
			$jsonTemplate->deviceOSSpecific = self::$deviceOSSpecific;
			$jsonTemplate->isMobile         = (!self::$isMobile && ($uaFeatures->mobile == 1)) ? true : self::$isMobile;
			$jsonTemplate->isTablet         = (!self::$isTablet && ($uaFeatures->tablet == 1)) ? true : self::$isTablet;
			$jsonTemplate->isComputer       = self::$isComputer;
			$jsonTemplate->isSpider         = self::$isSpider;
			
			// push features into the same level as the other info and change to true/false
			foreach($uaFeatures as $key => $value) {
				if (is_object($value)) {
					foreach ($value as $vkey => $vvalue) {
						$value->$vkey = ($vvalue == 1) ? true : false;
					}
					$jsonTemplate->$key = $value;
				} else {
					$jsonTemplate->$key = ($value == 1) ? true : false;
				}
			}
			
			// write out to disk for future requests that might have the same UA
			$jsonTemplate = json_encode($jsonTemplate);
			$fp = fopen(__DIR__."/../user-agents/ua.".self::$uaHash.".core.json", "w");
			fwrite($fp, $jsonTemplate);
			fclose($fp);
			
			// unset the cookie that held the test data
			setcookie(self::$uaHash,"",time()-3600);
			
			// add our collected data to the session for use in future requests
			if (isset($_SESSION)) {
				$_SESSION[self::$uaHash] = $jsonTemplate;
			}
			
			// return the collected data to the script for use in this go around
			return $jsonTemplate;
			
		} else if ($uaJSON = @file_get_contents(__DIR__."/../user-agents/ua.".self::$uaHash.".json")) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "file";
			
			// decode the json, put it into session, and return it to the script
			$json = json_decode($uaJSON);
			if (isset($_SESSION)) {
				$_SESSION[self::$uaHash] = $json;
			}
			return $json;
			
		} else {
			
			// didn't recognize that the user had been here before nor the UA string.
			// gather info by sending Modernizr & custom tests
			print "<html><head><script type='text/javascript'>";
			readfile(__DIR__ . '/' . self::$uaFeaturesCore_js);
			if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesCoreExtra_js)) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." && $entry != "..") {
			            readfile(__DIR__ . '/' . self::$uaFeaturesCoreExtra_js . $entry);
			        }
			    }
			    closedir($handle);
			}
			print self::_mer() . "</script></head><body></body></html>";
			exit;
		}
	}
	
	static function _mer() {
		return "".
		  "var m=Modernizr;c='';".
		  "for(var f in m){".
		    "if(f[0]=='_'){continue;}".
		    "var t=typeof m[f];".
		    "if(t=='function'){continue;}".
		    "c+=(c?'|':'".self::$uaHash."=')+f+':';".
		    "if(t=='object'){".
		      "for(var s in m[f]){".
		        "c+='/'+s+':'+(m[f][s]?'1':'0');".
		      "}".
		    "}else{".
		      "c+=m[f]?'1':'0';".
		    "}".
		  "}".
		  //"for(var k in n){".
		  //	"c+='|'+k+':'+n[k];".
		  //"}".
		  "c+=';path=/';".
		  "try{".
		    //"console.log(c);".
		    "document.cookie=c;".
		    "document.location.reload();".
		  "}catch(e){}".
		"";
		}

	static function _ang($cookie) {
		$uaFeatures = new Detector();
		foreach (explode('|', $cookie) as $feature) {
			list($name, $value) = explode(':', $feature, 2);
			if ($value[0]=='/') {
				$value_object = new stdClass();
				foreach (explode('/', substr($value, 1)) as $sub_feature) {
					list($sub_name, $sub_value) = explode(':', $sub_feature, 2);
					$value_object->$sub_name = $sub_value;
				}
				$uaFeatures->$name = $value_object;
			} else {
				$uaFeatures->$name = $value;
			}
		}
		return $uaFeatures;
	}

	// returns the general device type based on user agent string matching. can get very specific depending on usage.
	private static function classifyUA() {

		if (preg_match("/ipod/i",self::$ua)) {
			$type = 'ipod';
			self::findUAVersion();
		} else if (preg_match("/iphone/i",self::$ua)) {
			$type = 'iphone';
			self::findUAVersion();
		}
		else if (preg_match("/ipad/i",self::$ua)) {
			$type = 'ipad';
			self::findUAVersion();
		}
		else if (preg_match("/android/i",self::$ua)) {
			$type = "android";
			self::findUAVersion();
		}
		else if (preg_match('/WebOS/i',self::$ua)) {
			$type = "webos";
		}
		else if (preg_match('/opera mini/i',self::$ua)) {
			$type = "opera_mini";
		} 
		else if (preg_match('/opera mobi/i',self::$ua)) {
			$type = "opera_mobile";
		}
		else if (preg_match('/blackberry/i',self::$ua)) {
			$type = "blackberry";
		}
		else if (preg_match('/(palm os|palm|treo)/i',self::$ua)) {
			$type = "palm";
		}
		else if (preg_match('/symbian/i',self::$ua)) {
			$type = "symbian";
		}
		else if (preg_match('/(windows ce; ppc;|windows ce; smartphone;|windows ce; iemobile)/i',self::$ua)) {
			$type = "windowsmobile";
		} 
		else if (preg_match('/kindle/i',self::$ua)) {
			$type = "kindle";
		}
		else if (preg_match('/psp/i',self::$ua)) {
			$type = "playstationportable";
		}
		else if (preg_match('/(hiptop|avantgo|plucker|xiino|blazer|elaine|up.browser|up.link|mmp|smartphone|midp|wap|vodafone|o2|pocket|mobile|pda)/i',self::$ua)) {
			$type = "genericsmartphone";
	  	}
	  	else if ((strpos(self::$accept,'text/vnd.wap.wml') > 0) || (strpos(self::$accept,'application/vnd.wap.xhtml+xml') > 0) || isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']) || in_array(strtolower(substr(self::$ua,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex',
			'anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','comp'=>'comp','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai',
			'emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac',
			'iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno',
			'm1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21',
			'mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki',
			'nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600',
			'raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams',
			'sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-',
			'telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','tosh'=>'tosh','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu',
			'x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java',
			'jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-',
			'send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-'))) {
			$type = "genericfeaturephone";
		}
		else if (preg_match('/bot|borg|google|yahoo|slurp|msnbot|msrbot|openbot|archiver|netresearch|lycos|scooter|altavista|teoma|gigabot|baiduspider|blitzbot|oegp|charlotte|furlbot|http%20client|polybot|htdig|ichiro|mogimogi|larbin|pompos|scrubby|searchsight|seekbot|semanticdiscovery|silk|snappy|speedy|spider|voila|vortex|voyager|zao|zeal/i',self::$ua)){
			$type = "spider"; // this regex taken from yiibu's profile project
		}
		else {
			$type = "computer";
		}

		//
		self::$deviceOSGeneral = $type;
		self::$deviceOSSpecific = $type.self::$majorVersion.self::$minorVersion;
		
		self::$isTablet = (preg_match('/ipad|kindle/',$type)) ? true : false;
		self::$isComputer = ($type == "computer");
		self::$isSpider = ($type == "spider");
		if (!self::$isTablet && !self::$isComputer && !self::$isSpider) {
			self::$isMobile = true;
		}
	}

	private static function findUAVersion() {
		if (preg_match('/\ ([0-9]{1,2})\.([0-9]{1,2})/i',self::$ua,$matches)) {
			self::$majorVersion = $matches[1];
			self::$minorVersion = $matches[2];
		}
	}

	// create a function to include the extra JS
}

$ua = Detector::build();

?>