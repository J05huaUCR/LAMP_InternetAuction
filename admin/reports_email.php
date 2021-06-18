<?php

require("config.php");

$trace = $_GET['trace'];

/* SET DATE(S) */
$DAY_UNIT = (1 * 24 * 60 * 60);
$TODAY = time();
$YESTERDAY = time() - $DAY_UNIT;

$YEAR = (int) date("Y", $YESTERDAY); // Get the current year
$MONTH = (int) date("n", $YESTERDAY); // Get the current month
$MONTH_SQL = (int) date("m", $YESTERDAY); // Get the current month

$date_YMD = date("Y-m-d", $YESTERDAY); // need day before
$date_slash = date("F j, Y", $YESTERDAY); // need day before

// SET UP DATE ARRAY
$MONTH_DAYS = array ();
$MONTH_DAYS[0] = "";
$MONTH_DAYS[1]['month'] = "January";$MONTH_DAYS[1]['days'] = "31";
$MONTH_DAYS[2]['month'] = "February";$MONTH_DAYS[2]['days'] = "28";
$MONTH_DAYS[3]['month'] = "March";$MONTH_DAYS[3]['days'] = "31";
$MONTH_DAYS[4]['month'] = "April";$MONTH_DAYS[4]['days'] = "30";
$MONTH_DAYS[5]['month'] = "May";$MONTH_DAYS[5]['days'] = "31";
$MONTH_DAYS[6]['month'] = "June";$MONTH_DAYS[6]['days'] = "30";
$MONTH_DAYS[7]['month'] = "July";$MONTH_DAYS[7]['days'] = "31";
$MONTH_DAYS[8]['month'] = "August";$MONTH_DAYS[8]['days'] = "31";
$MONTH_DAYS[9]['month'] = "September";$MONTH_DAYS[9]['days'] = "30";
$MONTH_DAYS[10]['month'] = "October";$MONTH_DAYS[10]['days'] = "31";
$MONTH_DAYS[11]['month'] = "November";$MONTH_DAYS[11]['days'] = "30";
$MONTH_DAYS[12]['month'] = "December";$MONTH_DAYS[12]['days'] = "31";

if ( ( ($YEAR % 4 === 0) && ($YEAR % 100 !== 0) ) || ( ($YEAR % 100 === 0) && ($YEAR % 400 === 0) ) ) { // Leap Year Test
	$MONTH_DAYS[2]['days'] = "29"; // change number of days in February is 29
}

// test for stats table
$TABLES = array();
if (fCheckTableExists($TABLE_STATS, $MYSQL_CONNECTION) ) $TABLES['stats'] = "Present"; else $TABLES['stats'] = "Missing";
if (fCheckTableExists($TABLE_FEEDS, $MYSQL_CONNECTION) ) $TABLES['feeds'] = "Present"; else $TABLES['feeds'] = "Missing";
if (fCheckTableExists($TABLE_USERS, $MYSQL_CONNECTION) ) $TABLES['users'] = "Present"; else $TABLES['users'] = "Missing";

// get list of emails /feeds to send
$sql = "SELECT f.feed_id as feed_id, f.feed_name as feed, u.first_name as first_name,u.last_name as last_name,u.email as email FROM $TABLE_USERS as u JOIN $TABLE_FEEDS as f USING (feed_id) WHERE u.receive_reports >= 0";
$email_list = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);

