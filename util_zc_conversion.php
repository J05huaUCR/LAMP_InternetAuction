<?php

require("config.php");
require("includes/util_conversion_functions.php");
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() );  // connect to db
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error()); // select db

echo "Starting...<br />";
$block = 250;
$date = $_GET['date'];
//$date = "20120430";
$table_source = "Tracking_Bids_" . $date; // source table
$table_sessions = "dcx_" . $date . "_sessions"; // destination sessions table
$table_bids = "dcx_" . $date . "_bids"; // destination bids table

echo "DATE: $date <br />";
echo "SOURCE TABLE: $table_source <br />";
echo "SESSIONS TABLE: $table_sessions <br />";
echo "BIDS TABLE: $table_bids<br />";

flush(); @ob_flush();

fMakeNewTables($date, $table_sessions, $table_bids, $MYSQL_CONNECTION); // Create tables if not already done

$i = 0;
$record_count = 0;
do {
	echo "NOW PROCESSING RECORDDS ". ($i * $block) . " to " . ((($i+1) * $block)-1) . "<br />";
	flush(); @ob_flush();
	$source = array();
	$sql = "SELECT * FROM " . $table_source . " LIMIT " . ($block * $i) . ",".$block."";
	echo "SQL: $sql <br /";
	$source = fQuery_Assoc_Conv($sql, $MYSQL_CONNECTION);
	for ($j = 0; $j < sizeof($source); $j++) {
		/*1.0
		===========
		time => sessions > time, bids > time
		time_elapsed => sessions > time_elapsed, bids > time_elapsed
		id => sessions > id, bids > id
		feed => bids > feed
		agent => sessions > agent 
		region => sessions > region 
		ip => sessions > ip 
		referer => sessions > referer 
		keywords => sessions > keywords
		domain => sessions > domain 
		url => sessions > url 
		xml_feed => bids > xml_feed
		xml_bid => bids > bid
		xml_redirect => bids > redirect
		xml_title => bids > title
		xml_description => bids > description
		xml_url => bids > url
		won => bids > won
		adult => session > adult
		datetime => sessions > datetime
		status => sessions > watch for js, cookies, adult, blackflag, bot, bids > status
		
		// Setup Session Data
		INSERT IGNORE INTO dc_<date>_sessions 
		(datetime,platform,id,time,time_elapsed,agent,region,ip,url,domain,referer,keywords,cookies,javascript,adult,bot,blackflag)
		VALUES
		()
		
		*/
		$session['datetime'] = $source[$j]['datetime'];
		$session['platform'] = "zc";
		$session['id'] = $source[$j]['id'];
		$session['time'] = $source[$j]['time'];
		$session['time_elapsed'] = $source[$j]['time_elapsed'];
		$session['agent'] = mysql_real_escape_string(urldecode($source[$j]['agent']));
		$session['region'] = mysql_real_escape_string(urldecode($source[$j]['region']));
		$session['ip'] = $source[$j]['ip'];
		$session['url'] = mysql_real_escape_string(urldecode($source[$j]['url']));
		$session['domain'] = mysql_real_escape_string(urldecode($source[$j]['domain']));
		$session['referer'] = mysql_real_escape_string(urldecode($source[$j]['referer']));
		$session['keywords'] = mysql_real_escape_string(urldecode($source[$j]['keywords']));
		if (strpos($source[$j]['status'],"ookies_on"   )) $session['cookies']    = 1; else $session['cookies']    = 0; 
		if (strpos($source[$j]['status'],"avascript_on")) $session['javascript'] = 1; else $session['javascript'] = 0; 
		$session['adult'] = $source[$j]['adult'];
		if (strpos($source[$j]['status'],"ot,"       )) $session['bot']        = 1; else $session['bot']        = 0; 
		if (strpos($source[$j]['status'],"lack"     )) $session['blackflag']  = 1; else $session['blackflag']  = 0; 
		
		fWriteToDB($MYSQL_CONNECTION, &$session, $table_sessions);

		/* setup bid data
		
		INSERT INTO dc_<date>_bids
		(id,feed,time,time_elapsed,xml_feed,ratio,bid_calc,bid,redirect,title,description,url,position,won,status)
		VALUES
		*/
		$bids['id'] = $source[$j]['id'];
		$bids['feed'] = $source[$j]['feed'];
		$bids['time'] = $source[$j]['time'];
		$bids['time_elapsed'] = $source[$j]['time_elapsed'];
		$bids['xml_feed'] = mysql_real_escape_string(urldecode($source[$j]['xml_feed']));
		$bids['ratio'] = 1;
		$bids['bid_calc'] = $source[$j]['xml_bid'];
		$bids['bid'] = $source[$j]['xml_bid'];
		$bids['redirect'] = mysql_real_escape_string(urldecode($source[$j]['xml_redirect']));
		$bids['title'] = mysql_real_escape_string(urldecode($source[$j]['xml_title']));
		$bids['description'] = mysql_real_escape_string(urldecode($source[$j]['xml_description']));
		$bids['url'] = $source[$j]['url'];
		$bids['position'] = 0;
		$bids['won'] = $source[$j]['won'];
		$bids['status'] = mysql_real_escape_string(urldecode($source[$j]['status']));
		fWriteToDB($MYSQL_CONNECTION, &$bids, $table_bids);
	}
	$i++;
} while (sizeof($source) > 1);


?>