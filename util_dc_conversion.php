<?php

require("config.php");
require("includes/util_conversion_functions.php");
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() );  // connect to db
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error()); // select db

echo "Starting...<br />";
$block = 250;
$date = $_GET['date'];
//$date = "20120430";
$table_bids_source = "PPC_" . $date . "_Bids"; // source table
$table_feeds_source = "PPC_" . $date . "_Feeds"; // source table
$table_sessions_source = "PPC_" . $date . "_Sessions"; // source table
$table_sessions = "ppcx_" . $date . "_sessions"; // destination sessions table
$table_bids = "ppcx_" . $date . "_bids"; // destination bids table

echo "DATE: $date <br />";
echo "SOURCE TABLE: $table_source <br />";
echo "SESSIONS TABLE: $table_sessions <br />";
echo "BIDS TABLE: $table_bids<br />";

flush(); @ob_flush();

fMakeNewTables($date, $table_sessions, $table_bids, $MYSQL_CONNECTION); // Create tables if not already done

$i = 0;
$record_count = 0;
do {
	echo "NOW PROCESSING RECORDS ". ($i * $block) . " to " . ((($i+1) * $block)-1) . "<br />";
	flush(); @ob_flush();
	$source = array();
	$sql = "SELECT * FROM " . $table_feeds_source . ", " . $table_bids_source ." ";
	$sql .= "WHERE " . $table_feeds_source . ".id = " . $table_bids_source .".id LIMIT " . ($block * $i) . ",".$block."";
	echo "SQL: $sql <br /";
	$source = fQuery_Assoc_Conv($sql, $MYSQL_CONNECTION);
	for ($j = 0; $j < sizeof($source); $j++) {

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
		$bids['bid_calc'] = $source[$j]['bid'];
		$bids['bid'] = $source[$j]['bid'];
		$bids['redirect'] = mysql_real_escape_string(urldecode($source[$j]['redirect']));
		$bids['title'] = mysql_real_escape_string(urldecode($source[$j]['title']));
		$bids['description'] = mysql_real_escape_string(urldecode($source[$j]['description']));
		$bids['url'] = $source[$j]['url'];
		$bids['position'] =  $source[$j]['position'];
		$bids['won'] = $source[$j]['won'];
		$bids['status'] = mysql_real_escape_string(urldecode($source[$j]['status']));
		fWriteToDB($MYSQL_CONNECTION, &$bids, $table_bids);
	}
	$i++;
} while (sizeof($source) > 1);


?>