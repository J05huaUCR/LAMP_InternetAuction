<?php

$MYSQL_CONNECTION = mysql_connect("localhost", "dclick", "ClickMe1300!") or die(mysql_error());
$MYSQL_DB = "DClick";
$MYSQL_TABLE = "IP_BlackList";
mysql_select_db($MYSQL_DB, $MYSQL_CONNECTION);

$target_path = "uploads/";
$target_path .= basename( $_FILES['uploadedfile']['name']); 

if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
	$myFile = basename( $_FILES['uploadedfile']['name'] );
	echo "The file ".  $myFile. " has been uploaded.<br />";
	echo "Processing...<br/>";
	$fh = fopen("uploads/" . $myFile, 'r');
	while ($row = fgets($fh)) {

		$ip = array();
		$ip = explode('.', $row);
		$sql = "INSERT IGNORE INTO ".$MYSQL_TABLE ." (quad0, quad1, quad2, quad3) VALUES (".$ip[0].", ".$ip[1].", ".$ip[2].", ".$ip[3].")";
		mysql_query($sql, $MYSQL_CONNECTION);

	}
	fclose($fh);
	echo "Done.";
} else{
    echo "There was an error uploading the file, please try again!";
}

?>