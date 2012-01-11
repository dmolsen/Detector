<?php 
	require("../../lib/Detector/Detector.php");
	header("content-type: application/x-javascript");
	Detector::perrequest();
?>