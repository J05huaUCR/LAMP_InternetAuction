<?php

/* =====================================================================================================================
   Returns a feed id from the list of feed ids rotated and then updates count to assure even rotation
	=====================================================================================================================
	@PARAMETERS: 
		$MYSQL_CONNECTION		- valid mysql connection
	
	@RETURNS:
		int					 	- feed_id
*  ======================================================================================================================*/
function fAdmanage_GetFeed($trace = 0) {
	global $MYSQL_CONNECTION;
	$TABLE = "dc_admanage_feed_ids";
	
	// retrieve id and its count
	$sql = "SELECT * FROM $TABLE ORDER BY count ASC, feed_id ASC";
	if ($trace > 1) echo "fAdmanage_GetFeed SQL: | $sql |<br />";
	$results = fQuery_Assoc_Row($sql, $trace);
	$id = $results['feed_id'];
	$count = (int) $results['count'];
	
	++$count; // increment count
	
	// Update db
	$sql = "UPDATE $TABLE SET count=$count WHERE feed_id='$id'";
	if ($trace > 1) echo "fAdmanage_GetFeed SQL: | $sql |<br />";
	fUpdate_Values($sql, $trace);

	return $id;
}

/** =====================================================================================================================
    Checks a domain in DoubleClick to see if it is adult
	=====================================================================================================================
	@PARAMETERS: 
		$domain as string		- the domain to be checked
	
	@RETURNS:
		int					 	- [1] adult [0] non-adult [-1] not found
* ======================================================================================================================*/
function fAdultCheck($MYSQL_CONNECTION, $domain = '',$keywords = '',$trace = 0) {
	$adult = 0; // default not adult
	
	if ($domain != '') { // Domain Check
		$feed = "http://googleads.g.doubleclick.net/apps/domainpark/domainpark.cgi?callback=_google_json_callback&output=js&client=ca-dp-adultcheck_xml&domain_name=" . $domain;
		$json = file_get_contents($feed);
		$offset = strpos($json, "\"adult\": "); // location of "adult" key in feed
		if ($offset) { // string found
			$bool_true = strpos($json, ": \"true\"", $offset); // search for "true" near "adult"
			if (($bool_true - $offset) < 10 && $bool_true > 0) $adult = 1; // if within 10 characters, the flag for adult is "true"
		} 
	}
	
	if ($keywords != '') { // Keywords check
		$sql = "SELECT * FROM keywords_adult WHERE";
		$key_array = explode(" ",$keywords);
		$i = 0;
		foreach($key_array as $word) {
			if ($i == 0) {
				$sql .= " keywords LIKE '%".$word."%'";
			} else {
				$sql .= " AND keywords LIKE '%".$word."%'";
			}
		}
		$results = fQuery_Single($sql, $MYSQL_CONNECTION, $trace = 0);
		if ( $results[0] != '') $adult = 1;
	}
	if ($trace) echo "ADULT FLAG: |$adult|<br />";
	return $adult;
}

/** =====================================================================================================================
    Checks Agent header value to see if 'bot' 
	=====================================================================================================================
	@PARAMETERS: 
		$agent as string		- the string to search for 
	
	@RETURNS:
		bool					 	- [1] is bot [0] not bot 
* ======================================================================================================================*/
function fBotCheck($agent) {
	if ( strpos($agent, 'bot') || strpos($agent, 'spider') || strpos($agent, 'spinn3r.com/robot') ) {
		$botflag = 1;
	} else  {
		$botflag = 0;
	}
	return $botflag;
}

