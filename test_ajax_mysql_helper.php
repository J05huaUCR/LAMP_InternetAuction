<?php

require("config.php");
require("admin/includes/functions.php");

// check for passed in 
$TABLE_STATS = "dc_stats_daily";
$datetime = date("F j, Y  g:m:sa", time()); // current datetime
$date_format = date("Y-m-d", time()); // current datetime
(isset($_GET['days'])) ? $days = $_GET['days'] : $days="0";
(isset($_GET['view'])) ? $view = $_GET['view'] : $view = "feeds";
$column = $_GET['col'];
$sort = $_GET['sort'];
$TRACE = 0;
if (!isset($days)) $days = 0;
$days_key = "?days=" . $days;

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

$date = fGetTime($days);

$revenue = 0;

$table_sessions = $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX;
$table_bids = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
$table_join = "$table_sessions as s JOIN $table_bids as b USING(id)";

$zc_count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='zc' ",$MYSQL_CONNECTION);
$dc_count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='dc' ",$MYSQL_CONNECTION);
$update_time = fQuery_Single("SELECT update_time FROM $TABLE_STATS WHERE stats_date='$date_format' ",$MYSQL_CONNECTION);
$stats_datetime = date("F j, Y  g:m:sa", $update_time); // current datetime
$total = $zc_count + $dc_count;

$table .= "<h3>ZeroClick&trade; has $zc_count session(s) out of $total total.</h3>";
$stats_zc_platform = array();
$sql  = "SELECT feed as 'Feed', resp_us as 'US', resp_intl as 'Intl',(resp_us + resp_intl) as 'Total', num_bids_won as 'Wins',";
$sql .= "FORMAT(bid_max,6) as 'Max Bid',FORMAT(bid_min,6) as 'Min Bid',FORMAT(bid_avg,6) as 'Avg Bid', FORMAT(bid_median,6) as 'Median', ";
$sql .= "FORMAT(resp_fastest,6) as 'Fastest', FORMAT(resp_slowest,6) as 'Slowest',";
$sql .= "FORMAT(resp_avg,6) as 'Avg Time',FORMAT(rev_us,2) as 'US $', FORMAT(rev_intl,2) as 'Intl $',FORMAT(rev_us + rev_intl, 2) as 'Total $' ";
$sql .= "FROM $TABLE_STATS ";
$sql .= "WHERE stats_date ='$date' and platform='zc'";
$stats_zc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION, $TRACE);
$table .= fOutputTable(&$stats_zc_platform);

$table .= "<h3>DirectClick&trade; has $dc_count session(s) out of $total total.</h3>";
$stats_dc_platform = array();
$sql  = "SELECT feed as 'Feed', resp_us as 'US', resp_intl as 'Intl', (resp_us + resp_intl) as 'Total',num_bids_won as 'Clicks', ";
$sql .= "FORMAT(bid_max,6) as 'Max Bid',FORMAT(bid_min,6) as 'Min Bid',FORMAT(bid_avg,6) as 'Avg Bid', FORMAT(bid_median,6) as 'Median', ";
$sql .= "FORMAT(resp_fastest,6) as 'Fastest', FORMAT(resp_slowest,6) as 'Slowest',";
$sql .= "FORMAT(resp_avg,6) as 'Avg Time',FORMAT(rev_us,2) as 'US $', FORMAT(rev_intl,2) as 'Intl $', FORMAT(rev_us + rev_intl, 2) as 'Total $'";
$sql .= "FROM $TABLE_STATS ";
$sql .= "WHERE stats_date ='$date' and platform='dc'";
$stats_dc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
$table .= fOutputTable(&$stats_dc_platform);
$table .= "Updated at $stats_datetime.";
echo $table;
?>