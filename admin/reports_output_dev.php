<?php

require("config_dev.php");

session_start(); // Session Timing

// check for passed in 
(isset($_POST['reports']))  	? $view = $_POST['reports']   	: $view = 'overview';
(isset($_POST['days']))			? $days = $_POST['days']   		: $days = 0;
(isset($_POST['flag']))  		? $flag = $_POST['flag'] 			: $flag = 'days';
(isset($_POST['platform']))  	? $platform = $_POST['platform'] : $platform = '0';
(isset($_POST['feed']))  		? $feed = $_POST['feed'] 			: $feed = 0;
(isset($_POST['qty']))  		? $qty = $_POST['qty'] 				: $qty = 20;
(isset($_POST['col']))   		? $column = $_POST['col']  		: $column = 1;
(isset($_POST['sort']))  		? $sort = $_POST['sort']   		: $sort = '';
(isset($_GET['trace'])) 		? $TRACE = $_GET['trace'] 			: $TRACE = '';
(isset($_POST['output']) ) 	? $output = $_POST['output'] 		: $output = 0;

/* for testing
foreach($_POST as $key => $value) {
	echo "$key => $value <br />";	
}
*/

$days_key = "?days=" . $days;

$date = fGetTime($days);
$revenue = 0;
$sql_where = "";
$table_sessions = $TABLE_PREFIX . $date . $TABLE_SESSIONS_POSTFIX;
$table_bids = $TABLE_PREFIX . $date . $TABLE_BIDS_POSTFIX;
$table_join = "$table_sessions as s JOIN $table_bids as b USING(id)";

// Date calculations
switch ($days) {
	case "ytd":
		//echo "YTD <br />";
		$start_date = mktime(0, 0, 0, 1 , 19, date("Y")); // start of year
		$end_date   = time(); // to date
	break;
	
	case "mtd":
		//echo "MTD <br />";
		$start_date = mktime(0, 0, 0, date("m") , 0, date("Y")); // start of month
		$end_date   = time(); // to date
	break;
	
	case 0:
		//echo "Today Only <br />";
		$start_date = mktime(1, 0, 0, date("m") , date("d"), date("Y")); // start of day
		$end_date   = time(); // current time
	break;
	
	default:
		//echo "Today Only <br />";
		$start_date = mktime(1, 0, 0, date("m") , date("d") - $days, date("Y")); // start of month
		$end_date   = time(); // to date
	break;
}
$dates = fGetDates($start_date);
$sql_where .= "WHERE update_time > $start_date AND update_time < $end_date ";

// Generate Platform array for query
if ($platform == '0') {
	$platforms = array('zc','dc','ac');	
} else {
	$platforms = array($platform);	
}

if($feed) $sql_where .= "and feed='$feed' "; // feed selected
//if($region == "US") $sql_where .= "and region='$region' "; // region selected

