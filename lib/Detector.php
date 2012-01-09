<?php

class Detector {
	
	public static $ua;
	static $uaHash;
	public static $accept;
	public static $isBot;
	
	static $uaFeatures_js = 'modernizr/modernizr-latest.js';
	
	public function get() {
		self::$ua     = $_SERVER["HTTP_USER_AGENT"];
		self::$uaHash = md5(self::$ua);
	    self::$accept = $_SERVER["HTTP_ACCEPT"];
		self::$isBot  = self::isBot();
		
		$uaHash = self::$uaHash;
		
		// just use what we already have saved in the session for this browser
		if (session_start() && isset($_SESSION) && isset($_SESSION[$uaHash])) {
			echo("found via session");
			return $_SESSION[$uaHash];
		} else if (isset($_COOKIE) && isset($_COOKIE[$uaHash])) {
			echo("found via cookie");
			$uaFeatures = self::_ang($_COOKIE[$uaHash]);
			if (isset($_SESSION)) {
				$_SESSION[$uaHash] = $uaFeatures;
			}
			if ($uaJSONTemplate = @file_get_contents(__DIR__."/../user-agents/ua.template.json")) {
				$jsonTemplate = json_decode($uaJSON);
				$jsonTemplate->ua     = self::$ua;
				$jsonTemplate->uaHash = self::$uaHash;
				$jsonTemplate->isBot  = self::$isBot;
				$jsonTemplate->features = $uaFeatures;
				$jsonTemplate = json_encode($jsonTemplate);
				$fp = fopen(__DIR__."/../user-agents/ua.".$uaHash.".json", "w");
				fwrite($fp, $jsonTemplate);
				fclose($fp);
			}
			setcookie($uaHash,"",time()-3600);
			return $uaFeatures;
		} else if ($uaJSON = @file_get_contents(__DIR__."/../user-agents/ua.".self::$uaHash.".json")) {
			echo("found via profile");
			$json = json_decode($uaJSON);
			if (isset($_SESSION)) {
				$_SESSION[$uaHash] = $json->features;
			}
			return $json->features;
		} else {
			print "<html><head><script type='text/javascript'>";
			readfile(__DIR__ . '/' . self::$uaFeatures_js);
			print self::_mer() . "</script></head><body></body></html>";
			exit;
		}
	}
	
	static function _mer() {
		return "".
		  "var m=Modernizr,c='';".
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
		  "c+=';path=/';".
		  "try{".
		    "document.cookie=c;".
		    "document.location.reload();".
		  "}catch(e){}".
		"";
		}

	static function _ang($cookie) {
		$uaFeatures = new UA();
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
	
	private function isBot() {
		// bot list shamelessly taken from @yiibu's kb.json file from Profile
		if (preg_match('/bot|borg|google|yahoo|slurp|msnbot|msrbot|openbot|archiver|netresearch|lycos|scooter|altavista|teoma|gigabot|baiduspider|blitzbot|oegp|charlotte|furlbot|http%20client|polybot|htdig|ichiro|mogimogi|larbin|pompos|scrubby|searchsight|seekbot|semanticdiscovery|silk|snappy|speedy|spider|voila|vortex|voyager|zao|zeal/i',self::$ua)) {
			return true;
		} else {
			return false;
		}
	}
}

$ua = Detector::get();

?>