/** =====================================================================================================================
    Build Feed
	=====================================================================================================================
	@PARAMETERS: 
		$results as array		- description
		$date as string		- The date in YMD format
	
	@RETURNS:
		$feed_url as string 	- feed URL to get XML feed 
* ======================================================================================================================*/
function fBuildFeed($MYSQL_CONNECTION, $date, &$session_data, $feed, $num = 1, $trace = 0) {
	global $MIN_RPM, $NUM_PPC_ADS;
	
	if ($trace == 3) {
		echo "fBuildFeed called. feed = |$feed|<br /> ";
	}

	$platform = $session_data['platform'];
	$keywords = urlencode($session_data['keywords']);
	$agent = urlencode($session_data['agent']);
	$referer = urlencode($session_data['referer']);
	$domain = urlencode($session_data['domain']);
	$url = urlencode($session_data['url']);
	$region = urlencode($session_data['region']);
	$ip = $session_data['ip'];
	$adult = $session_data['adult'];
	$blackflag = $session_data['blackflag'];
	$bot = $session_data['bot'];
	$cookies = $session_data['cookies'];
	$javascript = $session_data['javascript'];
	$num_ads = $session_data['num_ads'];
	$PLATFORM_ID = "netsphere_" . $platform;
	/*
	if ($platform  == 'dc') {
		$PLATFORM_ID = "netsphere_dc";
		$num_ads = $NUM_PPC_ADS; 
	} else { 
		$PLATFORM_ID = "netsphere_zc";
		$num_ads = 1;
	}*/
	$feed_url = "";
	
	/* Build feed_url */
	switch($feed) {
		case "7search":
			/*
			http://meta.7search.com/feed/xml.aspx?
			affiliate=75667
			&token=A935CA9C73EF4DA9BA1B7F492ACBF6F2
			&rid=zc1300
			&qu=<keywords>
			&ip_address= <ip>
			&page_url=<page url where these ads will be>
			&st=typein
			&pn=1<Pagenumber default (opt)>
			&r=5<number of results per page (opt)>
			&filter=no <Adult filtering? (opt)>
			
			if ( !fCheckPrior($MYSQL_CONNECTION, $date, $ip, $domain, $trace) ) // <===================================================NEED TO CHECK ON THIS FUNCTION
			*/
			if ($adult != 1) { 
				$affiliate_id = "75667";
				$token = "014BF76EAF7943CD8AB00E5789846209";
				$sub_id = $PLATFORM_ID;
				$landing_page = urlencode("http://www.toptrafficsource.com/dc/7search.php");
				
				$feed_url  = "http://meta.7search.com/feed/xml.aspx?";
				$feed_url .= "affiliate=" . $affiliate_id; 
				$feed_url .= "&token=" . $token;
				$feed_url .= "&rid=" . $sub_id;
				($platform == 'zc') ? $feed_url .= "&qu=cf+" . $keywords : $feed_url .= "&qu=" . $keywords;
				$feed_url .= "&ip_address=" . $ip;
				$feed_url .= "&page_url=" . $landing_page;
				$feed_url .= "&st=typein";
				$feed_url .= "&pn=1"; // optional
				$feed_url .= "&r=" . $num_ads; // optional
				$feed_url .= "&filter=no"; // optional
			}
		break;
		
		case "adknowledge":
			/* 
			http://v10.xmlsearch.miva.com/bin/findwhat.dll?getresults
			&base=0 // An integer value representing the first record to be retrieve
			&dc=1 // number of documents to be retrieved
			&mt=gifts // HTML encoded text string representing what the user is searching 
			&ip_addr=xxx.xxx.xxx.xxx
			&aff_id=78661 // partner id
			ID		Platform				Authotoken
			78659 Nsphere – 1 click 6PK7CK9MH
			78661 Nsphere – 0 click MDRL99E89
			&aff_sub_id=zc1300 // 
			&fl=0 // adult filter is on or off
			&fmt=xml8859-2 // the data format
			&at=MDRL99E89 // authorization token 
			&ua=xxxxx // user agent
			&ru=xxxxx //the url of the page where the ads will be shown
			&iu=0 // An integer value that indicates whether to include image url in response
			*/
			if ($adult != 1) {
				// initiate
				$affiliate_id = "78661";
				$token = "MDRL99E89";
				$landing_page = urlencode("http://www.toptrafficsource.com/7search.php");
				
				// Build feed url
				$feed_url  = "http://v10.xmlsearch.miva.com/bin/findwhat.dll?getresults";
				$feed_url .= "&base=0";
				$feed_url .= "&dc=1";
				$feed_url .= "&mt=" . $keywords;
				$feed_url .= "&ip_addr=" . $ip;
				$feed_url .= "&aff_id=" . $affiliate_id; 
				$feed_url .= "&aff_sub_id=" . $PLATFORM_ID;
				$feed_url .= "&fl=0";
				$feed_url .= "&fmt=xml8859-2";
				$feed_url .= "&at=" . $token;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&ru=" . $landing_page; 
				$feed_url .= "&iu=0";
			}
		break;
		
		case "adlux":
			/*
			also, it seems like the adlux feed is using the wrong IDs....use these:
			24785 Adlux_PremiumDomainsZC_AU Yes bvuk
			24790 Adlux_PremiumDomainsZC_SEA Yes Y3aV
			24791 Adlux_PremiumDomainsZC_UK Yes eu02
			24798 Adlux_PremiumDomainsZC_US Yes 
			
			SEA:
			Philippines, PH
			Indonesia, ID
			Vietnam, VN
			Singapore, SG
			Thailand, TH
			Malaysia, MY

			Sample URL:
			http://xml.nf.adlux.com/feed?sid=24784&auth=K03P
			&subid=<subid> // optional
			&q=<query>&ip=<ipaddress>
			&ua=<useragent>
			&ref=<referer>
			&count=<count> // optional
			&state=<state> // optional
			&city=<city> // optional
			*/
			if (($region == "US" || 
				  $region == "CA" || 
				  $region == "AU" || 
				  $region == "NZ" || 
				  $region == "PH" || 
				  $region == "ID" || 
				  $region == "VN" || 
				  $region == "SG" || 
				  $region == "TH" || 
				  $region == "MY" || 
				  $region == "GB") 
				  && $adult != 1) {
				$feed_url = "";
				$auth = "";
				/* SWITCH BASED ON MULTIPLE REGIONS, only using AU per Amer 1/26/12*/
				if ($platform == "zc") { // ZeroClick
					switch ($region) { // logic to correctly determine region
						case "US": //
							$sid = "24798";
							$auth = "ELy4";
						break;	
						case "AU": //
							$sid = "24785";
							$auth = "bvuk";
						break;	
						case "NZ"://
							$sid = "24785";
							$auth = "bvuk";
						break;	
						case "PH":
							$sid = "24790";
							$auth = "Y3aV";
						break;	
						case "ID":
							$sid = "24790";
							$auth = "Y3aV";
						break;
						case "VN":
							$sid = "24790";
							$auth = "Y3aV";
						break;
						case "SG":
							$sid = "24790";
							$auth = "Y3aV";
						break;
						case "TH":
							$sid = "24790";
							$auth = "Y3aV";
						break;
						case "MY":
							$sid = "24790";
							$auth = "Y3aV";
						break;
						case "GB":
							$sid = "24791";
							$auth = "eu02";
						break;	
					}
				} else if ($platform == "dc") { // DirectClick
					switch ($region) { // logic to correctly determine region
						case "US": //
							$sid = "24792";
							$auth = "H7li";
						break;	
						case "AU": //
							$sid = "24784";
							$auth = "K03P";
						break;	
						case "NZ"://
							$sid = "24784";
							$auth = "K03P";
						break;	
						case "PH":
							$sid = "24787";
							$auth = "WCP6";
						break;	
						case "ID":
							$sid = "24787";
							$auth = "WCP6";
						break;
						case "VN":
							$sid = "24787";
							$auth = "WCP6";
						break;
						case "SG":
							$sid = "24787";
							$auth = "WCP6";
						break;
						case "TH":
							$sid = "24787";
							$auth = "WCP6";
						break;
						case "MY":
							$sid = "24787";
							$auth = "WCP6";
						break;
						case "GB":
							$sid = "24788";
							$auth = "k64I";
						break;	
					}
				} 
				
				//$feed_url  = "http://xml.nf.adlux.com/feed?"; // old
				/*
				http://24785.8252.adlux-xml.com/feed?
				sid=24785
				&auth=bvuk
				*/
				$feed_url  = "http://$sid.8252.adlux-xml.com/feed?"; // old
				$feed_url .= "sid=" . $sid;
				$feed_url .= "&auth=" . $auth;
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&count=" . $num_ads;
			}
		break;
		
		case "admanage":		
			/*
			
			Countries Accepted: 
			CA, US, AT, BE, DK, FI, FR, DE, GR, IE, IT, NL, NO, PT, ES, SE, CH, GB, AU, NZ, AF, AM, AZ, 
			BH, BD, BT, BN, KH, CN, CY, GZ, GE, HK, IN, ID, IR, IQ, IL, JP, JO, KZ, KP, KR, KW, KG, LO, 
			MO, MY, MV, MN, MM, NP, OM, PK, PS, PH, QA, SA, SG, LK, SY, TW, TJ, TH, TR, TM, AE, UZ, VN, 
			YE, AR, AW, BZ, BO, BR, CL, CO, CR, CU, DO, EC, SV, GF, GP, GT, GY, HT, HN, MQ, MX, AN, NI, 
			PA, PY, PE, PR, PM, UY, VE
			
			http://62003.xml.admanage.com/xml/?
			fid=62003
			&keywords=<Keywords>
			&user_ip=<User_IP>
			&ua=<User_Agent>
			&serve_url=<Page_url_with_results>
			&subid=<Campaign_SubID> // optional
			&count=1
			&state=<State> // optional
			&city=<City> // optional
			&zip=<Zip> // optional
			&location=<Location> // optional
			*/
			if ($adult != 1 && (
				 $region == "CA" || $region == "US" || $region == "AT" || $region == "BE" || $region == "DK" ||
				 $region == "FI" || $region == "FR" || $region == "GR" || $region == "IE" || $region == "IT" ||
				 $region == "NL" || $region == "NO" || $region == "PT" || $region == "ES" || $region == "SE" ||
				 $region == "CH" || $region == "GB" || $region == "AU" || $region == "NZ" || $region == "AF" ||
				 $region == "AM" || $region == "AZ" || $region == "BH" || $region == "BD" || $region == "BT" ||
				 $region == "BN" || $region == "KH" || $region == "CN" || $region == "CY" || $region == "GZ" ||
				 $region == "GE" || $region == "HK" || $region == "IN" || $region == "ID" || $region == "IR" ||
				 $region == "IQ" || $region == "IL" || $region == "JP" || $region == "JO" || $region == "KZ" ||
				 $region == "KP" || $region == "KR" || $region == "KW" || $region == "KG" || $region == "LO" ||
				 $region == "MO" || $region == "MY" || $region == "MV" || $region == "MN" || $region == "MM" ||
				 $region == "NP" || $region == "OM" || $region == "PK" || $region == "PS" || $region == "PH" ||
				 $region == "QA" || $region == "SA" || $region == "SG" || $region == "LK" || $region == "SY" ||
				 $region == "TW" || $region == "TJ" || $region == "TH" || $region == "TR" || $region == "TM" ||
				 $region == "AE" || $region == "UZ" || $region == "VN" || $region == "YE" || $region == "AR" ||
				 $region == "AW" || $region == "BZ" || $region == "BO" || $region == "BR" || $region == "CL" ||
				 $region == "CO" || $region == "CR" || $region == "CU" || $region == "DO" || $region == "EC" ||
				 $region == "SV" || $region == "GF" || $region == "GP" || $region == "GT" || $region == "GY" ||
				 $region == "HT" || $region == "HN" || $region == "MQ" || $region == "MX" || $region == "AN" ||
				 $region == "NI" || $region == "PA" || $region == "PY" || $region == "PE" || $region == "PR" ||
				 $region == "PM" || $region == "UY" || $region == "VE" 
				 ))  {
				$feed_url  = "http://62003.xml.admanage.com/xml/?";
				$feed_url .= "fid=62003";
				$feed_url .= "&keywords=" . $keywords;
				$feed_url .= "&user_ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&serve_url=" . urlencode("http://www.toptrafficsource.com/dc/$platform.php");
				$feed_url .= "&count=" . $num_ads;
				$feed_url .= "&subid=" . $domain;
				// $feed_url .= "&state=<State> // optional
				// $feed_url .= "&city=<City> // optional
				// $feed_url .= "&zip=<Zip> // optional
				// $feed_url .= "&location=<Location> // optional
			}
		break;
		
		case "admanage_adult":		
			/*
			
			Feed ID:  #63384
			Countries Accepted: CA, US, FR, DE, GB, AU
			
			http://63384.xml.premiumxml.com/xml/?
			fid=63384
			&keywords=<KEYWORD>
			&user_ip=<USER_IP>
			&ua=<USER_AGENT>
			&serve_url=<SERVE_URL>
			*/
			if ($adult == 1 && (
				 $region == "CA" || $region == "US" || $region == "FR" || $region == "DE" || $region == "GB" ||
				 $region == "AU"
				 ))  {
				$feed_url  = "http://63384.xml.premiumxml.com/xml/?";
				$feed_url .= "fid=63384";
				$feed_url .= "&keywords=" . $keywords;
				$feed_url .= "&user_ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&subid=" . $domain;
				$feed_url .= "&serve_url=" . urlencode("http://www.toptrafficsource.com/dc/$platform.php");
				$feed_url .= "&count=" . $num_ads;
				// $feed_url .= "&state=<State> // optional
				// $feed_url .= "&city=<City> // optional
				// $feed_url .= "&zip=<Zip> // optional
				// $feed_url .= "&location=<Location> // optional
			}
		break;
		
		case "admanage_premium":		
			/*
			
			Feed ID:  #63383
			Countries Accepted: CA, US, AT, DK, FI, FR, DE, IT, NL, NO, ES, SE, CH, GB, AU, HK, SG, TW, AR, BR, CL, CO, MX, PE, 
			
			http://63383.xml.premiumxml.com/xml/?
			fid=63383
			&keywords=<KEYWORD>
			&user_ip=<USER_IP>
			&ua=<USER_AGENT>
			&serve_url=<SERVE_URL>
			

			*/
			if ($adult != 1 && (
				 $region == "CA" || $region == "US" || $region == "AT" || $region == "DK" || $region == "DE" ||
				 $region == "FI" || $region == "FR" || $region == "IT" || $region == "NL" || $region == "NO" || 
				 $region == "ES" || $region == "SE" || $region == "CH" || $region == "GB" || $region == "AU" ||
				 $region == "HK" || $region == "SG" || $region == "TW" || $region == "AR" || $region == "BR" ||
				 $region == "CL" || $region == "CO" || $region == "MX" || $region == "PE"
				 ))  {
				$id = fAdmanage_GetFeed($trace);
				$feed_url  = "http://$id.xml.premiumxml.com/xml/?";
				$feed_url .= "fid=63383";
				$feed_url .= "&keywords=" . $keywords;
				$feed_url .= "&user_ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&subid=" . $domain;
				$feed_url .= "&serve_url=" . urlencode("http://www.toptrafficsource.com/dc/$platform.php");
				$feed_url .= "&count=" . $num_ads;
				// $feed_url .= "&state=<State> // optional
				// $feed_url .= "&city=<City> // optional
				// $feed_url .= "&zip=<Zip> // optional
				// $feed_url .= "&location=<Location> // optional
			}
		break;
		
		case "admarketplace":		
			/*
			http://netsphere.ampfeed.com/xmlamp/feed?
			partner=pub_netsphere
			&kw=[keyword]
			&skip=[skip] // optional
			&results=1
			&cdata=[0,1] // optional
			&ip=[users_ip]
			&ua=[users_agent]
			&rfr=[refering_url]
			
			blocked:
			174.36.241.149
			173.192.238.48
			173.192.238.57
			173.192.238.44
			173.192.238.35
			173.192.238.51
			174.36.228.156
			173.192.238.58
			174.36.241.151
			67.125.22.87
			!fCheckPrior($date, $ip, $results['feed'], '', 3) // 
			*/
			if ($adult != 1 && 
				 $cookies == true && 
				 $bot != true &&
				 ($ip != '174.36.241.149' || 
				  $ip != '173.192.238.48' || 
				  $ip != '173.192.238.57' || 
				  $ip != '173.192.238.44' || 
				  $ip != '173.192.238.35' || 
				  $ip != '173.192.238.51' || 
				  $ip != '174.36.228.156' || 
				  $ip != '173.192.238.58' || 
				  $ip != '174.36.241.151' || 
				  $ip !=  '67.125.22.87' )
				 ) { // not adult, accepts cookies, not bot
				$feed_url  = "http://netsphere.ampfeed.com/xmlamp/feed?";
				$feed_url .= "partner=pub_netsphere/$PLATFORM_ID";
				$feed_url .= "&kw=" . $keywords;
				// $feed_url .= "&skip="; // optional
				$feed_url .= "&results=" . $num_ads;
				// $feed_url .= "&cdata="; // [0,1] optional
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&rfr=" . $referer;
			}
			/* from v1.0			
			if ($results['adult'] != 1 && 
				 $cookies == true && 
				 !strpos($results['status'], 'bot') &&
			    !strpos($results['agent'], 'spinn3r.com/robot') &&
				 ($results['ip'] != '174.36.241.149' || 
				  $results['ip'] != '173.192.238.48' || 
				  $results['ip'] != '173.192.238.57' || 
				  $results['ip'] != '173.192.238.44' || 
				  $results['ip'] != '173.192.238.35' || 
				  $results['ip'] != '173.192.238.51' || 
				  $results['ip'] != '174.36.228.156' || 
				  $results['ip'] != '173.192.238.58' || 
				  $results['ip'] != '174.36.241.151' || 
				  $results['ip'] !=  '67.125.22.87' )
				 ) { // not adult, accepts cookies, not bot
				$feed_url  = "http://netsphere.ampfeed.com/xmlamp/feed?";
				$feed_url .= "partner=pub_netsphere";
				if ($results['keywords'] == "") $results['keywords'] = fKeywordLookup($results['domain']); // if no keywords, lookup domain
				$results['keywords'] = str_replace('%2B','%20',$results['keywords']);
				$results['keywords'] = str_replace('+','%20',$results['keywords']);
				$feed_url .= "&kw=" . $results['keywords'];
				// $feed_url .= "&skip="; // optional
				$feed_url .= "&results=1";
				// $feed_url .= "&cdata="; // [0,1] optional
				$feed_url .= "&ip=" . $results['ip'];
				$feed_url .= "&ua=" . $results['agent'];
				$feed_url .= "&rfr=" . $results['referer'];
			}*/
		break;
		
		case "admedia":		
			/*
			http://xml.admedia.com/xml.php?
			affiliate=nsphere3
			&subid=[your-sub-id-number] // optional
			&Terms=[your-search-query]
			&IP=[your-user-ip-address]
			&rpp=1 // optional
			&ua=[user-agent]
			*/
			if ($adult != 1) {
				$feed_url  = "http://xml.admedia.com/xml.php?";
				($platform == 'zc') ? $feed_url .= "affiliate=nsphere3" : $feed_url .= "affiliate=nsphere1";
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&Terms=" . $keywords;
				$feed_url .= "&IP=" . $ip;
				$feed_url .= "&rpp=" . $num_ads;
				$feed_url .= "&ua=" . $agent;
			}
		break;
		
		case "adrenalads":	
			/*
			http://api.adrenalads.com/request.php?
			http://dev.api.adrenalads.com/request.php? // for testing
			api_id=10101
			&api_key=5887832f80ab0ea801c1a6c31e8f2f43 
			&q=[QUERY]
			&ip=[IP]
			&ua=[USER_AGENT]
			&source=[SOURCE]
			&referrer=[REFERRER]
			&min_bid=[MIN_BID]
			&module=default
			*/
			if ($adult != 1) {
				$feed_url  = "http://api.adrenalads.com/request.php?";
				$feed_url .= "api_id=10101";
				$feed_url .= "&api_key=5887832f80ab0ea801c1a6c31e8f2f43";
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&source=" . $domain; 
				//$feed_url .= "&referer=" . $referer;
				$feed_url .= "&min_bid=" . $MIN_RPM;
				$feed_url .= "&module=default";		
			}
		break;
		
		case "affinity":	
			// Affinity should see traffic from US, CA, IN, AU, FR, DE, BR, GB.	
			/*
			http://[customer_key].syn.affinity.com/feed?
			o=[customer_key]
			&s=[site_id]
			&q=[query]
			&qt=[query_type]
			&ip=[X.X.X.X]
			&ua=[user_agent]
			&rf=[http_referer]
			&n=[no_of_ads]
			&requrl=[URL]
			&reqdom=[domain] 
			*/
			if ( !fCheckPrior($MYSQL_CONNECTION, $date, $ip, $domain, $trace) && $adult != 1 && 
				($region == "US" || $region == "CA" || $region == "IN" || $region == "AU" ||
				 $region == "FR" || $region == "DE" || $region == "BR" || $region == "GB"   ) ) {
				$feed_url  = "http://nzh50.syn.affinity.com/feed?";
				$feed_url .= "o=nzh50";
				$feed_url .= "&s=58571";
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&qt=1";
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&rf=" . $referer;
				$feed_url .= "&n=" . $num_ads;
				$feed_url .= "&reqdom=" . $url;
			} 
		break;
		
		case "bluelink":		
			/*
			http://net274.bluelinkmarketing.com/xml.php?
			id_site=274
			&id_pub=275
			&q=[KEYWORD] keywords for ads (example: 'cars')
			&limit=1 number of ads in block (example: 6),
			&ref=[REFERER] link to page where ad block is located (example:'http://example.net/page.html'),
			&ip=[IP_ADDRESS] visitor IP-address (example: '192.168.0.1'),
			&user_agent=[USER_AGENT] information about visitor¹s User-Agent (example:'Mozilla/4.0 (compatible; MSIE 7.0b; Win32)'),
			&accept=text a list of legal source formats (example: 'text/html'),
			&ip_proxy=[IP_PROXY_ADDRESS] // optional IP-address of visitor¹s proxy-server (example:'192.168.0.1'). This parameter is optional.
			&subid=[PUB_SUBID] // optional
			*/
			if (fKeywordsByFeed($MYSQL_CONNECTION, $keywords, $feed, $trace) && 
				 !fCheckPrior($MYSQL_CONNECTION, $date, $ip, $domain, $trace) 
				 && $region == "US" && $adult != 1 && $cookies == true && $bot != true) {
				$referer = urlencode("www.collegecafe.net");
				$feed_url  = "http://net374.bluelinkmarketing.com/xml.php?";
				$feed_url .= "id_site=374";
				$feed_url .= "&id_pub=275";
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&limit=" . $num_ads;
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&user_agent=" . $agent;
				$feed_url .= "&accept=text";
				// $feed_url .= "&ip_proxy=[IP_PROXY_ADDRESS]"; // optional
				$feed_url .= "&subid=" . $platform;
			}
		break;
		
		case "bravemedia":	
			// REGIONS:AR, AU, BR, CA, CL, CO, DE, FR, ID, IT, MX, MY, NZ, PE, PH, SG, TH, UK, US, VE, VN
			if ( ( $region == "AR" 
			    || $region == "AU" 
				 || $region == "BR" 
				 || $region == "CA" 
				 || $region == "CL" 
				 || $region == "CO" 
				 || $region == "DE" 
				 || $region == "FR" 
				 || $region == "ID" 
				 || $region == "IT" 
				 || $region == "MX" 
				 || $region == "MY" 
				 || $region == "NZ" 
				 || $region == "PE" 
				 || $region == "PH"
				 || $region == "SG" 
				 || $region == "TH" 
				 || $region == "UK" 
				 || $region == "US" 
				 || $region == "VE" 
				 || $region == "VN") && $adult != 1 ) {	
				/*
				http://feed.bravexml.com/feed/nsphere/mid.xml?
				ref=http%3A%2F%2Fwww.google.ca
				&search=car+loan
				&subid=ns1300
				&ip=76.89.206.120
				&ua=Mozilla%2F5.0+%28Windows+NT+6.1%3B+WOW64%3B+rv%3A8.0%29+Gecko%2F20100101+Firefox%2F8.0
				&items=1 // option
				*/
				$feed_url  = "http://feed.bravexml.com/feed/nsphere/mid.xml?";
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&search=" . $keywords;
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&items=" . $num_ads;
			}
		break;
		
		case "elephant":
			/*
			http://feed.elephant-traffic.com/nsphere/XMLfeed/?domain=[domain.com]&ip=[X.X.X.X]&agent=[agent]
			*/
			if ( ($region == "US" || $region == "CA") && $adult != 1) {
				$feed_url  = "http://feed.elephant-traffic.com/nsphere/XMLfeed/?";
				$feed_url .= "domain=" . $domain;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&agent=" . $agent;
				$ask_rpm = fGetRPV($domain, $region, $MYSQL_CONNECTION, $trace);
				if ($ask_rpm > 0) $ask_rpm = round(1000 * $ask_rpm); else $ask_rpm = $MIN_BID;
				$feed_url .= "&ask_rpm=" . $ask_rpm;// optional
			}
		break;
		
		case "geoads":
			/*
			http://33140.12519.geoads-xml.com/feed?
			sid=33140
			&auth=U020
			&subid=<subid>
			&q=<query>
			&ip=<ipaddress>
			&ua=<useragent>
			&ref=<referer>
			&count=<count>
			&state=<state>
			&city=<city> 
			*/
			if ($adult != 1) {
				$feed_url  = "http://33140.12519.geoads-xml.com/feed?";
				$feed_url .= "sid=33140";
				$feed_url .= "&auth=U020";
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&count=" . $num_ads;
			}
		break;
		
		case "prostreamedia":		
			/*
			http://prostreamxml.com/feed?sid=29393
			&auth=bk55
			&subid=<subid> // optional
			&q=<query>
			&ip=<ipaddress>
			&ua=<useragent>
			&ref=<referer>
			&count=<count> // optional
			&state=<state> // optional
			&city=<city> // optional
			*/
			if ($adult != 1) {
				$feed_url  = "http://prostreamxml.com/feed?sid=29393";
				$feed_url .= "&auth=bk55";
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&q=" . $keywords; // fKeywordLookup(
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&count=" . $num_ads;
				//$feed_url .= "&state="; // optional
				//$feed_url .= "&city="; // optional
			}
		break;
		
		case "relevad":		
			/*
			http://9289.relestar.com/feed/search/?
			a=9289
			&m=z08kIm4NEeGoCAAwSL7RWA
			&i=1.2.3.4
			&q=hotel
			&s=http%3A%2F%2Fwww.yoursite.com%2Fresults.jsp%3Fquery%3Dhotel
			&u=Mozilla%2F5.0%20%28X11%3B%20U%3B%20Linux%20i686%3B%20en-US%3B%20rv%3A1.9.0.5%29%20Gecko%2F2007120121%20Firefox%2F3.0.5
			&z=123_abc // subid
			&r= // referer, optional
			&l= // language setting, optional
			$M= // max number of ads, optional
			*/
			if ($region == 'US' && $agent != '' && $adult != 1) {
				$feed_url  = "http://9289.relestar.com/feed/search/?";
				$feed_url .= "a=9289"; // affiliate ID
				$feed_url .= "&m=z08kIm4NEeGoCAAwSL7RWA"; // authorization token
				$feed_url .= "&z=" . $PLATFORM_ID;
				$feed_url .= "&i=" . $ip;
				$feed_url .= "&q=" . $keywords; // fKeywordLookup(
				$feed_url .= "&s=" . $url; // full url
				$feed_url .= "&u=" . $agent;
				$feed_url .= "&r=" . $referer; // optional
				$feed_url .= "&M=" . $num_ads; // max number of ads, optional
				//$feed_url .= "&l"; // language, optional
			}
		break;
		
		case "searchmylocal":		
			/*
			http://xml.searchmylocal.com/search?feed=2624
			&auth=TxVvmU
			&subid=ns1300
			&ua={ua}
			&url=[domain]
			&count=1
			&user_ip=[ip]
			&query=[keywords] 
			*/
			if ($session_data && $adult != 1) {
				$feed_url  = "http://xml.searchmylocal.com/search?feed=2624";
				$feed_url .= "&auth=TxVvmU";
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&ua=" . $agent; // optional
				$feed_url .= "&url=" . $domain; // optional
				$feed_url .= "&count=" . $num_ads; // optional
				$feed_url .= "&user_ip=" . $ip;
				$feed_url .= "&query=" . $keywords;
			}
			
		break;
		
		case "seedcorn":
			/*
			http://34108.10678.seedcornppc-xml.com/feed?
			sid=34108
			&auth=3t1b
			&subid=<subid>
			&q=<query>
			&ip=<ipaddress>
			&ua=<useragent>
			&ref=<referer>
			&count=<count> // optional
			&state=<state>// optional
			&city=<city> // optional
			*/
			if ($adult != 1) {
				$feed_url  = "http://34108.10678.seedcornppc-xml.com/feed?";
				$feed_url .= "sid=34108";
				$feed_url .= "&auth=3t1b";
				$feed_url .= "&subid=" . $PLATFORM_ID;
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&ref=" . $referer;
				$feed_url .= "&count=" . $num_ads;
				//$feed_url .= "&state="; // optional
				//$feed_url .= "&city="; // optional
			}
		break;
		
		case "sendori":
			/*
			http://zeroclick.sendori.com/domain/feed?apikey=abc123
			&domain=example.org
			&request_url=http%3A%2F%2Fexample.org%2F
			&ip=63.100.1.1
			&ask_rpm=25
			&referer=
			&agent=Mozilla%2F5.0%20(X11%3B%20U%3B%20Linux%20i686%3B%20en-US%3B%20rv%3A1.9.2.16)%20Gecko%2F20110323%20Ubuntu%2F10.10%20(maverick)%20Firefox%2F3.6.16
			*/
			if (($region == "US" || $region == "CA") && 
				(substr($agent, 0, 7) == "Mozilla" || substr($agent, 0, 5) == "Opera") && 
				$adult != 1) {
				$ask_rpm = fGetRPV($domain, $region, $MYSQL_CONNECTION, $trace);
				if ($ask_rpm > 0) $ask_rpm = round((1000 * $ask_rpm), 2); else $ask_rpm = $MIN_BID;
				$feed_url  = "http://zeroclick.sendori.com/domain/feed?apikey=yFL2jNeC";
				$feed_url .= "&domain=" . $domain;
				$feed_url .= "&request_url=" . urlencode("http://" . $domain);
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ask_rpm=" . $ask_rpm;
				$feed_url .= "&referrer=" . $referer;
				$feed_url .= "&agent=" . $agent;
			}
		break;

		case "stormwest":
			/*
			http://netsphere.stormwest.com/request?
			domain=$domain
			&ip=$ip
			&referer=$ref
			&useragent=$user_agent
			&url=$domain
			*/
			if ($adult != 1) {
				$feed_url  = "http://netsphere.stormwest.com/request?";
				$feed_url .= "domain=" . $domain;
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&referer=" . $referer;
				$feed_url .= "&useragent=" . $agent;
				$feed_url .= "&url=" . $url;
			}
		break;
		
		case "strata":
			/*
			http://psfbids.com/?api_key=64afe108d8b8c8f3e75762131542d5ad
			&domain_name=[DOMAIN_NAME]
			&keyword=[KEYWORD]
			&ip_address=[IP_ADDRESS]
			&user_agent=[USER_AGENT]
			*/
			if ( !fCheckPrior($MYSQL_CONNECTION, $date, $ip, $domain, $trace) && $region == "US" && $adult != 1 && $bot != true ) {
				$feed_url  = "http://psfbids.com/?api_key=64afe108d8b8c8f3e75762131542d5ad";
				$feed_url .= "&domain_name=" . $domain;
				$feed_url .= "&keyword=" . $keywords;
				$feed_url .= "&ip_address=" . $ip;
				$feed_url .= "&user_agent=" . $agent;
			}
		break;
		
		case "wheresbigfoot":
			/*
			http://bxi99.xml.wheresbigfoot.com/feed?
			o=bxi99
			&s=46732
			&q=travel
			&qt=1
			&ip=216.139.221.20
			&ua=Mozilla/5.0%20%28Windows%20NT%206.1%3B%20WOW64%29%20AppleWebKit/535.7%20%28KHTML%2C%20like%20Gecko%29%20Chrome/16.0.912.75%20Safari/535.7
			&rf=admin.pub.wheresbigfoot.com
			&n=10
			*/
			if (($region == "US" || $region == "AU") && $adult != 1 && $keywords) {
				$feed_url  = "http://bxi99.xml.wheresbigfoot.com/feed?";
				$feed_url .= "o=bxi99";
				$feed_url .= "&s=46732";
				$feed_url .= "&q=" . $keywords;
				$feed_url .= "&qt=1";
				$feed_url .= "&ip=" . $ip;
				$feed_url .= "&ua=" . $agent;
				$feed_url .= "&rf=" . $referer;
				$feed_url .= "&n=" . $num_ads;
			}
		break;
	}
	if ($trace > 1) {
		echo "<br /><b>XML FEED:</b> ", $feed_url, "<br /><br />";
		flush(); @ob_flush();
	}
	return $feed_url;
}