for ($i = 0; $i < sizeof($email_list); $i++){
	$feed_id = $email_list[$i]['feed_id'];
	$feed_name = $email_list[$i]['feed'];
	$first_name = $email_list[$i]['first_name'];
	$last_name = $email_list[$i]['last_name'];
	$email = $email_list[$i]['email'];
	
	// Begin generating mesasge
	$message = '
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>DirectClick&reg; | Reporting for $date_slash</title>
	<style>
		html, body {font-family: Tahoma, Arial, Helvetica, sans-serif;font-size: 12px;color: #000;margin:10px;line-height:20px;}
		.container_email {width: 550px;margin: 0 auto 0 auto;}
		table{width:100%;border-spacing: 0px;border-collapse: collapse;z-index:2;}
		th {font-weight:bold;width:auto;text-align: center;vertical-align:text-top;padding: 2px 10px 0 10px;background: #DDD;border-bottom: 1px solid #BBB;-moz-border-radius: 10px 10px 0px 0px;-webkit-border-radius: 10px 10px 0px 0px;border-radius: 10px 10px 0px 0px;}
		td {width:auto;text-align:right;vertical-align:text-top;border-bottom: 1px solid #BBB;background: #FFF;padding: 2px 10px 0 10px;}
		td.left {text-align: left;}
		td.alt {background: #EEE;}
		tr.alt {background: #EEE;}
		h3 {font-size: 16px;}
	</style>
	</head>
	
	<body>
		<div class="container_email">
			<div class="content_wrap">';
	$message .= '
				<h3>Stats for '.$date_slash.'</h3>';
				// Process the day before
				$sql  = "SELECT IF(platform = 'zc', 'ZeroClick', 'DirectClick') as 'Platform',";
				$sql .= "SUM(num_bids) as '# Bids',SUM(num_bids_won) as '# Redirects',";
				$sql .= "FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $',FORMAT(SUM(rev_us+rev_intl),2)  as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$date_YMD' and stats_date <= '$date_YMD' ";
				$sql .= "GROUP BY platform UNION ";
				$sql .= "SELECT '','','','','Total',FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$date_YMD' and stats_date <= '$date_YMD' GROUP BY feed";
				$data = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
				$message .= fOutputTable(&$data, $trace = 0, $rank = 0);
	$message .= '		
				<h3>Stats for the month of '.$MONTH_DAYS[$MONTH]['month'].'</h3>';
				// Process the day before
				$sql  = "SELECT IF(platform = 'zc', 'ZeroClick', 'DirectClick') as 'Platform',";
				$sql .= "SUM(num_bids) as '# Bids',SUM(num_bids_won) as '# Redirects',";
				$sql .= "FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $',FORMAT(SUM(rev_us+rev_intl),2)  as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$YEAR-$MONTH_SQL-01' and stats_date <= '$date_YMD' ";
				$sql .= "GROUP BY platform UNION ";
				$sql .= "SELECT '','','','','Total',FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$YEAR-$MONTH_SQL-01' and stats_date <= '$date_YMD' GROUP BY feed";
				$data = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
				$message .= fOutputTable(&$data, $trace = 0, $rank = 0);
	$message .= '		
				<h3>Stats for the year of '.$YEAR.' to '.$date_slash.'</h3>';
				// Process the day before
				$sql  = "SELECT IF(platform = 'zc', 'ZeroClick', 'DirectClick') as 'Platform',";
				$sql .= "SUM(num_bids) as '# Bids',SUM(num_bids_won) as '# Redirects',";
				$sql .= "FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $',FORMAT(SUM(rev_us+rev_intl),2)  as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$YEAR-01-01' and stats_date <= '$date_YMD' ";
				$sql .= "GROUP BY platform UNION ";
				$sql .= "SELECT '','','','','Total',FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
				$sql .= "FROM $TABLE_STATS WHERE feed = '$feed_id' and stats_date >= '$YEAR-01-01' and stats_date <= '$date_YMD' GROUP BY feed";
				$data = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
				$message .= fOutputTable(&$data, $trace = 0, $rank = 0);

	$message .= '
				</div><!--/content_wrap-->
			<br clear="all" />
		</div><!--/container-->
	</body>
	</html>
				';	
	fMailDailyReport($date_slash, $feed_name, $email, $first_name, $last_name, $message);
	
	if ($trace) {
		echo "Feed: $feed_name, id=$feed_id<br />";
		echo "Name: $first_name $last_name<br />";
		echo "Email: $email<br />";
		echo "SQL: $sql <br />";
	}
}
if ($trace) {
	echo "YM: $YEAR$MONTH <br />";
	echo "DATE = $date_YMD<br />";
	echo "SIZE: " . sizeof($MONTH_DAYS) . "<br />";
	echo "<br /><br />";
	echo "Done. <br />";
}


?>