<?php

require("ping/config.php");
require("functions/utilities.php");

echo "Killing...";

$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
fMySQL_Kill(1, $MYSQL_CONNECTION);

echo "DONE.";

?>