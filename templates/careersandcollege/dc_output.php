<?php
// dc_output.php
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Top Traffic Source | <?=$session_vars['domain']?></title>

<style>
.ppc_wrapper {font-family:arial,verdana,geneva,helvetica,sans-serif;font-size:12px;font-style:normal;color:#000;background-color:#0b5198;clear:both;overflow:hidden;border:solid 1px #0b5198;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;margin:0 10px 0 10px;}
.ppc_wrapper h2 {color:#fff;font:bold 15px arial; padding:15px 0 0 15px;margin:0;}
.ppc_ad_wrapper {clear:both;padding:0;margin:0 0 20px 10px;}
.ppc_ad_pad {background-color:#fff;clear:both;margin:10px;padding:15px 10px 0 10px;overflow:hidden;border:solid 1px #fff;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;}
.ppc_ad_title {color:#3e86c0;font-size:18px;font-weight:bold;text-decoration:underline;}
.ppc_ad_url {color:#666666;font-size:12px;text-decoration:none;}
</style>
</head>

<body>
<div class="ppc_wrapper">
	<h2>Displaying results for: <?=$session_vars['keywords']?></h2>
	<div class="ppc_ad_pad">
		<?
		for ($j = 0; $j < sizeof($feed_ads); $j++) {
		$feed_ads[$j]['position'] = $j + 1;
		$url = $base_url . urlencode($feed_ads[$j]['redirect']) . '&position=' . ($j + 1);
		?>
		<!--<div onClick="window.open('<?=$url?>');"  style="display:block;cursor:pointer;"  class="ppc_ad_wrapper">	-->
		<div class="ppc_ad_wrapper">		
		<a class="ppc_ad_title" href="<?=$url?>" target="_blank"><?=$feed_ads[$j]['title']?></a><br />
		<?=$feed_ads[$j]['description']?><br />
		<a class="ppc_ad_url" href="<?=$url?>" target="_blank"><?=$feed_ads[$j]['url']?></a>
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