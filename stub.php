<?php

//require("ping/config.php");
//require("ping/functions.php");

$MYSQL_CONNECTION = mysql_connect("localhost", "dclick", "ClickMe1300!") or die( mysql_error() ); 
mysql_select_db("DClick", $MYSQL_CONNECTION) or die(mysql_error());

$results = array();
$results['feed'] = $_GET['feed'];
$results['time'] = $_GET['time'];
$results['referer'] = urlencode("http://www.google.com/?q=cars#sclient=psy&hl=en&safe=off&site=&source=hp&q=cars&btnG=Google+Search");
$results['id'] = $_GET['session_id'];
$results['agent'] = "Mozilla%2F4.0+%28compatible%3B+MSIE+7.0%3B+Windows+NT+5.1%3B+InfoPath.1%3B+.NET+CLR+2.0.50727%3B+.NET+CLR+1.1.4322%3B+.NET+CLR+3.0.04506.30%29";
$results['region'] = $_GET['region'];
$results['ip'] = "64.139.155.101";
$results['keywords'] = $_GET['keys'];
$results['domain'] = $_GET['domain'];
$results['url'] = $_GET['url'];
$date = $_GET['date'];

function fSearchCampaigns(&$results) {
	global $MYSQL_CONNECTION;
	echo "KEYS: ", $results['keywords'], "<br />";
	echo "AGENT: ", $results['agent'], "<br />";
	echo "REFERER: ", $results['referer'], "<br />";
	echo "IP: ", $results['ip'], "<br />";
	
	$domains = array();
	$regions = array();
	$keywords = array();
	$user_agents = array();
	$domains = explode(' ', $results['domain']);
	$regions = explode(' ', $results['region']);
	$keywords = explode(' ', $results['keywords']);
	$user_agents = explode(' ', $results['agent']);
	//substr($results['agent'], 0, 7) == "Mozilla" || substr($results['agent'], 0, 5) == "Opera"
	// regions, domains, keywords, user_agents
	$query = "SELECT * FROM campaigns WHERE active > 0 AND ";
	for($i = 0; $i < sizeof($keywords); ++$i) {
		if ($i > 0) $query .= " OR ";
		$query .= "keywords LIKE '%" . $keywords[$i] ."%' ";
	}
	echo "SQL: ", $query, "<br />";
	$result = mysql_query($query, $MYSQL_CONNECTION) or die($error = mysql_error());
	$results_array = array();
	
	while($row = mysql_fetch_array($result)) $results_array[] = $row;
	echo "SIZE OF MULTIPLE RESULTS: ", sizeof($results_array), "<br />";
  	
	return $results_array[0][10];
	//return $redirect;
}

$output = fSearchCampaigns(&$results);

echo "INDEX: ", $output, "<br />";
?>