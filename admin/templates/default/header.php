<?php ?>

<div class="frame_header"><!-- frame_header -->

	<div id="frame_welcome"><!-- frame_welcome -->
		<h1>Welcome, <?=$_SESSION['first_name']?>!</h1>
		<form id="logout" name="logout" method="post" action="index<?=$DEV?>.php">
			<input name="login" type="hidden" id="login" value="0"  />
			<input name="email" type="hidden" id="email" value="logout" /> 
			<input name="submit" type="submit" id="submit" value="Logout"> 
		</form>
	</div><!-- /frame_welcome -->
	
	<div id="frame_center"><!-- frame_center -->
		<div class="header">ADMINISTRATION</div>
		<?=$header?>
	</div><!-- /frame_center -->
	
	<div id="frame_report"><!-- frame_report -->
		<form name="statsReports" >
		<div id="frame_reports"><!-- frame_reports -->
			<div id="frame_submit_button">
				<a href="javascript:void(showStats())">Go!</a>
			</div>
		</div><!-- /frame_reports -->
		<div id="frame_reports"><!-- frame_reports -->
			<div id="reports_select"><!-- reports_select -->
				<h7>Select Platform</h7>
				<select name="platform" id="whichPlatform">
					<option value="zc" selected="selected">ZeroClick&trade;</option>
					<option value="dc">DirectClick&trade;</option>
					<option value="ac">AdClick&trade;</option>
					<option value="0">All</option>
				</select>
			</div>
			<div id="reports_select"><!-- reports_select -->
				<h7>Select Feed</h7>
				<select name="feed" id="whichFeed">
					<option value="0" selected="selected">All</option>
<?
foreach($feeds as $feed) {
	print "	<option value=\"".$feed['feed_id']."\" >".$feed['feed_name']."</option>\n";
}
?>
				</select>
			</div>
			<div id="reports_select"><!-- reports_days -->
				<h7># of Results</h7>
					<select name="results" id="numResults">
						<option value="20" >20</option>
						<option value="50" >50</option>
						<option value="100">100</option>
						<option value="500">500</option>
						<option value="1000">1000</option>
					</select>
			</div>
		</div><!-- /frame_reports -->
		<div id="frame_reports"><!-- frame_reports -->
			<div id="reports_select"><!-- reports_select -->
				<h7>Select Report</h7>
				<select name="reports" id="whichReport" onchange="fDisable(this.selected)">
					<option value="overview" selected="selected">Overview</option>
					<option value="revenue">Revenue</option>
					<option value="bids">Bids</option>
					<option value="keywords">Keywords</option>
					<option value="domains">Domains</option>
					<option value="redirects">Redirects</option>
				</select>
			</div>
			<div id="reports_select"><!-- reports_days -->
				<h7>Select Date(s)</h7>
					<select name="stats" id="whichDays">
						<option value="0" selected="selected">Today</option>
						<option value="1">Yesterday</option>
						<option value="7">Prior 7 Days</option>
						<option value="14">Prior 2 Weeks</option>
						<option value="30">Prior 30 Days</option>
						<option value="mtd">Month to Date</option>
						<option value="ytd">Year to Date</option>
					</select>
			</div>
			<div id="reports_select"><!-- reports_days -->
				<h7>Summary by:</h7>
					<select name="range" id="whichRange">
						<option value="days" selected="selected">Summary</option>
						<option value="range">By Range</option>
					</select>
			</div>
		</div><!-- /frame_reports -->
		</form>
	</div><!-- /frame_report -->
</div><!-- /frame_header -->

<div id="loading">
	<img src="<?=$TEMPLATE_IMAGES?>loading_circle.gif" width="214" height="206" />
</div><!-- /loading -->
<div id="content"> 