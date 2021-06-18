<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Top Traffic Source | 7Search Test</title>

<link href="7search.css" rel="stylesheet" type="text/css">

</head>

<body>

<?php

require("ping/config.php");
require("ping/functions.php");

// Set up array to handle bid results
$results = array();

(isset($_POST['referer']) ) ? $results['referer'] = $_POST['referer'] : $results['referer'] = urlencode($_SERVER['HTTP_REFERER']);
(isset($_POST['agent']) ) ? $results['agent'] = $_POST['agent'] : $results['agent'] = urlencode($_SERVER['HTTP_USER_AGENT']);
(isset($_POST['ip']) ) ? $results['ip'] = $_POST['ip'] : $results['ip'] = urlencode($_SERVER['REMOTE_ADDR']);
(isset($_POST['domain']) ) ? $results['domain'] = $_POST['domain'] : $results['domain'] = urlencode($_GET['domain']);
$results['keywords'] = urlencode($_POST['keySearch']);

// Retrieve GETS
$results['feed'] = "7search";
$date = $_GET['date'];
$results['time'] = $_GET['time'];
//$results['referer'] = $_GET['referer'];
//$results['domain'] = $_GET['domain'];
$results['url'] = $_GET['url'];
$results['id'] = $_GET['session_id'];
//$results['agent'] = $_GET['agent'];
$results['region'] = $_GET['locale'];
//$results['ip'] = $_GET['ip'];

//$trace = $_GET['trace'];
$trace = 1;
$cookies_enabled = $_GET['cookies'];
$js_enabled = $_GET['js'];

if ($cookies_enabled == "true") $results['status'] .= "cookies_on, ";
if ($js_enabled  == "true") $results['status'] .= "javascript_on, ";
if ($_GET['botflag'] == "1") $results['status'] .= "bot, ";
if ($_GET['blackflag'] == "1") $results['status'] .= "blacklisted, ";

if (isset($_POST['keySearch'])) {
	
	$results['keywords'] = urlencode($_POST['keySearch']);

	// Get bid results from xml
	$results['xml_feed'] = fBuildFeed(&$results, $date, $cookies);
	$xml = simplexml_load_file($results['xml_feed']);
	
	fParseFeed(&$results, &$xml); // parse feed to get bids if any
	
	// Prepare Output
	$txt = "<div id='sponsorAds'>\n";				
	$txt .= "<div class='row'>\n";
	$txt .= "<div class='redirectURL'><a href='" . $results['xml_redirect'] . "'>" . $results['xml_title'] . "</a></div>\n";
	$txt .= "<div class='description'>" . $results['xml_description'] . "</div>\n";
	$txt .= "<div class='sponsorURL'><a href='" . $results['xml_redirect'] . "'>" . $results['xml_url'] . "</a></div>\n";
	$txt .= "</div>\n";
	$txt .= "</div>\n";
	$txt .=  "<p><a href=\"7search.php\" target=\"_self\" >Again...</a></p>\n";
	
/*
	echo "FEED:" . $results['xml_feed']. "<br />";
	echo "AGENT: " . $results['agent'] . "<br />";
	echo "REFERER: " . $results['referer'] . "<br />";
	echo "IP: " . $results['ip'] . "<br />";
	echo "SEARCH TERMS: ".$results['keywords']."<br />";
	echo "BID: ".$results['xml_bid']."<br />";
	echo "REDIRECT: ".$results['xml_redirect']."<br />";
	echo "TITLE: ".$results['xml_title']."<br />";
	echo "DESCRIPTION: ".$results['xml_description']."<br />";
	echo "HTTPLINK: ".$results['xml_url']."<br />";*/
	
	echo $txt;
} else {
?>



<form id="keySearch" name="keySearch" method="post" action="7search.php">
	<input type="hidden" name="referer" value="<?=$results['referer']?>" />
	<input type="hidden" name="agent" value="<?=$results['agent']?>" />
	<input type="hidden" name="ip" value="<?=$results['ip']?>" />
	<input type="hidden" name="domain" value="youtubu.com" />
	<input name="keySearch" type="text" id="keySearch" value="Enter search term..." size="20" />
	<input type="submit" value="Search" />
</form>

<br />

<?	
}



//end

?>
</body>
</html>