# Detector v0.1 #

Detector is a simple PHP-based library that combines both feature- and browser-detection to help 
server-side developers know what type of HTML5 & CSS3 features a requesting browser may or may not support. With Detector a developer 
can serve appropriate mark-up, styles, and JavaScript without being completely dependent on front-end-only 
script loaders. Detector is based on [modernizr-server](https://github.com/jamesgpearce/modernizr-server) by James Pearce ([@jamespearce](http://twitter.com/#!/jamespearce)) and, obviously, utilizes [Modernizr](http://www.modernizr.com/) 
for its feature detection support. It also uses the browser detection guts of [Mobile Web OSP](https://github.com/dmolsen/MIT-Mobile-Web).

## How It Works ##

Detector is fairly simple and is based on the notion that a browser's user agent string can _(sort of)_ be seen as a fingerprint. Basically,
if you've seen one browser with that user agent string you've seen them all. If the first browser you saw with that user agent string has
certain attributes then all future browsers should, theoretically, have those same attributes. With that introduction here is how it works:

1. Detector checks to see if the user requesting information has visited before by checking for an open session. If so it relies on that already created session for the list of features.
2. If a session is not already open Detector compares the user agent string with a list of user agent strings that have already visited the site. If it matches one Detector users that for the list of features that user should have.
3. If Detector doesn't find a match it will send a full suite of Modernizr tests to the browser, record the results of those tests in a cookie, reload the page, and save those results to the server making those features available to the developer.

All in all it's pretty painless.

## Adding Detector to Your Application ##

stuff here with code example...

## Adding Your Own Tests ##

Tests for Detector are broken down into three types: `Core`, `Extended`, and `Per Request`. Modifying `Core` tests should be avoided. Obviously at v0.1 it's not a huge deal
but going forward I hope those can be firmed up into a standard.

### Extended Tests ###

`Extended` tests are meant to be those tests that, when sending out the full suite of Modernizr tests, get run along-side core. To add your own `extended` tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `lib/modernizr/extended`. Test names should start with `extended-` so that they get put
into the appropriate `user-agent` file.

### Per Request Tests ###

`Per Request` tests are ones that get run on every request the browser sends. I'm not sure they'll operate this way in the future. They're supposed
to capture information for tests like DPI since that kind of feature changes on a per device basis versus per browser basis. To add your own `per request` tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `lib/modernizr/perrequest`. Test names should start with `pr-` so that they're
not added to any of the `user-agent` files.

## Future Plans ##

## Currently Available Tests ##

The following are currently tested and tracked by Detector via Modernizr. Because Detector is using Modernizr these tests can always be expanded upon.