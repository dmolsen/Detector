<h3>Archive</h3>
<p>
	The following user agent profiles are already in the system:
</p>
<ul>
<?php
	if ($handle = opendir(__DIR__ .'/../../lib/Detector/user-agents-core/')) {
	    while (false !== ($entry = readdir($handle))) {
	        if ($entry != "." && $entry != ".." && $entry != "README" && $entry != "ua.template.json" && $entry != ".gitignore") {
	            $uaJSONCore = file_get_contents(__DIR__."/../../lib/Detector/user-agents-core/".$entry);
				$uaJSONCore = json_decode($uaJSONCore);
				print "<li> <a href=\"/?pid=".$uaJSONCore->uaHash."\">".trim(substr($uaJSONCore->ua, 0, 28))."...</a></li>";
	        }
	    }
	    closedir($handle);
	}
?>
</ul>