/** =====================================================================================================================
    Checks to see a particular domain /ip address has been search for a particular date
	=====================================================================================================================
	@PARAMETERS: 
		$ip as string			- ip address
		$domain as string		- domain name
		$date as string		- date in YMD format to select proper table
	
	@RETURNS:
		$var as bool		 	- Returns true if it has been found, false if new
* ======================================================================================================================*/
function fCheckPrior($MYSQL_CONNECTION, $date, $ip, $domain = '', $trace = 0, $limit = 1) {
	global $TABLE_PLATFORM_PREFIX, $TABLE_SESSION_POSTFIX;
	
	$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX;
	
	$sql = "SELECT * FROM " . $table . " WHERE ip='" . $ip . "'";
	if ($domain != "") $sql .= " AND domain='" . $domain ."'";
	if ($trace == 3) {
		echo "fCheckPrior SQL: " . $sql . "<br />";
		flush(); @ob_flush();
	}
	
	$results = mysql_query($sql, $MYSQL_CONNECTION) or die(); // write to db
	$check = array();
	while ($row = mysql_fetch_row($results) ) {
		$check[] = $row; 
	}
	
	if ($trace == 3) {
		echo "fCheckPrior #results: " . sizeof($check) . ", limit = ". $limit . "<br />";
		flush(); @ob_flush();
	}
	if (sizeof($check) > $limit) return true; else return false;
}

