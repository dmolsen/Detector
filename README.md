# Detector v0.1 #

Detector is a simple PHP-based library that offers both feature- and browser-detection giving
server-side developers information on what type of device may be requesting their content as well as the HTML5 & CSS3 features a requesting browser may or may not support. With Detector a developer 
can serve the appropriate mark-up, styles, and JavaScript for a browser without being completely dependent on front-end-only 
script loaders. Detector is based on [modernizr-server](https://github.com/jamesgpearce/modernizr-server) by James Pearce ([@jamespearce](http://twitter.com/#!/jamespearce)) and, obviously, utilizes [Modernizr](http://www.modernizr.com/) 
for its feature-detection support. For browser-detection Detector uses a library from [Mobile Web OSP](https://github.com/dmolsen/MIT-Mobile-Web).

## Demo ##

A very [simple demo of Detector](http://detector.dmolsen.com/) is available for testing.

## How It Works ##

Detector's implementation is fairly simple. Detector is based on the notion that a browser's user agent string can _(sort of)_ be seen as a fingerprint.
Essentially, if you've had one visit from a browser with a particular user agent string, and recorded its features, then their is a good likelihood that future visits from browsers with that user agent
string will share the same features. With that introduction here is how it works:

1. Detector checks to see if the user requesting information has visited the site before by checking for an open session. If there is an open session Detector relies on that session for the list of features for that browser.
2. If a session is not already open Detector compares the user agent string with a list of user agent strings that have already visited the site. If it matches one of those user agent strings Detector users that user agent's list of features for the ones that the new visitor should have.
3. If Detector doesn't find a match in the list of existing user agent strings it will: send a full suite of Modernizr tests to the new browser, record the results of those tests in a cookie, reload the page, and save those results to the server. Those results are then available to the developer.

All in all it's pretty painless.

## Adding Detector to Your Application ##

Detector has not been thoroughly tested yet so I wouldn't include it in anything production or anywhere near production. If you do want to try it out you should:

1. Copy the `Detector` directory found in the `lib` directory to your project.
2. Make sure the directories `user-agents-core` & `user-agents-extended` are writable by your web server.
3. Copy `features.js.php` _(found in `js-include`)_ to a public directory and reference it in the `<head>` of your application.

## Adding Your Own Tests ##

Tests for Detector are broken down into three types: `Core`, `Extended`, and `Per Request`. Modifying Core tests should be avoided. Obviously at v0.1 it's not a huge deal
but going forward I hope those can be firmed up into a standard.

### Extended Tests ###

Extended tests are meant to be those tests that, when sending out the full suite of Modernizr tests, get run along-side core. To add your own Extended tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `modernizr/extended/`. The names of the tests should start with `extended-` so that their values get put
into the appropriate `user-agent` file.

### Per Request Tests ###

Per Request tests are ones that get run on every request the browser sends. I'm not sure they'll operate this way in the future. They're supposed
to capture information for tests like DPI since that kind of feature changes on a per device basis versus a per browser basis. To add your own Per Request tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `modernizr/perrequest/`. The names of the tests should start with `pr-` so that their values
are not added to any of the `user-agent` files.

## Future Plans ##

At some point I would like to see the following implemented with this project:

* sampling so that features can be checked continuously
* versioning of the core tests
* a separate repository for the core user agent files so that user agent data can be shared with others. it would also open up the possibility for pull requests so many people could share their information.

That last point is very important to me and one of the real benefits of a project like Detector.
