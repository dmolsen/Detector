<?php 

// require Detector so we can popular identify the browser & populate $ua
require("../lib/Detector/Detector.php"); 

// switch templates based on device type
if ($ua->isMobile) {
	include("templates/index.mobile.inc.php");
} else {
	include("templates/index.default.inc.php");
}

?>

