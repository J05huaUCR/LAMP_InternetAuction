<?php

echo "WTF<br />";

// Required
require("../config_dev.php");

// Initiate Session
$date = date("Ymd");
$start = fGetMicroTime(); 
$session = array();
$bids = array();

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

// Retrieve GETS
// feed=admarketplace&ratio=&session_id=8b81d75fe2a4e1e5e9f41212915f6d5e&trace=1
$feed = $_GET['feed'];
if (isset($_GET['ratio']) ) $bids['ratio'] = $_GET['ratio']; else $bids['ratio'] = 1.00;
$session_id = $_GET['session_id'];
$trace = $_GET['trace'];
$bids['id'] = $session_id;
$bids['time'] = $start;
$bids['feed'] = $feed;

// start session
session_id($session_id);
session_start(); 
set_time_limit($MAX_TIME);  // Time limit
// check
if ($trace) {
	echo "<br />RATIO: " . $bids['ratio'] . "<br />";
	$bids['status'] .= "TEST, ";
}
flush(); @ob_flush(); 

// Retrieve Session Info
$sql = "SELECT * FROM " . $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX . " WHERE id='" . $session_id . "'";
$session = fQuery_Assoc_Array($sql, $MYSQL_CONNECTION, $trace);

/* Session Vars passed in */
if ($trace) {
	echo "<br />SESSSION VARIABLES PASSED INTO PING: <br />";
	foreach($session as $key => $value) {
		echo "<b>" . strtoupper($key) . ":</b> " . $value . "<br />";
	}
}
flush(); @ob_flush(); 

// Get bid results from xml
$bids['xml_feed'] = fBuildFeed($MYSQL_CONNECTION, $date, &$session, $bids['feed'], 1, $trace);

if (!empty($bids['xml_feed']) || $bids['xml_feed'] != '' || !isset($bids['xml_feed']) ) {
	$xml = simplexml_load_file($bids['xml_feed']);
	if ($trace) echo "SIZE BEFORE: ", sizeof($bids), "<br />";// get number of entries in results before parsing to determine if anything added
	flush(); @ob_flush(); 
	fParseFeed(&$bids, &$xml, &$session, $trace); // parse feed to get bids if any
}

// write results to db regardless of bids
fWriteBids($MYSQL_CONNECTION, &$bids, $date, $trace); 

// close db connection
mysql_close($MYSQL_CONNECTION);

if ($trace) {
	echo "===================================================================================<br />";
}

flush(); @ob_flush();  ## make sure that all output is sent in real-time

?>