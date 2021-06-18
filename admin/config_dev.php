<?php

require("includes/functions.php");
require("includes/db.php");

// Variable Declaration
global $TRACKING_DB_NAME, $TABLE_BIDS_PREFIX, $TABLE_PREFIX, $TABLE_BIDS_POSTFIX, $TABLE_SESSIONS_POSTFIX, $FEEDS_TABLE, $USERS_TABLE, $TABLE_KEYWORDS_DOMAIN;
global $MYSQL_CONNECTION, $MYSQL_HOST, $MYSQL_USER, $MYSQL_PW, $SITE_URL, $PATH, $TRACE;
global $TEMPLATE, $TEMPLATE_PATH, $TEMPLATE_STYLES, $TEMPLATE_IMAGES; // templates

$DEV = "_dev";

// Initialize constants
$MYSQL_HOST = "";
$MYSQL_USER ="";
$MYSQL_PW = "";
$TRACKING_DB_NAME = "";

$TABLE_PREFIX = "dc_";
$TABLE_BIDS_POSTFIX = "_bids";
$TABLE_SESSIONS_POSTFIX = "_sessions";
$TABLE_FEEDS = "dc_feeds";
$TABLE_USERS = "dc_users";
$TABLE_STATS = "dc_stats_daily";
$TABLE_KEYWORDS_DOMAIN = "keywords_by_domain";

$SITE_URL = "";
$PATH = "";
$TEMPLATE = "default";
$TEMPLATE_PATH = "templates/$TEMPLATE/";
$TEMPLATE_STYLES = $TEMPLATE_PATH . "styles/admin_styles$DEV.css";
$TEMPLATE_IMAGES = $TEMPLATE_PATH . "images/";

$PLATFORM_NAMES = array('zc'=>'ZeroClick&trade;','dc'=>'DirectClick&trade;','ac'=>'AdClick&trade;');	

// connect to db
$MYSQL_CONNECTION = mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PW) or die( mysql_error() ); 
mysql_select_db($TRACKING_DB_NAME, $MYSQL_CONNECTION) or die(mysql_error());

?>