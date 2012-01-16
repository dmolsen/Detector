Modernizr.addTest("core-mobile", function() {
	if (Modernizr.mq('only screen and (max-width: 320px)')) {
		return true;
	} else if (Modernizr.mq('only screen and (min-width: 321px) and (max-width: 480px)')) {
		return true;
	} else {
		return false;
	}
});