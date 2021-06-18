<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DirectClick 2.0 | <?=$view?></title>
<link rel="stylesheet" href="<?=$TEMPLATE_STYLES?>" type="text/css" media="screen" />

<!--[if IE 6]>
<style>
body {behavior: url("csshover3.htc");}
#menu li .drop {background:url("img/drop.gif") no-repeat right 8px; 
</style>
<![endif]-->
<script>

function fDisable() {
	// Get selected view
	var selected = document.getElementById("whichReport");
	var whichView = selected.options[selected.selectedIndex].value;

	// Get and reset all drop downs
	whichRange = document.getElementById("whichRange");
	whichDays = document.getElementById("whichDays");
	whichPlatform = document.getElementById("whichPlatform");
	whichFeed = document.getElementById("whichFeed");
	numResults = document.getElementById("numResults");
	
	whichRange.disabled=false;
	whichDays.disabled=false;
	whichPlatform.disabled=false;
	whichFeed.disabled=false;
	numResults.disabled=false;
	
	switch(whichView) {
		case "overview":
   		numResults.disabled=true;
		break;	
		
		case "revenue":
   		whichRange.disabled=true;
			whichDays.disabled=true;
			whichPlatform.disabled=true;
			whichFeed.disabled=true;
			numResults.disabled=true;
		break;
		
		case "bids":
   		whichRange.disabled=true;
   		whichFeed.disabled=true;
		break;	
		
		case "keywords":
			whichRange.disabled=true;
   		whichFeed.disabled=true;
		break;
		
		case "domains":
   		whichRange.disabled=true;
   		whichFeed.disabled=true;
		break;	
		
		case "redirects":
   		whichFeed.disabled=true;
		break;	
		
		case "trending":
   		whichFeed.disabled=true;
		break;	
		
		default:
			whichRange.disabled=false;
			whichDays.disabled=false;
			whichPlatform.disabled=false;
			whichFeed.disabled=false;
			numResults.disabled=false;
		break;
	}
	
}
// retrieving info from mySQL
function showStats() {
	
	var text_height = 0;
	var text_offset = 206;
	var height = window.innerHeight;
	var padding = (height / 2) - (text_height /2);
	document.getElementById('loading').style.visibility='visible';
	document.getElementById('loading').style.height=text_height;
	document.getElementById('loading').style.padding=(padding-text_offset) + 'px 0 ' + (padding+text_offset) + 'px 0';
	document.getElementById('content').style.top='-'+(height - text_height) + 'px';

	/* Report Selection */
	var report = document.getElementById("whichReport");
	var whichReport = report.options[report.selectedIndex].value;
	
	/* Day(s) Selection */	
	var days = document.getElementById("whichDays");
	var whichDays = days.options[days.selectedIndex].value;
	
	/* Selection of per day summary or range summary */
	var range = document.getElementById("whichRange");
	var days_flag = range.options[range.selectedIndex].value;
	
	/* Selection of platform */
	var platforms = document.getElementById("whichPlatform");
	var platform = platforms.options[platforms.selectedIndex].value;
	
	/* Selection of Feed */
	var feeds = document.getElementById("whichFeed");
	var feed = feeds.options[feeds.selectedIndex].value;
	
	/* Selection of Region */
	var numResults = document.getElementById("numResults");
	var qty = numResults.options[numResults.selectedIndex].value;
	
	
	var url = "reports_output.php";
	var params = "view="+whichReport+"&days="+whichDays+"&flag="+days_flag+"&platform="+platform+"&feed="+feed+"&qty="+qty;
	
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttpStats=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xmlhttpStats=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	xmlhttpStats.onreadystatechange=function() {
		if (xmlhttpStats.readyState==4 && xmlhttpStats.status==200) {
			document.getElementById("content").innerHTML=xmlhttpStats.responseText;
			document.getElementById('loading').style.visibility='hidden'
			document.getElementById('loading').style.padding='0'
			document.getElementById('loading').style.height='0'
			document.getElementById('content').style.top='0px'
		}
	}
	xmlhttpStats.open("POST",url,true);
	//Send the proper header information along with the request
	xmlhttpStats.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttpStats.setRequestHeader("Content-length", params.length);
	xmlhttpStats.setRequestHeader("Connection", "close");
	
	xmlhttpStats.send(params);
}
</script>

</head>

<?
if ($view == 'engine') {
	?>
<frameset rows="200,100%">
	<frame src="header<?=$DEV?>.php" />
	<frame src="test_engine.php" />
</frameset><noframes></noframes>
	<?
} else {
	?>
	<body <?=$default?>>
		<div class="container">
			<div class="content_wrap" id="content_wrap">
	<?
}

