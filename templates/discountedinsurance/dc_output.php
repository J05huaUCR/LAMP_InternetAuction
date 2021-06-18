<?php
// dc_output.php
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Top Traffic Source | <?=$session_vars['domain']?></title>

<style>
.ppc_wrapper {border:solid 0px #0b5198;clear:both; color:#000;font:normal 12px arial,verdana,geneva,helvetica,sans-serif; margin:0 10px 0 10px;padding:10px;overflow:hidden;}
.ppc_wrapper h2 {font:normal 17px arial,verdana,geneva,helvetica,sans-serif; padding: 0 0 0 0; margin:0 0 0 0;}
.ppc_ad_wrapper {clear:both;padding:0; margin:0 0 20px 0;}
.ppc_ad_pad {background-color:#fff;clear:both;margin:10px 10px 10px 0; padding:5px 10px 0 0; overflow:hidden;}
.ppc_ad_title {color:#1122cc;font-size:18px; font-weight:normal;text-decoration:underline;}
.ppc_ad_url {color:#009933;font-size:12px;text-decoration:none;}
.searchResults {color:#1122cc;text-decoration:underline;}
a.searchResults {color:#1122cc;text-decoration:underline;}
a.searchResults:visited {color:#660099;text-decoration:underline;}
a.searchResults:hover {color:#1122cc;text-decoration:underline;}
a.searchResults:active {color:#660099;text-decoration:underline;}
</style>
</head>

<body>
<div class="ppc_wrapper">
	<h2>Displaying results for: <span class="searchResults"><?=$session_vars['keywords']?></span></h2>
	<div class="ppc_ad_pad">
		<?
		for ($j = 0; $j < sizeof($feed_ads); $j++) {
		$feed_ads[$j]['position'] = $j + 1;
		$url = $base_url . urlencode($feed_ads[$j]['redirect']) . '&position=' . ($j + 1);
		?>
		<!--<div onClick="window.open('<?=$url?>');"  style="display:block;cursor:pointer;"  class="ppc_ad_wrapper">	-->
		<div class="ppc_ad_wrapper">		
		<a class="ppc_ad_title" href="<?=$url?>" target="_blank"><?=$feed_ads[$j]['title']?></a><br />
		
		<a class="ppc_ad_url" href="<?=$url?>" target="_blank"><?=$feed_ads[$j]['url']?></a><br />
        
        <?=$feed_ads[$j]['description']?>
		<?
		if ($trace == 1) echo "(". $feed_ads[$j]['feed'] . "," . $feed_ads[$j]['bid'] . ")\n";
		?>
		</div>
		<?
		}
		?>
		<!--Content End-->
	</div><!--/ppc_ad_pad-->
</div><!--/ppc_wrapper-->

</body>
</html>