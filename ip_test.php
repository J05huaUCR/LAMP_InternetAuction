<?php

/*
testing user/pw for dclick db:
U:  JoshTest
P:  TestPass1234!
*/

require("ping/config.php");
require("ping/functions.php");

$ip = "107.22.165.99"; // found 0.15783905983
//$ip = "192.168.1.69"; // not found 0.000557899475098

$start = fGetMicroTime();



$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die( mysql_error() ); 
/*
$ip = explode('.', $ip);

$sql = "SELECT COUNT(quad0) FROM " . $TABLE_IP_BLACKLIST . " WHERE quad0=".$ip[0]." AND quad1=".$ip[1]." AND quad2=".$ip[2]." AND quad3=".$ip[3]."";
$results = mysql_query($sql, $MYSQL_CONNECTION) or die( mysql_error() ); 
$row = mysql_fetch_row($results);

	echo "SQL: ", $sql, "<br />";
	echo "RESULT: ", $row[0], "<br />";
	*/
	
	(fIPcheck($ip, $MYSQL_CONNECTION) ) ? $result = "true" : $result = "false";

echo "BLACKLISTED: ". $result .  "<BR />";
echo "TIME: ", fMarkTime($start);


?>