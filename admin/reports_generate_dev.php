<?php

require("config.php");

/* SET DATE */
$trace = $_GET['trace'];
if (isset($_GET['days_back'])) $DAYS_BACK = $_GET['days_back']; else $DAYS_BACK = 0;
$NOW = time();
$OFFSET = 1 * 24 * 60 * 60;
$time = $NOW - ($OFFSET * $DAYS_BACK); 
$previous = $time - 300; // minus 5 minutes to make sure to grab the previous day when running at midnight
$date_YMD = date("Ymd", $previous); 
$table_date = date("Y-m-d", $previous);

if ($trace) {
	echo "TIME NOW: $NOW<br />";
	echo "DATE: $date_YMD<br />";
}
/*
for ($i = $DAYS_BACK; $i > 0; $i--) {
	$time = $NOW - ($OFFSET * $i);
	$date_YMD = date("Y-m-d", $time); // need day before
	$sql = "UPDATE $TABLE_STATS SET update_time = $time WHERE stats_date = '$date_YMD'";
	mysql_query($sql, $MYSQL_CONNECTION);
	
	echo "DATE = $date_YMD<br />";
	echo "TIME: $time<br />";
	echo "SQL: $sql<br />";
	echo "==========================<br />";
	
}*/
//$days_back = 120;
//$days_back = $_GET['days_back'];

//$date_YMD = $_GET['date']; // testing
//$date_slash = date("F j, Y", $yesterday); // need day before

$sessions = $TABLE_PREFIX . $date_YMD . $TABLE_SESSIONS_POSTFIX;
$bids = $TABLE_PREFIX . $date_YMD . $TABLE_BIDS_POSTFIX;
//$table_join = "FROM $sessions,$bids WHERE $sessions.id = $bids.id AND $sessions.platform = ";
$table_join = "FROM $bids b JOIN $sessions s USING(id) ";

$FEEDS = array();
$sql = "SELECT DISTINCT feed from $bids WHERE feed <> '' ORDER BY feed ASC";
if ($trace) echo "SQL: $sql<br />";
$FEEDS = fQuery_Multiple_Assoc($sql, $MYSQL_CONNECTION);
$PLATFORMS = array('ac');

/* for backtracking updates in stats */
$DATES = array();
$sql = "SELECT DISTINCT update_time FROM dc_stats_daily WHERE update_time > $previous"; // Get set of update times
if ($trace) echo "SQL: $sql<br />";
$DATES = fQuery_Indexed_Array($sql, $MYSQL_CONNECTION);

$prev_time = fQuery_Single("SELECT update_time FROM dc_stats_daily WHERE stats_date = '$date_YMD' AND platform <> 'ac' GROUP BY update_time",$MYSQL_CONNECTION);


