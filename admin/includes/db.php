<?php

/* =====================================================================================================================
   Checks for table passed in
	=====================================================================================================================
	@PARAMETERS: 
		$table as string		- name of table to be checked
		$MYSQL_CONNECTION		- connection object
	
	@RETURNS:
		boolean					- true if tested present, false otherwise
* ======================================================================================================================*/
function fCheckTableExists($table, $MYSQL_CONNECTION) {
	if ( mysql_num_rows( mysql_query("SHOW TABLES LIKE '$table'", $MYSQL_CONNECTION))) return true; else return false;
}

/* =====================================================================================================================
   Short Description
	=====================================================================================================================
	@PARAMETERS: 
		$variable as type		- description
	
	@RETURNS:
		$variable as type		- description
* ======================================================================================================================*/
function fInsert_Assoc_Array($MYSQL_CONNECTION, &$array, $table, $date, $trace = 0) {
	if ($trace == 3) echo "fInsert_Assoc_Array DATE: $date<br>";
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
	if ($trace == 3) echo "fInsert_Assoc_Array SQL: $sql<br>";
	mysql_query($sql, $MYSQL_CONNECTION) or die(print mysql_error());
	echo mysql_error(); 

	return;
}

/* ===============================================================
*	Executes valid mySQL query and returns an array
** ===============================================================*/		
function fQuery_Array($MYSQL_CONNECTION, $sql, $trace = 0) {
	if ($trace) echo "QUERY_ARRAY: ", $sql, "<br />";

	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_array($result)) $results_array[] = $row[0];
	
	if ($trace) echo "SIZE OF RESULTS: ", sizeof($results_array), "<br />";
  
	return $results_array;
}

/* =====================================================================================================================
   Short Description
	=====================================================================================================================
	@PARAMETERS: 
		$variable as type		- description
	
	@RETURNS:
		$variable as type		- description
* ======================================================================================================================*/
function fQuery_Assoc_Array($sql, $MYSQL_CONNECTION, $trace = 0) {
	if ($trace == 3) echo "<br />SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_assoc($result) ) {
		$results[] = $row;
	}

	return $results[0];	
}

/* =====================================================================================================================
   Short Description
	=====================================================================================================================
	@PARAMETERS: 
		$variable as type		- description
	
	@RETURNS:
		$variable as type		- description
* ======================================================================================================================*/
function fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION, $trace = 0) {
	if ($trace == 3) echo "fQuery_Assoc_Multiple SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_assoc($result) ) {
		$results[] = $row;
	}

	if (sizeof($results) > 0) return $results; return -1;
}

/* =====================================================================================================================
   Short Description
	=====================================================================================================================
	@PARAMETERS: 
		$variable as type		- description
	
	@RETURNS:
		$variable as type		- description
* ======================================================================================================================*/
function fQuery_Indexed_Array($sql, $MYSQL_CONNECTION) {
	if ($trace) echo "SQL: $sql <br>";
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_row($result) ) {
		$results[] = $row[0];
	}

	return $results;	
}

/* ===============================================================
*	Executes valid mySQL query and returns an array
** ===============================================================*/		
function fQuery_Multiple($MYSQL_CONNECTION, $sql, $trace = 0) {
	if ($trace) echo "fQuery_Multiple called with SQL: |$sql|<br />";

	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_array($result)) $results_array[] = $row;
	
	if ($trace) echo "SIZE OF RESULTS: |". sizeof($results_array[0]). "|<br />";
  
	return $results_array[0];
}

/* ===============================================================
*	Executes valid mySQL query and returns an array
** ===============================================================*/		
function fQuery_Multiple_Array($query) {
	global $TRACKING_DB_NAME, $TABLE_PREFIX, $MYSQL_CONNECTION, $TRACE;
	
	if ($TRACE) echo "QUERY_MULTIPLE_ARRAY: ", $query, "<br />";

	$result = mysql_query($query, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_array($result)) $results_array[] = $row;
	
	if ($TRACE) echo "SIZE OF RESULTS: ", sizeof($results_array[0]), "<br />";
  
	return $results_array;
}

/* ===============================================================
*	Executes valid mySQL query and returns an array from 1 row of results (DEPRECATED)
** ===============================================================*/		
function fQuery_Multiple_Assoc($query, $MYSQL_CONNECTION, $trace=0) {
	
	if ($trace > 0) echo "fQuery_Multiple_Assoc: ", $query, "<br />";
	
	$result = mysql_query($query, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_assoc($result)) $results_array[] = $row;
	
	if ($trace > 0) echo "SIZE OF RESULTS: ", sizeof($results_array), "<br />";
  
	return $results_array;
}

/* ===============================================================
*	Executes valid mySQL query and returns an array
** ===============================================================*/		
function fQuery_Multiple_Rows($query) {
	global $TRACKING_DB_NAME, $TABLE_PREFIX, $MYSQL_CONNECTION, $TRACE;
	
	if ($TRACE) echo "fQuery_Multiple_Rows: ", $query, "<br />";

	$result = mysql_query($query, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_row($result)) $results_array[] = $row;
	
	if ($TRACE) echo "SIZE OF RESULTS: ", sizeof($results_array), "<br />";
  
	return $results_array;
}

/* ===============================================================
*	Executes valid mySQL query and returns an array from 1 row of results
** ===============================================================*/		
function fQuery_Row_Assoc($query) {
	global $TRACKING_DB_NAME, $TABLE_PREFIX, $MYSQL_CONNECTION, $TRACE;
	
	if ($TRACE) echo "fQuery_Row_Assoc: ", $query, "<br />";
	
	$result = mysql_query($query, $MYSQL_CONNECTION) or die(mysql_error());
	$results_array = array();
	while($row = mysql_fetch_assoc($result)) $results_array[] = $row;
	
	if ($TRACE) echo "SIZE OF RESULTS: ", sizeof($results_array[0]), "<br />";
  
	return $results_array[0];
}

/* ===============================================================
*	Executes valid mySQL query and returns a single value
** ===============================================================*/		
function fQuery_Single($query, $MYSQL_CONNECTION, $trace = 0) {	
	if ($trace) echo "fQuery_Single called with SQL: |$query|<br />";
	$result = mysql_query($query, $MYSQL_CONNECTION);
	if (mysql_error()) {
		die ("Sql error of <b>".$_SERVER["SCRIPT_NAME"]."</b>. fQuery_Single called in ".__FILE__." on line: ".__LINE__."<br>".mysql_error()."<br><b>SQL:</b><br>$sql<br>");
	}
	if ($trace) echo "Size of results: |".sizeof($results)."| Error:<br />";
	
	
	$row = mysql_fetch_row( $result );

	$value = $row[0];
	return $value;
}

?>