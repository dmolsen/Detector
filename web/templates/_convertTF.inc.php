<?php
	function convertTF($value) {
		if ($value) {
			print "<span class='label success'>true</span>";
		} else {
			print "<span class='label important'>false</span>";
		}
	}
?>