<?php

require("ping/config_dev.php");
require("ping/functions.php");

session_start(); // start session
$start = fGetMicroTime();

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

$date = date("Ymd");
fCheckTables($date, $MYSQL_CONNECTION); // db

// GET ENV Vars
if ( $_GET['try'] == "1" ) { // been here already
	$session_id = $_GET['id']; // get new id
	

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

	/* Pull Passed in VARS 
	$trace = $_GET['trace'];
	$agent = $_GET['agent'];
	$region = $_GET['region'];
	$ip_address = $_GET['ip'];
	$url = $_GET['url'];
	$domain = $_GET['domain'];
	$referer = $_GET['referer'];
	$keywords = $_GET['keywords'];
	$botflag = $_GET['bot'];
	$blackflag = $_GET['blackflag'];
	$adultflag = $_GET['adult'];*/
	
	/* Get Session Vars */
	$trace = $_SESSION['trace'];
	$session_id = $_SESSION['id'];
	$agent = $_SESSION['agent'];
	$region = $_SESSION['region'];
	$ip_address = $_SESSION['ip'];
	$url = $_SESSION['url'];
	$domain = $_SESSION['domain'];
	$referer = $_SESSION['referer'];
	$keywords = $_SESSION['keywords'];
	$botflag = $_SESSION['bot'];
	$blackflag = $_SESSION['blackflag'];
	$adultflag = $_SESSION['adult'];
	
	if ($trace) echo "Time after Getting Vars: " . fMarkTime($start) . "<br />";
	
} else { // virgin session
	$session_id = session_id(); // get new id

	$blackflag = $_GET['blackflag'];
	$adultflag = $_GET['adult'];
	
	// DOMAIN & URL
	if ( !empty($_GET['domain']) || !empty($_GET['domainname']) ) { // Domain passed in
		(isset($_GET['domain']) ) ? $domain = $_GET['domain'] : $domain = $_GET['domainname'];// Multiple domain check
		$domain = str_replace("http://","",$domain); // clean domain
		$domain = str_replace("www.","",$domain); 
		$dp = 1;
	} else { // no domain
		$domain_keys = fDomainLookup($MYSQL_CONNECTION, $trace);
		$domain = $domain_keys['domain'];
		$keywords = $domain_keys['keywords'];
		$dp = 0;
	}
	
	// Test and assign
	( isset($_GET['trace'])    ) ? $trace = $_GET['trace'] 	  		: $trace = 0; // TRACE FLAG
	( isset($_GET['keywords']) ) ? $keywords= $_GET['keywords']		: $keywords = fKeywordLookup($domain, $MYSQL_CONNECTION, $trace); // KEYWORDS
	( isset($_GET['url'])      ) ? $url = $_GET['url'] 		  		: $url = "http://www.".$domain ."/"; // URL
	( isset($_GET['ip']) 	   ) ? $ip_address = $_GET['ip']   		: $ip_address = $_SERVER['REMOTE_ADDR']; // IP ADDRESS
	( isset($_GET['referer'])  ) ? $referer = $_GET['referer']		: $referer = $_SERVER['HTTP_REFERER']; // REFERER
	( isset($_GET['agent'])    ) ? $agent = $_GET['agent'] 	  		: $agent = $_SERVER['HTTP_USER_AGENT']; // AGENT
	( isset($_GET['bot'])      ) ? $botflag = $_GET['bot']     		: $botflag = fBotCheck($_GET['agent']); // BOT FLAG
	( isset($_GET['blackflag'])) ? $blackflag = $_GET['blackflag'] : $blackflag = 0; // BLACKLIST IP FLAG
	( isset($_GET['adult'])    ) ? $adultflag = $_GET['adult'] 		: $adultflag = fAdultCheck($domain); // ADULT FLAG
	
	// if no referer, build referer from Google using keywords or domain
	if ( empty($_GET['referer']) || !isset($_GET['referer']) || $_GET['referer'] = '') { 
		if ($keywords) {
			$referer = "http://www.google.com/?q=" . urlencode($keywords) . "#sclient=psy&hl=en&safe=off&site=&source=hp&q=" . urlencode($keywords) . "&btnG=Google+Search";
		} else {
			$referer = "http://www.google.com/?q=" . urlencode($domain) . "#sclient=psy&hl=en&safe=off&site=&source=hp&q=" . urlencode($domain) . "&btnG=Google+Search";
		}
	}
	
	// REGION
	if(isset($_GET['region'])) { 
		$region = $_GET['region'];	
	} else {
		$region = fIPlookup($ip_address, $MYSQL_CONNECTION); // db
		if(!$region || $region == -1) $region = "ZZ"; // no region return for IP
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

	// BUILD RETURN PATH FOR RELOAD
	$RETURN_PATH = "";
	$RETURN_PATH .= $SITE_PATH . "?";
	$RETURN_PATH .= "domain=" . $domain; // NEED AT LEAST DOMAIN 
	/*
	if (isset($url)) $RETURN_PATH .= "&url=". urlencode($url); // FULL URL
	if (isset($trace)) $RETURN_PATH .= "&trace=". $trace; // TRACE FLAG
	if (isset($region)) $RETURN_PATH .= "&region=". $region; // REGION
	if (isset($ip_address)) $RETURN_PATH .= "&ip=". $ip_address; // IP ADDRESS
	if (isset($referer)) $RETURN_PATH .= "&referer=". urlencode($referer); // REFERER
	if (isset($keywords)) $RETURN_PATH .= "&keywords=". urlencode($keywords); // KEYWORDS
	if (isset($agent)) $RETURN_PATH .= "&agent=". urlencode($agent); // USER AGENT
	if (isset($botflag)) $RETURN_PATH .= "&botflag=". $botflag; // USER AGENT
	if (isset($blackflag)) $RETURN_PATH .= "&blackflag=". urlencode($blackflag); // USER AGENT*/
	
	/* Set Session Variables */
	$_SESSION['trace']=$trace;
	$_SESSION['id']=$session_id;
	$_SESSION['agent']=$agent;
	$_SESSION['region']=$region;
	$_SESSION['ip']=$ip_address;
	$_SESSION['url']=$url;
	$_SESSION['domain']=$domain;
	$_SESSION['referer']=$referer;
	$_SESSION['keywords']=$keywords;
	$_SESSION['bot']=$botflag;
	$_SESSION['blackflag']=$blackflag;
	$_SESSION['adult']=$adultflag;
	
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
	} // end fresh session
}

