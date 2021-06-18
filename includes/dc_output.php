<?php
// dc_output.php
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Top Traffic Source | <?=$session_vars['domain']?></title>

<link rel="stylesheet" type="text/css" href="styles/styles.css" />
</head>
<body style="margin:0 0 0 0;">

<div align="center"><!-- Container -->
	<div class="ppc_wrapper"><!-- Wrapper -->
		<div class="ppc_header_wrapper"><!-- Header -->
			<div class="ppc_header_layout">
				<img src="styles/images/domain_logo.jpg" align="absmiddle" /> 
				<span class="ppc_header_headline"><?=$session_vars['domain']?></span>
			</div>
		</div><!-- /Header -->
	
		<div class="ppc_adspace_wrapper"><!-- Adspace wrapper-->
			<div class="ppc_adspace"><!-- Adspace -->
				<div class="ppc_adspace_results_header">
					<span class="ppc_adspace_results_sponsored">
						Results for <b><?=$session_vars['keywords']?></b>
					</span>
					<br />
					<span class="ppc_adspace_results_sponsored_listings">Sponsored Listings:</span>
				</div>
				<?=$txt?>
			</div><!--/Adspace -->
		</div><!-- /Adspace wrapper-->
		
		<div class="ppc_footer_wrapper">
				<form id="keySearch" name="keySearch" method="post" action="<?=$DEFAULT_PATH?><?=$session_vars['domain']?>">
					<input type="hidden" name="id" value="<?=$session_id?>" />
					<input type="hidden" name="try" value="<?=$try?>" />
					<input type="hidden" name="trace" value="<?=$trace?>" />
					<input name="keywords" type="text" id="keywords" class="ppc_searchbar" style="border:solid 1px #7892B2; height:17px; width:270px;">
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
