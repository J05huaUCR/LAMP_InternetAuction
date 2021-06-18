<?php

require("config_dev.php");

session_set_cookie_params(14400); 
session_start(); // Session Timing

// check for passed in 
$datetime = date("F j, Y  g:m:sa", time()); // current datetime
$date_format = date("Y-m-d", time()); // current datetime
$month = date("m", time()); // current month with leading zero
$year = date("Y", time()); // get current year
(isset($_GET['days']))  ? $days = $_GET['days'] : $days= 0;
(isset($_GET['view']))  ? $view = $_GET['view'] : $view= '';
(isset($_GET['col']))   ? $column = $_GET['col'] : $column = 1;
(isset($_GET['sort']))  ? $sort = $_GET['sort']   : $sort = '';
(isset($_GET['trace'])) ? $TRACE = $_GET['trace'] : $TRACE = '';
(isset($_POST['update']) ) ? $update = $_POST['update'] : $update = 0;
$days_key = "?days=" . $days;
$date = fGetTime($days);
$revenue = 0;
$table_sessions = $TABLE_PREFIX . $date . $TABLE_SESSIONS_POSTFIX;
$table_bids = $TABLE_PREFIX . $date . $TABLE_BIDS_POSTFIX;
$table_join = "$table_sessions as s JOIN $table_bids as b USING(id)";

$header = "";
$header .= "<a href=\"".$PATH."$days_key&view=feeds\">Feeds</a><br />" ;
$header .= "Domains<br />";
$header .= "<a href=\"".$PATH."?view=domains_keywords\">Domain /Keywords </a><br />" ;

/* For AJAX reports */
if ($view == '') {
	$view = 'Reports';
	$default = " onload=\"showStats(0)\"";	
}

include($TEMPLATE_PATH."head$DEV.php");

if ($_SESSION['email']) { // Logged In
	if ($_POST['email'] == "logout") { // logging out
		$_SESSION = array(); // Unset all of the session variables.
		if (ini_get("session.use_cookies")) {
			 $params = session_get_cookie_params();
			 setcookie(session_name(), '', time() - 42000,
				  $params["path"], $params["domain"],
				  $params["secure"], $params["httponly"]
			 );
		}
		session_destroy(); // Finally, destroy the session and logout.
		$error_msg = "You have logged out.";
		include($TEMPLATE_PATH."login.php");
		header("Location: index$DEV.php");  // Reload
		exit();
	} else {
		$inactive = 14400; // set timeout period in seconds (4 hours)
		if( isset($_SESSION['timeout']) ) { // check to see if $_SESSION['timeout'] is set
			$session_life = time() - $_SESSION['timeout'];
			if($session_life > $inactive) { // session expired
				session_destroy(); 
				header("Location: index$DEV.php");  // Reload
				exit();
			}
		} else {
			$_SESSION['timeout'] = time();
		} 
	}
} else { // Not Logged in
	if ( isset($_POST['email']) && !empty($_POST['email']) && $_POST['email'] != '') { // attempt login verification
		$email = $_POST['email'];
		$password = $_POST['password'];
		$login = $_POST['login'];
		$sql = "SELECT * FROM " . $TABLE_USERS . " WHERE email='$email' AND password='$password'";
		$user_info = fQuery_Row_Assoc($sql);
		if (sizeof($user_info) > 1 && $user_info != -1) {
			$error_code = 0;
			foreach ($user_info as $key => $value) {
				$_SESSION[$key] = $user_info[$key]; // set up session	
			}
		} else {
			$error_code = -1; // failed to login
			if ($error_code != 0) {
				$error_msg = "LOGIN FAILED!";
			}
			include($TEMPLATE_PATH."login.php");
			exit();
		}
	} else {
		if ($error_code != 0) {
			$error_msg = "LOGIN FAILED!";
		}
		include($TEMPLATE_PATH."login.php");
		exit();
	}
}

