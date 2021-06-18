<?php

require("ping/config.php");
require("functions/db.php");
require("functions/utilities.php");

echo "Checking...<br>";

$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

$sql = "SELECT * FROM IP_Table WHERE Begin_Number=End_Number AND Begin_IP <> End_IP";
$results = fQuery_Assoc_Array($sql, $MYSQL_CONNECTION);

for ($o = 0; $o < sizeof($results) ; $o++) {
	echo "Begin_IP: ".$results[$o]['Begin_IP'] . " => " . fIPtoNUM($results[$o]['Begin_IP'], $MYSQL_CONNECTION). ", listed as " . $results[$o]['Begin_Number'] . "<br />";
	echo "End_IP: ".$results[$o]['End_IP'] . " => " . fIPtoNUM($results[$o]['End_IP'], $MYSQL_CONNECTION). ", listed as " . $results[$o]['End_Number'] . "<br />";
	$sql = "UPDATE IP_Table SET Begin_Number=". fIPtoNUM($results[$o]['Begin_IP']) . ", End_Number=" . fIPtoNUM($results[$o]['End_IP']) . " ";
	$sql .= "WHERE Begin_IP='" . $results[$o]['Begin_IP'] ."' AND End_IP='" . $results[$o]['End_IP'] ."'";
	mysql_query($sql, $MYSQL_CONNECTION);
}

echo "DONE.";

?>