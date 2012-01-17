<h3>Archive</h3>
<p>
	The following user agent profiles are already in the system (<a href="archive.php">readable list</a>):
</p>
<ul>
	<?php
		if ($uaListJSON = @file_get_contents(__DIR__."/../../lib/Detector/user-agents-core/ua.list.json")) {
			$uaList = (array) json_decode($uaListJSON);
			asort($uaList);
			foreach ($uaList as $key => $value) {
				print "<li> <a href=\"/?pid=".$key."\">".trim(substr($value, 0, 28))."...</a></li>";
			}
		}
	?>
</ul>
