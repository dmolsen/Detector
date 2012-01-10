<?php 
	require("../../lib/Detector.php");
	header("content-type: application/x-javascript");
	Detector::perrequest();
?>