<?php require("../lib/Detector/Detector.php"); ?>

<html>
	<head>
		<script src="js/features.js.php"></script>
	</head>
	<body>
		<pre>
			<?php print_r($ua); ?>
		</pre>	
		
		<?php
			if (!$ua->touch) { echo("touch NOT supported"); }
		?>
		<?php echo("touch: ".$ua->touch); ?>
		<?php echo("touch: ".Detector::$foundIn); ?>
	</body>
</html>

