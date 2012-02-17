# Detector v0.5 #

Detector is a simple, PHP- and JavaScript-based browser- and feature-detection library that can adapt to new devices & browsers on its own without the need to pull from a central database of browser information.
	
Detector dynamically creates profiles using a browser's _(mainly)_ unique user-agent string as a key. Using [Modernizr](http://modernizr.com/) it records the HTML5 & CSS3 features a requesting browser may or may not support. [ua-parser-php](https://github.com/dmolsen/ua-parser-php) is used to collect and record any useful information _(like OS or device name)_ the user-agent string may contain. 

With Detector a developer can serve the appropriate markup, stylesheets, and JavaScript to a requesting browser without being completely dependent on a front-end-only resource loader nor a browser-detection library being up-to-date.

The server-side portion of Detector is based upon [modernizr-server](https://github.com/jamesgpearce/modernizr-server) by James Pearce ([@jamespearce](http://twitter.com/#!/jamespearce)) and the browser-detection library [ua-parser-php](https://github.com/dmolsen/ua-parser-php). Detector utilizes [Modernizr](http://www.modernizr.com/) for its client-side, feature-detection support.

## Demo of Detector ##

A very [simple demo of Detector](http://detector.dmolsen.com/) is available for testing.

## Features ##

The following features are as of v0.5 of Detector:

* stores features detected with [Modernizr 2.5.2](http://www.modernizr.com/) ([list](http://detector.dmolsen.com/demo/modernizr-listing/)) and browser & device information detected with [ua-parser-php](https://github.com/dmolsen/ua-parser-php) (based on [ua-parser](http://code.google.com/p/ua-parser/)) on the server as part of a browser profile for easy retrieval
* uses the user agent string as a unique key for looking up information (e.g. one profile per user agent)
* tests are run only once per unique user agent string so only one user is ever tested & redirected
* [add your own feature tests](https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial) and store the results using Modernizr's addTest() API
* version your browser profiles so you can force them to be recreated after adding new tests
* [easily organize browsers into families](https://github.com/dmolsen/Detector/wiki/Detector-Family-Tutorial) based on a mix of supported features & device information
* browsers & bots that don't support JavaScript can still use your site
* use with a templating system like Mustache to [create a RESS system](https://github.com/dmolsen/Detector/wiki/Templating-with-Detector-&-Mustache-Tutorial).

## More Information ##

* [How Detector Works](https://github.com/dmolsen/Detector/wiki/How-Detector-Works)
* [Adding & Using Detector With Your Application](https://github.com/dmolsen/Detector/wiki/Adding-&-Using-Detector-With-Your-Application)
* [Detector Test Tutorial](https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial)
* [Detector Family Tutorial](https://github.com/dmolsen/Detector/wiki/Detector-Family-Tutorial)
* [Templating with Detector & Mustache Tutorial](https://github.com/dmolsen/Detector/wiki/Templating-with-Detector-&-Mustache-Tutorial)
* [Why I Created Detector](http://www.dmolsen.com/mobile-in-higher-ed/2012/01/18/introducing-detector-combining-browser-feature-detection-for-your-web-app/)

## Credits ##

First and foremost, thanks to James Pearce ([@jamespearce](http://twitter.com/jamespearce)) for putting together [modernizr-server](https://github.com/jamesgpearce/modernizr-server) and giving me a great base to work from. I also took some of the copy from his README and used it in the section, "Adding Detector to Your Application."  Also, thanks to the guys behind [Modernizr](http://www.modernizr.com/) for giving developers a great lib as well as the the ability to expand Modernizr via `Modernizr.addTest()`. Finally, thanks to Bryan Rieger ([@bryanrieger](http://twitter.com/bryanrieger)) & Stephanie Rieger ([@stephanierieger](http://twitter.com/stephanierieger)) of Yiibu and Luke Wroblewski ([@lukew](http://twitter.com/lukew)) for providing inspiration via [Profile](https://github.com/yiibu/profile) and [RESS](http://www.lukew.com/ff/entry.asp?1392) respectively.