<?php

/*======================================
* Initialize
* ======================================*/
require("config_dev.php");
$start = fGetMicroTime(); // Mark start of processing
$date = date("Ymd"); // get date
$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX; // build table
if ( empty($platform) ) $platform = "xx"; // if platform not set, probably a bot

/* Retrieve variables */
if(isset( $_GET['trace']) ) { // tracing?
	$trace = $_GET['trace'];
} 

// Attempt count
if(isset( $_GET['try']) ) { 
	$try = $_GET['try'];
} else if( !empty($_POST['try']) ) {
	$try = 2; // Flag to need new SID for next write
} else {
	$try = 0;
}

/*======================================
* PickUp Session
* ======================================*/
if( !empty( $_GET['id']) ) { // GET SID
	$session_id = $_GET['id'];
} else if( !empty($_POST['id']) ) {
	$session_id = $_POST['id'];
} 

/*======================================
* db
* ======================================*/
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() );  // connect to db
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error()); // select db
fCheckTables($date, $MYSQL_CONNECTION); // Create tables if not already done

/*============================================================
* Process Session Info and Cookie and JavaScript Check Section
* ============================================================*/
if ( empty($session_id) ) { // "There is no try." - Yoda (virgin session)

	// Erase any previous sessions
	session_start();
	unset($_SESSION, $_COOKIE);
	session_destroy();
	
	// generate new session
	session_regenerate_id();
	session_start();
	$session_id = session_id();

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
	
	// Test and assign is passed in for testing
	( isset($_GET['keywords']) ) ? $keywords = $_GET['keywords'] 	: $keywords = fKeywordLookup($domain, $MYSQL_CONNECTION); // KEYWORDS lookup needs to be domain-specific
	//( isset($_GET['keywords']) ) ? $keywords = $_GET['keywords'] 	: $keywords = substr($domain, 0, strpos($domain, '.') ); 
	( isset($_GET['url'])      ) ? $url = $_GET['url'] 		  		: $url = "http://www.".$domain ."/"; // URL
	( isset($_GET['ip']) 
	     && $_GET['ip'] != ''  ) ? $ip_address = $_GET['ip']   		: $ip_address = $_SERVER['REMOTE_ADDR']; // IP ADDRESS
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
	if (fIPcheck($ip_address, $MYSQL_CONNECTION) ) $redirect = $DEFAULT_REDIRECT . "domain=". $domain;	// Blacklisted IP's get redirect
	
	// CLOSE MYSQL CONNECTION
	mysql_close($MYSQL_CONNECTION);

	// BUILD RETURN PATH FOR RELOAD
	$RETURN_PATH = $SITE_PATH . "?domain=" . $domain; // NEED AT LEAST DOMAIN 

	/* SET SESSION VARIABLES */
	$_SESSION['trace']=$trace;
	$_SESSION['time'] = $start;
	//$_SESSION['id']=$session_id;
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
	$_SESSION['platform']=$platform;
	session_write_close();
		
	/* Cookie /JavaScript Test */
	$expire = time()+60*60*24*30;
	setcookie('directclick_cookie_enabled', '1', $expire,'/',$DEFAULT_DOMAIN); // attempt to set cookie via php
	
	// attempt to use javascript and then reload ?>
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
		window.location.href = "<?=$RETURN_PATH?>&id=<?=$session_id?>&js=1&try=1"; <!-- javascript enabled, reload using js -->
	}
	createCookie('directclick_js_enabled', '1', 1);
	</script>
	
	<noscript><!-- javascript disabled, reload using refresh -->
		<meta http-equiv="refresh" content="0;url=<?=$RETURN_PATH?>&id=<?=$session_id?>&js=0&try=1">
	</noscript>
	<?
	exit();
}/* End new session============================================================*/

// start session
session_id($session_id);
session_start(); 

if (!isset($trace) ) $trace = $_SESSION['trace']; // assign trace from session

// PickUpKeywords via post - if any
if( !empty($_POST['keywords'])) { 
	$keywords = $_POST['keywords'];
	$keysfrom = "POST";
	$_SESSION['keywords'] = $_POST['keywords'];
} else if( !empty($_SESSION['keywords']) ) {
	$keysfrom = "SESSION";
	$keywords = $_SESSION['keywords'];
} 

if ($trace > 2) {
	echo "SESSION ID: |$session_id|<br />";	
	echo "TRACE: |$trace|<br />";	
	echo "TRY: |$try|<br />";	
	echo "KEYWORDS: |$keywords| from |$keysfrom|<br />";	
	if ($try > 0) echo "Reloaded.<br />"; else echo "First Time.<br />";
}

/*======================================
* db
* ======================================*/
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() );  // connect to db
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error()); // select db

/*==================================================
* Get Session Vars from POST or SESSSION
* ==================================================*/
$session_vars = array(); // Build Session Array
$session_vars['platform'] = $_SESSION['platform']; // Changes
$session_vars['id'] = fNewId(); // Changes
$session_vars['time'] = $_SESSION['time']; // Changes
$session_vars['agent'] = mysql_real_escape_string($_SESSION['agent']);
$session_vars['region'] = $_SESSION['region'];
$session_vars['ip'] = mysql_real_escape_string($_SESSION['ip']);
$session_vars['url'] = mysql_real_escape_string($_SESSION['url']);
$session_vars['domain'] = mysql_real_escape_string($_SESSION['domain']);
$session_vars['referer'] = mysql_real_escape_string($_SESSION['referer']);
(isset($_POST['keySearch']) ) ? 
	$session_vars['keywords'] = mysql_real_escape_string($_POST['keySearch']) : 
	$session_vars['keywords'] = mysql_real_escape_string($_SESSION['keywords']);

