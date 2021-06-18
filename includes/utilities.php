<?php

/** =====================================================================================================================
    Batch Fix on db /IP table where numbers are incorrect
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fIPconvert($db_name, $table_name, $MYSQL_CONNECTION) {
	mysql_select_db($db_name, $MYSQL_CONNECTION) or die(mysql_error());
	$sql = "SELECT * FROM $table_name WHERE Begin_Number=End_Number AND Begin_IP <> End_IP";
	$results = fQuery_Assoc_Array($sql, $MYSQL_CONNECTION);
	
	for ($o = 0; $o < sizeof($results) ; $o++) {
		echo "Begin_IP: ".$results[$o]['Begin_IP'] . " => " . fIPtoNUM($results[$o]['Begin_IP'], $MYSQL_CONNECTION). ", listed as " . $results[$o]['Begin_Number'] . "<br />";
		echo "End_IP: ".$results[$o]['End_IP'] . " => " . fIPtoNUM($results[$o]['End_IP'], $MYSQL_CONNECTION). ", listed as " . $results[$o]['End_Number'] . "<br />";
		$sql = "UPDATE IP_Table SET Begin_Number=". fIPtoNUM($results[$o]['Begin_IP']) . ", End_Number=" . fIPtoNUM($results[$o]['End_IP']) . " ";
		$sql .= "WHERE Begin_IP='" . $results[$o]['Begin_IP'] ."' AND End_IP='" . $results[$o]['End_IP'] ."'";
		mysql_query($sql, $MYSQL_CONNECTION);
	}
	
	return 0;
}

/** =====================================================================================================================
    Returns number block representation of IP address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fIPtoNUM($ip_address = 0, $MYSQL_CONNECTION) {
	if ($ip_address == 0) return -1; // error
	
	$w = $x = $y = $z = 0; // initialize

	list($w, $x, $y, $z) = split('[.]', $ip_address);
	$w = ( $w * 16777216 ) ;
	$x = ( $x * 65536    ) ;
	$y = ( $y * 256      ) ;
	$z = ( $z            ) ;
	$ip_number = $w + $x + $y + $z;
	
	return $ip_number;
}

/** =====================================================================================================================
    Kills all open MySQL threads on connection passed
	=====================================================================================================================
	@PARAMETERS: 
		$time as int					- number of seconds process has been running to kill, default is 100 seconds.
		$MYSQL_CONN						- MySQK connection object
	
	@RETURNS:
		nothing
* ======================================================================================================================*/
function fMySQL_Kill($time = 100, $MYSQL_CONNECTION) {
	$result = mysql_query("SHOW FULL PROCESSLIST");
	while ($row = mysql_fetch_array($result) ) {
		$process_id = $row["Id"];
		if ($row["Time"] > $time ) {
			$sql="KILL $process_id";
			mysql_query($sql);
		}
	}
}

?>