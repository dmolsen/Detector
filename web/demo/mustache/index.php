<?php

// require mustache for the templates
require_once "lib/mustache-php/Mustache.php";
require_once "lib/mustache-php/MustacheLoader.php";

// require detector to get the family, autoloads the $ua var
require_once "../../../lib/Detector/Detector.php"; 

$template = file_get_contents("templates/index.mustache");
$data = array(
	'title'       => 'Hello, World!',
	'description' => 'This extremely simple demo is meant to show how Detector and Mustache can be combined to create a Responsive Web Design + Server Side Component (RESS) System.',
	'link'        => 'https://github.com/dmolsen/Detector/wiki/Templating-with-Detector-&-Mustache-Tutorial',
	'images'      => array(
		               array('index'=>'1','title'=>'Automobile','alt'=>'auto','img'=>'images/automobile.jpg','img_sml'=>'images/automobile_sml.jpg','src'=>'http://farm4.staticflickr.com/3347/3411775886_fcf0af1a42_z.jpg'),
		               array('index'=>'2','title'=>'Bus','alt'=>'bus','img'=>'images/bus.jpg','img_sml'=>'images/bus_sml.jpg','src'=>'http://www.flickr.com/photos/d0a98042/3774873571/sizes/z/in/photostream/'),
		               array('index'=>'3','title'=>'Train','alt'=>'train','img'=>'images/train.jpg','img_sml'=>'images/train_sml.jpg','src'=>'http://www.flickr.com/photos/30827349@N02/3965800996/sizes/z/in/photostream/'),
	),
);

$m = new Mustache();
$partials = new MustacheLoader("templates/partials/".$ua->family,"mustache","templates/partials/base");

print $m->render($template, $data, $partials);

?>