$_SESSION['date'] = $date;
$_SESSION['start'] = $start;
$_SESSION['cookies'] = $cookies_enabled;
$_SESSION['javascript'] = $js_enabled;
$_SESSION['bot'] = $botflag;
$_SESSION['blackflag'] = $blackflag;

// OUTPUT
if ($trace) {
	echo "DATE: ". $_SESSION['date'] ."<br />";
	echo "TIME: ". $_SESSION['start'] ."<br />";
	echo "SID: ". $_SESSION['id'] ."<br />";
	echo "DOMAIN: " . $_SESSION['domain'] . "<br />";
	echo "URL: " . $_SESSION['url'] . "<br />";
	echo "TRACE: " . $_SESSION['trace'] . "<br />";
	echo "LOCALE: " . $_SESSION['region'] . "<br />";
	echo "IP: " . $_SESSION['ip'] . "<br />";
	echo "REFERER: " . $_SESSION['referer'] . "<br />";
	echo "KEYWORDS: " . $_SESSION['keywords'] . "<br />";
	echo "AGENT: " . $_SESSION['agent'] . "<br />";
	echo "COOKIES: " . $_SESSION['cookies'] . "<br />";
	echo "JAVASCRIPT: " . $_SESSION['javascript'] . "<br />";
	echo "BOT: " . $_SESSION['bot'] . "<br />";
	echo "BLACKLISTED: " . $_SESSION['blackflag'] . "<br />";
	echo "DOMAIN PASSED IN: " . $dp . "<br />";
	if ($trace) echo "Time after reload: " . fMarkTime($start) . "<br />";
}
// HERE+=================================================================================================================
// HERE+=================================================================================================================
$sql = "INSERT IGNORE INTO " . $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX . "<BR /";
echo "SQL: $sql <br />";
// HERE+=================================================================================================================
// HERE+=================================================================================================================

//==========================================================================================
// BEGIN MULTI-PROGRAMMING SECTION
//==========================================================================================

$fp = array();
if (1) {  

	for ($i = 0; $i < sizeof($FEEDS); ++$i) { // build feed URL to send to multi-processes
		
		$which = $PING_PATH. '?feed=' . $FEEDS[$i];
		$which .= '&session_id='. $session_id;
		/*
		$which .= '&date=' . $date; 
		$which .= '&time='. $start; 
		$which .= '&referer=' . urlencode($referer); 
		$which .= '&domain=' . $domain; 
		$which .= '&url=' . urlencode($url);
		$which .= '&agent=' . urlencode($agent); 
		$which .= '&region=' . $region; 
		$which .= "&ip=" .$ip_address;
		$which .= '&keywords=' . urlencode($keywords); 
		$which .= '&trace=' . $trace; 
		$which .= '&cookies=' . $cookies_enabled; 
		$which .= '&js=' . $js_enabled;
		$which .= '&botflag=' . $botflag;
		$which .= '&blackflag=' . $blackflag;
		$which .= '&dp=' . $dp;*/
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