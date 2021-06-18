<?php 

/*
[ ] GET ENV's, check for passed in
[ ] test agent for bot, exit if necessary
[ ] test ip for black list, exit if necessary
[ ] test for js enabled and cookies, redirect if necessary, passing ENV's as needed
*/

// Required libraries
require("ping/config.php");
require("ping/functions.php");

// start
$start = fGetMicroTime();

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

// GET ENV Vars
if (isset($_GET['sid'])) { // have session id, been here before
	$sid = $_GET['sid'];
	session_id($sid); // retrieve session
	session_start(); // start
	$js_enabled = $_GET['js']; 
	$trace = $_SESSION['trace'];
	$region = $_SESSION['region'];
	$domain = $_SESSION['domain'];
	$url = $_SESSION['url'];
	$ip_address = $_SESSION['ip'];
	$referer = $_SESSION['ref'];
	$keywords = $_SESSION['keywords'];
	$agent = $_SESSION['agent'];
} else { // virgin session
	session_start(); // start session
	$sid = session_id(); // get new id
	
	// check if sent in from testing
	if ( isset($_GET['domain']) ) {
		$trace = $_GET['trace'];
		$region = $_GET['region'];
		if (isset($_GET['domain']) ) {
			$domain = str_replace("www.","",$_GET['domain']); 
		} else if (isset($_GET['domainname']) ) {
			$domain = str_replace("www.","",$_GET['domainname']); 
		} else {
			$domain = "nsphere.com";	
		}
		$url = urlencode($_GET['url']);
		$ip_address = $_GET['ip'];
		$referer = urlencode($_GET['ref']);
		$keywords = urlencode($_GET['keywords']);
		$agent = $_SERVER['HTTP_USER_AGENT'];
		// bot check
		if ( strpos($agent, 'bot') ) {
			mysql_close($MYSQL_CONNECTION);
			exit(); // this is a bot, cncel
		}
	}
}

// Test and assign
if (!$url) $url = urlencode($domain); else urlencode($url);
if(!isset($referer)) $referer = $_SERVER['HTTP_REFERER'];
if(!isset($region)) $region = fIPlookup($ip_address, $MYSQL_CONNECTION); // db
if(!$region || $region == -1) $region = "ZZ"; // no region return for IP
if(!isset($ip_address)) $ip_address = $_SERVER['REMOTE_ADDR'];

// ip check
if (fIPcheck($ip_address, $MYSQL_CONNECTION, $trace) || strpos($agent, 'spinn3r.com/robot')) {
	$redirect = $DEFAULT_REDIRECT . $domain;	
	if ($trace) {
		echo "REDIRECT: ", $redirect, "<br />";
		mysql_close($MYSQL_CONNECTION);
		exit(); 
	} else { 
		fRedirect($redirect); // DONE
	}
}

// set session vars
$_SESSION['trace'] = $trace;
$_SESSION['region'] = $region;
$_SESSION['domain'] = $domain;
$_SESSION['ip'] = $ip_address;
$_SESSION['ref'] = $referer;
$_SESSION['keywords'] = $keywords;
$_SESSION['agent'] = $agent;

// Cookie /JavaScript Check
if (isset($_COOKIE['directclick_cookie_enabled']) && isset($_COOKIE['directclick_js_enabled'])) { // both cookies are sent
	echo "HAVE COOKIES...<br />";
	$js_enabled = $_COOKIE['directclick_js_enabled']; // ... set to cookie
	$cookies_enabled = $_COOKIE['directclick_cookie_enabled']; // ... set to cookie
} else { // one or both cookies not set, proceed to test
	$expire = time()+60*60*24*30;
	setcookie("directclick_cookie_enabled", "true", $expire ); 
	if (!isset($_GET['sid'])) { // haven't been here yet
		// now we've been here
		$_SESSION['try'] = 1;
	
		// attempt to set cookie via php
		setcookie("directclick_cookie_enabled", "true", $expire ); 
		
		// attempt to use javascript and then reload
		?>
		<script language='javascript'>
		function createCookie(name,value,days) {
			if (days) {
				var date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*24*30));
				var expires = "; expires="+date.toGMTString();
			} else {
				var expires = "";
			}
			
			document.cookie = name+"="+value+expires+"; path=/";
			window.location.href = "js_cookietest.php?sid=<?=$sid?>&js=true"; <!-- javascript enabled, reload using js -->
		}
		createCookie('directclick_js_enabled', 'true', 1);
		</script>
		
		<noscript><!-- javascript disabled, reload using refresh -->
			<meta http-equiv="refresh" content="0;url=js_cookietest.php?sid=<?=$sid?>&js=false">
		</noscript>
		<?
	} else { // already tried, did we get lucky on anything?
		//( isset($_SESSION['js']) ) ? $js_enabled = $_SESSION['js'] :  $js_enabled = 'false'; // JavaScript check
		( isset($_COOKIE['directclick_cookie_enabled']) ) ? $cookies_enabled = 'true': $cookies_enabled = 'false'; // Cookie check
	}	
}

// OUTPUT

echo "SID: ".$sid ."<br />";
echo "DOMAIN: " . $domain . "<br />";
echo "TRACE: " . $trace . "<br />";
echo "REGION: " . $region . "<br />";
echo "IP: " . $ip_address . "<br />";
echo "REFERER: " . $referer . "<br />";
echo "KEYWORDS: " . $keywords . "<br />";
echo "AGENT: " . $agent . "<br />";
if ($js_enabled == 'true') echo 'JAVASCRIPT is enabled.<br />'; else echo 'JAVASCRIPT is disabled.<br />';
if ($cookies_enabled == 'true') echo 'COOKIES enabled.<br />'; else echo 'COOKIES disabled.<br />';

echo "TIME: " . fMarkTime($start) . "<br />";
mysql_close($MYSQL_CONNECTION);

?>
