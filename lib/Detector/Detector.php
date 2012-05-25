<?php

/*!
 * Detector v0.5.1
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

// address 5.2 compatibility
if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
if (!function_exists('json_decode') || !function_exists('json_encode')) {
	require_once(__DIR__."/lib/json/jsonwrapper.php");
}

// include the ua-parser-php library to rip apart user agent strings
require_once(__DIR__."/lib/ua-parser-php/UAParser.php");

// include the browserFamily library to classify the browser by features
require_once(__DIR__."/lib/feature-family/featureFamily.php");

class Detector {
	
	private static $debug               = false; // gets overwritten by the config so changing this won't do anything for you...
	
	public static  $ua;
	public static  $accept;
	
	public static  $foundIn;             // this is just for the demo. won't ever really be needed i don't think
	
	private static $uaHash;
	private static $sessionID;
	private static $cookieID;
	private static $uaFeaturesMaxJS;     // all the default Modernizr Tests
	private static $uaFeaturesMinJS;     // NO default tests except media queries, meant to run those in the perrequest folder
	private static $uaFeaturesCore; 
	private static $uaFeaturesExtended;
	private static $uaFeaturesPerRequest;
	
	private static $uaDirCore;
	private static $uaDirExtended;
	
	/**
	* Tests to see if:
	*     - see if this is a debug request with appropriately formed pid, else
	*     - see if a session has already been opened for the request browser, if so send the info back, else
	*     - see if the cookie has been set so we can build the profile, if so build the profile & send the info back, else
	*     - see if this browser reports being a spider, doesn't support JS or doesn't support cookies
	*     - see if detector can find an already created profile for the browser, if so send the info back, else
	*     - start the process for building a profile for this unknown browser
	*
	* Logic is based heavily on modernizr-server
	*
	* @return {Object}       an object that contains all the properties for this particular user agent
	*/
	public static function build() {
		
		// set-up the configuration options for the system
		if (!($config = @parse_ini_file(__DIR__."/config/config.ini"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy(__DIR__."/config/config.ini.default", __DIR__."/config/config.ini")) {
			    print "Please make sure config.ini.default exists before trying to have Detector build the config.ini file automagically.";
				exit;
			} else {
				$config = @parse_ini_file(__DIR__."/config/config.ini");	
			}
		}
		
		// populate some standard variables out of the config
		self::$debug                = $config['debug'];
		self::$uaFeaturesMaxJS      = $config['uaFeaturesMaxJS'];
		self::$uaFeaturesMinJS      = $config['uaFeaturesMinJS']; 
		self::$uaFeaturesCore       = $config['uaFeaturesCore']; 
		self::$uaFeaturesExtended   = $config['uaFeaturesExtended'];
		self::$uaFeaturesPerRequest = $config['uaFeaturesPerRequest'];
		
		self::$uaDirCore            = $config['uaDirCore'];
		self::$uaDirExtended        = $config['uaDirExtended'];
		
		$coreVersion                = $config['coreVersion'];
		$extendedVersion            = $config['extendedVersion'];
		
		$noJSCookieFamilySupport	= $config['noJSCookieFamilySupport'];
		$noJSSearchFamily			= $config['noJSSearchFamily'];
		$noJSDefaultFamily			= $config['noJSDefaultFamily'];
		$noCookieFamily				= $config['noCookieFamily'];
		
		// populate some standard variables based on the user agent string
		self::$ua                   = strip_tags($_SERVER["HTTP_USER_AGENT"]);
		self::$accept               = strip_tags($_SERVER["HTTP_ACCEPT"]);
		self::$uaHash               = md5(self::$ua);
		self::$sessionID            = md5(self::$ua."-session-".$coreVersion."-".$extendedVersion);
		self::$cookieID             = md5(self::$ua."-cookie-".$coreVersion."-".$extendedVersion);
		
		$uaFileCore                 = __DIR__."/".self::$uaDirCore."ua.".self::$uaHash.".json";
		$uaFileExtended             = __DIR__."/".self::$uaDirExtended."ua.".self::$uaHash.".json";
		
		$uaTemplateCore             = __DIR__."/".self::$uaDirCore."ua.template.json";
		$uaTemplateExtended         = __DIR__."/".self::$uaDirExtended."ua.template.json";
		
		$pid                        = (isset($_REQUEST['pid']) && preg_match("/[a-z0-9]{32}/",$_REQUEST['pid'])) ? $_REQUEST['pid'] : false;
		
		// offer the ability to review profiles saved in the system
		if ($pid && self::$debug) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "archive";
			
			// decode the core data
			$uaJSONCore     = json_decode(@file_get_contents(__DIR__."/".self::$uaDirCore."ua.".$pid.".json"));
			
			// find and decode the extended data
			$uaJSONExtended = json_decode(@file_get_contents(__DIR__."/".self::$uaDirExtended."ua.".$pid.".json"));
			
			// merge the data
			$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return to the script
			return $mergedInfo;
		
		} else if (@session_start() && isset($_SESSION) && isset($_SESSION[self::$sessionID])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "session";
			
			// parse the per request cookie
			$cookiePerRequest = new stdClass();
			$cookiePerRequest = self::parseCookie("pr",$cookiePerRequest);
			
			// merge the session info we already have and the info from the cookie
			$mergedInfo = (isset($cookiePerRequest)) ? (object) array_merge((array) $_SESSION[self::$sessionID], (array) $cookiePerRequest) : $_SESSION[self::$sessionID];
			
			// save the new info to the session
			$_SESSION[self::$sessionID] = $mergedInfo;

			// send the data back to the script to be used
			return $mergedInfo;
			
		} else if (isset($_COOKIE) && isset($_COOKIE[self::$cookieID])) {
			
			// to be clear, this section means that a UA was unknown, was profiled with modernizr & now we're saving that data to build a new profile
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "cookie";
			
			// open the JSON template core & extended files that will be populated
			$jsonTemplateCore     = self::openUAFile($uaTemplateCore);
			$jsonTemplateExtended = self::openUAFile($uaTemplateExtended);
			
			// use ua-parser-php to set-up the basic properties for this UA, populate other core properties
			$jsonTemplateCore->ua          = self::$ua;
			$jsonTemplateCore->uaHash      = self::$uaHash;
			$jsonTemplateCore->coreVersion = $coreVersion;
			$jsonTemplateCore              = self::createUAProperties($jsonTemplateCore);
			
			// populate extended properties
			$jsonTemplateExtended                  = !isset($jsonTemplateExtended) ? new stdClass() : $jsonTemplateExtended;
			$jsonTemplateExtended->ua              = self::$ua;
			$jsonTemplateExtended->uaHash          = self::$uaHash;
			$jsonTemplateExtended->extendedVersion = $extendedVersion;
			
			// create an object to hold any of the per request data. it shouldn't be saved to file but it should be added to the session
			$cookiePerRequest = new stdClass();
			
			// push features into the same level as the general device information
			// change 1/0 to true/false. why? 'cause that's what i like to read ;)
			$jsonTemplateCore     = self::parseCookie("core",$jsonTemplateCore,true);
			$jsonTemplateExtended = self::parseCookie("extended",$jsonTemplateExtended,true);
			$cookiePerRequest     = self::parseCookie("pr",$cookiePerRequest,true);

			// merge the data for future requests
			$mergedInfo = new stdClass();
			$mergedInfo = ($jsonTemplateExtended) ? (object) array_merge((array) $jsonTemplateCore, (array) $jsonTemplateExtended) : $jsonTemplateCore;
			$mergedInfo = ($cookiePerRequest) ? (object) array_merge((array) $mergedInfo, (array) $cookiePerRequest) : $mergedInfo;
			
			// use the uaFeatures to classify the feature family for this browser
			$mergedInfo->family           = featureFamily::find($mergedInfo);
			$jsonTemplateExtended->family = $mergedInfo->family;

			// write out to disk for future requests that might have the same UA
			self::writeUAFile(json_encode($jsonTemplateCore),$uaFileCore);
			self::writeUAFile(json_encode($jsonTemplateExtended),$uaFileExtended);
			
			
			// unset the cookie that held the vast amount of test data
			setcookie(self::$cookieID,"");
			
			// add our collected data to the session for use in future requests, also add the per request data
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// add the user agent & hash to a list of already saved user agents
			self::addToUAList();
			
			// return the collected data to the script for use in this go around
			return $mergedInfo;
		
		} else if ((($userAgent = UA::parse()) && isset($userAgent->spider) && ($userAgent->spider === true)) || (isset($_REQUEST["nojs"]) && ($_REQUEST["nojs"] == "true")) || (isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true"))) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "nojs";

			// open the JSON template core & extended files that will be populated
			$jsonTemplateCore     = self::openUAFile($uaTemplateCore);
			$jsonTemplateExtended = self::openUAFile($uaTemplateExtended);
			
			// use ua-parser-php to set-up the basic properties for this UA, populate other core properties
			// include the basic properties of the UA
			$jsonTemplateCore->ua          = self::$ua;
			$jsonTemplateCore->uaHash      = self::$uaHash;
			$jsonTemplateCore->coreVersion = $coreVersion;
			$jsonTemplateCore              = self::createUAProperties($jsonTemplateCore);
			
			// populate extended properties
			$jsonTemplateExtended                  = !isset($jsonTemplateExtended) ? new stdClass() : $jsonTemplateExtended;
			$jsonTemplateExtended->ua              = self::$ua;
			$jsonTemplateExtended->uaHash          = self::$uaHash;
			$jsonTemplateExtended->extendedVersion = $extendedVersion;

			$mergedInfo = new stdClass();
			$mergedInfo = (object) array_merge((array) $jsonTemplateCore, (array) $jsonTemplateExtended);
			
			// use the uaFeatures to classify the feature family for this browser
			if ($noJSCookieFamilySupport) {
				if (isset($userAgent->spider) && ($userAgent->spider === true)) {
					$mergedInfo->family			  = $noJSSearchFamily;
					$jsonTemplateExtended->family = $noJSSearchFamily;
				} else if (isset($_REQUEST["nojs"]) && ($_REQUEST["nojs"] == "true")) {
					$mergedInfo->family			  = $noJSDefaultFamily;
					$jsonTemplateExtended->family = $noJSDefaultFamily;
				} else if (isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true")) {
					$mergedInfo->family			  = $noCookieFamily;
					$jsonTemplateExtended->family = $noCookieFamily;
				} 
			} else {
				$mergedInfo->family           = featureFamily::find($mergedInfo);
				$jsonTemplateExtended->family = $mergedInfo->family;
			}

			// try setting the session unless cookies are actively not supported
			if (!(isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true")) && isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return the collected data to the script for use in this go around
			return $mergedInfo;

		} else if (($uaJSONCore = json_decode(@file_get_contents($uaFileCore))) && ($uaJSONExtended = json_decode(@file_get_contents($uaFileExtended)))) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "file";
			
			// double-check that the already created profile matches the current version of the core & extended templates
			if (($uaJSONCore->coreVersion != $coreVersion) || ($uaJSONExtended->extendedVersion != $extendedVersion)) {
				
				// versions don't match so build the test page to get new data
				self::buildTestPage();
				
			} else {
				
				// merge the data
				$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;	

				// put the merged JSON info into session
				if (isset($_SESSION)) {
					$_SESSION[self::$sessionID] = $mergedInfo;
				}

				// return to the script
				return $mergedInfo;
				
			}
			
		} else {
			
			// didn't recognize that the user had been here before nor the UA string.
			self::buildTestPage();

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
		self::readDirFiles(self::$uaFeaturesPerRequest);
		print self::_mer(false,'-pr');
	}
	
	/**
	* Builds the browser test page
	*/
	public static function buildTestPage() {
		
		// build the noscript link just in case
		$noscriptLink = $_SERVER["REQUEST_URI"];
		if (isset($_SERVER["QUERY_STRING"]) && ($_SERVER["QUERY_STRING"] != "")) {
			$noscriptLink .= "?".$_SERVER["QUERY_STRING"]."&nojs=true";
		} else {
			$noscriptLink .= "?nojs=true";
		}
		
		// gather info by sending Modernizr & custom tests
		print "<!DOCTYPE html><html lang=\"en\"><head><meta name=\"viewport\" content=\"width=device-width\"><script type='text/javascript'>";
		readfile(__DIR__ . '/' . self::$uaFeaturesMaxJS);
		self::readDirFiles(self::$uaFeaturesCore);
		self::readDirFiles(self::$uaFeaturesExtended);
		self::readDirFiles(self::$uaFeaturesPerRequest);
		print self::_mer() . "</script></head><body onload='checkCookieSupport();'><noscript><meta http-equiv='refresh' content='0; url=".$noscriptLink."'></noscript></body></html>";
		exit;
		
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
		    "c+=(c?'|':'".self::$cookieID.$cookieExtra."=')+f+':';".
		    "if(t=='object'){".
		      "for(var s in m[f]){".
				"if (typeof m[f][s]=='boolean') { c+='/'+s+':'+(m[f][s]?1:0); }".
		        "else { c+='/'+s+':'+m[f][s]; }".
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
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	*/
	private static function addToUAList() {
		
		// open user agent list and decode the JSON
		if ($uaListJSON = @file_get_contents(__DIR__."/".self::$uaDirCore."ua.list.json")) {
			$uaList = json_decode($uaListJSON);
		} 
		
		// merge the old list with the new user agent
		$mergedInfo = (object) array_merge((array) $uaList, array(self::$uaHash => self::$ua));
		
		// write out the data to the user agent list
		$uaListJSON = json_encode($mergedInfo);
		$fp = fopen(__DIR__."/".self::$uaDirCore."ua.list.json", "w");
		fwrite($fp, $uaListJSON);
		fclose($fp);
		
	}
	
	/**
	* Writes out the UA file to the specified location
	* @param  {String}        encoded JSON
	* @param  {String}        file path
	*/
	private static function writeUAFile($jsonEncoded,$uaFilePath) {
		$fp = fopen($uaFilePath, "w");
		fwrite($fp, $jsonEncoded);
		fclose($fp);
	}
	
	/**
	* Opens the UA file at the specificed location
	* @param  {String}        file path
	*/
	private static function openUAFile($uaFilePath) {
		// open the JSON template extended file that will be populated & start populating its object
		if ($uaJSONTemplate = @file_get_contents($uaFilePath)) {
			$uaJSONTemplate = json_decode($uaJSONTemplate);
			return $uaJSONTemplate;
		} else {
			print "couldn't open the JSON file at ".$uaFilePath." for some reason. permissions? bad path? bombing now...";
			exit;
		}
		
		return $obj;
	}
	
	/**
	* Adds classes to the HTML tag if necessary
	* @param  {Object}        the user agent features
	* @param  {String}        list of browser features to include in the css, bad idea to leave features blank...
	* reads out all the files in a directory
	* @param  {String}        file path
	*/
	public static function createHTMLList($obj,$features = null,$printUAProps = false) {
		if ($features != null) {
			$features_a = explode(",",$features);
			array_walk($features_a,create_function('&$val', '$val = trim($val);'));
		}
		foreach($obj as $key=>$value) {
			if (is_object($value)) {
				foreach ($value as $vkey => $vvalue) {
					$vkey = $key."-".$vkey;
					if (!$features || in_array($vkey,$features_a)) {
						$result = ($vvalue) ? $vkey : 'no-'.$vkey;
						print $result.' ';
					}
				}
			} else {
				if (!$features || in_array($key,$features_a)) {
					$result = ($value) ? $key : 'no-'.$key;
					print $result.' ';
				}
			}
		}
		if ($printUAProps) {
			$uaProps = array("os","osFull","browserFull","device","deviceFull");
			foreach($uaProps as $uaProp) {
				print str_replace(" ","-", strtolower($obj->$uaProp)).' ';
			}
	private static function readDirFiles($dir) {
		if ($handle = opendir(__DIR__ .'/'. $dir)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != "." && $entry != ".." && $entry != "README") {
		            readfile(__DIR__ .'/'. $dir . $entry);
		        }
		    }
		    closedir($handle);
		}
	}
	
	/**
	* Adds a JavaScript object to the page so features collected on the server can be used client-side
	* @param  {Object}        the user agent features
	* @param  {String}        list of browser features to include in the css, bad idea to leave features blank...
	* Parses the cookie for a list of features
	* @param  {String}        file path
	* @param  {Object}        the object to be modified/added too
	* @param  {Boolean}       if this is the main cookie ingest of info
	*
	* @return {Object}        values from the cookie for that cookieExtension
	*/
	public static function createJavaScriptObj($obj,$features = null) {
			print "<script type=\"text/javascript\">";
			print "Detector=new Object();";
			if ($features) {
				$features_a = explode(",",$features);
				array_walk($features_a,create_function('&$val', '$val = trim($val);'));
			}
			foreach($obj as $key=>$value) {
				if (is_object($value)) {
					$i = 0;
					foreach ($value as $vkey => $vvalue) {
						if (!$features || in_array($key."-".$vkey,$features_a)) {
							if ($i == 0) {
								print "Detector.".$key."=new Object();\n";
								$i++;
							}
							$vkey = str_replace("-","",$vkey);
							if ($vvalue) {
								print "Detector.".$key.".".$vkey."=true;\n";
	private static function parseCookie($cookieExtension,$obj,$default = false) {
		$cookieName = $default ? self::$cookieID : self::$cookieID."-".$cookieExtension;
		if (isset($_COOKIE[$cookieName])) {
			$uaFeatures = self::_ang($_COOKIE[$cookieName]);
			foreach($uaFeatures as $key => $value) {
				if ((strpos($key,$cookieExtension."-") !== false) || (($cookieExtension == 'core') && (strpos($key,"extended-") === false) && (strpos($key,"pr-") === false))) {
					$key = str_replace($cookieExtension."-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								print "Detector.".$key.".".$vkey."=false;\n";
								$value->$vkey = $vvalue;
							}
							
						}
					}
				} else {
					if (!$features || in_array($key,$features_a)) {
						$key = str_replace("-","",$key);
						if ($value === true) {
							print "Detector.".$key."=true;\n";
						} else if ($value == false) {
							print "Detector.".$key."=false;\n";
						} else {
							print "Detector.".$key."='".$value."';\n";
						}
						$obj->$key = $value;
					} else {
						$obj->$key = ($value == 1) ? true : false;
					}
				}
			}
			print "</script>";
			return $obj;
		}
	}
	
	/**
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	* @param  {Object}        the core template object
	*
	* @return {Object}        the core template object "filled out" from ua-parser-php
	*/
	private static function createUAProperties($obj) {
		
		// classify the user agent string so we can learn more what device this really is. more for readability than anything
		$userAgent = UA::parse();
		
		// save properties from ua-parser-php
		foreach ($userAgent as $key => $value) {
			$obj->$key = $value;
		}
		
		return $obj;
	}
	
}

$ua = Detector::build();

?>