<?php

require("config.php");

// Passed in
$days = $_GET['days']; // as int
$time_frame = $_GET['span']; // as int
$keys = urldecode($_GET['keys']); // as an array
echo "KEYWORDS: ", $keys, "<br />";

/* need to parse $keys into individual words */
$keywords = array();
$keywords = explode(' ', $keys);
echo "SIZE OF KEYWORDS ARRAY: ", sizeof($keywords), "<br />";

// defaults if not passed in
if (!isset($days)) $days = 0;
if (!isset($time_frame)) $time_frame = 7; 
if (!isset($keys)) $keys = -1; // to view all keywords in db

$dates = array();
for ($d = 0; $d < $time_frame; ++$d) {
	$dates[] = fGetTime($days + $d);
}
echo "SIZE OF DATES ARRAY: ", sizeof($dates), "<br />";

$feeds = array();
$feeds = fQuery_Array("SELECT DISTINCT feed FROM " . $TABLE_PREFIX . $dates[0] . " ORDER BY feed ASC");

echo "SIZE OF FEEDS ARRAY: ", sizeof($feeds), "<br />";

$results = array();

flush(); @ob_flush();  ## make sure that all output is sent in real-time


$sql = "SELECT SUM(xml_bid) as revenue, COUNT(xml_bid) as redirects FROM (
SELECT * FROM Tracking_Bids_20120209 WHERE feed = 'admarketplace' and won > 0 AND domain LIKE '%advanceconsolidation%'
) as test";
$results = fQuery_Row_Assoc($sql);
echo "SIZE OF RESULTS ARRAY: ", sizeof($results), "<br />";

/*
for ($i = 0; $i < sizeof($keywords); ++$i) { // keywords
	for ($j = 0; $j < sizeof($time_frame); ++$j) { // date
		for ($k = 0; $k < sizeof($feeds); ++$k) { // Feeds
			$sql = "SELECT SUM(xml_bid) as revenue, COUNT(xml_bid) as redirects FROM (
				SELECT * FROM Tracking_Bids_20120209 WHERE feed = 'admarketplace' and won > 0 AND domain LIKE '%advanceconsolidation%'
			) as test";
			$results = fQuery_Row_Assoc($sql);
		}
	}
}*/

$revenue = 0;
/*
$feeds = array();
$stats = array();
$feeds = fQuery_Array("SELECT DISTINCT feed FROM " . $TABLE_PREFIX . $date . " ORDER BY feed ASC");
foreach ($feeds as $feed) {
	$stats[$date][] = fQuery_Multiple("SELECT '". $feed ."', 
		(SELECT COUNT(DISTINCT time) as total FROM ". $TABLE_PREFIX . $date.") as total,
		max(xml_bid) as max_bid, 
		(SELECT min(xml_bid) as min FROM ". $TABLE_PREFIX . $date." WHERE xml_bid > 0) as min_bid,
		avg(xml_bid) as avg_bid,
		(SELECT COUNT(xml_bid) as bids FROM ". $TABLE_PREFIX . $date." WHERE feed = '". $feed ."' AND xml_bid > 0) as num_bids,
		(SELECT COUNT(xml_bid) as won FROM ". $TABLE_PREFIX . $date." WHERE feed = '". $feed ."' AND won > 0) as num_won,
		(SELECT avg(xml_bid) as avg_win FROM ". $TABLE_PREFIX . $date." WHERE feed = '". $feed ."' AND won > 0) as avg_win_bid,
		min(time_elapsed) as fastest_response,
		max(time_elapsed) as slowest_response,
		avg(time_elapsed) as average_response,
		(SELECT SUM(xml_bid) FROM ". $TABLE_PREFIX . $date." WHERE feed = '". $feed ."' AND won > 0) as revenue
		FROM ". $TABLE_PREFIX . $date." WHERE feed = '". $feed ."'");
}

// Keywords
$stats_keywords = array();
$sql = "SELECT keywords, COUNT(keywords) as qty FROM ". $TABLE_PREFIX . $date." GROUP BY keywords ORDER BY qty DESC";
$stats_keywords = fQuery_Multiple_Array($sql);
*/
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DirectClick | Admin | Past Week</title>
<link rel="stylesheet" href="templates/default/styles/admin_styles.css" type="text/css" media="screen" />
<!--<link rel="stylesheet" href="admin_menu.css" type="text/css" media="screen" />-->

<!--[if IE 6]>
<style>
body {behavior: url("csshover3.htc");}
#menu li .drop {background:url("img/drop.gif") no-repeat right 8px; 
</style>
<![endif]-->

</head>

<body>
<div class="container">
	<div class="content_wrap">
		<table>
			<tr>
				<th>Date</th>
				<th>Feed</th>
				<th>Total</th>
				<th>Max Bid</th>
				<th>Min Bid</th>
				<th>Avg Bid</th>
				<th># Bids</th>
				<th># Won</th>
				<th>% Won</th>
				<th>Avg Win Bid</th>
				<th>Fastest</th>
				<th>Slowest</th>
				<th>Average Time</th>
				<th>Revenue</th>
			</tr>
			<?
			/*
				for ($r = 0; $r < sizeof($stats[$date]); ++$r) {
					print "<tr>\n";
					if ($r % 2 != 0) {
						$tint = " class=\"alt\"";
					} else {
						$tint = "";
					}
					print "<td".$tint.">".$date."</td>\n";
					for ($c = 0; $c < 12; ++$c) {
						if (isset($stats[$date][$r][$c])) {
							if ( is_numeric($stats[$date][$r][$c])) {
								print "<td".$tint.">" .round($stats[$date][$r][$c], 6) . "</td>\n";
							} else {
								print "<td".$tint.">" . $stats[$date][$r][$c] . "</td>\n";
							}
						} else {
							print "<td".$tint.">0</td>\n";
						}
						if ($c == 6) print "<td".$tint.">" .round((($stats[$date][$r][6] / $stats[$date][$r][5]) * 100), 2) . "%</td>\n";
					}
					$revenue += $stats[$date][$r][11];
					print "</tr>\n";	
				}
				$revenue = round($revenue, 2);*/
			?>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>Total Revenue:</td>
				<td>$ <?=$revenue?></td>
			</tr>
		</table>
		<br clear="all" />
	</div><!--/content_wrap-->
	<br clear="all" />
</div><!--/container-->

</body>

</html>