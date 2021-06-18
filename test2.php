<?php
$domain_og = $_GET['domain'];
$domain2 = str_replace("www.","",$domain_og);
global $domain;
$domain  = $domain2;
$location = "http://www.dealfox.com/directclick/?domain=$domain";
echo $location;
//Header("Location: " . $location);
?>