for ($i = 0; $i < sizeof($FEEDS); ++$i){	
	$feed = $FEEDS[$i]['feed'];
	if ($trace) echo "FEED: |$feed| <br />";
	
	$p = 0;
	$sql = "";
	
	foreach($PLATFORMS as $v) {
		if($p) $sql .= "UNION ";

		$where = "WHERE b.feed = '$feed' AND s.platform = '$v' ";
		
		$sql .= "SELECT '$v' as platform,";
		$sql .= "( SELECT COUNT(b.bid) $table_join $where AND s.region = 'US' ) as resp_us,";
		$sql .= "( SELECT COUNT(b.bid) $table_join $where AND s.region <>'US' ) as resp_intl,";
		$sql .= "( SELECT MAX(b.bid)   $table_join $where ) as bid_max,";
		$sql .= "( SELECT MIN(b.bid)   $table_join $where AND b.bid > 0) as bid_min,";
		$sql .= "( SELECT AVG(b.bid)   $table_join $where ) as bid_avg,";
		// Median
		$sql .= "( SELECT t1.bid as MedianBid FROM ";
		$sql .= "( SELECT @rownum:=@rownum+1 as `row_number`, b.bid $table_join,  (SELECT @rownum:=0) r $where ORDER BY b.bid) as t1, 
					( SELECT count(*) as total_rows $table_join $where ) as t2
					  WHERE 1 AND t1.row_number=floor(total_rows/2)+1) as bid_median,";
		$sql .= "( SELECT COUNT(b.bid) $table_join $where) as num_bids,";
		$sql .= "( SELECT COUNT(b.bid) $table_join $where AND b.won > 0) as num_bids_won,";
		$sql .= "( SELECT AVG(b.bid)   $table_join $where AND b.won > 0) as bids_won_avg,";
		// Median Won
		$sql .= "( SELECT t1.bid as MedianBidWon FROM ";
		$sql .= "( SELECT @rownum:=@rownum+1 as `row_number`, b.bid $table_join,  (SELECT @rownum:=0) r $where ORDER BY b.bid) as t1, 
					( SELECT count(*) as total_rows $table_join $where ) as t2
					  WHERE 1 AND t1.row_number=floor(total_rows/2)+1) as bids_won_median,";
		$sql .= "( SELECT MIN(s.time_elapsed) $table_join $where ) as resp_fastest,";
		$sql .= "( SELECT MAX(s.time_elapsed) $table_join $where ) as resp_slowest,";
		$sql .= "( SELECT AVG(s.time_elapsed) $table_join $where ) as resp_avg,";
		$sql .= "( SELECT SUM(b.bid)   $table_join $where AND b.won > 0 AND s.region ='US' ) as rev_us,";
		$sql .= "( SELECT SUM(b.bid)   $table_join $where AND b.won > 0 AND s.region <>'US' ) as rev_intl,";
		$sql .= "( SELECT SUM(b.bid)   $table_join $where AND b.won > 0) as RevTotal ";
		$p++;
	}
		
	if ($trace) echo "SQL: $sql<br /><br />";
	$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
	
	// check db for stats
	$sql = "SELECT feed, update_time FROM $TABLE_STATS WHERE stats_date = '$date_YMD' AND feed='$feed' and platform='ac'";
	if ($trace) echo "SQL: $sql <br />";
	$result = fQuery_Assoc_Array($sql, $MYSQL_CONNECTION);
	
	if ($result) { // stats present
		if ($trace) echo "STATS: Present, update in db.<br />";
		
		for ($j = 0 ; $j < sizeof($stats) ; $j++) {
			$stat_sql  = "UPDATE $TABLE_STATS SET ";
			if ($DAYS_BACK > 0) {
				$stat_sql .= "update_time='".$prev_time."',"; // set time when backtracking
			} else {
				$stat_sql .= "update_time='$NOW',"; // set time to current
			}
			$stat_sql .= "resp_us='".$stats[$j]['resp_us']."',resp_intl='".$stats[$j]['resp_intl']."',bid_max ='".$stats[$j]['bid_max'] ."',";
			$stat_sql .= "bid_min='". $stats[$j]['bid_min'] ."',bid_avg='".$stats[$j]['bid_avg'] ."',bid_median='".$stats[$j]['bid_median'] ."',";
			$stat_sql .= "num_bids='".$stats[$j]['num_bids']."',num_bids_won='".$stats[$j]['num_bids_won']."',";
			$stat_sql .= "bids_won_avg='".$stats[$j]['bids_won_avg']."',bids_won_median='".$stats[$j]['bids_won_median']."',";
			$stat_sql .= "resp_fastest='".$stats[$j]['resp_fastest']."',resp_slowest='".$stats[$j]['resp_slowest']."',";
			$stat_sql .= "resp_avg='".$stats[$j]['resp_avg'] ."',rev_us='".$stats[$j]['rev_us']."',rev_intl='".$stats[$j]['rev_intl']."' ";
			$stat_sql .= "WHERE stats_date = '$table_date' AND feed = '$feed' AND platform = '".$stats[$j]['platform']."'";
			
			if ($trace) echo "SQL: $stat_sql<br />";
			mysql_query($stat_sql,$MYSQL_CONNECTION);
		}/**/
	} else { // no stats
		if ($trace) echo "STATS: Not present, insert into db.<br />";
	
		for ($j = 0 ; $j < sizeof($stats) ; $j++) {
			$stat_sql  = "INSERT INTO $TABLE_STATS ";
			$stat_sql .= "(update_time,stats_date,feed,platform,resp_us,resp_intl,bid_max,bid_min,bid_avg,bid_median,num_bids,num_bids_won,";
			$stat_sql .= "bids_won_avg,bids_won_median,resp_fastest,resp_slowest,resp_avg,rev_us,rev_intl) ";
			$stat_sql .= "VALUES (";
			$stat_sql .= "'$NOW','$table_date','$feed','".$stats[$j]['platform']."',";
			$stat_sql .= "'".$stats[$j]['resp_us']."','".$stats[$j]['resp_intl']."','".$stats[$j]['bid_max'] ."','". $stats[$j]['bid_min'] ."',";
			$stat_sql .= "'".$stats[$j]['bid_avg']."','".$stats[$j]['bid_median']."',";
			$stat_sql .= "'".$stats[$j]['num_bids']."','".$stats[$j]['num_bids_won']."','".$stats[$j]['bids_won_avg'] ."','".$stats[$j]['bids_won_median'] ."',";
			$stat_sql .= "'".$stats[$j]['resp_fastest']."','".$stats[$j]['resp_slowest']."','".$stats[$j]['resp_avg'] ."',";
			$stat_sql .= "'".$stats[$j]['rev_us']."','".$stats[$j]['rev_intl']."'";
			$stat_sql .= ")";
			
			if ($trace) echo "SQL: $stat_sql<br />";
			mysql_query($stat_sql,$MYSQL_CONNECTION);
		}/**/
	}
	unset($stats);
}

if ($trace) echo "<br /><br />";
if ($trace) echo "Done. <br />";

?>