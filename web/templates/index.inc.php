<?php
	function convertTF($value) {
		if ($value) {
			print "<span class='label success'>true</span>";
		} else {
			print "<span class='label important'>false</span>";
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Detector - browser- &amp; feature-detection combined for your app</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      /* Override some defaults */
      html, body {
        background-color: #eee;
      }
      .container > footer p {
        text-align: center; /* center align it with the container */
      }
      .container {
        width: 820px; /* downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. */
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
        -webkit-border-radius: 0 0 6px 6px;
           -moz-border-radius: 0 0 6px 6px;
                border-radius: 0 0 6px 6px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

      /* Page header tweaks */
      .page-header {
        background-color: #f5f5f5;
        padding: 20px 20px 10px;
        margin: -20px -20px 20px;
      }

      /* Styles you shouldn't keep as they are for displaying this base example only */
      .content .span10,
      .content .span4 {
        min-height: 500px;
      }
      /* Give a quick and non-cross-browser friendly divider */
      .content .span4 {
        margin-left: 0;
        padding-left: 19px;
        border-left: 1px solid #eee;
      }

      .topbar .btn {
        border: 0;
      }

    </style>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">

	<!-- My Scripts -->
	<script type="text/javascript" src="js/features.js.php"></script>
	<script type="text/javascript" src="js/modernizr.pre21.js"></script>
  </head>

  <body>

    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1>Detector <small>browser- &amp; feature-detection combined for your app</small></h1>
        </div>
        <div class="row">
          <div class="span10">
            <!--<h2>Introduction</h2>-->
						<p>
							With the initial release of <a href="http://yiibu.com/">Yiibu's</a> <a href="https://github.com/yiibu/profile">Profile</a>, <a href="https://github.com/dmolsen/Detector">Detector</a> is already <abbr title="Yet Another Browser- and Feature-Detection Library">YABFDL</abbr>
							<em>(Yet Another Browser- and Feature-Detection Library)</em>. The concepts behind Detector have been floating around in my head since I heard Yiibu's talk, 
							<a href="http://www.slideshare.net/yiibu/adaptation-why-responsive-design-actually-begins-on-the-server">Adaptation</a>. I've finally put my ideas into <a href="https://github.com/dmolsen/Detector">code</a> and have created this demo.
							To learn more about <a href="https://github.com/dmolsen/Detector">Detector</a> and how it works please check out the <a href="https://github.com/dmolsen/Detector">README on GitHub</a>. 
						</p>
						<h3>Detector's Browser Profile for You</h3>
						<p>
							The following browser profile was created using the browser-detection of Detector. It, as well as the the following feature profile, were <strong>
							<?php
								if (Detector::$foundIn == 'cookie') {
									print " created when you first hit this page because Detector didn't recognize your user-agent. You may have experienced a very brief redirect when loading the page initially. The profiles have now been saved for use with other visitors.";
								} else if (Detector::$foundIn == 'file') {
									print " created in the past when another user with the same user agent visited this demo. Detector simply pulled the already existing information for your visit.";
								} else {
									print " pulled from session because you've visited this page before.";
								}
							?></strong>
						</p>
						<table class="zebra-striped span9">
							<thead>
								<tr>
									<th colspan="2">Browser Properties</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="span3">User Agent:</td>
									<td><?=$ua->ua?></td>
								</tr>
								<tr>
									<td class="span3">Gen. <? if ($ua->isMobile) { ?>OS<? } else { ?> Grouping<? } ?>:</td>
									<td><?=$ua->deviceOSGeneral?></td>
								</tr>
								<? if ($ua->isMobile) { ?>
									<tr>
										<td>Specific OS:</td>
										<td><?=$ua->deviceOSSpecific?></td>
									</tr>
								<? } ?>
								<? if ($ua->majorVersion != 0) { ?>
									<tr>
										<td>Major Version:</td>
										<td><?=$ua->majorVersion?></td>
									</tr>
									<tr>
										<td>Minor Version:</td>
										<td><?=$ua->minorVersion?></td>
									</tr>
								<? } ?>
								<tr>
									<td>Is Mobile?</td>
									<td><?=convertTF($ua->isMobile)?></td>
								</tr>
								<tr>
									<td>Is Tablet?</td>
									<td><?=convertTF($ua->isTablet)?></td>
								</tr>
								<tr>
									<td>Is Computer?</td>
									<td><?=convertTF($ua->isComputer)?></td>
								</tr>
								<tr>
									<td>Is Spider?</td>
									<td><?=convertTF($ua->isSpider)?></td>
								</tr>
							</tbody>
						</table>
						<h3>Detector's Feature Profile for You</h3>
						<p>
							The following feature profile was primarily created using <a href="http://www.modernizr.com/docs/#s2">Modernizr's core tests</a>. In addition to the core tests
							I've added an extended test that checks for emoji support as well as a per request test to check the device pixel ratio. Both were added using the <a href="http://www.modernizr.com/docs/#addtest">Modernizr.addTest() Plugin API</a>.
							To learn more about core, extended, and per request tests please <a href="https://github.com/dmolsen/Detector">review the README</a>.  To access any of these options in your PHP app you'd simple type <code>$ua->featureName</code>.
						</p>
						<table class="zebra-striped span9">
							<thead>
								<tr>
									<th>Features</th>
									<th>Client</th>
									<th>Cloud</th>
								</tr>
							</thead>
							<tbody>
							<script type="text/javascript">console.log(Modernizr)</script>
								<?php
									foreach($ua as $key => $value) {
										if (!preg_match("/(ua|uaHash|deviceOSGeneral|deviceOSSpecific|majorVersion|minorVersion|isMobile|isTablet|isComputer|isSpider)/",$key)) {
											if (is_object($value)) {
												foreach ($value as $vkey => $vvalue) { ?>
													<tr>
														<th class="span7"><?=$key?>-<?=$vkey?>:</th>
														<td class="span1"><script type="text/javascript">if (Modernizr['<?=$key?>']['<?=$vkey?>']) { document.write("<span class='label success'>true</span>"); } else { document.write("<span class='label important'>false</span>"); }</script></td>
														<td class="span1"><?=convertTF($vvalue)?></td>
													</tr>
												<?php }
												$jsonTemplateCore->$key = $value;
											} else { ?>
												<tr>
													<th class="span7"><?=$key?>:</th>
													<? if (!preg_match("/(desktop|mobile|tablet|colordepth|json|overflowscrolling|emoji|hirescapable)/",$key)) { ?>
														<td class="span1"><script type="text/javascript">if (Modernizr['<?=$key?>']) { document.write("<span class='label success'>true</span>"); } else { document.write("<span class='label important'>false</span>"); }</script></td>
													<? } else { ?>
														<td class="span1"><span class='label'>N/A</span></td>
													<? } ?>
													<td class="span1"><?=convertTF($value)?></td>
												</tr>
											<?php }
										}
									}
								?>
							</tbody>
						</table>
						<p>
							Please note, the only reason why a full slate of Modernizr tests is always done with this demo is for the feature profile comparison. You can include as much or as little of Modernizr as you want on your site. You can even leave it out entirely.
						</p>
          </div>
          <div class="span4">
						<h3>About Detector</h3>
						<p>
							<a href="https://github.com/dmolsen/Detector">Detector</a> is a simple, PHP- and JavaScript-based browser- and feature-detection library. Detector gives
							server-side developers information on what types of devices may be requesting their content as well as the HTML5 &amp; CSS3 features a requesting browser may or may not support. With Detector a developer 
							can serve the appropriate markup, stylesheets, and JavaScript to a requesting browser without being completely dependent on front-end-only 
							script loaders. <a href="https://github.com/dmolsen/Detector">Check out the README</a> for more information.
						</p>
						<h3>More Information</h3>
						<ul>
							<li> <a href="https://github.com/dmolsen/Detector">Detector on GitHub</a></li>
							<li> <a href="http://twitter.com/dmolsen/">@dmolsen on Twitter</a></li>
						</ul>
            <h3>Credits</h3>
						<p>
							Detector is based on <a href="http://www.modernizr.com/">Modernizr</a>, <a href="https://github.com/jamesgpearce/modernizr-server">modernizr-server</a>, and the browser-detection library from <a href="https://github.com/dmolsen/MIT-Mobile-Web">Mobile Web OSP</a>.
							It also benefits from a healthy dose of inspiration from <a href="http://yiibu.com/">Yiibu's</a> <a href="https://github.com/yiibu/profile">Profile</a>. 
						</p>
          </div>
        </div>
      </div>

      <footer>
        <p>&copy; <a href="http://dmolsen.com/">Dave Olsen</a> 2012 | Made with <a href="http://twitter.github.com/bootstrap/">Bootstrap</a></p>
      </footer>

    </div> <!-- /container -->

  </body>
</html>