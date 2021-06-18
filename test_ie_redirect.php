<?php
$url = 'http://bridge.ame.admarketplace.net/ct?version=1.0.0&enURL=jrO7D4JUJfNLLSxTjwoAPQYISMP48lV/SbfMj5kaJIERZr+/Va0FGfSkqS6rQxXq297CIL1gIavmfCQFdPciA5ppJXCZZDzEVVOx3h2/0EAFE4ohz/4zBY3qkfhGW/lAhTw1/8UzNwme2Q2HF+nAA6tQz0nUHqpY&queryid=3332201847&rtpid=&adid=1&invid=61349573&ampsc=26&ampsctid=1&upid=&orgkw=girls&crid=3306950&fs=e-xml-42&ts=e-xml-42&pb=470.0&txid=100000000&iic=61349569;61349570;61349571;61349572;61349573&pycid=13750e6d-3b8e-44bf-900f-0fa4c8cdb484&pypos=1&campts=0&atsl=0&atsid=1068025&pupi=10484&mt=9&cp=0.1000,101140,1639528,1,pub_netsphere-,girls,backfill_conducive/l=COND';
$type = 301;

switch($type) {
	case 301:
		header('HTTP/1.0 301 Moved Permanently');
	break;	
	case 302:
		header('HTTP/1.0 302 Found');
	break;	
	case 307:
		header('HTTP/1.0 307 Temporary Redirect');
	break;
	default:
		header('HTTP/1.0 301 Moved Permanently');
	break;	
}
header("Location: " . $url);

//echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="100; URL='.$url.'" /></head><title>Redirecting...</title><body></body></html>';
exit();
?>