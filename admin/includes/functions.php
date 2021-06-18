<?php

/* ===============================================================
*	Download script
** ===============================================================*/		
function fDownloadAs($filename, &$data, $trace = 0){
	//echo "fDownloadAs called for filename: $filename<br >";

	$csv  = "domain,url,keywords,etc\r\n";
	$csv .= "youtubl.com,toptrafficsource.com/dc/admin,cats dogs,123:45:651\r\n";
	
	// Open file handlers
	!$handle = fopen($filename, 'w');
	fwrite($handle, $csv);
	fclose($handle);
	
	// Send Headers
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Length: ". filesize("$filename").";");
	header("Content-Disposition: attachment; filename=$filename");
	header("Content-Type: application/octet-stream; "); 
	header("Content-Transfer-Encoding: binary");
	
	echo $csv; // to output to browser
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
function fMailDailyReport($date_slash, $feed_name ='', $email = '', $first_name = '', $last_name='', $message) {
	global $SITE_URL;
	$email = ""; /* turn off for live*/
	$subject = "Report generated for $first_name $last_name of $feed_name for $date_slash:";


	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
	$headers .= "From: DirectClick reports@".$SITE_URL."\r\n";
	$headers .= "Reply-To: reports@".$SITE_URL."\n" . 
					"X-Mailer: PHP/" . phpversion();
	//$status = true;			
	$status = mail($email, $subject, $message, $headers);

	if ($status) {
		echo "SUCCESS!<br />";	
		echo "TO: $email<br />";
		echo "SUBJECT: $subject<br />";
		echo "$message<br />";
		echo "$headers<br />";
		echo "Stats for $feed_name on $date_slash sent to $first_name $last_name's email at $email.<br />";
	} else {
		echo "FAILED TO SEND!<br />";	
	}

	return;
}

/* ===============================================================
*	Return current time
** ===============================================================*/		
function fGetMedian(&$stats){
	global $TRACE;
	$size = sizeof($stats[0]);
	if ($TRACE) echo "Median called. SIZE: ",$size ,"<br />";
	if ( ($size % 2) == 0) { // even
		if ($TRACE) echo "EVEN STAT 1: " , $stats[($size / 2)][0], "<br />";
		$median = ($stats[($size / 2)][0] + $stats[(($size /2) + 1)][0]) / 2;
	} else { // odd
		if ($TRACE) echo "ODD STAT: " , $stats[( ($size  + 1) / 2)][0], "<br />";
		$median = $stats[( ($size  + 1) / 2)][0];
	}
	return $median;
}

/* ===============================================================
*	Return current time
** ===============================================================*/		
function fGetTime($days = 0){
	$now = time();
	$time_offset = $days * 86400;
	$now = $now - $time_offset;

	$NOW_DATETIME = strftime("%Y%m%d", $now);
	return $NOW_DATETIME;	
}

/** =====================================================================================================================
    Takes multi-dimensional array and outputs formatted table
	=====================================================================================================================
	@PARAMETERS: 
		$data as array			- Multi-dimensional associative array
	
	@RETURNS:
		$table as string	 	- Returns table in html form
* ======================================================================================================================*/
function fOutputCSV(&$data, $trace = 0) {
	if ($trace) echo "fOutputCSV called. <br />Size: |".sizeof($data)."|<br />";
	$csv = ""; // initialize
	
	for ($r = 0; $r < sizeof($data); ++$r) { // number of rows
		$c = 0;
		foreach ($data[$r] as $key => $value) {
			($c == 0) ? $csv .= urldecode($value) : $csv .= ",". urldecode($value);
			$c++;
		}
		$csv .= "\r\n";	// end row
	}
	
	return $csv;
}

/** =====================================================================================================================
    Takes multi-dimensional array and outputs formatted table
	=====================================================================================================================
	@PARAMETERS: 
		$data as array			- Multi-dimensional associative array
	
	@RETURNS:
		$table as string	 	- Returns table in html form
* ======================================================================================================================*/
function fOutputTable(&$data, $trace = 0, $rank = 1) {
	$table = ""; // initialize
	$table = "<table>\n\t"; // opening tag
	
	for ($r = 0; $r < sizeof($data); ++$r) { // number of rows
		if ($r == 0) { // first row, print keys as column headers
			$table .= "<tr>\n";
			if ($rank) $table .= "<th>Rank</th>";
			foreach($data[$r] as $key => $value) $table .= "<th>$key</th>\n";
			$table .= "</tr>\n";
		}
		$table .= "<tr>\n"; // begin row
		($r % 2 != 0) ? $tint = " class=\"alt\"" : $tint = ""; // alternating rows shading
		if ($rank) $table .= "<td".$tint.">". ($r + 1) .") </td>\n"; // rank column
		foreach($data[$r] as $key => $value) $table .= "<td".$tint.">".urldecode($value)."</td>\n";
		$table .= "</tr>\n";	// end row
	}
	
	$table .= "\n\t</table>\n<br clear=\"all\" />\n"; // closing tag
	return $table;
}

/** =====================================================================================================================
    Pagination function 
	=====================================================================================================================
	@PARAMETERS: 
		$target_page as string 	- base url
		$page as int 				- current page being viewed
		$total_pages as int		- total number of pages to be presented
		$limit as int				- number of items per page
		$ADJACENT_PAGE_COUNT		- number of pages to display to either side of current page
		 as int
		
	@RETURNS:
		$pagination as string	- html code for page controls
* ======================================================================================================================*/
function fPagination($targetpage, $page, $total_pages, $limit, $ADJACENT_PAGE_COUNT) {
	if ($page == 0) $page = 1;						//if no page var is given, default to 1.
	$prev = $page - 1;								//previous page is page - 1
	$next = $page + 1;								//next page is page + 1
	$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
	$lpm1 = $lastpage - 1;							//last page minus 1
	
	$pagination = "";
	if($lastpage > 1){	
		$pagination .= "<div class='pagination'>";
		($page > 1) ? $pagination.= "<a href='". $targetpage . "?page=". $prev . "'>&laquo; previous</a>" : $pagination.= "<span class='disabled'>&laquo; previous</span>";	//previous button
		
		//pages	
		if ($lastpage < 7 + ($ADJACENT_PAGE_COUNT * 2)) {		//not enough pages to bother breaking it up
			for ($counter = 1; $counter <= $lastpage; $counter++){
				($counter == $page) ? $pagination.= "<span class='current'>". $counter . "</span>" : $pagination.= "<a href='". $targetpage . "?page=". $counter . "'>". $counter. "</a>";					
			}
		} elseif($lastpage > 5 + ($ADJACENT_PAGE_COUNT * 2)) { //enough pages to hide some
			if($page < 1 + ($ADJACENT_PAGE_COUNT * 2)) {	//close to beginning; only hide later pages	
				for ($counter = 1; $counter < 4 + ($ADJACENT_PAGE_COUNT * 2); $counter++) {
					($counter == $page) ? $pagination.= "<span class='current'>". $counter. "</span>" : $pagination.= "<a href='".$targetpage."?page=".$counter."'>".$counter."</a>";		
				}
				$pagination.= "...";
				$pagination.= "<a href='".$targetpage."?page=".$lpm1."'>".$lpm1."</a>";
				$pagination.= "<a href='".$targetpage."?page=".$lastpage."'>".$lastpage."</a>";		
			} elseif($lastpage - ($ADJACENT_PAGE_COUNT * 2) > $page && $page > ($ADJACENT_PAGE_COUNT * 2)) { //in middle; hide some front and some back
				$pagination.= "<a href='".$targetpage."?page=1'>1</a>";
				$pagination.= "<a href='".$targetpage."?page=2'>2</a>";
				$pagination.= "...";
				for ($counter = $page - $ADJACENT_PAGE_COUNT; $counter <= $page + $ADJACENT_PAGE_COUNT; $counter++) {
					($counter == $page) ? $pagination.= "<span class='current'>".$counter."</span>" : $pagination.= "<a href='".$targetpage."?page=".$counter."'>".$counter."</a>";					
				}
				$pagination.= "...";
				$pagination.= "<a href='".$targetpage."?page=".$lpm1."'>".$lpm1."</a>";
				$pagination.= "<a href='".$targetpage."?page=".$lastpage."'>".$lastpage."</a>";		
			} else { //close to end; only hide early pages
				$pagination.= "<a href='".$targetpage."?page=1'>1</a>";
				$pagination.= "<a href='".$targetpage."?page=2'>2</a>";
				$pagination.= "...";
				for ($counter = $lastpage - (2 + ($ADJACENT_PAGE_COUNT * 2)); $counter <= $lastpage; $counter++) {
					($counter == $page) ? $pagination.= "<span class='current'>".$counter."</span>" : $pagination.= "<a href='".$targetpage."?page=".$counter."'>".$counter."</a>";					
				}
			}
		}
		
		//next button
		($page < $counter - 1) ? $pagination.= "<a href='".$targetpage."?page=".$next."'>next &raquo;</a>" : $pagination.= "<span class='disabled'>next &raquo;</span>";
		$pagination.= "</div>\n";		
		
	}
	return $pagination;
}

/* ===============================================================
*	Returns Range of Dates from a starting time until current
** ===============================================================*/		
function fGetDates($start_time){
	//echo "fGetDates called with start_time: |$start_time|. ";
	
	$dates = array(); // init array
	$now = time(); // get current time
	$day_offset = 86400;
	
	//echo "now: |$now|. ";

	while ($start_time < $now) {
		$dates[] = strftime("%Y%m%d", $start_time); // add date to array
		//echo "DATE ADDED: |" . strftime("%Y%m%d", $start_time) . "|<br />";
		$start_time += $day_offset;	// add a day
	}
	
	//echo "Size of returned array: |" . sizeof($dates) . "|<br />";
	
	return $dates;	// return array
}

?>