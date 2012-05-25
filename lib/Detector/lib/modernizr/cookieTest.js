function getCookie() {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
  	x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  	y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  	x=x.replace(/^\s+|\s+$/g,"");
  	if (x == "testCookie") {
    	return unescape(y);
    }
  }
}

function checkCookieSupport() {
	document.cookie = "testCookie=testData";
	if (getCookie() != "testData") {
		window.location = (window.location.href.match(/\?/)) ? window.location.href + "&nocookies=true" : window.location.href + "?nocookies=true";
	}
}

