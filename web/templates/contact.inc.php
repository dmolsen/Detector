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

	  thead > tr > th { font-size: 14px; }

    </style>

	<!-- My Scripts -->
	<script type="text/javascript" src="js/features.js.php"></script>
	<script type="text/javascript" src="js/modernizr.pre21.js"></script>
  </head>

  <body>

    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1><a href="/" style="color: black;">Detector</a> <small>combined browser- &amp; feature-detection for your app</small></h1>
        </div>
        <div class="row">
          <div class="span10">
			<?php
			 	if ($_POST['post']) {
					include("templates/_contactty.inc.php");
				} else {
					include("templates/_contactform.inc.php");
				}
			?>
          </div>
          <div class="span4">
				<? include("templates/_about.inc.php"); ?>
				<? include("templates/_moreinfo.inc.php"); ?>
          			<? include("templates/_credits.inc.php"); ?>
				<? include("templates/_archive.inc.php"); ?>
          </div>
        </div>
      </div>

      <footer>
        <p>&copy; <a href="http://dmolsen.com/">Dave Olsen</a> 2012 | Design based on <a href="http://twitter.github.com/bootstrap/">Bootstrap</a></p>
      </footer>

    </div> <!-- /container -->
	<? include("templates/_gauges.inc.php"); ?>
  </body>
</html>