$feeds = array();
$sql = "SELECT * FROM " . $TABLE_FEEDS . " ORDER BY feed_name ASC";
$feeds = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);

include($TEMPLATE_PATH."header$DEV.php");

$table = "";
switch($view) {		
	case "overview": // Overview
		$zc_count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='zc' ", $MYSQL_CONNECTION);
		$dc_count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='dc' ", $MYSQL_CONNECTION);
		$ac_count = fQuery_Single("SELECT COUNT(id) FROM $table_sessions WHERE platform='ac' ", $MYSQL_CONNECTION);
		$update_time = fQuery_Single("SELECT update_time FROM $TABLE_STATS WHERE stats_date='$date_format' ", $MYSQL_CONNECTION);
		$stats_datetime = date("F j, Y  g:m:sa", $update_time); // current datetime
		$total = $zc_count + $dc_count + $ac_count;
	
		$table .= "<h3>ZeroClick&trade; has $zc_count session(s) out of $total total.</h3>";
		$stats_zc_platform = array();
		$sql  = "SELECT feed as 'Feed', resp_us as 'US', resp_intl as 'Intl', (resp_us + resp_intl) as 'Total',num_bids_won as 'Wins', ";
		$sql .= "FORMAT(bid_max,6) as 'Max Bid',FORMAT(bid_min,6) as 'Min Bid',FORMAT(bid_avg,6) as 'Avg Bid',FORMAT(bid_median,6) as 'Median', ";
		$sql .= "FORMAT(resp_fastest,6) as 'Fastest', FORMAT(resp_slowest,6) as 'Slowest',";
		$sql .= "FORMAT(resp_avg,6) as 'Avg Time',FORMAT(rev_us,2) as 'US $', FORMAT(rev_intl,2) as 'Intl $',FORMAT(rev_us + rev_intl, 2) as 'Total $'";
		$sql .= "FROM $TABLE_STATS ";
		$sql .= "WHERE stats_date ='$date' and platform='zc'";
		$stats_zc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_zc_platform);
		
		flush(); @ob_flush(); 
		
		$table .= "<h3>DirectClick&trade; has $dc_count session(s) out of $total total.</h3>";
		$stats_dc_platform = array();
		$sql  = "SELECT feed as 'Feed', resp_us as 'US', resp_intl as 'Intl',(resp_us + resp_intl) as 'Total',num_bids_won as 'Clicks', ";
		$sql .= "FORMAT(bid_max,6) as 'Max Bid',FORMAT(bid_min,6) as 'Min Bid',FORMAT(bid_avg,6) as 'Avg Bid', FORMAT(bid_median,6) as 'Median', ";
		$sql .= "FORMAT(resp_fastest,6) as 'Fastest', FORMAT(resp_slowest,6) as 'Slowest',";
		$sql .= "FORMAT(resp_avg,6) as 'Avg Time',FORMAT(rev_us,2) as 'US $', FORMAT(rev_intl,2) as 'Intl $', FORMAT(rev_us + rev_intl, 2) as 'Total $'";
		$sql .= "FROM $TABLE_STATS ";
		$sql .= "WHERE stats_date ='$date' and platform='dc'";
		$stats_dc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_dc_platform);
		
		$table .= "<h3>AdClick&trade; has $ac_count session(s) out of $total total.</h3>";
		$stats_dc_platform = array();
		$sql  = "SELECT feed as 'Feed', resp_us as 'US', resp_intl as 'Intl',(resp_us + resp_intl) as 'Total',num_bids_won as 'Clicks', ";
		$sql .= "FORMAT(bid_max,6) as 'Max Bid',FORMAT(bid_min,6) as 'Min Bid',FORMAT(bid_avg,6) as 'Avg Bid', FORMAT(bid_median,6) as 'Median', ";
		$sql .= "FORMAT(resp_fastest,6) as 'Fastest', FORMAT(resp_slowest,6) as 'Slowest',";
		$sql .= "FORMAT(resp_avg,6) as 'Avg Time',FORMAT(rev_us,2) as 'US $', FORMAT(rev_intl,2) as 'Intl $', FORMAT(rev_us + rev_intl, 2) as 'Total $'";
		$sql .= "FROM $TABLE_STATS ";
		$sql .= "WHERE stats_date ='$date' and platform='ac'";
		$stats_dc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_dc_platform);
		
		flush(); @ob_flush(); 
		
		$table .= "Updated at $stats_datetime.";
		
		flush(); @ob_flush(); 
	break;
	
	case "revenue": // Revenue
		$sql  = "SELECT 'Month-To-Date' as Period, FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $', FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
		$sql .= "FROM dc_stats_daily WHERE stats_date >= '$year-$month-01' UNION ";
		$sql .= "SELECT 'Year-To-Date' as Period, FORMAT(SUM(rev_us),2) as 'US $', FORMAT(SUM(rev_intl),2) as 'Intl $', FORMAT(SUM(rev_us+rev_intl),2) as 'Total $' ";
		$sql .= "FROM dc_stats_daily";
		$stats_dc_platform = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_dc_platform);
		$table .= "As of $datetime.";
	break;
	
	case "bids":// Bids
		$stats_bids = array();
		$sql = "SELECT bid, COUNT(bid) as qty FROM ". $table_bids." WHERE won > 0 GROUP BY bid ORDER BY qty DESC";
		$stats_bids = fQuery_Multiple_Rows($sql);
		
		$stats_median = array();
		$sql = "SELECT bid FROM ". $table_bids ." WHERE won > 0 GROUP BY bid ORDER BY bid DESC";
		$stats_median = fQuery_Multiple_Rows($sql);
		$median_bid = fGetMedian(&$stats_median);
		
		$table = "<table>\n\t";
		$table .= "<tr>
				<th>Rank</th>
				<th>Bid Amount</th>
				<th>Count</th>
				<th>Revenue</th>
			</tr>";
		for ($r = 0; $r < sizeof($stats_bids); ++$r) {
			$table .= "<tr>\n";
			if ($r % 2 != 0) {
				$tint = " class=\"alt\"";
			} else {
				$tint = "";
			}
			$table .= "<td".$tint.">". ($r + 1) ."</td>\n"; // rank
			
			foreach($stats_bids[$r] as $key => $value) {
				$table .= "<td".$tint.">".urldecode($value)."</td>\n";
			}
			$table .= "<td".$tint.">". ((float) $stats_bids[$r][0] * (float) $stats_bids[$r][1]) ."</td>\n";
			$table .= "</tr>\n";	
		}
		$table .= "<tr>
				<td></td>
				<td>Median Bid: $ $median_bid</td>
				<td></td>
				<td></td>
			</tr>";	
		$table .= "\n\t</table>\n";	
	break;
	
	case "keywords":// Keywords
		$stats_keywords = array();
		
		$table = "<h3>ZeroClick&trade; top 100 keywords in the US:</h3>";
		
		$sql  = "SELECT s.keywords as Keywords, COUNT(s.keywords) as Qty, SUM(b.bid) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'zc' AND s.region = 'US'";
		$sql .= "GROUP BY s.keywords ";
		$sql .= "ORDER BY Qty DESC LIMIT 0,100";
		
		$stats_keywords = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_keywords);
		
		$table .= "<h3>ZeroClick&trade; top 100 keywords outside the US:</h3>";
		
		$sql  = "SELECT s.keywords as Keywords, COUNT(s.keywords) as Qty, SUM(b.bid) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'zc' AND s.region <> 'US'";
		$sql .= "GROUP BY s.keywords ";
		$sql .= "ORDER BY Qty DESC LIMIT 0,100";
		
		$stats_keywords = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_keywords);
		
		$table .= "<h3>DirectClick&trade; top 100 keywords in the US:</h3>";
		
		$sql  = "SELECT s.keywords as Keywords, COUNT(s.keywords) as Qty, SUM(b.bid) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'dc' AND s.region = 'US'";
		$sql .= "GROUP BY s.keywords ";
		$sql .= "ORDER BY Qty DESC LIMIT 0,100";
		
		$stats_keywords = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_keywords);
		
		$table .= "<h3>DirectClick&trade; top 100 keywords outside the US:</h3>";
		
		$sql  = "SELECT s.keywords as Keywords, COUNT(s.keywords) as Qty, SUM(b.bid) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'dc' AND s.region <> 'US'";
		$sql .= "GROUP BY s.keywords ";
		$sql .= "ORDER BY Qty DESC LIMIT 0,100";
		
		$stats_keywords = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats_keywords);
		break;
	
	case "domains":// Domains
		$stats = array();
		
		$table = "<h3>ZeroClick&trade; top 100 domains in the US:</h3>";
		
		$sql  = "SELECT s.domain as Domain, COUNT(b.bid) as '#Bids', FORMAT(SUM(b.bid),2) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'zc' AND s.region = 'US' and b.won > 0 ";
		$sql .= "GROUP BY Domain ORDER BY Revenue DESC LIMIT 0,100";

		$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats);
		
		$table .= "<h3>ZeroClick&trade; top 100 domains outside the US:</h3>";
		
		$sql  = "SELECT s.domain as Domain, COUNT(b.bid) as '#Bids', FORMAT(SUM(b.bid),2) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'zc' AND s.region <> 'US' and b.won > 0 ";
		$sql .= "GROUP BY Domain ORDER BY Revenue DESC LIMIT 0,100";
		
		$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats);
		
		$table .= "<h3>DirectClick&trade; top 100 domains in the US:</h3>";
		
		$sql  = "SELECT s.domain as Domain, COUNT(b.bid) as '#Bids', FORMAT(SUM(b.bid),2) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'dc' AND s.region = 'US' and b.won > 0 ";
		$sql .= "GROUP BY Domain ORDER BY Revenue DESC LIMIT 0,100";
		
		$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats);
		
		$table .= "<h3>DirectClick&trade; top 100 domains outside the US:</h3>";
		
		$sql  = "SELECT s.domain as Domain, COUNT(b.bid) as '#Bids', FORMAT(SUM(b.bid),2) as Revenue ";
		$sql .= "FROM $table_join WHERE s.platform = 'dc' AND s.region <> 'US' and b.won > 0 ";
		$sql .= "GROUP BY Domain ORDER BY Revenue DESC LIMIT 0,100";
		
		$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table .= fOutputTable(&$stats);
	break;
	
	case "redirects":// Redirects
		$stats_redirects = array();
		$sql = "SELECT 
					s.domain as 'Domain',
					b.redirect as 'Redirect Path',
					COUNT(b.redirect) as '# Redirects', 
					FORMAT(SUM(b.bid),2) as Revenue 
					FROM $table_sessions s JOIN $table_bids b USING(id)
					WHERE b.won > 0 
					GROUP BY redirect ORDER BY revenue DESC";

		$stats_redirects = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table = fOutputTable(&$stats_redirects);
	break;
	
	case 'engine':
		//
	break;
	
	case 'feeds': // Feeds
		$table  = "\n<form id=\"update\" name=\"update\" action=\"index$DEV.php?view=feeds\" method=\"post\" enctype=\"multipart/form-data\">\n";
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
	
	case 'domains_keywords': // Domain Keywords Pairings
		$sql = "SELECT domain, key1 FROM $TABLE_KEYWORDS_DOMAIN WHERE domain LIKE '%face%' ORDER BY domain ";
		$stats = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION);
		$table = fOutputTable(&$stats,0,0);
	break;
	
	default:
		//
	break;
}
echo $table;


include($TEMPLATE_PATH."foot.php");

