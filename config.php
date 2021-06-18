<?php

// Global Declaration
global $MYSQL_HOST, $MYSQL_USER, $MYSQL_PW;
global $TRACKING_DB_NAME, $TABLE_BIDS_PREFIX, $TABLE_IP_LOCATIONS, $TABLE_DOMAIN_KEYWORDS, $TABLE_KEYWORDS, $TABLE_KEYWORDS_RELATED, $TABLE_RPV, $TABLE_FEEDS;
global $TABLE_PLATFORM_PREFIX, $TABLE_SESSION_POSTFIX, $TABLE_BIDS_POSTFIX;
global $SITE_URL, $SITE_DOMAIN, $SITE_PATH, $PING_PATH, $DEFAULT_REDIRECT, $KEYWORDS_INDEX, $DOMAINS_INDEX, $DEFAULT_DOMAIN, $DEFAULT_PATH;
global $MAX_TIME, $TO_RPC, $MIN_BID, $MIN_RPM, $NUM_PPC_ADS, $KEYWORDS_DEFAULT;
global $DEV_MODE, $FEEDS;
global $TPL_PATH; $TPL; // template info

// db Connection
$MYSQL_HOST = "";
$MYSQL_USER = "";
$MYSQL_PW   = "";

// db Tables 20120319
$TRACKING_DB_NAME = "DClick";
$TABLE_PLATFORM_PREFIX = "dc_"; // Change to "dc_" when testing is done
$TABLE_SESSION_POSTFIX = "_sessions";
$TABLE_BIDS_POSTFIX = "_bids";
$TABLE_IP_LOCATIONS = "IP_Table";
$TABLE_IP_BLACKLIST = "ip_blacklist";
$TABLE_DOMAIN_KEYWORDS = "Domain_Keyword";
$TABLE_KEYWORDS = "Keywords";
$TABLE_KEYWORDS_RELATED = "keywords_related";
$TABLE_RPV = "dc_domain_rates";
$TABLE_FEEDS = "dc_feeds";
//$DEV_MODE = "_dev";

// Required
require("includes/functions$DEV_MODE.php");
require("includes/utilities.php");
require("includes/db.php");

// Constants
$KEYWORDS_INDEX = 1026; // totoal number of keywords
$DOMAINS_INDEX = 148000; 
$MAX_TIME = .6; // in seconds
$TO_RPC = .001; // converstion ratio from RPM to RPC
$MIN_BID = 1; // minimum bid in dollars per thousand clicks
$MIN_RPM = $MIN_BID * $TO_RPC; // minimum bid in dollars for 1 click
$NUM_PPC_ADS = 10; 
$SITE_URL = "http://www.toptrafficsource.com/";
$DEFAULT_DOMAIN = "toptrafficsource.com";
$DEFAULT_PATH = $SITE_URL . "dc/dc". $DEV_MODE .".php?domain=";
$SITE_DOMAIN = "toptrafficsource.com";
$SITE_PATH = "index". $DEV_MODE .".php"; // switch when implemented
$PING_PATH = "/dc/ping". $DEV_MODE .".php"; // switch when implemented
$DEFAULT_REDIRECT = $SITE_URL . "dc/dc.php?";
$TPL = "metallic";
$TPL_PAGE = "dc_output.php";
$TPL_PATH = "templates/$TPL/$TPL_PAGE";
$TPL_CSS = "$SITE_URLdc/templates/$TPL/styles/";

/*
Online College	
Direct TV	
Free Online Dating	
Mobile Phone	
Poker Play	
Daily Deals	
Careers and College	
Favorite Videos	
Discounted Insurance	
Credit Cards	
Best Vacations	
Online Education Degrees	
Affordable Life Insurance	
Free Checking	
IT Online Learning	
Best Travel Deals
*/
$KEYWORDS_DEFAULT = array();
$KEYWORDS_DEFAULT[][0] = "Online College";
$KEYWORDS_DEFAULT[][0] = "Direct TV";
$KEYWORDS_DEFAULT[][0] = "Free Online Dating";
$KEYWORDS_DEFAULT[][0] = "Mobile Phone";
$KEYWORDS_DEFAULT[][0] = "Poker Play";
$KEYWORDS_DEFAULT[][0] = "Daily Deals";
$KEYWORDS_DEFAULT[][0] = "Careers and College";
$KEYWORDS_DEFAULT[][0] = "Favorite Videos";
$KEYWORDS_DEFAULT[][0] = "Discounted Insurance";
$KEYWORDS_DEFAULT[][0] = "Credit Cards";
$KEYWORDS_DEFAULT[][0] = "Best Vacations";
$KEYWORDS_DEFAULT[][0] = "Online Education Degrees";
$KEYWORDS_DEFAULT[][0] = "Affordable Life Insurance";
$KEYWORDS_DEFAULT[][0] = "Free Checking";
$KEYWORDS_DEFAULT[][0] = "IT Online Learning";
$KEYWORDS_DEFAULT[][0] = "Best Travel Deals";
?>