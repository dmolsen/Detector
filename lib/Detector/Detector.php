<?php

/*!
 * Detector v0.1
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

class Detector {
	
	public static  $ua;
	public static  $accept;
	
	public static  $foundIn;             // this is just for the demo. won't ever really be needed i don't think
	
	private static $uaHash;
	private static $sessionID;
	private static $uaFeaturesMaxJS      = "modernizr/modernizr.pre21.js";    // all the default Modernizr Tests
	private static $uaFeaturesMinJS      = "modernizr/modernizr.no-tests.js"; // NO default tests except media queries, meant to run those in the perrequest folder
	private static $uaFeaturesCore       = "modernizr/core/"; 
	private static $uaFeaturesExtended   = "modernizr/extended/";
	private static $uaFeaturesPerRequest = "modernizr/perrequest/";
	
	private static $isMobile             = false;
	private static $isTablet             = false;
	private static $isComputer           = false;
	private static $isSpider             = false;
	private static $mobileType           = '';
	private static $deviceOSGeneral;
	private static $deviceOSSpecific;
	private static $majorVersion         = 0;
	private static $minorVersion         = 0;
	
	/**
	* Tests to see if:
	*     - a session has already been opened for the request browser, if so send the info back, else
	*     - see if the cookie has been set so we can build the profile, if so build the profile & send the info back, else
	*     - see if detector can find an already created profile for the browser, if so send the info back, else
	*     - start the process for building a profile for this unknown browser
	*
	* Based heavily on modernizr-server
	*
	* @return {Object}       an object that contains all the properties for this particular user agent
	*/
	public function build() {
		
		// populate some standard variables
		self::$ua        = $_SERVER["HTTP_USER_AGENT"];
		self::$uaHash    = md5(self::$ua);
		self::$sessionID = self::$uaHash."-session";
	    self::$accept    = $_SERVER["HTTP_ACCEPT"];
		
		// offer the ability to review profiles saved in the system
		if (preg_match("/[a-z0-9]{32}/",$_REQUEST['pid'])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "archive";
			
			// decode the core data
			$uaJSONCore     = @file_get_contents(__DIR__."/user-agents-core/ua.".$_REQUEST['pid'].".json");
			$uaJSONCore     = json_decode($uaJSONCore);
			
			// find and decode the extended data
			$uaJSONExtended = @file_get_contents(__DIR__."/user-agents-extended/ua.".$_REQUEST['pid'].".json");
			$uaJSONExtended = json_decode($uaJSONExtended);
			
			// merge the data
			$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return to the script
			return $mergedInfo;
		
		} else if (session_start() && isset($_SESSION) && isset($_SESSION[self::$sessionID])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "session";
			
			// grab features out of the cookie that are being checked on every request. update the session as appropriate
			$uaFeatures = self::_ang($_COOKIE[self::$uaHash."-pr"]);
			$cookiePerRequest = new stdClass();
			foreach($uaFeatures as $key => $value) {
				$key = str_replace("pr-", "", $key);
				if (is_object($value)) {
					foreach ($value as $vkey => $vvalue) {
						$value->$vkey = ($vvalue == 1) ? true : false;
					}
					$cookiePerRequest->$key = $value;
				} else {
					$cookiePerRequest->$key = ($value == 1) ? true : false;
				}
			}
			
			// merge the session info we already have and the info from the cookie
			$mergedInfo = ($cookiePerRequest) ? (object) array_merge((array) $_SESSION[self::$sessionID], (array) $cookiePerRequest) : $_SESSION[self::$sessionID];
			
			// save the new info to the session
			$_SESSION[self::$sessionID] = $mergedInfo;

			// send the data back to the script to be used
			return $mergedInfo;
			
		} else if (isset($_COOKIE) && isset($_COOKIE[self::$uaHash])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "cookie";
			
			// grab the features from the cookie and create an object with them (self::_ang)
			$uaFeatures = self::_ang($_COOKIE[self::$uaHash]);
			
			// classify the user agent string so we can learn more what device this really is. more for readability than anything
			self::classifyUA();
			
			// open the JSON template core file that will be populated
			if ($uaJSONTemplateCore = @file_get_contents(__DIR__."/user-agents-core/ua.template.json")) {
				$jsonTemplateCore = json_decode($uaJSONTemplateCore);
			} 
			
			// open the JSON template core file that will be populated
			if ($uaJSONTemplateExtended = @file_get_contents(__DIR__."/user-agents-extended/ua.template.json")) {
				$jsonTemplateExtended = json_decode($uaJSONTemplateExtended);
			}
			
			// create an object to hold any of the per request data. it shouldn't be saved to file but it should be added to the session
			$cookiePerRequest = new stdClass();
			
			$jsonTemplateCore->ua               = self::$ua;
			$jsonTemplateCore->uaHash           = self::$uaHash;
			$jsonTemplateCore->deviceOSGeneral  = self::$deviceOSGeneral;
			$jsonTemplateCore->deviceOSSpecific = self::$deviceOSSpecific;
			$jsonTemplateCore->isMobile         = (!self::$isMobile && ($uaFeatures->mobile == 1)) ? true : self::$isMobile;
			$jsonTemplateCore->isTablet         = (!self::$isTablet && ($uaFeatures->tablet == 1)) ? true : self::$isTablet;
			$jsonTemplateCore->isComputer       = self::$isComputer;
			$jsonTemplateCore->isSpider         = self::$isSpider;
			
			$jsonTemplateExtended->ua           = self::$ua;
			$jsonTemplateExtended->uaHash       = self::$uaHash;
			
			// push features into the same level as the general device information
			// change 1/0 to true/false. why? 'cause that's what i like to read ;)
			foreach($uaFeatures as $key => $value) {
				$pos1 = strpos($key,"extended-");
				$pos2 = strpos($key,"pr-");
				if ($pos1 !== false) {
					$key = str_replace("extended-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							$value->$vkey = ($vvalue == 1) ? true : false;
						}
						$jsonTemplateExtended->$key = $value;
					} else {
						$jsonTemplateExtended->$key = ($value == 1) ? true : false;
					}
				} else if ($pos2 !== false) {
					$key = str_replace("pr-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							$value->$vkey = ($vvalue == 1) ? true : false;
						}
						$cookiePerRequest->$key = $value;
					} else {
						$cookiePerRequest->$key = ($value == 1) ? true : false;
					}
				} else {
					$key = str_replace("core-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							$value->$vkey = ($vvalue == 1) ? true : false;
						}
						$jsonTemplateCore->$key = $value;
					} else {
						$jsonTemplateCore->$key = ($value == 1) ? true : false;
					}
				}
			}

			// merge the data for future requests
			$mergedInfo = new stdClass();
			$mergedInfo = ($jsonTemplateExtended) ? (object) array_merge((array) $jsonTemplateCore, (array) $jsonTemplateExtended) : $jsonTemplateCore;
			$mergedInfo = ($cookiePerRequest) ? (object) array_merge((array) $mergedInfo, (array) $cookiePerRequest) : $mergedInfo;

			// write out to disk for future requests that might have the same UA
			$jsonTemplateCore = json_encode($jsonTemplateCore);
			$fp = fopen(__DIR__."/user-agents-core/ua.".self::$uaHash.".json", "w");
			fwrite($fp, $jsonTemplateCore);
			fclose($fp);
			
			$jsonTemplateExtended = json_encode($jsonTemplateExtended);
			$fp = fopen(__DIR__."/user-agents-extended/ua.".self::$uaHash.".json", "w");
			fwrite($fp, $jsonTemplateExtended);
			fclose($fp);
			
			// unset the cookie that held the vast amount of test data
			setcookie(self::$uaHash,"");
			
			// add our collected data to the session for use in future requests, also add the per request data
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return the collected data to the script for use in this go around
			return $mergedInfo;
			
		} else if ($uaJSONCore = @file_get_contents(__DIR__."/user-agents-core/ua.".self::$uaHash.".json")) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "file";
			
			// decode the core data
			$uaJSONCore     = json_decode($uaJSONCore);
			
			// find and decode the extended data
			$uaJSONExtended = @file_get_contents(__DIR__."/user-agents-extended/ua.".self::$uaHash.".json");
			$uaJSONExtended = json_decode($uaJSONExtended);
			
			// merge the data
			$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return to the script
			return $mergedInfo;
			
		} else {
			
			// didn't recognize that the user had been here before nor the UA string.
			
			// check to see if it's a spider or device without javascript (currently throwing generic feature phones under the bus)
			// gather info by sending Modernizr & custom tests
			print "<html><head><script type='text/javascript'>";
			readfile(__DIR__ . '/' . self::$uaFeaturesMaxJS);
			if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesCore)) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." && $entry != "..") {
			            readfile(__DIR__ .'/'. self::$uaFeaturesCore . $entry);
			        }
			    }
			    closedir($handle);
			}
			if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesExtended)) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." && $entry != "..") {
			            readfile(__DIR__ .'/'. self::$uaFeaturesExtended . $entry);
			        }
			    }
			    closedir($handle);
			}
			if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesPerRequest)) {
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != "." && $entry != "..") {
			            readfile(__DIR__ .'/'. self::$uaFeaturesPerRequest . $entry);
			        }
			    }
			    closedir($handle);
			}
			print self::_mer() . "</script></head><body></body></html>";
			exit;
		}
	}
	
	/**
	* Reads in the per request feature tests and sends them to the function that builds out the JS & cookie
	*
	* from modernizr-server
	*
	* @return {String}       the HTML & JavaScript that tracks the per request test
	*/
	public static function perrequest() {
		readfile(__DIR__ . '/' . self::$uaFeaturesMinJS);
		if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesPerRequest)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != "." && $entry != "..") {
		            readfile(__DIR__ . '/' . self::$uaFeaturesPerRequest . $entry);
		        }
		    }
		    closedir($handle);
		}
		print self::_mer(false,'-pr');
	}
	
	/**
	* Creates the JavaScript & cookie that tracks the features for a particular browser
	* @param  {Boolean}      if the javascript should include a page reload statement
	* @param  {String}       if the cookie that is created should have a string appended to it. used for per request tests.
	*
	* from modernizr-server
	*
	* @return {String}       the HTML & JavaScript that tracks the per request test
	*/
	private static function _mer($reload = true, $cookieExtra = '') {
		$output = "".
		  "var m=Modernizr;c='';".
		  "for(var f in m){".
		    "if(f[0]=='_'){continue;}".
		    "var t=typeof m[f];".
		    "if(t=='function'){continue;}".
		    "c+=(c?'|':'".self::$uaHash.$cookieExtra."=')+f+':';".
		    "if(t=='object'){".
		      "for(var s in m[f]){".
		        "c+='/'+s+':'+(m[f][s]?'1':'0');".
		      "}".
		    "}else{".
		      "c+=m[f]?'1':'0';".
		    "}".
		  "}".
		  "c+=';path=/';".
		  "try{".
		    "document.cookie=c;";
		if ($reload) {
			$output .= "document.location.reload();";
		}
		$output .= "}catch(e){}"."";
		return $output;
	}

	/**
	* Reads in the cookie values and breaks them up into an object for use in build()
	* @param  {String}       the value from the cookie
	*
	* from modernizr-server
	*
	* @return {Object}       key/value pairs based on the cookie
	*/
	private static function _ang($cookie) {
		$uaFeatures = new Detector();
		if ($cookie != '') {
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
		}
		return $uaFeatures;
	}

	/**
	* returns the general device type based on user agent string matching. can get very specific depending on usage.
	*
	* from mobile web osp
	*/
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
		else if (preg_match('/silk/i',self::$ua) && preg_match('/webkit/i',self::$ua)) {
			$type = "kindle_fire";
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
		
		self::$isTablet = (preg_match('/ipad|kindle|kindle_fire/',$type)) ? true : false;
		self::$isComputer = ($type == "computer");
		self::$isSpider = ($type == "spider");
		if (!self::$isTablet && !self::$isComputer && !self::$isSpider) {
			self::$isMobile = true;
			self::$mobileType = $type;
		}
	}

	/**
	* attempts to create version numbers from the user agent. only used for iPhone, iPod, iPad and Android devices.
	*/
	private static function findUAVersion() {
		if (preg_match('/\ ([0-9]{1,2})(\.|\_)([0-9]{1,2})/i',self::$ua,$matches)) {
			self::$majorVersion = $matches[1];
			self::$minorVersion = $matches[3];
		}
	}

}

$ua = Detector::build();

?>