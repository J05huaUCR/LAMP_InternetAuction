<?php

// Prepare Output
$txt = "";
for($i = 0; $i < sizeof($FEEDS); $i++) {
	if ($trace) {
		echo "FEED: |" . $FEEDS[$i] . "|<br />";
		echo "SIZE: |" . sizeof($feed_ads[$FEEDS[$i]]['ads']) . "|<br />";
	}
	$base_url = "http://www.toptrafficsource.com/dc/exit/?redirect=";
	for ($j = 0; $j < sizeof($feed_ads[$FEEDS[$i]]['ads']); $j++) {
		$feed_ads[$FEEDS[$i]]['ads']['ad'.$j]['position'] = $j + 1;
		$url = $base_url . urlencode($feed_ads[$FEEDS[$i]]['ads']['ad'.$j]['redirect']);
		$txt .= "<div onclick=\"location.href='" . $i . $url . "';\"  style=\"display:block;cursor:pointer;\"  class=\"ppc_ad_wrapper\">\n";				
		$txt .= "<a class=\"ppc_ad_title\" href=\"2" . $url . "\">" . $feed_ads[$FEEDS[$i]]['ads']['ad'.$j]['title'] . "</a><br />\n";
		$txt .= $feed_ads[$FEEDS[$i]]['ads']['ad'.$j]['description'] . "<br />\n";
		$txt .= "<a class=\"ppc_ad_url\" href=\"" . $url . "\">" . $feed_ads[$FEEDS[$i]]['ads']['ad'.$j]['url'] . "</a>\n";
		$txt .= "</div>\n";
	}
}
$feed_ads['common']['time_elapsed'] = fMarkTime($start);
fWriteBidsPPC(&$feed_ads, $date, $MYSQL_CONNECTION, $trace);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Top Traffic Source | <?=$feed_ads['common']['domain']?></title>

<link rel="stylesheet" type="text/css" href="styles/styles.css" />
</head>
<body style="margin:0 0 0 0;">

<div align="center"><!-- Container -->
	<div class="ppc_wrapper"><!-- Wrapper -->
		<div class="ppc_header_wrapper"><!-- Header -->
			<div class="ppc_header_layout">
				<img src="styles/images/domain_logo.jpg" align="absmiddle" /> 
				<span class="ppc_header_headline"><?=$feed_ads['common']['domain']?></span>
			</div>
		</div><!-- /Header -->
	
		<div class="ppc_adspace_wrapper"><!-- Adspace -->
			<div class="ppc_adspace">
				<div class="ppc_adspace_results_header">
					<span class="ppc_adspace_results_sponsored">
						Results for <b><?=$returned?></b>
					</span>
					<br />
					<span class="ppc_adspace_results_sponsored_listings">Sponsored Listings:</span>
				</div>
				<?=$txt?>
			</div>
		</div><!-- /Adspace -->
		
		<div class="ppc_footer_wrapper">
				<form id="keySearch" name="keySearch" method="post" action="<?=$DEFAULT_PATH?><?=$feed_ads['common']['domain']?>">
					<input type="hidden" name="referer" value="<?=$feed_ads['common']['referer']?>" />
					<input type="hidden" name="agent" value="<?=$feed_ads['common']['agent']?>" />
					<input type="hidden" name="ip" value="<?=$feed_ads['common']['ip']?>" />
					<input type="hidden" name="domain" value="<?=$feed_ads['common']['domain']?>" />
					<input name="keySearch" type="text" id="keySearch" class="ppc_searchbar" style="border:solid 1px #7892B2; height:17px; width:270px;">
					<input class="searchbutton" name="submit" type="submit" value="Search" />
				</form>
				<div class="ppc_terms_nav">
					&copy; 2012&nbsp;All rights reserved. <a href="" target="_blank" class="ppc_link">Privacy Policy</a> | <a href="" class="ppc_link">Inquire about this domain</a>
				</div>
		</div><!-- /footer wrapper -->
	</div><!-- /Wrapper -->
</div><!-- /Container -->

</body>
</html>
