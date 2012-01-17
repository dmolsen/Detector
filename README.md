# Detector v0.2 #

Detector is a simple, PHP- and JavaScript-based browser- and feature-detection library. Detector gives
server-side developers information about what types of devices may be requesting their content as well as the HTML5 & CSS3 features a requesting browser may or may not support. With Detector a developer 
can serve the appropriate markup, stylesheets, and JavaScript to a requesting browser without being completely dependent on a browser-detection library being up-to-date nor completely dependent on a front-end-only resource loader.
The server-side portion of Detector is based upon [modernizr-server](https://github.com/jamesgpearce/modernizr-server) by James Pearce ([@jamespearce](http://twitter.com/#!/jamespearce)) and 
the browser-detection library from [Mobile Web OSP](https://github.com/dmolsen/MIT-Mobile-Web). Detector utilizes [Modernizr](http://www.modernizr.com/) 
for its client-side, feature-detection support.

## Demo of Detector ##

A very [simple demo of Detector](http://detector.dmolsen.com/) is available for testing.

## How Detector Works ##

Detector's implementation is fairly simple. Detector is based upon the notion that a browser's user agent string can _(sort of)_ be seen as a fingerprint.
Essentially, if you've had one visit from a browser with a particular user agent string and recorded its features then their is a good chance that future visits from browsers with that user agent
string will share the same features. With that introduction here is how it works:

1. Detector checks to see if the user requesting information has visited the site before by checking for an open session. If there is an open session Detector relies on that session for the list of features for that browser.
2. If a session is not already open Detector compares the user agent string with a list of user agent strings that have already visited the site. If it matches one of those user agent strings Detector users that user agent's list of features for the ones that the new visitor should have.
3. If Detector doesn't find a match in the list of existing user agent strings it will: send a full suite of Modernizr tests to the new browser, record the results of those tests in a cookie, reload the page, and save those results to the server. Those results are then available to the developer.

All in all it's pretty painless.

## Adding Detector to Your Application ##

Detector has not been thoroughly tested yet so I wouldn't include it in anything production nor anything near production. 
If you do want to try it out you should:

1. Copy the `Detector` directory found in the `lib` directory into your project.
2. Make sure the Detector directories `user-agents-core` & `user-agents-extended` are writable by your web server.
3. Include `<?php require('path/to/Detector/Detector.php');>` at the very start of your script.
4. Copy `features.js.php` _(found in `js-include`)_ to a public directory and reference it in the `<head>` of your HTML.

In order to access the browser features you can use the `$ua` object in the
same way that you would have used the `Modernizr` object on the client:

    if ($ua->svg) {
        ...
    } elseif ($ua->canvas) {
        ...
    }
        
See the [Modernizr documentation](www.modernizr.com/docs/) for all of the features
that are tested and available with Detector.
        
Some features, (in particular `video`, `audio`, `input`, and `inputtypes`)
have sub-features, so these are available as nested PHP objects:
 
    if ($ua->inputtypes->search) {
        print "<input type='search' ...";
    } else {
        print "<input type='text' ...";
    }
    
All features are returned as integer `1` or `0` for `true` or
`false`, so they can be used in logical evaluations in PHP. Sub-features can return `1`, `0`, or a real value (e.g. screen width).

## Adding Your Own Modernizr Tests ##

Modernizr-based tests for Detector are broken down into three types: `Core`, `Extended`, and `Per Request`. Modifying Core tests should be avoided. Obviously at v0.2 it's not a huge deal
but going forward I hope those tests can be firmed up into a standard.

### Extended Tests ###

Extended tests are tests that, when sending out the full suite of Modernizr tests, get run along-side Core tests but are instead saved to their own user-agent profile. They're meant
to provide developers with a way to add their own Core-like tests but in a way that allows Core to be a standard. To add your own Extended tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `tests/extended/`. The names of the tests should start with `extended-` so that their values get put
into the appropriate `user-agent` file. The string `extended-` is stripped from the test name when placing it in session.

### Per Request Tests ###

Per Request tests are tests that get run on every request the browser sends. I'm not sure they'll operate this way in the future. They were designed to
capture features that change on a per device basis versus a per browser basis. An example of this would be device pixel ratio. To add your own Per Request tests 
simply follow the [Modernizr.addTest() format](http://www.modernizr.com/docs/#addtest) and put them in `tests/perrequest/`. The names of the tests should start with `pr-` so that their values
are not added to any of the `user-agent` files. The string `pr-` is stripped from the test name when placing it in session.

## Future Plans ##

At some point I would like to see the following features implemented with this project:

* sampling so that features can be checked continuously
* versioning of the core tests
* an API format so that Detector can be a standalone repository for users
* a separate repository for the core user agent files so that user agent data can be shared with others. it would also open up the possibility for pull requests so many people could share their information.

That last point is very important to me and one of the real benefits of a project like Detector.

## Credits ##

First and foremost thanks to James Pearce for putting together [modernizr-server](https://github.com/jamesgpearce/modernizr-server) and giving me a great base to work from.
I also took some of the copy from his README and used it in the section, "Adding Detector to Your Application." 
Also, thanks to the guys behind [Modernizr](http://www.modernizr.com/) (Faruk Ateş, Paul Irish, & Alan Sexton) for giving developers the ability to expand Modernizr via `Modernizr.addTest()`. 
