<?php
// dc_output.php
?>
<div class="ppc_adspace_wrapper"><!-- Adspace wrapper-->
	<div class="ppc_adspace"><!-- Adspace -->
		<div class="ppc_adspace_results_header">
			<span class="ppc_adspace_results_sponsored">
				Results for <b><?=$session_vars['keywords']?></b>
			</span>
			<br />
			<span class="ppc_adspace_results_sponsored_listings">Sponsored Listings:</span>
		</div>
		<?
		for ($j = 0; $j < sizeof($feed_ads); $j++) {
			$feed_ads[$j]['position'] = $j + 1;
			$url = $base_url . urlencode($feed_ads[$j]['redirect']);
			?>
			<div onClick="location.href='<?=$url?>';"  style="display:block;cursor:pointer;"  class="ppc_ad_wrapper">			
				<a class="ppc_ad_title" href="<?=$url?>"><?=$feed_ads[$j]['title']?></a><br />
				<?=$feed_ads[$j]['description']?><br />
				<a class="ppc_ad_url" href="<?=$feed_ads[$j]['url']?>"><?=$feed_ads[$j]['url']?></a>
			<?
			if ($trace == 1) echo "(". $feed_ads[$j]['feed'] . "," . $feed_ads[$j]['bid'] . ")\n";
			?>
			</div><?
		}
		?>
	</div><!--/Adspace -->
</div><!-- /Adspace wrapper-->