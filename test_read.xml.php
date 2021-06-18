<?php

//include("example.php");

function fParseXML($xml, $feed) {
	echo "fParseXML called.<br />";
	switch ($feed) {
		case "adlux":
			if ( ((int) $xml->results->count) > 0) {
				echo "fParseXML called for AdLux<br />";
				echo "COUNT: ", ((string) $xml->results->count), ".<br />";
				echo "BID: ", $xml->results->sponsored->listing[0]->cpc, ".<br />";
				echo "URL: ", $xml->results->sponsored->listing[0]->url, ".<br />";
				echo "DISPLAYURL: ", $xml->results->sponsored->listing[0]->displayurl, ".<br />";
				echo "TITLE: ", $xml->results->sponsored->listing[0]->title, ".<br />";
				echo "DESCRIPTION: ", $xml->results->sponsored->listing[0]->description, ".<br />";
			}
		break;
		
		case "admarketplace":
			if ( ((int) $xml['count'] ) > 0) {
				echo "fParseXML called for AdMarketplace<br />";
				echo "COUNT: ", ((string) $xml['count']), ".<br />";
				echo "BID: ", $xml->adlistings->listing[0]->bid_price, ".<br />";
				echo "URL: ", $xml->adlistings->listing[0]->clickurl, ".<br />";
			}
		break;
		
		case "admedia":
			if ( $xml->bid ) {
				echo "fParseXML called for AdLux<br />";
				echo "BID: ", $xml->bid, ".<br />";
				echo "URL: ", $xml->redirect, ".<br />";
			}
		break;
		
		case "affinity":
			if ( $xml->ad->bid ) {
				echo "fParseXML called for Affinity<br />";
				echo "BID: ", $xml->ad->bid, ".<br />";
				echo "URL: ", $xml->ad->rurl, ".<br />";
			}
		break;
		
		case "bravemedia":
			if ( $xml->results->cost ) {
				echo "fParseXML called for BraveMedia<br />";
				echo "BID: ", $xml->results->cost, ".<br />";
				echo "URL: ", $xml->results->link, ".<br />";
			}
		break;
		
		case "elephant":
			if ( $xml->rpm ) {
				echo "fParseXML called for elephant<br />";
				echo "BID: ", $xml->rpm, ".<br />";
				echo "URL: ", $xml->redirect, ".<br />";
			}
		break;
		
		case "prostreamedia":
			if ( ((int) $xml->results->count) > 0) {
				echo "fParseXML called for Prostreamedia<br />";
				echo "COUNT: ", ((string) $xml->results->count), ".<br />";
				echo "BID: ", $xml->results->sponsored->listing[0]->cpc, ".<br />";
				echo "URL: ", $xml->results->sponsored->listing[0]->url, ".<br />";
			}
		break;
		
		case "searchmylocal":
			$i = 0;
			if (1) { // filter
				while ( $xml->listing[$i]) {
					echo "BID:". ((float) $xml->listing[$i]['bid']) . "<br />";
					echo "TITLE: ". ((string) $xml->listing[$i]['title']) . "<br />";
					echo "REDIRECT: ". ((string) $xml->listing[$i]['url']) . "<br />";
					echo "DESC: ". ((string)$xml->listing[$i]['descr']) . "<br />";
					echo "DISPLAY-URL: ". ((string)$xml->listing[$i]['site']) . "<br />";
					$i++;
				}
			}
		break;
		
		case "sendori":
			if ( $xml->rpm ) {
				echo "fParseXML called for Sendori<br />";
				$bid = ((float) $xml->rpm) * .001;
				echo "BID: ", $bid, ".<br />";
				echo "URL: ", $xml->redirect, ".<br />";
			}
		break;
		
		case "stormwest":
			if ($xml->response['accepted'] == true) {
				echo "fParseXML called for StormWest<br />";
				//print_r($xml);
				echo "BID: ", $xml->response['bid'], ".<br />";
				echo "URL: ", $xml->response['url'], ".<br />";
			}
		break;		
	}
}

print "Starting...<br />";

//$movies = new SimpleXMLElement($xmlstr);

/* Example 2
print_r($movies);
echo $movies->movie[0]->plot;
echo "<br/>";*/

/* Example 3
echo $movies->movie->{'great-lines'}->line;
echo "<br/>";*/

/* Example 4
foreach ($movies->movie->characters->character as $character) {
   echo $character->name, ' played by ', $character->actor, PHP_EOL;
}
echo "<br/>";*/

/* Example 5 
foreach ($movies->movie[0]->rating as $rating) {
    switch((string) $rating['type']) { // Get attributes as element indices
    case 'thumbs':
        echo $rating, ' thumbs up';
        break;
    case 'stars':
        echo $rating, ' stars';
        break;
    }
}
echo "<br/>";*/

/* Example 6 
if ((string) $movies->movie->title == 'PHP: Behind the Parser') {
    print 'My favorite movie.';
}
echo htmlentities((string) $movies->movie->title);
echo "<br/>";*/

/* Example 8 
foreach ($movies->xpath('//character') as $character) {
    echo $character->name, 'played by ', $character->actor, PHP_EOL;
}
echo "<br/>";*/

/* Exmple 9 

$movies->movie[0]->characters->character[0]->name = 'Miss Coder';
echo $movies->asXML();
echo "<br/>";*/

/*
if (file_exists("admedia.xml")) $xml = simplexml_load_file("admedia.xml");
fParseXML($xml, "admedia");

if (file_exists("stormwest.xml")) $xml = simplexml_load_file("stormwest.xml");
fParseXML($xml, "stormwest");

if (file_exists("adlux.xml")) $xml = simplexml_load_file("adlux.xml");
fParseXML($xml, "adlux");

if (file_exists("affinity.xml")) $xml = simplexml_load_file("affinity.xml");
fParseXML($xml, "affinity");

if (file_exists("sendori.xml")) $xml = simplexml_load_file("sendori.xml");
fParseXML($xml, "sendori");

if (file_exists("elephant.xml")) $xml = simplexml_load_file("elephant.xml");
fParseXML($xml, "elephant");

if (file_exists("admedia.xml")) $xml = simplexml_load_file("admedia.xml");
fParseXML($xml, "admedia");

if (file_exists("prostreamedia.xml")) $xml = simplexml_load_file("prostreamedia.xml");
fParseXML($xml, "prostreamedia");

if (file_exists("bravemedia.xml")) $xml = simplexml_load_file("bravemedia.xml");
fParseXML($xml, "bravemedia");*/

if (file_exists("searchmylocal.xml")) $xml = simplexml_load_file("searchmylocal.xml");
fParseXML($xml, "searchmylocal");
/**/
?>