<?php
	function createFT($ua,$match,$title,$note = '') {
		print "<table class=\"zebra-striped span9\">
			<thead>
				<tr>
					<th>".$title."</th>
					<th>Your Browser</th>
					<th>Detector Profile</th>
				</tr>
			</thead>
			<tbody>";
		foreach($ua as $key => $value) {
			if (preg_match($match,$key)) {
				if (is_object($value)) {
					foreach ($value as $vkey => $vvalue) {
						print "<tr>";
						print "<th class=\"span7\">".$key."->".$vkey.":</th>";
						if (Detector::$foundIn == "archive") {
							print "<td class=\"span1\"><span class='label'>N/A</span></td>";
						} else {
							print "<td class=\"span1\"><script type=\"text/javascript\">if (Modernizr['".$key."']['".$vkey."']) { document.write(\"<span class='label success'>true</span>\"); } else { document.write(\"<span class='label important'>false</span>\"); }</script></td>";
						}
						print "<td class=\"span1\">".convertTF($vvalue)."</td>";
						print "</tr>";
					}
				} else {
					print "<tr>";	
					print "<th class=\"span7\">".$key.":</th>";
					if (Detector::$foundIn == "archive") {
						print "<td class=\"span1\"><span class='label'>N/A</span></td>";
					} else if (!preg_match("/(desktop|mobile|tablet|colordepth|json|overflowscrolling|emoji|hirescapable)/",$key)) {
						print "<td class=\"span1\"><script type=\"text/javascript\">if (Modernizr['".$key."']) { document.write(\"<span class='label success'>true</span>\"); } else { document.write(\"<span class='label important'>false</span>\"); }</script></td>";
					} else {
						print "<td class=\"span1\"><span class='label'>N/A</span></td>";
					}
					print "<td class=\"span1\">".convertTF($value)."</td>";
					print "</tr>";
				}
			}
		}
		print "</tbody>";
		print "</table>";
		if ($note != '') {
			print "<div class=\"featureNote span9\">";
			print "<small><em>".$note."</em></small>";
			print "</div>";
		}
	}
?>