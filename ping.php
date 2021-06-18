<?php

// Required
require("config.php");

// Initiate Session
$session = array();
$bids = array();

$date = date("Ymd");
$bids['time'] = $start = fGetMicroTime(); 

// Retrieve GETS
// feed=admarketplace&ratio=&session_id=8b81d75fe2a4e1e5e9f41212915f6d5e&trace=1
// $trace = $_GET['trace'];
// $feed = $_GET['feed'];
// $session_id = $_GET['session_id'];
(!empty($_GET['session_id']) ) ? $bids['id']  		= $_GET['session_id'] : $bids['id'] = ''; 				 // Session ID
(!empty($_GET['feed'])       ) ? $bids['feed']     = $_GET['feed'] 		 : $bids['feed'] = ''; 				 // Feed ID
(!empty($_GET['template'])   ) ? $bids['template'] = $_GET['template']   : $bids['template'] = $TPL; 		 // Template
(!empty($_GET['ratio'])      ) ? $bids['ratio'] 	= $_GET['ratio'] 		 : $bids['ratio'] = 1.00;  		 // Bid Ratio
(!empty($_GET['trace'])      ) ? $trace 				= $_GET['trace'] 		 : $trace = 0; 						 // Trace

if ($trace > 1) {
	echo "<br />=============================================<br />";
	echo "GET variables passed into ping.php:<br >";
	foreach ($_GET as $key => $value) {
		echo "<b>$key:</b> $value<br />";
	}
	echo "=============================================<br />";
	flush(); @ob_flush(); 
}

//$bids['time'] = $start;
$session_id = $bids['id'];
$feed = $bids['feed'];
$template = $bids['template'];

set_time_limit($MAX_TIME);  // Time limit
// check for trace
if ($trace > 1) {
	$bids['status'] .= "TRACED, ";
	flush(); @ob_flush(); 
}

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
if ($trace) echo "ERROR: |" . mysql_error() . "|<br />";
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());
if ($trace) echo "ERROR: |" . mysql_error() . "|<br />";

// Retrieve Session Info
$sql = "SELECT * FROM " . $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX . " WHERE id='" . $session_id . "'";
if ($trace > 1) {
	echo "SQL: |$sql|<br />";	
	flush(); @ob_flush(); 
}
$session = fQuery_Assoc_Array($sql, $MYSQL_CONNECTION, $trace);

/* Session Vars passed in */
if ($trace > 1) {
	echo "<br />=============================================<br />";
	echo "SESSSION VARIABLES PASSED INTO PING: <br />";
	foreach($session as $key => $value) {
		echo "<b>" . strtoupper($key) . ":</b> " . $value . "<br />";
	}
	flush(); @ob_flush(); 
}

$bids['keywords'] = $session['keywords'];

// Get bid results from xml
$bids['xml_feed'] = fBuildFeed($MYSQL_CONNECTION, $date, &$session, $bids['feed'], 1, $trace);

//===========================================================================
// If AdManage_Premium Write the id number to status
//===========================================================================
if ($bids['feed'] == "admanage_premium") {
	$premium_id = substr($bids['xml_feed'],7,5);
	$bids['status'] .= "id=$premium_id, ";
}
//===========================================================================

if ($trace > 1) { echo "fBuildFeed completed.<br />"; }

if (!empty($bids['xml_feed']) || $bids['xml_feed'] != '' || !isset($bids['xml_feed']) ) {
	$xml = simplexml_load_file($bids['xml_feed']);
	if ($trace > 1) {
		echo "SIZE BEFORE: ", sizeof($bids), "<br />";// get number of entries in results before parsing to determine if anything added
		flush(); @ob_flush(); 
	}
	fParseFeed(&$bids, &$xml, &$session, $template, $trace); // parse feed to get bids if any
} else {
	if ($trace > 1) echo "<b>No feed built for ".$bids['feed'].".</b><br />";
}

// write results to db regardless of bids
fWriteBids($MYSQL_CONNECTION, &$bids, $date, $trace); 

// close db connection
mysql_close($MYSQL_CONNECTION);

if ($trace > 0) {
	echo "<br />";
	if ($bids[$feed]['ads']['ad0']['bid'] > 0) {
		echo "BID: " . $bids[$feed]['ads']['ad0']['bid'] . "<br />"; 
		echo "REDIRECT URL: <a href=\"" . $bids[$feed]['ads']['ad0']['redirect'] . "\">LINK</a><br />";
	} else {
		echo "NO BID.<br />";
	}
}

if ($trace > 1) {
	echo "===================================================================================<br />";
}

flush(); @ob_flush();  ## make sure that all output is sent in real-time

?>