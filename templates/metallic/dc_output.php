<!DOCTYPE html>
<html>
<head>
<title>Top Traffic Source | <?=$session_vars['domain']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>
<meta name="Description" content="Look no further for the best information on <?=$session_vars['domain']?>.  Find all your results about <?=$session_vars['domain']?>" />
<meta name="Keywords" content="<?=$session_vars['keywords']?>" />
<meta name="robots" content="noindex,nofollow" />
<link rel="stylesheet" href="<?=$TPL_CSS?>styles.css" type="text/css" />
</head>

<body>
<div class="wrapper">
	<div class="header">
		<div class="headerContent">
			<div class="logoSearch">
				<div class="logo">
					<h1><?=$session_vars['domain']?></h1>
					Your best source for online information
				</div><!--/logo-->
				<!--<div class="search">
					<form id="keySearch" name="keySearch" method="post" action="<?=$DEFAULT_PATH?><?=$session_vars['domain']?>" class="searchForm">
						<input type="hidden" name="id" value="<?=$session_id?>" />
						<input type="hidden" name="try" value="<?=$try?>" />
						<input type="hidden" name="trace" value="<?=$trace?>" />
						<input name="keywords" type="text" id="keywords" class="searchInput">
						<input type="submit" value="Search" class="searchButton" name="submit" />
					</form>
					<form action='/domainserve/domainSearch' method='post' class="searchForm">
						<input type='text' name='searchterms' size='' class='searchInput'>
						<input type="submit" value="Search" class="searchButton"  />
						<input type='hidden' name='lg' value=''>
						<input type='hidden' name='dn' value='<?=$session_vars['domain']?>'>
						<input type='hidden' name='token' value=''>
						<input type='hidden' name='abadtest' value=''>
						<input type='hidden' name='searchtype' value='user'>
						<input type='hidden' name='layoutcategory' value='351'>
					</form>/search-->
				<!--</div>/search-->
			</div><!--/logoSearch-->
	<div class="header_navigation">
            
 
<!-- Related Searches -->
<ul>
	<li class='rel'>Related Search :</li>
	<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[0][1]?>'><?=$keywords_related[0][0]?></a></li>
	<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[1][1]?>'><?=$keywords_related[1][0]?></a></li>
	<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[2][1]?>'><?=$keywords_related[2][0]?></a></li>
</ul>
<!-- /Related Searches -->
            
            </div><!--/header_navigation-->
        </div><!--/headerContent-->
    </div><!--/header-->

	<div id="body">
		<div id="content">
			<div class="content_related_search" style="float:right;"><!--content_related_search-->
				<img src="<?=$TPL_CSS?>images/general.jpg" width="240" height="163" style="border:solid 0px #ccc; margin:0; " />
				<div class="related_head">
					<h2><a class='relatedLnk' rel='feedback_url' href='feedback_url' target='_blank'>Related Searches</a></h2>
				</div><!--/related_head-->
				<div class="related_list">			 
					<ul>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[4][1]?>'><?=$keywords_related[4][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[5][1]?>'><?=$keywords_related[5][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[6][1]?>'><?=$keywords_related[6][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[7][1]?>'><?=$keywords_related[7][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[8][1]?>'><?=$keywords_related[8][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[9][1]?>'><?=$keywords_related[9][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[10][1]?>'><?=$keywords_related[10][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[11][1]?>'><?=$keywords_related[11][0]?></a></li>
					</ul>
				</div><!--/related_list-->
			</div><!--/content_related_search-->
			<div class="contentSponsored">
				<div class="sponsoredlisting"><a class='sponsored' id='sponsorlink' href ='feedback_url' target='_blank'>Sponsored Listings</a> for:  <?=$session_vars['keywords']?></div>
<?
for ($j = 0; $j < sizeof($feed_ads); $j++) {
	$feed_ads[$j]['position'] = $j + 1;
	$redirect = $base_url . urlencode($feed_ads[$j]['redirect']) . '&position=' . ($j + 1);
?>
				<ul>           
					<li>
						 <a href='<?=$redirect?>' target='_blank' class='head'><?=$feed_ads[$j]['title']?></a><br />
						 <a href='<?=$redirect?>' target='_blank' class='deck'><?=$feed_ads[$j]['description']?></a><br />
						 <a href='<?=$redirect?>' target='_blank' class='url'><?=$feed_ads[$j]['url']?></a>
<? if ($trace) echo "(" .$feed_ads[$j]['feed']. ",".$feed_ads[$j]['bid'] . ")"; ?>
					</li>
				</ul>
<?
}
?>
			</div><!--/contentSponsored-->
		</div><!--/content-->
	</div><!--/body-->
	<div class="push"></div> 
</div><!--/wrapper-->

<div class="footer">
	<div class="footerContent">
            <div class="footerlist">
					<ul><!-- Footer | Related Searches -->
						<li class='rel'>Related Search :</li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[12][1]?>'><?=$keywords_related[12][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[13][1]?>'><?=$keywords_related[13][0]?></a></li>
						<li><a href='<?=$DEFAULT_PATH?><?=$session_vars['domain']?>&keywords=<?=$keywords_related[14][1]?>'><?=$keywords_related[14][0]?></a></li>
					</ul><!-- /Footer | Related Searches -->
            </div><!--/footerlist-->

        
            <div class="footerPrivacy">
                <a href="http://www.<?=$session_vars['domain']?>/domainserve/privacy?dn=<?=$session_vars['domain']?>" class="txtlink" target="_blank">Privacy Policy</a> | 
                <a href="javascript:contactus('/domainserve/contactus?dn=<?=$session_vars['domain']?>')" class="txtlink">Advertise</a> | 
                <a href="http://www.domainbrokeronline.com/rd.php?d=<?=$session_vars['domain']?>" class="txtlink" target="_blank">Domain For Sale</a>
            </div>
        

<!--
<script language=javascript>
function contactus(N) {
newWindow = window.open(N, 'contactus','top=60,left=300,toolbar=no,menubar=no,resizable=no,scrollbars=no,status=no,location=no,width=632,height=475');
}
</script>-->
    </div><!--/footerContent-->
</div><!--/footer-->



<!--
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-16899173-25']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_setDomainName', '<?=$session_vars['domain']?>']);
  
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>


<script language="javascript"> 
var dex_url 
dex_url = '<iframe src="http://domdex.com/f?c=417&k='+relatedArray[0][0]+'" width=0 height=0 frameborder=0></iframe>';
document.write (dex_url); //prints the value of x
</script>-->


</body>
</html>
<!--- load_test --->