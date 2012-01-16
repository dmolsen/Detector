Modernizr.addTest("core-tablet",function() {
	if (Modernizr.mq('only screen and (max-width: 600px)')) {
		return true;
	} else if (Modernizr.mq('only screen and (min-width: 601px) and (max-width: 801px)')) {
		return true;
	} else {
		return false;
	}
});