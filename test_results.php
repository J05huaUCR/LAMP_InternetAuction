<?php

$trace = $_GET['trace'];
$region = $_GET['region'];
$domain = $_GET['domain'];
$url = $_GET['url'];
$ip_address = $_GET['ip'];
$referer = $_GET['ref'];
$keywords = $_GET['keywords'];

if (isset($_GET['js']) ) {  // no cookie, but js value passed in
	$js_enabled = $_GET['js']; // assign
} else if ( isset($_COOKIE['directclick_js_enabled']) ) { // if cookie present....
	$js_enabled = $_COOKIE['directclick_js_enabled']; // ... set to cookie
} else if (!isset($_GET['js']) && $_GET['try'] != 1) { // no cookie, and not coming from main page
	header('Location: js_cookietest.php'); //perform check to determine whether the cookie expire OR it really was disabled.
} else  {
	$js_enabled = false; // no love.
}

if ( isset($_COOKIE['directclick_cookie_enabled']) ) { // if cookie present....
	$cookies_enabled = $_COOKIE['directclick_cookie_enabled']; // ... set to cookie
} else if (!isset($_COOKIE['directclick_cookie_enabled']) && $_GET['try'] != 1) { // no cookie, and not coming from main page
	header('Location: js_cookietest.php'); //perform check to determine whether the cookie expire OR it really was disabled.
} else if (isset($_GET['cookies']) ) {  // cookie, but no js value passed in
	$cookies_enabled = $_GET['cookies']; // assign
} else {
	$cookies_enabled = false; // no love.
}

if ($js_enabled == true) echo 'JAVASCRIPT is enabled.<br />'; else echo 'JAVASCRIPT is disabled.<br />';
if ($cookies_enabled == true) echo 'COOKIES enabled.<br />'; else echo 'COOKIES disabled.<br />';
?>
