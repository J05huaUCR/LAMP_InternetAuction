<?php

require("ping/config.php");
require("ping/functions.php");

$trace = $_GET['trace'];
session_start(); // start session
$session_id = session_id(); // get new id
$start = fGetMicroTime();
$date = date("Ymd");

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());
fCheckTable($date, $MYSQL_CONNECTION); // db

// GET ENV Vars
if ( $_GET['try'] == "1" ) { // been here already

	// JS test result
	if (isset($_GET['js']) ) { 
		$js_enabled = $_GET['js'];  
	} else if (isset($_COOKIE['directclick_js_enabled']) ) {
		$js_enabled = $_COOKIE['directclick_js_enabled'];
	} else {
		$js_enabled = false;
	}
	// Cookie test
	if (isset($_COOKIE['directclick_cookie_enabled']) ) {
		$cookies_enabled = 'true'; // cookie test
	} else {
		$cookies_enabled = 'false'; // cookie test
	}

	// Pull Passed in VARS
	$region = $_GET['region'];
	$domain = $_GET['domain'];
	$url = $_GET['url'];
	$ip_address = $_GET['ip'];
	$referer = $_GET['referer'];
	$keywords = $_GET['keywords'];
	$agent = $_GET['agent'];
	$botflag = $_GET['botflag'];
	$blackflag = $_GET['blackflag'];
	
	if ($trace) echo "Time after Getting Vars: " . fMarkTime($start) . "<br />";
	
} else { // virgin session
	
	// if sent in
	$keywords = $_GET['keywords']; // KEYWRODS
	$botflag = $_GET['botflag'];
	$blackflag = $_GET['blackflag'];
	
	// DOMAIN & URL
	if ( !empty($_GET['domain']) || !empty($_GET['domainname']) ) { // Domain passed in
		// Multiple domain check
		if (isset($_GET['domain']) ) {
			$domain = $_GET['domain'];
		} else if (isset($_GET['domainname']) ) {
			$domain = $_GET['domainname'];
		} 
		// clean domain
		$domain = str_replace("http://","",$domain); 
		$domain = str_replace("www.","",$domain); 
	} else { // no domain
		$domain = fDomainLookup($MYSQL_CONNECTION);
		//$domain = $DEFAULT_DOMAIN;
	}
	
	// Test and assign
	( isset($_GET['uri'])     ) ? $url = $_GET['uri'] 			 : $url = "http://www.".$domain ."/"; // URL
	( isset($_GET['ip']) 	  ) ? $ip_address = $_GET['ip']   : $ip_address = $_SERVER['REMOTE_ADDR']; // IP ADDRESS
	( isset($_GET['referrer'])) ?	$referer = $_GET['referrer']: $referer = $_SERVER['HTTP_REFERER']; // REFERER
	( isset($_GET['agent'])   ) ? $agent = $_GET['agent'] 	 : $agent = $_SERVER['HTTP_USER_AGENT']; // AGENT
	
	// REGION
	if(isset($_GET['region'])) { 
		$region = $_GET['region'];	
	} else {
		$region = fIPlookup($ip_address, $MYSQL_CONNECTION); // db
		if(!$region || $region == -1) $region = "ZZ"; // no region return for IP
	}

	// BOT CHECK
	if ( strpos($agent, 'bot') || strpos($agent, 'spider')  ) {
		$botflag = 1;
		//mysql_close($MYSQL_CONNECTION);
		//exit(); // this is a bot, cancel
	} 
	
	// IP BLACKLIST CHECK
	if (fIPcheck($ip_address, $MYSQL_CONNECTION, $trace) ) {
		$redirect = $DEFAULT_REDIRECT . $domain;	
		if ($trace) {
			echo "REDIRECT: ", $redirect, "<br />";
			mysql_close($MYSQL_CONNECTION);
			//exit(); 
		} else { 
			$blackflag = 1;
			//fRedirect($redirect); // DONE
		}
	}
	
	// CLOSE MYSQL CONNECTION BEFORE REDIRECT
	//mysql_close($MYSQL_CONNECTION);
	
	// BUILD RETURN PATH FOR RELOAD
	$RETURN_PATH = "";
	$RETURN_PATH .= $SITE_PATH . "?";
	$RETURN_PATH .= "domain=" . $domain; // NEED AT LEAST DOMAIN 
	if (isset($url)) $RETURN_PATH .= "&url=". urlencode($url); // FULL URL
	if (isset($trace)) $RETURN_PATH .= "&trace=". $trace; // TRACE FLAG
	if (isset($region)) $RETURN_PATH .= "&region=". $region; // REGION
	if (isset($ip_address)) $RETURN_PATH .= "&ip=". $ip_address; // IP ADDRESS
	if (isset($referer)) $RETURN_PATH .= "&referer=". urlencode($referer); // REFERER
	if (isset($keywords)) $RETURN_PATH .= "&keywords=". urlencode($keywords); // KEYWORDS
	if (isset($agent)) $RETURN_PATH .= "&agent=". urlencode($agent); // USER AGENT
	if (isset($botflag)) $RETURN_PATH .= "&botflag=". $botflag; // USER AGENT
	if (isset($blackflag)) $RETURN_PATH .= "&blackflag=". urlencode($blackflag); // USER AGENT
	
	// Cookie /JavaScript Check
	if (isset($_COOKIE['directclick_cookie_enabled']) && isset($_COOKIE['directclick_js_enabled'])) { // both cookies are sent
		$js_enabled = $_COOKIE['directclick_js_enabled']; // ... set to cookie
		$cookies_enabled = $_COOKIE['directclick_cookie_enabled']; // ... set to cookie
	} else { // one or both cookies not set, proceed to test
		$expire = time()+60*60*24*30;
		setcookie("directclick_cookie_enabled", "true", $expire ); 
		if ($_GET['try'] != "1") { // haven't been here yet
		
			setcookie("directclick_cookie_enabled", "true", $expire ); // attempt to set cookie via php

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
				window.location.href = "<?=$RETURN_PATH?>&js=true&try=1"; <!-- javascript enabled, reload using js -->
			}
			createCookie('directclick_js_enabled', 'true', 1);
			</script>
			
			<noscript><!-- javascript disabled, reload using refresh -->
				<meta http-equiv="refresh" content="0;url=<?=$RETURN_PATH?>&js=false&try=1">
			</noscript>
			<?
		} else { // already tried, did we get lucky on anything?
			( isset($_COOKIE['directclick_js_enabled']) ) ? $js_enabled = 'true' :  $js_enabled = 'false'; // JavaScript check
			( isset($_COOKIE['directclick_cookie_enabled']) ) ? $cookies_enabled = 'true': $cookies_enabled = 'false'; // Cookie check
		}	
	}
}

