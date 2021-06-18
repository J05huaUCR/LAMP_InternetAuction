<?php
$filename = 'test-download.csv';

$csv  = "domain,url,keywords,etc\r\n";
$csv .= "youtubl.com,toptrafficsource.com/dc/admin,cats dogs,123:45:651\r\n";

// Open file handlers
!$handle = fopen($filename, 'w');
fwrite($handle, $csv);
fclose($handle);

// Send Headers
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Length: ". filesize("$filename").";");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Type: application/octet-stream; "); 
header("Content-Transfer-Encoding: binary");

echo $csv; // to output to browser

?>
