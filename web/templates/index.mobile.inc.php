<? include("templates/_convertTF.inc.php"); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Detector - browser- &amp; feature-detection combined for your app</title>
    <meta name="description" content="">
    <meta name="author" content="">
	<meta name="viewport" content="width=device-width">

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

	  .container, .content, .span10, .span9, .span4, footer {
		width: 98%;
	  }
	
	  .span7 {
		width: 220px;
	  }
	
      .container > footer p {
        text-align: center; /* center align it with the container */
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 10px;
        margin: 0; /* negative indent the amount of the padding to maintain the grid system */
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
        margin: -10px -10px 10px;
      }
    </style>

	<!-- My Scripts -->
	<script type="text/javascript" src="js/features.js.php"></script>
	<script type="text/javascript" src="js/modernizr.pre21.js"></script>
  </head>

  <body>

    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1>Detector</h1>
        </div>
        <div class="row">
          <div class="span10">
						<? include("templates/_about.inc.php"); ?>
						<? include("templates/_browserprofile.inc.php"); ?>
						<? include("templates/_featureprofile.inc.php"); ?>
          </div>
          <div class="span4">
						<? include("templates/_moreinfo.inc.php"); ?>
            			<? include("templates/_credits.inc.php"); ?>
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