/** =====================================================================================================================
    Checks to see if tables present for the date passed in, creates it if not
	=====================================================================================================================
	@PARAMETERS: 
		$date as string		- date passed in as YMD
		$MYSQL_CONNECTION		- connection object
	
	@RETURNS:
		nothing
* ======================================================================================================================*/
function fCheckTables($date, $MYSQL_CONNECTION) {
	global $TABLE_PLATFORM_PREFIX, $TABLE_SESSION_POSTFIX, $TABLE_BIDS_POSTFIX;
	
	// Generate Table Names
	$session_table_name = $TABLE_PLATFORM_PREFIX . $date . $TABLE_SESSION_POSTFIX;
	$bid_table_name = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
	
	// Check for Sessions Table
	if( !mysql_num_rows( mysql_query("SHOW TABLES LIKE '". $session_table_name ."'", $MYSQL_CONNECTION))) { // Table not present
		// Create a MySQL table in the selected database
		mysql_query("CREATE TABLE " . $session_table_name . " (
					PRIMARY KEY(id),
					datetime DATETIME,
					platform VARCHAR(2) NOT NULL,
					id VARCHAR(32) NOT NULL,
					time VARCHAR(32) NOT NULL,
					time_elapsed VARCHAR(32),
					agent VARCHAR(255),
					region VARCHAR(32),
					ip VARCHAR(32),
					url VARCHAR(255),
					domain VARCHAR(255),
					referer VARCHAR(255),
					keywords VARCHAR(255),
					num_ads TINYINT,
					cookies TINYINT(2),
					javascript TINYINT(2),
					adult TINYINT(2),
					bot TINYINT(2),
					blackflag TINYINT(2)
					) TYPE=INNODB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
		, $MYSQL_CONNECTION ) or die(mysql_error());  
	}
	
	// Check for Bids Table
	if( !mysql_num_rows( mysql_query("SHOW TABLES LIKE '". $bid_table_name ."'", $MYSQL_CONNECTION))) { // Table not present
		// Create a MySQL table in the selected database
		mysql_query("CREATE TABLE " . $bid_table_name . " (
					PRIMARY KEY(bid_id),
					bid_id BIGINT NOT NULL AUTO_INCREMENT,
					id VARCHAR(32) NOT NULL, INDEX (id),
					feed VARCHAR(127) NOT NULL,
					time VARCHAR(32) NOT NULL,
					time_elapsed VARCHAR(32),
					keywords VARCHAR(255),
					xml_feed TEXT,
					ratio FLOAT (7,5),
					bid_calc FLOAT (10,6),
					bid FLOAT (10,6) NOT NULL,
					redirect TEXT NOT NULL,
					title VARCHAR(127),
					description TEXT,
					url TEXT,
					position TINYINT(10) Default 0,
					template VARCHAR(32),
					won TINYINT(1) Default 0,
					status VARCHAR(255)
					) TYPE=INNODB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
		, $MYSQL_CONNECTION ) or die(mysql_error());  
	}
	return;
}

