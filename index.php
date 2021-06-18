<?php

// DOMAIN
if ( isset($_GET['domain']) || isset($_GET['domainname']) ) { // Domain passed in
	// Multiple domain check
	if (isset($_GET['domain']) ) {
		$domain_get = $_GET['domain'];
	} else if (isset($_GET['domainname']) ) {
		$domain_get = $_GET['domainname'];
	} 
} 


// URL (if present)
if ( isset($_GET['uri']) ) $url=urlencode($_GET['uri']);

if ( isset($_GET['referer']) || isset($_GET['headerreferrer']) ) { // Domain passed in
	// Multiple domain check
	if (isset($_GET['headerreferrer']) ) {
		$referer_get = $_GET['headerreferrer'];
	} else if (isset($_GET['referer']) ) {
		$referer_get = $_GET['referer'];
	} 
} 

$domain = str_replace("http://","",$domain_get);// clean domain
$domain = urlencode(str_replace("www.","",$domain));
$referer = urlencode($referer_get);
$trace = $_GET['trace'];

Header("Location: url.php?domain=$domain&referer=$referer&url=$url&trace=$trace");
?>