switch($view) {		
	case "overview": // Overview
		$table = "";
		$csv = "";
		$update_time = fQuery_Single("SELECT update_time FROM $TABLE_STATS WHERE stats_date='$date' ", $MYSQL_CONNECTION);
		$stats_datetime = date("F j, Y  g:m:sa", $update_time); // current datetime
		foreach($platforms as $platform){
			$count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='".$platform."' ", $MYSQL_CONNECTION);
			
			$table .= "<h3>".$PLATFORM_NAMES[$platform]." has $count session(s) total.</h3>";		
			$csv .= $PLATFORM_NAMES[$platform] ."\r\n";
			$sql  = "SELECT ";
			if ($flag == 'range') $sql .= "stats_date as 'Date',";
			$sql .= "feed as 'Feed',SUM(resp_us) as 'US',SUM(resp_intl) as 'Intl',";
			$sql .= "(SUM(resp_us) + SUM(resp_intl)) as 'Total',SUM(num_bids_won) as 'Wins',"; 
			$sql .= "FORMAT(MAX(bid_max),6) as 'Max Bid',FORMAT(MIN(bid_min),6) as 'Min Bid',";
			$sql .= "FORMAT(AVG(bid_avg),6) as 'Avg Bid',FORMAT(AVG(bid_median),6) as 'Median',";
			$sql .= "FORMAT(MIN(resp_fastest),6) as 'Fastest',FORMAT(MAX(resp_slowest),6) as 'Slowest',FORMAT(AVG(resp_avg),6) as 'Avg Time',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT(SUM(rev_us), 2), ',', '') as 'US $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_intl), 2), ',', '') as 'Intl $',";
				$sql .= "REPLACE(FORMAT( (SUM(rev_us) + SUM(rev_intl) ),2), ',', '') as 'Total $' ";
			} else {
				$sql .= "FORMAT(SUM(rev_us), 2) as 'US $',";
				$sql .= "FORMAT(SUM(rev_intl), 2) as 'Intl $',";
				$sql .= "FORMAT( (SUM(rev_us) + SUM(rev_intl) ),2) as 'Total $' ";
			}
			$sql .= "FROM dc_stats_daily ";
			$sql .= "$sql_where and platform='".$platform."' ";
			($flag == 'range') ? $sql .= "GROUP BY stats_date,feed" : $sql .= "GROUP BY feed";
			//echo "$platform SQL: $sql <br />";
			$stats = array();
			$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats);
			$csv   .= fOutputCSV(&$stats);
		}
		$table .= "Updated at $stats_datetime.";
	break;
	
	case "revenue": // Revenue
		$platforms = array('zc','dc','ac');	
		$start_ytd = mktime(0, 0, 0, 0 , 0, date("Y")); // start of year
		$start_mtd = mktime(0, 0, 0, date("m") , 0, date("Y")); // start of month
		$start_wtd = mktime(1, 0, 0, date("m") , date("d") - 7, date("Y")); // last seven days
		$end_date   = time(); // to date
		
		$sql_where .= "WHERE update_time > $start_date AND update_time < $end_date ";
		
		$table = "";
		$csv = "";
		foreach($platforms as $platform){
			$table .= "<h3>".$PLATFORM_NAMES[$platform]."</h3>";	
			$csv .= $PLATFORM_NAMES[$platform]."\r\n";	
			
			$sql  = "SELECT 'Week-To-Date' as Period, (SUM(resp_us) + SUM(resp_intl)) as 'Total Queries',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT(SUM(rev_us),2), ',', '') as 'US $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_intl),2), ',', '') as 'Intl $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_us+rev_intl),2), ',', '') as 'Total $' ";
			} else {
				$sql .= "FORMAT(SUM(rev_us), 2) as 'US $',";
				$sql .= "FORMAT(SUM(rev_intl), 2) as 'Intl $',";
				$sql .= "FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
			}
			//$sql .= "FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $', FORMAT(SUM(rev_us+rev_intl),2) as 'Total $'"; 
			$sql .= "FROM dc_stats_daily WHERE update_time > $start_wtd AND update_time < $end_date and platform = '".$platform."' ";
			$sql .= "UNION ";
			$sql .= "SELECT 'Month-To-Date' as Period, (SUM(resp_us) + SUM(resp_intl)) as 'Total Queries',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT(SUM(rev_us),2), ',', '') as 'US $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_intl),2), ',', '') as 'Intl $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_us+rev_intl),2), ',', '') as 'Total $' ";
			} else {
				$sql .= "FORMAT(SUM(rev_us), 2) as 'US $',";
				$sql .= "FORMAT(SUM(rev_intl), 2) as 'Intl $',";
				$sql .= "FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
			}
			$sql .= "FROM dc_stats_daily WHERE stats_date >= '2012-07-01' and platform = '".$platform."'";
			$sql .= "UNION ";
			$sql .= "SELECT 'Year-To-Date' as Period, (SUM(resp_us) + SUM(resp_intl)) as 'Total Queries',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT(SUM(rev_us),2), ',', '') as 'US $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_intl),2), ',', '') as 'Intl $',";
				$sql .= "REPLACE(FORMAT(SUM(rev_us+rev_intl),2), ',', '') as 'Total $' ";
			} else {
				$sql .= "FORMAT(SUM(rev_us), 2) as 'US $',";
				$sql .= "FORMAT(SUM(rev_intl), 2) as 'Intl $',";
				$sql .= "FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
			}
			$sql .= "FROM dc_stats_daily WHERE platform = '".$platform."'";
			//echo "SQL: $sql <br />";
			$stats_dc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats_dc_platform);
			$csv   .= fOutputCSV(&$stats_dc_platform);
		}
		
		$table .= "As of $datetime.";
	break;
	
	case "bids":// Bids
		// Initialize
		$stats_bids = array();
		$table = "";
		$csv = "";
		$sql = ""; 
		
		foreach($platforms as $platform){
			$table .= "<h3>".$PLATFORM_NAMES[$platform]." top $qty bids:</h3>";
			$csv .= $PLATFORM_NAMES[$platform] ."\r\n";
			$sql  = "SELECT FORMAT(Bid,4) as 'Bid Amount',";
			$sql .= "COUNT( CASE WHEN Region = 'us' THEN Bid END ) as 'Count US',";
			$sql .= "COUNT( CASE WHEN Region != 'us' THEN Bid END ) as 'Count Intl',";
			$sql .= "COUNT( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END ) as 'Redirects_US',";
			$sql .= "COUNT( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END ) as 'Redirects_Intl',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 4), ',', '') as 'Revenue_US',";
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 4), ',', '') as 'Revenue_Intl' ";
			} else {
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 4) as 'Revenue_US',";
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 4) as 'Revenue_Intl' ";
			}
			$sql .= "FROM (";
			//echo "DATES: |". sizeof($dates) ."|<br />";
			for ($i=0; $i < sizeof($dates); $i++) {
				if ($i != 0) $sql .= "UNION ALL ";
				$sql .= "SELECT s.platform as 'Platform',s.region as 'Region',b.bid as 'Bid',b.won as 'Won' ";
				$sql .= "FROM dc_".$dates[$i]."_sessions as s JOIN dc_".$dates[$i]."_bids as b USING(id) ";
			}
			$sql .= ") as results WHERE Platform = '$platform' GROUP BY Bid ORDER BY Redirects_US DESC LIMIT 0,$qty";
			//echo "SQL: $sql<br />";
			$stats_bids = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats_bids);
			$csv   .= fOutputCSV(&$stats_bids);
		}
	break;
	
	case "keywords":// Keywords
		$stats_keywords = array();	
		$table = "";
		$csv = "";
		$sql = ""; 
		
		foreach($platforms as $platform){
			$table .= "<h3>".$PLATFORM_NAMES[$platform]." top $qty keywords:</h3>";
			$csv .= $PLATFORM_NAMES[$platform] ."\r\n";
			$sql  = "SELECT Keywords,";
			$sql .= "COUNT(CASE WHEN Region = 'us' THEN Keywords END) as 'Queries_US',";
			$sql .= "COUNT(CASE WHEN Region != 'us' THEN Keywords END) as 'Queries_Intl',";
			$sql .= "COUNT(CASE WHEN Won > 0 AND Region = 'us' THEN Keywords END ) as 'Redirects_US',";
			$sql .= "COUNT(CASE WHEN Won > 0 AND Region != 'us' THEN Keywords END ) as 'Redirects_Intl',";
			$sql .= "FORMAT(( (COUNT(CASE WHEN Won > 0 AND Region = 'us' THEN Keywords END ) / COUNT(Keywords) ) * 100),2) as 'Take Rate % US',";
			$sql .= "FORMAT(( (COUNT(CASE WHEN Won > 0 AND Region != 'us' THEN Keywords END ) / COUNT(Keywords) ) * 100),2) as 'Take Rate % Intl',";
			$sql .= "FORMAT( (SUM(  CASE WHEN Won> 0 AND Region = 'us' THEN Bid END) ) / (COUNT(CASE WHEN Won > 0 THEN Keywords END )) , 4) as '";
			if ($platform == 'zc') $sql .= "RPR US',"; else $sql .= "PPC US',";
			$sql .= "FORMAT( (SUM(  CASE WHEN Won> 0 AND Region != 'us' THEN Bid END) ) / (COUNT(CASE WHEN Won > 0 THEN Keywords END )) , 4) as '";
			if ($platform == 'zc') $sql .= "RPR Intl',"; else $sql .= "PPC Intl',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 2), ',', '') as 'Revenue_US',";
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 2), ',', '') as 'Revenue_Intl' ";
			} else {
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 2) as 'Revenue_US',";
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 2) as 'Revenue_Intl' ";
			}
			$sql .= "FROM ( ";
			for ($i=0; $i < sizeof($dates); $i++) {
				if ($i != 0) $sql .= "UNION ALL ";
				$sql .= "SELECT s.id, s.platform as 'Platform', s.region as 'Region', s.keywords as 'Keywords', b.bid as 'Bid', b.won as 'Won' ";
				$sql .= "FROM dc_".$dates[$i]."_sessions as s JOIN dc_".$dates[$i]."_bids as b USING(id) ";
			}
			$sql .= ") as results WHERE Platform = '$platform' GROUP BY Keywords ORDER BY Queries_US DESC LIMIT 0,$qty";
			$stats_keywords = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats_keywords);
			$csv   .= fOutputCSV(&$stats_keywords);
		}
		break;
	
	case "domains":// Domains
		$stats = array();
		$table = "";
		$csv = "";
		$sql = ""; 
		
		foreach($platforms as $platform){
			$table .= "<h3>".$PLATFORM_NAMES[$platform]." top $qty domains:</h3>";
			$csv .= $PLATFORM_NAMES[$platform] ."\r\n";
			$sql  = "SELECT Domains,";
			$sql .= "COUNT(CASE WHEN Region = 'us' THEN Domains END) as 'Queries US', ";
			$sql .= "COUNT(CASE WHEN Region != 'us' THEN Domains END) as 'Queries Intl',";
			$sql .= "COUNT(CASE WHEN Won > 0 AND Region = 'us' THEN Domains END ) as 'Redirects US',";
			$sql .= "COUNT(CASE WHEN Won > 0 AND Region != 'us' THEN Domains END ) as 'Redirects Intl',";
			$sql .= "FORMAT(( (COUNT(CASE WHEN Won > 0 AND Region = 'us' THEN Domains END ) / COUNT(Domains) ) * 100),2) as 'Take Rate % US',";
			$sql .= "FORMAT(( (COUNT(CASE WHEN Won > 0 AND Region != 'us' THEN Domains END ) / COUNT(Domains) ) * 100),2) as 'Take Rate % Intl',";
			$sql .= "FORMAT( (SUM(  CASE WHEN Won> 0 AND Region = 'us' THEN Bid END) ) / (COUNT(CASE WHEN Won > 0 THEN Domains END )) , 4) as '";
			if ($platform == 'zc') $sql .= "RPR US',"; else $sql .= "PPC US',";
			$sql .= "FORMAT( (SUM(  CASE WHEN Won> 0 AND Region != 'us' THEN Bid END) ) / (COUNT(CASE WHEN Won > 0 THEN Domains END )) , 4) as '";
			if ($platform == 'zc') $sql .= "RPR Intl',"; else $sql .= "PPC Intl',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 2), ',', '') as 'Revenue_US',";
				$sql .= "REPLACE(FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 2), ',', '') as 'Revenue_Intl' ";
			} else {
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region = 'us' THEN Bid END) , 2) as 'Revenue_US',";
				$sql .= "FORMAT( SUM( CASE WHEN Won > 0 AND Region != 'us' THEN Bid END) , 2) as 'Revenue_Intl' ";
			}
			$sql .= "FROM ( ";
			for ($i=0; $i < sizeof($dates); $i++) {
				if ($i != 0) $sql .= "UNION ALL ";
				$sql .= "SELECT s.id, s.platform as 'Platform', s.region as 'Region', s.domain as 'Domains', b.bid as 'Bid', b.won as 'Won' ";
				$sql .= "FROM dc_".$dates[$i]."_sessions as s JOIN dc_".$dates[$i]."_bids as b USING(id) ";
			}
			$sql .= ") as results WHERE Platform = '$platform' GROUP BY Domains ORDER BY Revenue_US DESC LIMIT 0,$qty";
			//echo "SQL: |$sql|<br />";
			$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats);
			$csv   .= fOutputCSV(&$stats);
		}
	break;
	
	case "redirects":// Redirects
		$stats_redirects = array();
		$table = "";
		$csv = "";
		
		foreach($platforms as $platform){
			$table .= "<h3>".$PLATFORM_NAMES[$platform]." top $qty redirects:</h3>";
			$csv .= $PLATFORM_NAMES[$platform] ."\r\n";
			$sql  = "SELECT Domain,Feed,CONCAT('<a href=\"',Link,'\">Link</a>') as 'Redirect Path',COUNT(Link) as 'Redirects',";
			if ($output == 1) { // remove commas if outputting to CSV
				$sql .= "REPLACE(FORMAT(SUM(Bid),4), ',', '') as 'Revenue' ";
			} else {
				$sql .= "FORMAT(SUM(Bid),4) as 'Revenue' ";
			}
			$sql .= "FROM (";
			for ($i=0; $i < sizeof($dates); $i++) {
				if ($i != 0) $sql .= "UNION ALL ";
				$sql .= "SELECT s.domain as 'Domain', s.platform as 'Platform',b.feed as 'Feed',b.redirect as 'Link', b.won as 'Won', b.bid as 'Bid' ";
				$sql .= "FROM dc_".$dates[$i]."_sessions as s JOIN dc_".$dates[$i]."_bids as b USING(id) ";
			}
			$sql .= ") as results WHERE Won > 0 and Platform = '$platform' GROUP BY Link ORDER BY Revenue DESC LIMIT 0,$qty";
			$stats_redirects = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			$table .= fOutputTable(&$stats_redirects);
			$csv   .= fOutputCSV(&$stats_redirects);
		}
	break;
	
	case 'engine':
		//
	break;
	
	default: // Feeds
		$table  = "\n<form id=\"update\" name=\"update\" action=\"index$DEV.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
		$table .= "<table>\n\t";
		$access = $_SESSION['access_level'];
		$message = "";
		if ( $access == 10) {
			$table .= "<tr>\n\t\t<th>Feed</th>
				<th>ZeroClick</th>
				<th>Multiplier</th>
				<th>DirectClick (PPC)</th>
				<th>Multiplier</th>
				<th>AdClick (PPC)</th>
				<th>Multiplier</th>
			</tr>";
		} else {
			$table .= "<tr>\n\t\t<th>Feed</th>
				<th>ZeroClick</th>
				<th>DirectClick (PPC)</th>
				<th>AdClick (PPC)</th>
			</tr>";
		}
		
		if ($update == "1") { // Display Update Screen
			$feeds = array();
			$sql = "SELECT * FROM " . $TABLE_FEEDS . " ORDER BY feed_name ASC";
			$feeds = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
			
			for ($r = 0; $r < sizeof($feeds); ++$r) {
				if ($feeds[$r]['zc_active']) $zc_active = "1"; else $zc_active = "0";
				if ($feeds[$r]['dc_active']) $dc_active = "1"; else $dc_active = "0";
				if ($feeds[$r]['ac_active']) $ac_active = "1"; else $ac_active = "0";
				($zc_active == "1") ? $zc_ratio = number_format($feeds[$r]['zc_ratio'], 3) : $zc_ratio = "1.00";
				($dc_active == "1") ? $dc_ratio = number_format($feeds[$r]['dc_ratio'], 3) : $dc_ratio = "1.00";
				($ac_active == "1") ? $ac_ratio = number_format($feeds[$r]['ac_ratio'], 3) : $ac_ratio = "1.00";
				
				$table .= "<tr>\n";
				if ($r % 2 != 0) {
					$tint = "class=\"alt\"";
				} else {
					$tint = "";
				}
				$table .= "<td $tint id=\"left\">". ($r+1) . ") <a href=\"".$PATH."?feed=" . $feeds[$r]['feed_id'] . "\" >" . $feeds[$r]['feed_name'] . "</a></td>\n";
				// ZC Active drop down
				$table .= "<td $tint >";
					if ($zc_active) {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_zc_active\" id=\"".$feeds[$r]['feed_id']."_zc_active\" class=\"active\">\n";
						$table .= "<option value=\"1\" selected=\"selected\">Active</option>\n";
						$table .= "<option value=\"0\">Inactive</option>\n";
						$table .= "</select>";
					} else {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_zc_active\" id=\"".$feeds[$r]['feed_id']."_zc_active\" class=\"inactive\">\n";
						$table .= "<option value=\"1\" >Active</option>\n";
						$table .= "<option value=\"0\" selected=\"selected\">Inactive</option>\n";
						$table .= "</select>";
					}
				$table .= "</td>\n";
				$table .= "<td $tint >";
				$table .= "<input type=\"text\" name=\"".$feeds[$r]['feed_id']."_zc_ratio\" id=\"".$feeds[$r]['feed_id']."_zc_ratio\" value=\"$zc_ratio\" size=\"5\" />";
				$table .= "</td>\n";
				// DC Active drop down
				$table .= "<td $tint >";
					if ($dc_active) {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_dc_active\" id=\"".$feeds[$r]['feed_id']."_dc_active\" class=\"active\">\n";
						$table .= "<option value=\"1\" selected=\"selected\">Active</option>\n";
						$table .= "<option value=\"0\">Inactive</option>\n";
						$table .= "</select>";
					} else {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_dc_active\" id=\"".$feeds[$r]['feed_id']."_dc_active\" class=\"inactive\">\n";
						$table .= "<option value=\"1\" >Active</option>\n";
						$table .= "<option value=\"0\" selected=\"selected\">Inactive</option>\n";
						$table .= "</select>";
					}	
				$table .= "</td>\n";
				$table .= "<td $tint >";
				$table .= "<input type=\"text\" name=\"".$feeds[$r]['feed_id']."_dc_ratio\" id=\"".$feeds[$r]['feed_id']."_dc_ratio\" value=\"$dc_ratio\" size=\"5\" />";
				$table .= "</td>\n";
				// AC Active drop down
				$table .= "<td $tint >";
					if ($dc_active) {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_ac_active\" id=\"".$feeds[$r]['feed_id']."_ac_active\" class=\"active\">\n";
						$table .= "<option value=\"1\" selected=\"selected\">Active</option>\n";
						$table .= "<option value=\"0\">Inactive</option>\n";
						$table .= "</select>";
					} else {
						$table .= "<select name=\"".$feeds[$r]['feed_id']."_ac_active\" id=\"".$feeds[$r]['feed_id']."_ac_active\" class=\"inactive\">\n";
						$table .= "<option value=\"1\" >Active</option>\n";
						$table .= "<option value=\"0\" selected=\"selected\">Inactive</option>\n";
						$table .= "</select>";
					}	
				$table .= "</td>\n";
				$table .= "<td $tint >";
				$table .= "<input type=\"text\" name=\"".$feeds[$r]['feed_id']."_dc_ratio\" id=\"".$feeds[$r]['feed_id']."_dc_ratio\" value=\"$dc_ratio\" size=\"5\" />";
				$table .= "</td>\n";
				$table .= "</tr>\n";
			}
			$update = "2";
		} else { // Display summary
			if ($update == "2") {
				// process form
				$feeds = array();
				$sql = "SELECT feed_id FROM " . $TABLE_FEEDS . " ORDER BY feed_id ASC";
				$feeds = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);

				for ($i = 0; $i < sizeof($feeds); $i++) {
					$feed = $feeds[$i]['feed_id'];
					$sql = "UPDATE $TABLE_FEEDS SET ";
					$sql.= "zc_active='". $_POST[$feed . '_zc_active'] . "',";
					$sql.= "zc_ratio='". $_POST[$feed . '_zc_ratio'] . "',";
					$sql.= "dc_active='". $_POST[$feed . '_dc_active'] . "',";
					$sql.= "dc_ratio='". $_POST[$feed . '_dc_ratio'] . "' ";
					$sql.= "ac_active='". $_POST[$feed . '_ac_active'] . "',";
					$sql.= "ac_ratio='". $_POST[$feed . '_ac_ratio'] . "' ";
					$sql.= "WHERE feed_id = '$feed'";
					mysql_query($sql, $MYSQL_CONNECTION);
				}
				$message = "Feeds updated.";
			} else {
				$message = "";
			}
			$feeds = array();
			$sql = "SELECT * FROM " . $TABLE_FEEDS . " ORDER BY feed_name ASC";
			$feeds = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		
			for ($r = 0; $r < sizeof($feeds); ++$r) {
				if ($feeds[$r]['zc_active']) $zc_active = "active"; else $zc_active = "...";
				if ($feeds[$r]['dc_active']) $dc_active = "active"; else $dc_active = "...";
				if ($feeds[$r]['ac_active']) $ac_active = "active"; else $ac_active = "...";
				($zc_active == "active") ? $zc_ratio = number_format($feeds[$r]['zc_ratio'], 3) : $zc_ratio = "...";
				($dc_active == "active") ? $dc_ratio = number_format($feeds[$r]['dc_ratio'], 3) : $dc_ratio = "...";
				($ac_active == "active") ? $ac_ratio = number_format($feeds[$r]['ac_ratio'], 3) : $ac_ratio = "...";
				
				$table .= "<tr>\n";
				if ($r % 2 != 0) {
					$tint = "class=\"alt\"";
				} else {
					$tint = "";
				}
				$table .= "<td $tint id=\"left\">" . ($r+1) . ") <a href=\"".$PATH."?feed=" . $feeds[$r]['feed_id'] . "\" >" . $feeds[$r]['feed_name'] . "</a></td>\n";
				$table .= "<td $tint >$zc_active</td>\n";
				if ($access == 10) $table .= "<td $tint >$zc_ratio</td>\n";
				$table .= "<td $tint >$dc_active</td>\n";
				if ($access == 10) $table .= "<td $tint >$dc_ratio</td>\n";
				$table .= "<td $tint >$ac_active</td>\n";
				if ($access == 10) $table .= "<td $tint >$ac_ratio</td>\n";
				$table .= "</tr>\n";
			}
			$update = "1";
		}
		$table .= "<tr>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "</td>\n";
		$table .= "<td>\n";
		$table .= "$message ";
		$table .= "<input name=\"update\" type=\"hidden\" value=\"$update\" />\n";
		$table .= "<input type=\"submit\" name=\"update_button\" id=\"update_button\" value=\"Update\" />\n";
		$table .= "</td>\n";
		$table .= "</tr>\n";
		$table .= "\n\t</table>\n";
		$table .= "</form>\n";
	break;		
}

if($output == 1) { // downloading

	$filename = "dc_".$view."_".$date.".csv";

	// Open file handlers
	!$handle = fopen($filename, 'w');
	fwrite($handle, $csv);
	fclose($handle);
	
	// Send Headers
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Length: ". filesize("$filename").";");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Type: application/octet-stream; "); 
	header("Content-Transfer-Encoding: binary");
	
	echo $csv; // to output to browser
	exit();
} else { // displaying
	echo $table;
}