/** =====================================================================================================================
    Function to return region, description from IP Address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address
	
	@RETURNS:
		$location as string	 		- [0] region
* ======================================================================================================================*/
function fDomainLookup($MYSQL_CONNECTION, $trace = 0) {
	global $TABLE_DOMAIN_KEYWORDS, $DOMAINS_INDEX, $DEFAULT_DOMAIN;
	$domain = array();
	
	$rnd = rand (1, $DOMAINS_INDEX);
	$sql = "SELECT Domain, Keyword FROM " . $TABLE_DOMAIN_KEYWORDS . " LIMIT $rnd, 1";
	$result = mysql_query($sql, $MYSQL_CONNECTION);
	$row = mysql_fetch_assoc($result);
	$domain['domain'] = $row['Domain'];
	$domain['keywords'] = $row['Keyword'];
	
	if ($trace == 3) {
		echo "fDomainLookUp called. Domain = ". $domain['domain'] ." , Keywords = " .$domain['keywords'] ."<br />";
		flush(); @ob_flush();
	}

	if (isset($domain)) {
		return $domain;
	} else {
		return $DEFAULT_DOMAIN;
	}
}

/* =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fGetFeeds($platform, $MYSQL_CONNECTION, $trace = 0){
	global $TABLE_FEEDS;
	
	if ($platform == "dc" || $platform == "zc") {
		$sql = "SELECT * FROM " . $TABLE_FEEDS . " WHERE "  . $platform . "_active='1'";
	} else {
		$sql = "SELECT * FROM " . $TABLE_FEEDS . " WHERE zc_active='1'"; // USE ZERO CLICK PLATFORM IF MOST LIKELY A BOT
	}
	if ($trace > 2) echo "fGetFeeds called for the $platform platform. SQL: $sql<br />";
	$results = array();
	$results = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION, $trace); // retrieve the feeds for that platform
	return $results;
}

/** =====================================================================================================================
    Returns time in sec.microseconds format
	=====================================================================================================================
	@PARAMETERS: 
		$e as int				- number of decimal places for microseconds
	
	@RETURNS:
		$time as string	 	- returns time in seconds.microseconds format
* ======================================================================================================================*/
function fGetMicroTime($e = 7) {
    list($u, $s) = explode(' ',microtime());
    return bcadd($u, $s, $e);
} 

/** =====================================================================================================================
    Returns time in sec.microseconds format
	=====================================================================================================================
	@PARAMETERS: 
		$domain as string		- domain to lookup
		$region as string		- region the ip comes back with
		$MYSQL_CONNECTION		- connection object
	
	@RETURNS:
		$bid as float	 		- returns bid as float, -1 if not in db
* ======================================================================================================================*/
function fGetRPV($domain, $region, $MYSQL_CONNECTION, $trace = 0) {
	global $TABLE_RPV; // globals required
	if ($domain == "") return -1; // no domain provided
	if (empty($region) ) $region = "ZZ";
	
	($region == "US" || $region = "ZZ") ? $field = "rpv_us" : $field = "rpv_intl"; // which field to retrieve
		
	$sql = "SELECT " . $field . " FROM " . $TABLE_RPV . " WHERE domain like'%" . $domain . "%'";
	if ($trace == 3) {
		echo "SQL_RPM: ", $sql, "<br />";
		flush(); @ob_flush();
	}
	$results = mysql_query($sql, $MYSQL_CONNECTION) or die( mysql_error() ); 
	if ($trace == 3) {
		echo "BID: ", $row[0], "<br />";
		flush(); @ob_flush();
	}
	$row = mysql_fetch_row($results);
	if ( $row[0] > 0 ) $bid = $row[0]; else $bid = -1;
	if ($trace == 3) {
		echo "BID: ",  $bid, "<br />";
		flush(); @ob_flush();
	}
	//if ( $row[0] >= 0) return $row[0]; else return -1;
	return $bid;
} 

/** =====================================================================================================================
    Function to check the frequency of IP per feed to filter multiple hits
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address
	
	@RETURNS:
		results as bool	 			- True if blacklisted, otherwise false
* ======================================================================================================================*/
function fIPfreqCheck($ip = 0, $date, $count = 0, $MYSQL_CONNECTION, $trace = 0) {
	global $TABLE_BIDS_PREFIX; // globals required
	if ($ip == 0) return -1; // no ip provided

	$sql = "SELECT COUNT(ip) FROM " . $TABLE_BIDS_PREFIX . $date . " WHERE ip='" . $ip . "'";
	if ($trace == 3) {
		echo "SQL: ", $sql, "<br />";
		flush(); @ob_flush();
	}
	$results = mysql_query($sql, $MYSQL_CONNECTION) or die( mysql_error() ); 
	$row = mysql_fetch_row($results);
	if ($trace == 3) {
		echo "COUNT: ", $row[0], "<br />";
		flush(); @ob_flush();
	}
	if ($row[0] <= $count) return true; else return false;
}

/** =====================================================================================================================
    Function to return region, description from IP Address
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address
	
	@RETURNS:
		$location as string	 		- [0] region
* ======================================================================================================================*/
function fIPlookup($ip_address = 0, $MYSQL_CONNECTION) {
	global $TABLE_IP_LOCATIONS; // globals required
	
	if ($ip_address == 0) return -1;

	list($w, $x, $y, $z) = split('[.]', $ip_address);
	$w = ( $w * 16777216 ) ;
	$x = ( $x * 65536    ) ;
	$y = ( $y * 256      ) ;
	$z = ( $z            ) ;
	$ip_number = $w + $x + $y + $z;
	
	$query = "SELECT Country_Code, Country FROM ".$TABLE_IP_LOCATIONS." WHERE Begin_Number < \"$ip_number\" and End_Number > \"$ip_number\"";
	$result = mysql_query($query, $MYSQL_CONNECTION); // Query the database
	$location = array();
	while ($row = mysql_fetch_row($result) ) {
		$location[] = $row; 
	}
	unset($result, $row);
	
	if ($location[0][0]) return $location[0][0]; else return -1;
}

