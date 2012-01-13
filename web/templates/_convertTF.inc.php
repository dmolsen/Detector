<?php
	function convertTF($value) {
		if ($value) {
			return "<span class='label success'>true</span>";
		} else {
			return "<span class='label important'>false</span>";
		}
	}
?>