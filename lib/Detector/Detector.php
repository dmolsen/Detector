<?php

/*!
 * Detector v0.1
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

// include the ua-parser-php library to rip apart user agent strings
require(__DIR__."/lib/ua-parser-php/UAParser.php");

// include the browserFamily library to classify the browser by features
require(__DIR__."/lib/feature-family/featureFamily.php");

class Detector {
	
	private static $debug               = false;
	
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
	*     - a session has already been opened for the request browser, if so send the info back, else
	*     - see if the cookie has been set so we can build the profile, if so build the profile & send the info back, else
	*     - see if detector can find an already created profile for the browser, if so send the info back, else
	*     - start the process for building a profile for this unknown browser
	*
	* Based heavily on modernizr-server
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
		
		// populate some standard variables based on the user agent string
		self::$ua                   = $_SERVER["HTTP_USER_AGENT"];
		self::$accept               = $_SERVER["HTTP_ACCEPT"];
		self::$uaHash               = md5(self::$ua);
		self::$sessionID            = md5(self::$ua."-session-".$coreVersion."-".$extendedVersion);
		self::$cookieID             = md5(self::$ua."-cookie-".$coreVersion."-".$extendedVersion);
		
		$uaFileCore                 = __DIR__."/".self::$uaDirCore."ua.".self::$uaHash.".json";
		$uaFileExtended             = __DIR__."/".self::$uaDirExtended."ua.".self::$uaHash.".json";
		
		$uaTemplateCore             = __DIR__."/".self::$uaDirCore."ua.template.json";
		$uaTemplateExtended         = __DIR__."/".self::$uaDirExtended."ua.template.json";
		
		$pid                        = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
		
		// offer the ability to review profiles saved in the system
		if (preg_match("/[a-z0-9]{32}/",$pid) && self::$debug) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "archive";
			
			// decode the core data
			$uaJSONCore     = @file_get_contents(__DIR__."/".self::$uaDirCore."ua.".$pid.".json");
			$uaJSONCore     = json_decode($uaJSONCore);
			
			// find and decode the extended data
			$uaJSONExtended = @file_get_contents(__DIR__."/".self::$uaDirExtended."ua.".$pid.".json");
			$uaJSONExtended = json_decode($uaJSONExtended);
			
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
			
			// grab features out of the cookie that are being checked on every request. update the session as appropriate
			if (isset($_COOKIE[self::$cookieID."-pr"])) {
				$uaFeatures = self::_ang($_COOKIE[self::$cookieID."-pr"]);
				$cookiePerRequest = new stdClass();
				foreach($uaFeatures as $key => $value) {
					$key = str_replace("pr-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								$value->$vkey = $vvalue;
							}
						}
						$cookiePerRequest->$key = $value;
					} else {
						$cookiePerRequest->$key = ($value == 1) ? true : false;
					}
				}
			}
			
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
			
			// open the JSON template core file that will be populated & start populating its object
			if ($uaJSONTemplateCore = @file_get_contents($uaTemplateCore)) {
				$jsonTemplateCore = json_decode($uaJSONTemplateCore);
			} 
			
			// use ua-parser-php to set-up the basic properties for this UA
			$jsonTemplateCore = self::createUAProperties($jsonTemplateCore);
			
			// note the current core format version
			$jsonTemplateCore->coreVersion = $coreVersion;
			
			// open the JSON template extended file that will be populated & start populating its object
			if ($uaJSONTemplateExtended = @file_get_contents($uaTemplateExtended)) {
				$jsonTemplateExtended = json_decode($uaJSONTemplateExtended);
			}
			
			$jsonTemplateExtended->ua              = self::$ua;
			$jsonTemplateExtended->uaHash          = self::$uaHash;
			$jsonTemplateExtended->extendedVersion = $extendedVersion;
			
			// create an object to hold any of the per request data. it shouldn't be saved to file but it should be added to the session
			$cookiePerRequest = new stdClass();
			
			// grab the features from the cookie and create an object with them (self::_ang)
			$uaFeatures = self::_ang($_COOKIE[self::$cookieID]);
			
			// push features into the same level as the general device information
			// change 1/0 to true/false. why? 'cause that's what i like to read ;)
			// this section is all sorts of redundant but i don't feel like DRYing it right now
			foreach($uaFeatures as $key => $value) {
				$pos1 = strpos($key,"extended-");
				$pos2 = strpos($key,"pr-");
				if ($pos1 !== false) {
					$key = str_replace("extended-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								$value->$vkey = $vvalue;
							}
						}
						$jsonTemplateExtended->$key = $value;
					} else {
						$jsonTemplateExtended->$key = ($value == 1) ? true : false;
					}
				} else if ($pos2 !== false) {
					$key = str_replace("pr-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								$value->$vkey = $vvalue;
							}
						}
						$cookiePerRequest->$key = $value;
					} else {
						$cookiePerRequest->$key = ($value == 1) ? true : false;
					}
				} else {
					$key = str_replace("core-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								$value->$vkey = $vvalue;
							}
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
			
			// use the uaFeatures to classify the feature family for this browser
			$mergedInfo->family           = featureFamily::find($mergedInfo);
			$jsonTemplateExtended->family = $mergedInfo->family;

			// write out to disk for future requests that might have the same UA
			$jsonTemplateCore = json_encode($jsonTemplateCore);
			$fp = fopen($uaFileCore, "w");
			fwrite($fp, $jsonTemplateCore);
			fclose($fp);
			
			// write out to disk for future requests that might have the same UA
			$jsonTemplateExtended = json_encode($jsonTemplateExtended);
			$fp = fopen($uaFileExtended, "w");
			fwrite($fp, $jsonTemplateExtended);
			fclose($fp);
			
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
		
		} else if (isset($_REQUEST["nojs"]) && ($_REQUEST["nojs"] == "true")) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "nojs";
			
			// classify the user agent string so we can learn more what device this really is. more for readability than anything
			self::classifyUA();
			
			// open the JSON template core file that will be populated
			if ($uaJSONTemplateCore = @file_get_contents($uaTemplateCore)) {
				$jsonTemplateCore = json_decode($uaJSONTemplateCore);
			} 
			
			// use ua-parser-php to set-up the basic properties for this UA
			$jsonTemplateCore = self::createUAProperties($jsonTemplateCore);
			
			$mergedInfo = $jsonTemplateCore;
			
			// write out to disk for future requests that might have the same UA
			$jsonTemplateCore = json_encode($jsonTemplateCore);
			$fp = fopen($uaFileCore, "w");
			fwrite($fp, $jsonTemplateCore);
			fclose($fp);
			
			// add our collected data to the session for use in future requests, also add the per request data
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// add the user agent & hash to a list of already saved user agents
			self::addToUAList();
			
			// return the collected data to the script for use in this go around
			return $mergedInfo;
			
		} else if (($uaJSONCore = @file_get_contents($uaFileCore)) && ($uaJSONExtended = @file_get_contents($uaFileExtended))) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "file";
			
			// decode the core data
			$uaJSONCore     = json_decode($uaJSONCore);
			
			// decode the extended data
			$uaJSONExtended = json_decode($uaJSONExtended);
			
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
		if ($handle = opendir(__DIR__ .'/'. self::$uaFeaturesCore)) {
		    while (false !== ($entry = readdir($handle))) {
		        if ($entry != "." && $entry != ".." && $entry != "README") {
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
		print self::_mer() . "</script></head><body><noscript>This version of the page you requested requires JavaScript. Please <a href=\"".$noscriptLink."\">view a version optimized for your browser</a>.</noscript></body></html>";
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
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	* @param  {Object}        the core template object
	*
	* @return {Object}        the core template object "filled out" from ua-parser-php
	*/
	private static function createUAProperties($obj) {
		
		// include the basic properties of the UA
		$obj->ua            = self::$ua;
		$obj->uaHash        = self::$uaHash;
		
		// classify the user agent string so we can learn more what device this really is. more for readability than anything
		$userAgent = UA::parse();
		
		// save properties from ua-parser-php
		foreach ($userAgent as $key => $value) {
			$obj->$key = $value;
		}
		
		return $obj;
	}
	
							}
}

$ua = Detector::build();

?>