/** =====================================================================================================================
    Function to check IP in blacklist
	=====================================================================================================================
	@PARAMETERS: 
		$ip_address as string		- ip address
	
	@RETURNS:
		results as bool	 			- True if blacklisted, otherwise false
* ======================================================================================================================*/
function fIPcheck($ip = 0, $MYSQL_CONNECTION, $trace = 0) {
	global $TABLE_IP_BLACKLIST; // globals required
	if ($trace == 3) {
		echo "IP: ", $ip, "<br />";
		flush(); @ob_flush();
	}
	if ($ip == 0) return -1; // no ip provided

	//$ip = explode('.', $ip);
	//$sql = "SELECT COUNT(quad0) FROM " . $TABLE_IP_BLACKLIST . " WHERE quad0=".$ip[0]." AND quad1=".$ip[1]." AND quad2=".$ip[2]." AND quad3=".$ip[3]."";
	$sql = "SELECT COUNT(*) FROM $TABLE_IP_BLACKLIST WHERE INET_ATON('$ip') >= ip_start AND INET_ATON('$ip') <= ip_end";
	if ($trace) echo "SQL: ", $sql, "<br />";
	$results = mysql_query($sql, $MYSQL_CONNECTION) or die( mysql_error() ); 
	$row = mysql_fetch_row($results);
	
	if ($row[0] < 1) return 0; else return 1;
}

/** =====================================================================================================================
    Function to start asynchronous PHP files
	=====================================================================================================================
	@PARAMETERS: 
		$server as string		- server the script is installed on
		$url as string			- the url to append, with GET variables included
	
	@RETURNS:
		$fp as filepointer	- file pointer to file stream
* ======================================================================================================================*/
function fJobStartAsync($server, $url, $port=80, $conn_timeout=30, $rw_timeout=86400) {
	$errno = '';
	$errstr = '';
	
	set_time_limit(5); // script timeout
	
	$fp = fsockopen($server, $port, $errno, $errstr, $conn_timeout); // open socket connection and retrieve file pointer
	if (!$fp) { // failed to open connection
	   echo "$errstr ($errno)<br />\n";
	   return false;
	} else {
		$out = "GET $url HTTP/1.1\r\n";
		$out .= "Host: $server\r\n";
		$out .= "Connection: Close\r\n\r\n";
		
		stream_set_blocking($fp, false); // set stream to receive data as it comes in
		stream_set_timeout($fp, $rw_timeout); // set stream timeout
		
		fwrite($fp, $out); // write values to stream
		
		return $fp; // return file pointer
	}
}

/** =====================================================================================================================
    Polling function
	=====================================================================================================================
	@PARAMETERS: 
		$fp as filepointer	- file pointer to file stream
	
	@RETURNS:
		file stream 		 	- contents of file stream
* ======================================================================================================================*/
function fJobPollAsync(&$fp) {
	if ($fp === false) return false;
	
	if (feof($fp)) { // check for end of file
		fclose($fp);
		$fp = false;
		return false;
	}
	
	return fread($fp, 10000); // return data from file stream from file pointer
}

/* =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fKeywordsByFeed($MYSQL_CONNECTION, $keywords = '', $feed='', $trace = 0) {
	if ($trace > 2) echo "fKeywordsByFeed. keywords = $keywords, feed = $feed <br />";
	
	if ($keywords == '' || $feed == '') return -1;
	
	$keywords = urldecode($keywords);
	
	$sql = "SELECT COUNT(keywords)  FROM keywords_$feed WHERE keywords LIKE '%$keywords%'";
	if ($trace > 2) echo "fKeywordsByFeed SQL: |$sql| <br />";
	$result = mysql_query($sql, $MYSQL_CONNECTION);
	
	if ($result > 0) return 1; else return 0;
}

/** =====================================================================================================================
    Looks up keywords associated with a domain
	=====================================================================================================================
	@PARAMETERS: 
		$domain as string		- The domain to lookup
	
	@RETURNS:
		$keywords as string 	- keywords URL-encoded if present, NULL if not
* ======================================================================================================================*/
function fKeywordLookup($domain = '', $MYSQL_CONNECTION, $trace = 0) {
	global $TABLE_DOMAIN_KEYWORDS, $TABLE_KEYWORDS,$KEYWORDS_INDEX, $DEFAULT_DOMAIN;
	
	if ($trace == 3) echo "fKeywordLookup called. domain = " . $domain . ", default domain=$DEFAULT_DOMAIN";
	
	$keywords = array();
	
	if ($domain != $DEFAULT_DOMAIN && !empty($domain) && !isset($domain) && $domain != '') { // if domain present and not default, pass in
		if ($trace == 3) echo ", using domain, ";
		
		$sql = "SELECT Keyword FROM " . $TABLE_DOMAIN_KEYWORDS . " WHERE Domain='" . $domain ."'";
		$result = mysql_query($sql, $MYSQL_CONNECTION);
		
		while ($row = mysql_fetch_row($result)) {
			$keywords[] = $row;
		}
	} else { // get random
		if ($trace == 3) echo ", using random, ";
		
		// $KEYWORDS_INDEX
		$rnd = rand (1, $KEYWORDS_INDEX);
		$sql = "SELECT keywords FROM " . $TABLE_KEYWORDS . " WHERE keyword_index=" . $rnd ."";
		$result = mysql_query($sql, $MYSQL_CONNECTION);
		while ($row = mysql_fetch_row($result)) {
			$keywords[] = $row;
		}
	}
	
	if ($trace == 3) echo "keywords = " . $keywords[0][0] . "<br />";
	
	if (isset($keywords[0][0])) {
		return $keywords[0][0];
	} else {
		return $domain;
	}
}

