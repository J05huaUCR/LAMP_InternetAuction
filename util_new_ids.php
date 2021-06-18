<?php

require("config.php");
require("includes/util_conversion_functions.php");
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() );  // connect to db
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error()); // select db

echo "Starting...<br />";
$date = $_GET['date'];
/* PPC */

//$table_feeds_source = "PPC_" . $date . "_Feeds"; // source table
$table_sessions_source = "DC2_" . $date . "_Sessions"; // source table
$table_bids_source = "DC2_" . $date . "_Bids"; // source table
$table_sessions = "dc_" . $date . "_sessions"; // destination sessions table
$table_bids = "dc_" . $date . "_bids"; // destination bids table

echo "DATE: $date <br />";
echo "SOURCE SESSIONS: $table_sessions_source <br />";
echo "SOURCE BIDS: $table_bids_source <br />";
echo "DESTINATION SESSIONS: $table_sessions <br />";
echo "DESTINATION BIDS: $table_bids<br />";

flush(); @ob_flush();

$sql = "SELECT s.id as id FROM $table_sessions_source as s, $table_sessions as d WHERE s.id=d.id";
echo "SQL: $sql <br />";
$dupe_ids = fQuery_Indexed_Array($sql, $MYSQL_CONNECTION);
for ($i = 0; $i < sizeof($dupe_ids) ; $i++) {
	$old_id = $dupe_ids[$i][0];
	$new_id = fNewId();
	echo "OLD ID $i: $old_id | NEW ID: $new_id<br />";	
	flush(); @ob_flush();
	/*
	UPDATE table_name
	SET column1=value, column2=value2,...
	WHERE some_column=some_value 
	
	$sql = "UPDATE $table_feeds_source SET id = '$new_id' WHERE id = '$old_id'";
	mysql_query($sql);
	echo "SQL: $sql | ERROR: " . mysql_error(). "<br />";*/
	
	$sql = "UPDATE $table_bids_source SET id = '$new_id' WHERE id = '$old_id'";
	mysql_query($sql);
	echo "SQL: $sql | ERROR: " . mysql_error(). "<br />";
	
	$sql = "UPDATE $table_sessions_source SET id = '$new_id' WHERE id = '$old_id'";
	echo "SQL: $sql | ERROR: " . mysql_error(). "<br />";
	mysql_query($sql);
	
	sleep(1);
}
echo "DONE.";
exit();
//fMakeNewTables($date, $table_sessions, $table_bids, $MYSQL_CONNECTION); // Create tables if not already done

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