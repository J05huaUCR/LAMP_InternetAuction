<?php
$domain_og = $_GET[domainname];
$domain2 = str_replace("www.","",$domain_og);
global $domain;
$domain  = $domain2;
Header("Location: url/?domain=$domain");
?>