/* =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fKeywordsRelated($MYSQL_CONNECTION, $keywords = '', $num_req = 10, $trace = 0) {
	global $TABLE_KEYWORDS_RELATED;
	
	if ($trace == 3) echo "fKeywordsRelated called with keyword(s): |$keywords| <br />";
	
	$sql =  "SELECT keywords FROM $TABLE_KEYWORDS_RELATED WHERE ad_group LIKE "; // begin SQL
	$search_terms = explode(' ',$keywords); // split if phrase
	$i = 0; // to identify first term
	foreach ($search_terms as $term) { // parse phrase
		if ($i == 0) {
			$sql .= "'%$term%' "; // first term
		} else {
			$sql .= "OR ad_group LIKE '%$term%' "; // every term after
		}
		$i++; // increment count
	}
	$sql .=  "GROUP BY keywords ORDER BY cpc DESC LIMIT 0,$num_req"; // complete SQL
	$results = fQuery_Indexed_Array($sql, $MYSQL_CONNECTION, $trace); // call db
	
	if (sizeof($results) > 0) {
		if ($trace == 3) echo "|".sizeof($results) . "| results returned. <br />";
		for ($i = 0; $i < sizeof($results); ++$i) {
			$results[$i][1] = urlencode($results[$i][0]);
		}
		return $results; 
	} else {
		if ($trace == 3) echo "No results returned. <br />";
		return -1; // return results or error if none.
	}
}


/* =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fMarkTime($start){
	return (fGetMicroTime() - $start);
}

/** =====================================================================================================================
    Generates new ID
	=====================================================================================================================
	@PARAMETERS: 
		<none>					- no parametrs
	
	@RETURNS:
		md5 integer			 	- session id in MD5 format
* ======================================================================================================================*/
function fNewId($trace = 0){
	$new_id = md5(microtime(true));
	if ($trace > 1) echo "NewID generated: $new_id<br />";
	return $new_id;
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fParseFeed(&$bids, &$xml, &$session, $template = 'default', $trace = 0) {
	global $TO_RPC, $NUM_ADS;
	
	/*
	id
	time
	time_elapsed
	feed
	xml_feed
	bid
	redirect
	title
	description
	url
	position
	url
	position
	template
	won
	status
*/
	
	if ($trace > 1) {
		echo "fParseFeed called.<br /><br />";
		echo "BID INFO:<br />";
		foreach($bids as $key => $value) {
			echo "<b>" . strtoupper($key) . ":</b> " . $value . "<br />";
		}
		/*
		echo "SESSION INFO:<br />";
		foreach($session as $key => $value) {
			echo "<b>" . strtoupper($key) . ":</b> " . $value . "<br />";
		}*/
		flush(); @ob_flush();
	}
	
	$feed = $bids['feed'];
	$i = 0;
	
	switch($feed) {
		case "7search": 
			while ( $xml->SITE[$i] ) {		
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->SITE[$i]->BID);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string($xml->SITE[$i]->URL);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string($xml->SITE[$i]->NAME);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string($xml->SITE[$i]->DESCRIPTION);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string($xml->SITE[$i]->HTTPLINK);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "adknowledge": 
			if ( $xml->adresults ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float)$xml->adresults->record[0]->bidprice);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->adresults->record[0]->clickurl);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->adresults->record[0]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->adresults->record[0]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->adresults->record[0]->url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "adlux": 
			while ( $xml->results->sponsored->listing[$i] ) {		
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float)$xml->results->sponsored->listing[$i]->cpc);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->displayurl);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "admanage":
			while ( $xml->listings->listing[$i] ) {		
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->listings->listing[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->listings->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->listings->listing[$i]->descr);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->host);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "admanage_adult":
			while ( $xml->listings->listing[$i] ) {		
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->listings->listing[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->listings->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->listings->listing[$i]->descr);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->host);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "admanage_premium":
			while ( $xml->listings->listing[$i] ) {		
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->listings->listing[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->listings->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->listings->listing[$i]->descr);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->listings->listing[$i]->host);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;
		
		case "admarketplace":
			while ( $xml->adlistings->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->adlistings->listing[$i]->bid_price);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->adlistings->listing[$i]->clickurl);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->adlistings->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->adlistings->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->adlistings->listing[$i]->displayurl);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			} 
		break;	
		
		case "admedia":
			while ( $xml->results->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->results->listing[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->listing[$i]->redirect);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->results->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->results->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->results->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "adrenalads":
			if ( $xml->bid ) { 
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->destination_url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->display_url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
			}
		break;

		
		case "affinity": 
			while ( $xml->ad[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->ad[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->ad[$i]->rurl);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->ad[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->ad[$i]->abstract);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->ad[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "bluelink": 
			while ( $xml->result[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->result[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->result[$i]->click_url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->result[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->result[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->result[$i]->display_url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "bravemedia": 
			if ( $xml->results->cost ) { 
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->results->cost);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->link);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
			}
		break;
		
		case "elephant": 
			if ( $xml->rpm ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->rpm) * $TO_RPC;
				$bids[$feed]['ads']['ad'.$i]['redirect']		= mysql_real_escape_string((string)$xml->redirect);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
			}
		break;
		
		case "geoads":
			while ( $xml->results->sponsored->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->results->sponsored->listing[$i]->cpc);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->displayurl);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "prostreamedia":
			while ( $xml->results->sponsored->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->results->sponsored->listing[$i]->cpc);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->displayurl);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "relevad": 
			while ( $xml->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->listing[$i]->bid);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->listing[$i]->title);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->listing[$i]->description);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->listing[$i]->site);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;

		case "searchmylocal": 
			while ( $xml->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->listing[$i]['bid']);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->listing[$i]['url']);
				$bids[$feed]['ads']['ad'.$i]['title'] 			= mysql_real_escape_string((string)$xml->listing[$i]['title']);
				$bids[$feed]['ads']['ad'.$i]['description'] 	= mysql_real_escape_string((string)$xml->listing[$i]['descr']);
				$bids[$feed]['ads']['ad'.$i]['url'] 			= mysql_real_escape_string((string)$xml->listing[$i]['site']);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;

		case "seedcorn": 
			while ( $xml->results->sponsored->listing[$i] ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->results->sponsored->listing[$i]->cpc);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string)$xml->results->sponsored->listing[$i]->url);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
				$i++;
			}
		break;
		
		case "sendori":
			if ( $xml->rpm ) { 
				$bids[$feed]['ads']['ad0']['bid'] 				= ((float) $xml->rpm) * .001;
				$bids[$feed]['ads']['ad0']['redirect'] 		= mysql_real_escape_string((string)$xml->redirect);
				$bids[$feed]['ads']['ad0']['url'] 				= mysql_real_escape_string((string)$xml->domain);
				$bids[$feed]['ads']['ad0']['bid_calc'] 		= $bids[$feed]['ads']['ad0']['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad0']['template'] 		= $template;
			}
		break;
		
		case "stormwest": 
			if ( ((string) $xml->response['accepted']) == 'true' ) {
				$bids[$feed]['ads']['ad'.$i]['bid'] 			= ((float) $xml->response['bid']);
				$bids[$feed]['ads']['ad'.$i]['redirect'] 		= mysql_real_escape_string((string) $xml->response['url']);
				$bids[$feed]['ads']['ad'.$i]['bid_calc'] 		= $bids[$feed]['ads']['ad'.$i]['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
			} 
		break;
		
		case "strata": 
			if ( ((float) $xml->cpv > 0) ) { 
				$bids[$feed]['ads']['ad0']['bid'] 				= ((float) $xml->cpv);
				$bids[$feed]['ads']['ad0']['redirect'] 		= mysql_real_escape_string((string)$xml->redirect_url);
				$bids[$feed]['ads']['ad0']['bid_calc'] 		= $bids[$feed]['ads']['ad0']['bid'] * $bids['ratio']; // Factorer
				$bids[$feed]['ads']['ad0']['template'] 		= $template;
			}
		break;
		
		case "wheresbigfoot": 
			if ( ((int) $xml->results->count) > 0 ) { 
				$bids[$feed]['ads']['ad0']['bid'] 				= ((float) $xml->results->sponsored->listing[0]->cpc);
				$bids[$feed]['ads']['ad0']['redirect'] 		= mysql_real_escape_string((string)$xml->results->sponsored->listing[0]->url);
				$bids[$feed]['ads']['ad'.$i]['template'] 		= $template;
			}
		break;
		
		default: // no bid
			return -1;
		break;	
	}
	( sizeof($bids['ads']) > 0) ? $bids['status'] .= "bid, responded, " : $bids['status'] .= "responded, ";		
	if ($trace > 2) {
		echo "SIZE AFTER PARSING: " . sizeof($bids) . "<br />";	
		flush(); @ob_flush();
	}
	return 1;
}