if ( $_COOKIE['directclick_cookie_enabled'] || $_SESSION['cookies'] = 1 ) {
	$_SESSION['cookies'] = 1;
	$session_vars['cookies'] = 1; 
} else {
	$_SESSION['cookies'] = 0;
	$session_vars['cookies']  = 0; 
}

if ($_GET['js'] == "1" || $_SESSION['javascript'] = 1) {
	$_SESSION['javascript'] = 1;
	$session_vars['javascript'] = 1;
} else {
	$_SESSION['javascript'] = 0;
	$session_vars['javascript'] = 0;
}
$session_vars['adult'] = mysql_real_escape_string($_SESSION['adult']);
$session_vars['bot'] = mysql_real_escape_string($_SESSION['bot']);
$session_vars['blackflag'] = mysql_real_escape_string($_SESSION['blackflag']);

if ( $try > 1) {
	$session_vars['id'] = $_SESSION['id'] = md5(microtime(true)); // get new session id if moving to dc from zc 
	$session_vars['platform'] = $_SESSION['platform'] = "dc";
}

if ( $try > 1 && $trace == 2) {
	echo "SESSION VARIABLES =======================================<br />";
	foreach ($session_vars as $key => $value){
		echo "<b>$key:</b> $value<br />";
	}
}
	

//==========================================================================================
// RUN AUCTION
//==========================================================================================
if ($session_vars['platform'] != "dc") { // ZeroClick

	/* TRACE OUTPUT */
	if ($trace == 2) {
		echo "Time after getting vars: " . fMarkTime($start) . "<br />";
		foreach($session_vars as $key => $value) {
			echo "<b>" . strtoupper($key) . ":</b> " . $value . "<br />";
		}
		echo "Time to MULTI-PROGRAMMING SECTION: " . fMarkTime($start) . "<br />";
		echo "===================================================================================<br /><br />";
	}

	fInsert_Assoc_Array($MYSQL_CONNECTION, &$session_vars, $table, $date, $trace); // Write Session Data to db

	/*==========================================================================================
	* BEGIN MULTI-PROGRAMMING SECTION
	* ==========================================================================================*/
	$FEEDS = fGetFeeds($session_vars['platform'], $MYSQL_CONNECTION, $trace); // GET FEED INFO BASED ON PLATFORM
	fPingFeeds(&$FEEDS, &$session_vars, $trace);
	if ($trace == 2) { echo "End Loop Time: " . fMarkTime($start) . "<br />"; }/* TRACE OUTPUT */
	// END MULTI-PROGRAMMING SECTION============================================================
	
	$redirect = fWhoWon($MYSQL_CONNECTION, $session_vars['platform'], $date, $session_vars['id'], $trace); // build redirect
	if ($redirect != -1) { // have winner
		fWriteSessionTime($MYSQL_CONNECTION, $start, $date, &$session_vars, $trace = 0); // Write Back Session Time
		if ($trace < 3) {
			fRedirect($redirect); // IF NOT TESTING, PERFORM REDIRECT
		} else if ($trace > 1) {
			$end = fGetMicroTime(); // get time of completion
			echo "<h3>Jobs Complete</h3>";
			echo "Done at $end.<br />";
			echo "&delta; " . ($end - $start) . "secs<br />";/**/
			echo "REDIRECT:", $redirect, "<br />";
		}
		session_write_close(); // Close Session
		session_destroy();
		exit();
	}
	
	// no winner
	$start = fGetMicroTime(); // Mark start of processing
	$session_vars['platform'] = 'dc'; // Changes
	$session_vars['id'] = fNewId(); // Changes
	$session_vars['time'] = $start; // Mark start of processing; // Changes
}

if ($session_vars['platform'] == "dc") { // DirectClick
	if ($trace > 1) echo "DC<br />";
	
	$feed_ads = fWhoWon($MYSQL_CONNECTION, $session_vars['platform'], $date, $session_vars['id'], $trace); // check for winning ads to display
	
	if ($feed_ads) { // Ads have been returned, need to be output
		$base_url = $SITE_URL . "dc/exit/?";
		($trace) ? $base_url .= "trace=$trace&redirect=" : $base_url .= "redirect=";
		
		fWritePosition($MYSQL_CONNECTION, &$feed_ads, $date, $trace); // Write Back Position of Ads on Page
		fWriteSessionTime($MYSQL_CONNECTION, $start, $date, &$session_vars, $trace = 0); // Write Back Session Time
		require($TPL_PATH); // output page
	} else { // No Ads returned
		echo "No Ads! :(<br />";
		fWriteSessionTime($MYSQL_CONNECTION, $start, $date, &$session_vars, $trace = 0); // Write Back Session Time
		if (!$trace) fRedirect($DEFAULT_REDIRECT . "domain=" . $domain); // IF NOT TESTING, PERFORM REDIRECT
	}
} else { // else zero click
	
}

session_write_close(); // Close Session
session_destroy();
?>