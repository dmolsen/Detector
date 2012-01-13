<?php include("templates/_header.inc.php"); ?>

<div class="span10">
	<p>
		With the initial release of <a href="http://yiibu.com/">Yiibu's</a> <a href="https://github.com/yiibu/profile">Profile</a>, <a href="https://github.com/dmolsen/Detector">Detector</a> is already <abbr title="Yet Another Browser- and Feature-Detection Library">YABFDL</abbr>
		<em>(Yet Another Browser- and Feature-Detection Library)</em>. The concepts behind Detector have been floating around in my head since I heard Yiibu's talk, 
		<a href="http://www.slideshare.net/yiibu/adaptation-why-responsive-design-actually-begins-on-the-server">Adaptation</a>. I've finally put my ideas into <a href="https://github.com/dmolsen/Detector">code</a> and have created this demo.
		To learn more about <a href="https://github.com/dmolsen/Detector">Detector</a> and how it works please check out the <a href="https://github.com/dmolsen/Detector">README on GitHub</a>. 
	</p>
	<?php include("templates/_browserprofile.inc.php"); ?>
	<?php include("templates/_featureprofile.inc.php"); ?>
</div>

<div class="span4">
	<?php include("templates/_about.inc.php"); ?>
	<?php include("templates/_moreinfo.inc.php"); ?>
	<?php include("templates/_credits.inc.php"); ?>
	<?php include("templates/_archive.inc.php"); ?>
</div>

<?php include("templates/_footer.inc.php"); ?>