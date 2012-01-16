// should be modified to return an object... with different possible color depths set to true/false?
Modernizr.addTest("pr-screenAttributes",function() { 
	var _windowHeight = (window.innerHeight > 0) ? window.innerHeight : screen.width;
	var _windowWidth  = (window.innerWidth > 0) ? window.innerWidth : screen.width;
	var _colorDepth   = screen.colorDepth;
	
	return { windowHeight: _windowHeight, windowWidth: _windowWidth, colorDepth: _colorDepth };
});