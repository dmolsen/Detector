<?php

// require Detector so we can popular identify the browser & populate $ua
require("../lib/Detector/Detector.php"); 

if ($_POST['post']) {
	$message = "Here is some feedback for Detector:

Email addy: 
".$_POST['email']."

Their message:
".$_POST['message'];

	mail('dmolsen@gmail.com', 'Detector Feedback', $message);
}

include("templates/contact.inc.php");

?>
