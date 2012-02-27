<?php

require("UAParser.php");

$result = UA::parse();

print "<html><head><meta name=\"viewport\" content=\"width=device-width\"><title>ua-parser-php Example</title></head><body><h1>ua-parser-php Test</h1>";
print "<p><a href='https://github.com/dmolsen/ua-parser-php'>ua-parser-php</a> is a pseudo-port of <a href='http://code.google.com/p/ua-parser/'>ua-parser</a>. Please use this page to test your browser. ";
print "<strong>NOTE:</strong> <a href='https://github.com/dmolsen/ua-parser-php'>ua-parser-php</a> does attempt to classify tablets via the user-agent string but it can be spotty. Especially with Android tablets running Android OSs less than 3.0.0.";
print "<a href='https://github.com/dmolsen/ua-parser-php'>ua-parser-php</a> was developed to help support my <a href='http://detector.dmolsen.com/'>Detector project</a>.</p>";

if ($result) {
	print "<pre>";
	foreach($result as $key => $value) {
		print $key.": ".$value."<br />";
	}
	print "</pre>";
} else {
	print "Sorry, ua-parser-php was unable to match your user agent which was: ";
	print $_SERVER["HTTP_USER_AGENT"];
}

print "<h2>Problem?</h2>";
print "<p>If you notice any incorrect information email me at dmolsen+uaparser@gmail.com. Please include the <em>uaOriginal</em> field as well as what you think the browser should be classified as in your message.</p>";
print "</body></html>";

?>