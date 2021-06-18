<?php

// Variable Declaration
global $TRACKING_DB_NAME, $TABLE_PREFIX, $MYSQL_CONNECTION;

// Initialize Variables
/*
DB: DClick
U:  dclick
P:  ClickMe1300!
*/
$MYSQL_CONNECTION = mysql_connect("localhost", "dclick", "ClickMe1300!") or die(mysql_error());
mysql_select_db("DClick", $MYSQL_CONNECTION);
$TRACKING_DB_NAME = "DClick";
$TABLE = "campaigns";

// Populate
$regions = array();
$regions['CA']['min_cpc'] = ".001";
$regions['CA']['max_cpc'] = ".025";
$regions['CA']['tolerance'] = ".1";
$regions['CA']['increment'] = ".001";
$regions['US']['min_cpc'] = ".001";
$regions['US']['max_cpc'] = ".037";
$regions['US']['tolerance'] = ".1";
$regions['US']['increment'] = ".001";
$regions['UK']['min_cpc'] = ".001";
$regions['UK']['max_cpc'] = ".037";
$regions['UK']['tolerance'] = ".1";
$regions['UK']['increment'] = ".001";

$keywords = array();
$keywords['car']['rate_rpm'] = "37";
$keywords['car']['rate_cpc'] = ".037";
$keywords['insurance']['rate_rpm'] = "17";
$keywords['insurance']['rate_cpc'] = ".017";

$region = 'US';
echo "MIN_CPC: ", $regions[$region]['min_cpc'], "<br />";
echo "MAX_CPC: ", $regions[$region]['max_cpc'], "<br />";
echo "TOLERANCE: ", $regions[$region]['tolerance'], "<br />";
echo "INCREMENT: ", $regions[$region]['increment'], "<br />";

$regions_serial = serialize($regions);
$keywords_serial = serialize($keywords);
echo "SERIALIZED: ", $regions_serial, "<br />"; 
echo "SERIALIZED: ", $keywords_serial, "<br />"; 

$regions_deserialized = unserialize($regions_serial);
echo "MIN_CPC: ", $regions_deserialized[$region]['min_cpc'], "<br />";
echo "MAX_CPC: ", $regions_deserialized[$region]['max_cpc'], "<br />";
echo "TOLERANCE: ", $regions_deserialized[$region]['tolerance'], "<br />";
echo "INCREMENT: ", $regions_deserialized[$region]['increment'], "<br />";

$sql = "SELECT * FROM campaigns ";
$result = mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error());

/* get mysql field names and place in indexed array 
$f = 0;
$fields = array();
while(mysql_field_name($result, $f)) {
	echo "FIELD $f: ", mysql_field_name($result, $f), "<br />";
	$fields[$f] = mysql_field_name($result, $f);
	++$f;
}
echo "SIZE OF FIELDS: ", sizeof($fields), "<br />";*/

$campaigns = array();
while($row = mysql_fetch_assoc($result)){ // fetch associative array
	$campaigns[] = $row;
}
unset($row, $result);

// retrieve as associative array
for ($r = 0; $r < sizeof($campaigns); ++$r) {
	foreach($campaigns[$r] as $key => $value) {
		
		if ($key == "regions") {
			$campaigns[$r]['regions'] = unserialize($value);
		} else {
			echo "ROW $r KEY: ", $key, ", VALUE: ", $value, "<br />";
		}
	}
}
?>