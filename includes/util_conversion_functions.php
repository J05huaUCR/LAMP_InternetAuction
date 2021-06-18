<?php

/** =====================================================================================================================
    Temporary Function for db conversion, KILL after conversion is complet
	=====================================================================================================================
	@PARAMETERS: 
		$date as string		- date passed in as YMD
		$MYSQL_CONNECTION		- connection object
	
	@RETURNS:
		nothing
* ======================================================================================================================*/
function fMakeNewTables($date, $session_table_name, $bid_table_name, $MYSQL_CONNECTION) {
	// Check for Sessions Table
	if( !mysql_num_rows( mysql_query("SHOW TABLES LIKE '". $session_table_name ."'", $MYSQL_CONNECTION))) { // Table not present
		// Create a MySQL table in the selected database
		mysql_query("CREATE TABLE " . $session_table_name . " (
					PRIMARY KEY(id),
					datetime DATETIME,
					platform VARCHAR(2) NOT NULL,
					id VARCHAR(32) NOT NULL,
					time VARCHAR(32) NOT NULL,
					time_elapsed VARCHAR(32),
					agent VARCHAR(255),
					region VARCHAR(32),
					ip VARCHAR(32),
					url VARCHAR(255),
					domain VARCHAR(255),
					referer VARCHAR(255),
					keywords VARCHAR(255),
					cookies TINYINT(2),
					javascript TINYINT(2),
					adult TINYINT(2),
					bot TINYINT(2),
					blackflag TINYINT(2)
					) TYPE=INNODB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
		, $MYSQL_CONNECTION ) or die(mysql_error());  
	}
	
	// Check for Bids Table
	if( !mysql_num_rows( mysql_query("SHOW TABLES LIKE '". $bid_table_name ."'", $MYSQL_CONNECTION))) { // Table not present
		// Create a MySQL table in the selected database
		mysql_query("CREATE TABLE " . $bid_table_name . " (
					PRIMARY KEY(bid_id),
					bid_id BIGINT NOT NULL AUTO_INCREMENT,
					id VARCHAR(32) NOT NULL, INDEX (id),
					feed VARCHAR(127) NOT NULL,
					time VARCHAR(32) NOT NULL,
					time_elapsed VARCHAR(32),
					xml_feed TEXT,
					ratio FLOAT (7,5),
					bid_calc FLOAT (10,6),
					bid FLOAT (10,6) NOT NULL,
					redirect TEXT NOT NULL,
					title VARCHAR(127),
					description TEXT,
					url TEXT,
					position TINYINT(10) Default 0,
					won TINYINT(1) Default 0,
					status VARCHAR(255)
					) TYPE=INNODB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
		, $MYSQL_CONNECTION ) or die(mysql_error());  
	}
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
function fQuery_Assoc_Conv($sql, $MYSQL_CONNECTION, $trace = 0) {
	
	$results = array();
	$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());
	while( $row = mysql_fetch_assoc($result) ) {
		$results[] = $row;
	}

	if (sizeof($results) > 0) return $results; return -1;
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWriteToDB($MYSQL_CONNECTION, &$data, $table, $ignore_flag = 0, $trace = 0){

	$keys = "";
	$values = "";
	$flag = 0;
	($ignore_flag) ? $ignore = '' : $ignore = 'IGNORE ';
	foreach($data as $key => $value) {
		if (!$flag) {
			$keys .= $key;
			$values .= "'" . $value . "'";
			$flag = 1;
		} else {
			$keys .= "," . $key;
			$values .= ", " . "'" . $value . "'";
		}
	}
	$sql = "INSERT " . $ignore . "INTO " . $table . " (" . $keys . ") ";
	$sql .= "VALUES (" . $values . ")";
	//echo "SQL: ", $sql, "<br /><br />";
	mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error() ); 

	return;	
}

?>