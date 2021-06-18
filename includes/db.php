<?php

/* =====================================================================================================================
   Short Description
	=====================================================================================================================
	@PARAMETERS: 
		$variable as type		- description
	
	@RETURNS:
		$variable as type		- description
* ======================================================================================================================*/
function fInsert_Assoc_Array($MYSQL_CONNECTION, &$array, $table, $date, $trace = 0) {
	if ($trace > 1) echo "fInsert_Assoc_Array DATE: $date<br>";
	if ($date) {
		$date_key = "datetime, "; 
		$date_val = "NOW(), "; 
	} else {
		$date_key = "";
		$date_val = "";
	}
	
	$flag = 0; // initiate flag var
	
	foreach($array as $key => $value) {
		if ($flag < 1) { // check to see if first value, no comma
			$keys .= $key;
			$values .= "\"" . $value . "\"";
			$flag++;
		} else { // after first key, prepend with comma to separate values
			$keys .= "," . $key;
			$values .= ", " . "\"" . $value . "\"";
		}
	}
	$sql = "INSERT INTO " . $table . " (" . $date_key . $keys . ") VALUES (" . $date_val . $values . ")";
	if ($trace > 1) echo "fInsert_Assoc_Array SQL: $sql<br>";
	mysql_query($sql, $MYSQL_CONNECTION);
	if ($trace > 1) echo "fInsert_Assoc_Array DONE.".mysql_error() ."<br>";
	return;
}

/** =====================================================================================================================
    Returns number block representation of IP address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fQuery_Indexed_Array($sql, $MYSQL_CONNECTION, $trace = 0) {
	if ($trace) echo "fQuery_Indexed_Array=> called with SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_row($result) ) $results[] = $row;

	if ($trace) echo "fQuery_Indexed_Array=> size of results:|".sizeof($results)."|<br>";
	if (sizeof($results) > 1) return $results; return -1;
}

/** =====================================================================================================================
    Returns number block representation of IP address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fQuery_Assoc_Array($sql, $MYSQL_CONNECTION, $trace = 0) {
	if ($trace == 3) echo "<br />fQuery_Assoc_Array SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_assoc($result) ) $results[] = $row;

	return $results[0];	
}

/** =====================================================================================================================
    Returns number block representation of IP address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION, $trace = 0) {
	if ($trace > 2) echo "fQuery_Assoc_Multiple SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die (mysql_error());
	if ($trace) echo "ERROR: |".mysql_error()."|<br />";
	while( $row = mysql_fetch_assoc($result) ) $results[] = $row;

	if (sizeof($results) > 0) return $results; return -1;
}

/** =====================================================================================================================
    Returns number block representation of IP address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address in dot notation
	
	@RETURNS:
		$ip_number as int		 		- number representation of IP address
* ======================================================================================================================*/
function fQuery_Assoc_Row($sql, $trace = 0) {
	global $MYSQL_CONNECTION;
	if ($trace > 1) echo "<br />fQuery_Assoc_Row SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_assoc($result) ) $results[] = $row;
	
	if ($trace > 1) echo "fQuery_Assoc_Row RESULT: ".$results[0]." <br>";
	return $results[0];	
}

/* ===============================================================
*	Executes valid mySQL query and returns a single value
** ===============================================================*/		
function fQuery_Single($query, $MYSQL_CONNECTION, $trace = 0) {
	global $TRACKING_DB_NAME, $TABLE_PREFIX, $MYSQL_CONNECTION;
 
	$result = mysql_query($query, $MYSQL_CONNECTION);
	$row = mysql_fetch_row( $result );

	$value = $row[0];
	return $value;
}

/* ===============================================================
*	Executes valid mySQL Update Query
** ===============================================================*/		
function fUpdate_Values($query, $trace = 0) {
	global $MYSQL_CONNECTION;
	
	if ($trace > 1) echo "fUpdate_Values SQL: | $query |<br />";
	
	mysql_query($query, $MYSQL_CONNECTION);
	return;
}

?>