$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

// DOMAIN & URL
if ( !empty($_GET['domain']) || !empty($_GET['domainname']) ) { // Domain passed in
	// Multiple domain check
	if (isset($_GET['domain']) ) {
		$domain = $_GET['domain'];
	} else if (isset($_GET['domainname']) ) {
		$domain = $_GET['domainname'];
	} 
	// clean domain
	$domain = str_replace("http://","",$domain); 
	$domain = str_replace("www.","",$domain); 
	$dp = 1;
} else { // no domain
	$domain_keys = fDomainLookup($MYSQL_CONNECTION, $trace);
	$domain = $domain_keys['domain'];
	$keywords = urlencode($domain_keys['keywords']);
	$dp = 0;
}
if (empty($url) || $url ='' || !strpos($url, 'http://') ) $url = urlencode("http://" . $domain . "/");
if ($keywords == '' || !isset($keywords) || empty($keywords) ) $keywords = fKeywordLookup($domain, $MYSQL_CONNECTION, $trace); 
$keywords = urlencode($keywords);

// OUTPUT
if ($trace) {
	echo "DATE: ".$date ."<br />";
	echo "TIME: ".$start ."<br />";
	echo "SID: ".$session_id ."<br />";
	echo "DOMAIN: " . $domain . "<br />";
	echo "URL: " . $url . "<br />";
	echo "TRACE: " . $trace . "<br />";
	echo "LOCALE: " . $region . "<br />";
	echo "IP: " . $ip_address . "<br />";
	echo "REFERER: " . $referer . "<br />";
	echo "KEYWORDS: " . urlencode($keywords) . "<br />";
	echo "AGENT: " . $agent . "<br />";
	echo "JAVASCRIPT: " . $js_enabled . "<br />";
	echo "COOKIES: " . $cookies_enabled . "<br />";
	echo "BOT: " . $botflag . "<br />";
	echo "BLACKLISTED: " . $blackflag . "<br />";
	echo "DOMAIN PASSED IN: " . $dp . "<br />";
	if ($trace) echo "Time after reload: " . fMarkTime($start) . "<br />";
}


//==========================================================================================
// BEGIN MULTI-PROGRAMMING SECTION
//==========================================================================================

$fp = array();
if (1) {  

	for ($i = 0; $i < sizeof($FEEDS); ++$i) { // build feed URL to send to multi-processes
		$which = $PING_PATH. '?feed=' . $FEEDS[$i];
		$which .= '&date=' . $date; 
		$which .= '&time='. $start; 
		$which .= '&referer=' . urlencode($referer); 
		$which .= '&domain=' . $domain; 
		$which .= '&url=' . urlencode($url);
		$which .= '&session_id='. $session_id;
		$which .= '&agent=' . urlencode($agent); 
		$which .= '&region=' . $region; 
		$which .= "&ip=" .$ip_address;
		$which .= '&keywords=' . urlencode($keywords); 
		$which .= '&trace=' . $trace; 
		$which .= '&cookies=' . $cookies_enabled; 
		$which .= '&js=' . $js_enabled;
		$which .= '&botflag=' . $botflag;
		$which .= '&blackflag=' . $blackflag;
		$which .= '&dp=' . $dp;
		$fp[] = fJobStartAsync('www.'.$SITE_DOMAIN, $which);	
	}
	if ($trace) echo "Time to enter Loop: " . fMarkTime($start) . "<br />";
	$start_loop = fGetMicroTime();
	while (fMarkTime($start_loop) < $MAX_TIME) {
		if ($trace) sleep(1); // lag for output
		
		$feed_count = sizeof($FEEDS);
		$feed_status = array();
		
		for ($i = 0; $i < $feed_count; ++$i) {
			$feed_status[$i] = fJobPollAsync($fp[$i]);
			if ($feed_status[$i] === false) { 
				--$feed_count; 
			} else {
				if ($trace) echo ($i + 1), ") <b>$FEEDS[$i]</b> = $feed_status[$i]<br />";
			}
		}
		flush(); @ob_flush();
		unset($feed_status);
		if ($feed_count < 1) break;
	}
	if ($trace) echo "End Loop Time: " . fMarkTime($start) . "<br />";
	$redirect = fWhoWon($start, $date, $MYSQL_CONNECTION);
	if ($redirect == -1) $redirect = $DEFAULT_REDIRECT . $domain;	
	$end = fGetMicroTime();
	
	mysql_close($MYSQL_CONNECTION);
	
	if ($trace) {
		echo "REDIRECT:", $redirect, "<br />";
		echo "<h3>Jobs Complete</h3>";
		echo "Done at $end.<br />";
		echo "&delta; " . ($end - $start) . "secs<br />";/**/
	}
	if (!$trace) fRedirect($redirect); // DONE
}

?>