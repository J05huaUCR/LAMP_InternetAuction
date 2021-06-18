<?php

require("../config.php");

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

$redirect = $_GET['redirect'];
$trace = $_GET['trace'];
$position = $_GET['position'];
$date = date("Ymd");
$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;

$sql = "UPDATE " . $table ." SET won=1 WHERE redirect='" . $redirect . "' and position='".$position."'";
mysql_query($sql, $MYSQL_CONNECTION);
mysql_close($MYSQL_CONNECTION);

/*
echo "REDIRECT: " . urldecode($_GET['redirect']) . "<br />";
echo "TABLE: " . $table . "<br />";
echo "SQL: " . $sql . "<br />";*/

if ($redirect != '') fRedirect($redirect);

?>