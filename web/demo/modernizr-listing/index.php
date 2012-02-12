<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Modernizr Listing</title>
    <meta name="description" content="">
    <meta name="author" content="">
	<script type="text/javascript" src="/js/modernizr.2.5.2.min.custom.js"></script>
  </head>

  <body>

	<h1>Listing of Modernizr Properties</h1>
	<script type="text/javascript">	
	 var m=Modernizr;
	 for(var f in m){
	    if(f[0]=='_'){continue;}
	    var t=typeof m[f];
	    if(t=='function'){continue;}
	    	document.write(f+':');
	    	if(t=='object'){
	      		for(var s in m[f]){
					if (typeof m[f][s]=='boolean') { document.write('/'+s+':'+(m[f][s]?1:0)+'<br />'); }
	        		else { document.write('/'+s+':'+m[f][s]+'<br />'); }
	      		}
	    	}else{
	      		c=m[f]?'1':'0';
				document.write(c+'<br />');
	    	}
	  	}
	</script>
  </body>
</html>