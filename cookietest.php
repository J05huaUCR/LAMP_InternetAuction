<?php

/*
// do a cookie test*/
if (!isset($_COOKIE['directclick']) ) { // no cookie
	if ($_GET['try'] != 1 ) { // if cookie set not attempted
		setcookie('directclick', 'true'); // attempt to set cookies
		//header('Location: http://www.'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?try=1'); // redirect
		echo 'Location: http://www.'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?try=1';
		//exit(0);
	} else {
		$cookies = 0; // attempt made and failed, cookies not accepted
	}
} else {
	$cookies = 1; // attempt successful
}

echo "http://www.". $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?try=1 <br />";
if ($cookies) {
	echo "COOKIES enabled.";
} else {
	echo "COOKIES disabled.";
}
?>