// handle older versions of IE with onload
if (window.addEventListener) { 
	window.addEventListener("load",cm,false);
} else if (window.attachEvent ) { 
	window.attachEvent("onload",cm);
} else if (window.onLoad) {
	window.onload = cm;
}