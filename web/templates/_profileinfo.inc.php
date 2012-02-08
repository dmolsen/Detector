<p>
Both of the following Detector profiles were <strong>
<?php
	if (Detector::$foundIn == "archive") {
		print " pulled from a profile already in the system that you asked to view. Because it's an archived profile the browser-side tests were not run.";
	} else if (Detector::$foundIn == 'cookie') {
		print " created when you first hit this page because Detector didn't recognize your user-agent. You may have experienced a very brief redirect when loading the page initially. The profiles have now been saved for use with other visitors.";
	} else if (Detector::$foundIn == 'file') {
		print " created in the past when another user with the same user-agent visited this demo. Detector simply pulled the already existing information for your visit.";
	} else if (Detector::$foundIn == 'nojs') {
		print " created when you first hit this page because Detector didn't recognize your user-agent. Because your browser didn't support JavaScript your profiles are very limited. The profiles have now been saved for use with other visitors.";
	} else {
		print " pulled from session because you've visited this page before.";
	}
?></strong>
</p>