// JavaScript Document
function fAdClick(keys,num_ads,div) {
	
	// Set Cookie
	var keywords = escape(keys);
	var domain = document.domain;
	var name = 'directclick_js_enabled';
	var value = 1;
	var days = 1;
	if (days) { // number of days
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*24*30));
		var expires = "; expires="+date.toGMTString();
	} else {
		var expires = "";
	}
	document.cookie = name+"="+value+expires+"; path=/";
	
	//alert ('keywords='+keywords +', num_ads='+num_ads+', div='+div);
	

	// Initiate AJAX
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttpSession=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttpSession=new ActiveXObject("Microsoft.XMLHTTP");
	}

	// Wait for response
	xmlhttpSession.onreadystatechange=function() {
		if (xmlhttpSession.readyState==4 && xmlhttpSession.status==200) {
			document.getElementById(div).innerHTML=xmlhttpSession.responseText;
		}
	}
	//var url = "http://www.toptrafficsource.com/dc/ac_dev.php?domain="+domain+"&keywords="+keywords+"&num_ads="+num_ads+"&js=1";
	var url = "http://www.toptrafficsource.com/dc/test_ajax_mysql_helper.php?days=0";
	//alert('url='+url);
	xmlhttpSession.open("GET",url,true);
	xmlhttpSession.send();
}