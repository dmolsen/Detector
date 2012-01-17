<h3>Archive</h3>
<p>
	The following user agent profiles are already in the system:
</p>
<ul>
	<?php
		if ($uaListJSON = @file_get_contents(__DIR__."/../../lib/Detector/user-agents-core/ua.list.json")) {
			$uaList = json_decode($uaListJSON);
			foreach ($uaList as $key => $value) {
				print "<li> <a href=\"/?pid=".$key."\">".trim(substr($value, 0, 28))."...</a></li>";
			}
		}
	?>
</ul>