/* =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fPingFeeds(&$FEEDS, &$session_vars, $template = '', $trace = 0) {
	global $PING_PATH, $SITE_DOMAIN, $MAX_TIME;
	
	if ($trace) {
		echo "fPingFeeds called. $SITE_DOMAIN<br />";
	}
	
	$fp = array();
	for ($i = 0; $i < sizeof($FEEDS); ++$i) { // build feed URL to send to multi-processes
		$which = $PING_PATH. '?feed=' . $FEEDS[$i]['feed_id'];
		$which .= '&ratio=' . $FEEDS[$i][$platform . '_ratio']; 
		$which .= '&session_id='. $session_vars['id'];
		$which .= '&template='. $template;
		$which .= '&trace=' . $trace; 
		$fp[] = fJobStartAsync('www.'.$SITE_DOMAIN, $which);	
	}
	
	$start_loop = fGetMicroTime();
	while (fMarkTime($start_loop) < $MAX_TIME) {
		if ($trace) sleep(2); // lag for output
		
		$feed_count = sizeof($FEEDS);
		$feed_status = array();
		
		for ($i = 0; $i < $feed_count; ++$i) {
			$feed_status[$i] = fJobPollAsync($fp[$i]);
			if ($feed_status[$i] === false) { 
				--$feed_count; 
			} else {
				if ($trace > 0) {
					echo "============================================<br />";
					echo ($i + 1), ") <b>".$FEEDS[$i]['feed_id']."</b> = $feed_status[$i]<br /><br />";
				}
			}
		}
		//flush(); @ob_flush();
		unset($feed_status);
		if ($feed_count < 1) break;
	}
	if ($trace) echo "fPingFeeds done.<br />";
	return;
}

/** =====================================================================================================================
    Function takes in url as string to redirect via 301 or 302
	=====================================================================================================================
	@PARAMETERS: 
		$url as string			- url to redirect to
		$type as int			- form of redirect
	
	@RETURNS:
		nothing
* ======================================================================================================================*/
function fRedirect($url, $type=301) {
	header_remove();
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
	header('Location: ' . $url);
	
	/*echo "<meta http-equiv=\"refresh\" content=\"0; URL=".$url."\" />";*/
	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="refresh" content="0;URL='.$url.'" /></head><title>Redirecting...</title><body></body></html>';
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWhoWon_OLD($time, $date, $MYSQL_CONNECTION) {
	global $TRACKING_DB_NAME, $TABLE_BIDS_PREFIX, $MIN_RPM, $trace;
	
	$table = $TABLE_BIDS_PREFIX . $date;
	
	$sql = "SELECT * FROM " . $table . " WHERE time=" . $time;
	if ($trace) echo "SQL: ", $sql, "<br />";
	$results = mysql_query($sql, $MYSQL_CONNECTION) or die(); // write to db
	$bids = array();
	while ($row = mysql_fetch_row($results) ) {
		$bids[] = $row; 
	}
	unset($results, $row);
	
	/* BID EVALUATION ALGORITHM
	[ ] initilialize bid_min = bid_max = bin_min_2nd = bin_max_2nd = $max_weight = $i = 0
	[ ] retrieve bids from db and XML
	[ ] init bids array and push bids to index 0
	[ ] if size of bids[$i] > 1 // staged bids: first pass on minimum
		[ ] loop through bids[$i]
			[ ] if feed_min >= bid || feed_max >= bid	 // this makes sure the max bid is never less than the current bid
				[ ] if feed_min > bid_min // raise bid if greater
					[ ] bin_min_2nd = bid
					[ ] bid = bids[$i][min]
				[ ] push bid[$i+1] to this round // will add bids if greater than or equal to current bid in either max or min bid
	[ ] else if size of bids[$i] == 1 // only one bid
		[ ] return bids[$i] // winner
	[ ] if sizeof bids[$i] == 0 return error // no bids at all
	++$i;
	[ ] if size of bids[$i] > 1 // we don't have a winner on min bids, make pass on max values
		[ ] loop through bids[$i]
			[ ] if feed_max >= bid	 // this makes sure the max bid is never less than the current bid
				[ ] if feed_max > bid // raise bid if greater
					[ ] bin_2nd = bid
					[ ] bid = bids[$i][max]
				[ ] push bid[$i+1] to this round // will add bids if greater than or equal to current bid in either max or min bid
	[ ] else if size of bids[$i] == 1 // only one bid
		[ ] return bids[$i] // winner
	[ ] if sizeof bids[$i] == 0 return error // no bids at all
	++$i;
	[ ] if size of bids[$i] > 1 // we don't have a winner on max bids, make pass on max_weight
		[ ] loop through bids[$i]
			[ ] if feed_max > max_weight // raise bid if greater
				[ ] bin_2nd = max_weight
				[ ] max_weight = bids[$i][max]
			[ ] push bid[$i+1] to this round // will add bids if greater than or equal to current bid in either max or min bid
	[ ] else if size of bids[$i] == 1 // only one bid
		[ ] return bids[$i] // winner
	[ ] if sizeof bids[$i] == 0 return error // no bids at all
	++$i;
	[ ] if size of bids[$i] > 1 // we don't have a winner on max bids, make pass on max_weight
		[ ] loop through bids[$i]
			[ ] if feed_max > max_weight // raise bid if greater
				[ ] bin_2nd = max_weight
				[ ] max_weight = bids[$i][max]
			[ ] push bid[$i+1] to this round // will add bids if greater than or equal to current bid in either max or min bid
	[ ] else if size of bids[$i] == 1 // only one bid
		[ ] return bids[$i] // winner
	[ ] if sizeof bids[$i] == 0 return error // no bids at all
	
	*/
	
	if (sizeof($bids) > 0) { 
		// set default
		$max_id = 0;
		$max_bid = 0;
		// check bids
		for ($i = 0; $i < sizeof($bids); ++$i) {
			if ($bids[$i][12] > $max_bid ) { // if greater, select
				$max_bid = $bids[$i][12]; // max bid set to value of current bid
				$max_id = $i; // set id to id of current bid 
			}
		}
		
		if ($max_bid > $MIN_RPM) {// if best bid is greater than minimum, update as won
			$sql = "UPDATE " . $table . " SET won=1 WHERE time='" . $time . "' AND id='" . $bids[$max_id][2] . "' AND feed='" . $bids[$max_id][3] ."' ";
			mysql_query($sql, $MYSQL_CONNECTION) or die(); // write to db
			if ($trace) echo "SQL: ", $sql, "<br />";
			return $bids[$max_id][13]; // returns redirect URL
		} else {
			return -1; // no bids greater than MIN_RPM
		}
	} else { // no bids returned
		return -1;
	}
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWhoWon($MYSQL_CONNECTION, $platform, $date, $session_id, $num_ads = 1, $trace = 0) {
	global $TABLE_PLATFORM_PREFIX, $TABLE_BIDS_POSTFIX, $MIN_RPM;
	
	$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
	
	$sql = "SELECT * FROM " . $table . " WHERE id='" . $session_id . "' ORDER BY bid_calc DESC LIMIT 0,".$num_ads;
	if ($trace > 2) echo "fWhoWon SQL: $sql<br />";	
	$bids = array(); // clear for results
	$bids = fQuery_Assoc_Multiple($sql, $MYSQL_CONNECTION, $trace);
	
	if ($platform == "dc" || $platform == "ac") { // direct click or AdClick
		if ((float) $bids[0]['bid'] > 0) { // we have bids
			if ($trace > 2) echo "fWhoWon returned ".sizeof($bids)." ads.<br />";	
			return $bids;
		} 
	} else { // zero click
		if ($trace > 2) {
			echo "FEED: |" . $bids[0]['feed'] . "|<br />"; // ZeroClick
			echo "WINNING BID: |" . $bids[0]['bid'] . "|<br />"; // ZeroClick
			echo "MIN_RPM: $MIN_RPM<br />";
			echo "SIZE OF BIDS: " . sizeof($bids) . "<br />";
		}
		if ((float) $bids[0]['bid'] >= $MIN_RPM) {// if best bid is greater than minimum, update as won
			$sql  = "UPDATE ".$table." SET won=1 ";
			$sql .= "WHERE time='".$bids[0]['time']."' AND bid_id='".$bids[0]['bid_id']."' AND id='".$bids[0]['id']."' AND feed='".$bids[0]['feed']."' ";
			if ($trace == 3) echo "UPDATE SQL: $sql<br />";
			mysql_query($sql, $MYSQL_CONNECTION) or die(); // write to db
			return $bids[0]['redirect']; // returns redirect URL
		} 
	}
	if ($trace > 1) {
		echo "NO WINNING BIDS!<br />"; 
	}
	return -1; // no valid bids
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWriteBids($MYSQL_CONNECTION, &$bids, $date, $trace = 0){
	global $TABLE_PLATFORM_PREFIX, $TABLE_BIDS_POSTFIX;
	global $DEFAULT_PATH, $NUM_ADS, $FEEDS;
	
	if ($trace > 1) {
		echo "<b>DATE:</b> " . $date . ", fWriteBids called. ";
		echo "SIZE OF BIDS: ", sizeof($bids), "<br />";
	}
	
	// Variable Declaration
	$keys = "";
	$values = "";
	$flag = 0;
	$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
	$bids['time_elapsed'] = fMarkTime($bids['time']);
	$feed = $bids['feed'];
	
	for ($i = 0; $i < sizeof($bids[$feed]['ads']); $i++) {
		$keys = "";
		$values = "";
		$flag = 0;
		foreach($bids[$feed]['ads']['ad' . $i] as $key => $value) {
			$keys .= "," . $key;
			$values .= ", " . "'" . $value . "'";
		}
		$sql = "INSERT INTO " . $table . " (id,time,time_elapsed,feed,keywords,xml_feed,ratio" . $keys . ",status) ";
		$sql .= "VALUES (";
		$sql .= "'" . $bids['id'] . "','" . $bids['time'] . "','" . $bids['time_elapsed'] . "','" . $bids['feed'] . "',";
		$sql .= "'" . $bids['keywords'] . "','" . $bids['xml_feed'] . "','" . $bids['ratio'] . "'" . $values . ",'" . $bids['status'] . "')";
		if ($trace > 1) {
			echo "SQL: ", $sql, "<br /><br />";
		}
		mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error() ); 
	}	
	return;	
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWriteData_OLD(&$bids, $date, $trace){
	global $TABLE_PLATFORM_PREFIX, $TABLE_BIDS_POSTFIX;
	global $DEFAULT_PATH, $NUM_ADS, $FEEDS, $MYSQL_CONNECTION;
	
	// Variable Declaration
	$keys = "";
	$values = "";
	$flag = 0;
	$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
	$bids['time_elapsed'] = fMarkTime($bids['time']);
	$feed = $bids['feed'];

	if ($trace > 1) {
		echo "fWriteData Called for feed ".$bids['feed'].". <br />";
		echo "SIZE OF ADS: ", sizeof($bids), "<br />";
	}
	
	for ($i = 0; $i < sizeof($bids[$feed]['ads']); $i++) {
		$keys = "";
		$values = "";
		$flag = 0;
		foreach($bids[$feed]['ads']['ad' . $i] as $key => $value) {
			$keys .= "," . $key;
			$values .= ", " . "'" . $value . "'";
		}
		$sql = "INSERT IGNORE INTO " . $table . " (id,time,time_elapsed,feed,xml_feed,ratio" . $keys . ",status) ";
		$sql .= "VALUES (";
		$sql .= "'" . $bids['id'] . "','" . $bids['time'] . "','" . $bids['time_elapsed'] . "',";
		$sql .= "'" . $bids['feed'] . "','" . $bids['xml_feed'] . "','" . $bids['ratio'] . "'" . $values . ",'" . $bids['status'] . "')";
		if ($trace) echo "SQL: ", $sql, "<br /><br />";
		mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error() ); 
	}	
	
	return;	
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWritePosition($MYSQL_CONNECTION, &$feed_ads, $date, $trace = 0) {
	global $TABLE_PLATFORM_PREFIX, $TABLE_BIDS_POSTFIX;
	
	if ($trace > 1) echo "fWritePosition called. ";
	
	$table = $TABLE_PLATFORM_PREFIX . $date . $TABLE_BIDS_POSTFIX;
	
	for ($i = 0; $i < sizeof($feed_ads); ++$i){
		$sql = "UPDATE " . $table . " SET position='".($i + 1)."' WHERE id='".$feed_ads[$i]['id'] . "' AND bid_id='".$feed_ads[$i]['bid_id'] . "'";
		if ($trace > 1) echo "SQL: $sql<br />";
		mysql_query($sql, $MYSQL_CONNECTION) or die(mysql_error() ); 
	}
	return;	
}

/** =====================================================================================================================
    description
	=====================================================================================================================
	@PARAMETERS: 
		$var as type			- description
	
	@RETURNS:
		$var as type		 	- description
* ======================================================================================================================*/
function fWriteSessionTime($MYSQL_CONNECTION, $start, $date, &$session_vars, $trace = 0) {
	global $TABLE_PLATFORM_PREFIX, $TABLE_SESSION_POSTFIX;
	
	if ($trace > 1) echo "fWriteSessionTime called.<br />";
	
	$session_vars['time_elapsed'] = fMarkTime($start); 
	$sql  = "UPDATE " .$TABLE_PLATFORM_PREFIX.$date.$TABLE_SESSION_POSTFIX . " ";
	$sql .= "SET time_elapsed = '" .$session_vars['time_elapsed']. "' WHERE id = '" .$session_vars['id']. "'";
	mysql_query($sql, $MYSQL_CONNECTION);
	mysql_close($MYSQL_CONNECTION); // close connection
}

?>