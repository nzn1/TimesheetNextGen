<?php

if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;
PageElements::setTheme('txsheet2');
//export data to excel (or not) (IE is broken with respect to buttons, so we have to do it this way)
if (isset($_REQUEST["export_excel"]) && $_REQUEST["export_excel"] == "1"){
	$export_excel=true;
}
else{
	$export_excel=false;
}

ob_start();
//Create the excel headers now, if needed
if($export_excel){
	// NOTE:  The session cache limiter and the excel stuff must appear before the session_start call, or the export to excel won't work in IE
	session_cache_limiter('public');
	header('Expires: 0');
	header('Cache-control: public');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");
	
	// If exporting data to excel, ensure the numbers written in the spreadsheet 
	// are in H.F format rather than HH:MI  
	$time_fmt = "decimal";
}
else if (isset($_REQUEST['time_fmt'])){
	$time_fmt = $_REQUEST['time_fmt'];
}
else{
	$time_fmt = "decimal";
}

//load local vars from request/post/get
if (isset($_REQUEST['uid'])){
	$uid = gbl::getUid();
}
else{
	$uid = gbl::getContextUser();
}

if (isset($_REQUEST['print'])){
	$print = true;
}
else{
	$print = false;
}
gbl::setDay(1);

$startDate = strtotime(date("d M Y",gbl::getContextTimestamp()));
$startStr = date("Y-m-d H:i:s",$startDate);
$endDate = Common::getMonthlyEndDate(gbl::getContextDate());
$endStr = date("Y-m-d H:i:s",$endDate);
$mode="monthly";
if (isset($_REQUEST['day'])) 
	$day = $_REQUEST['day'];
else
	$day = 1; 
$previousDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " -1 month");
$nextDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " +1 month");
//What bi-monthly period is the context date in?
if($day < 16) {
	$sday=1; 
	$eday=15;
} else {
	$sday=16;
	$eday=date('t',strtotime(gbl::getYear()."-".gbl::getMonth()."-15"));
}

//get start and end dates for the calendars use day
$startDay = $sday;
$startMonth = gbl::getMonth();
$startYear  = gbl::getYear();

$endDay = $eday;
//$endMonth   = isset($_GET['end_month'])   && $_GET['start_month'] ? (int)$_GET['end_month']   : gbl::getMonth();
//$endYear    = isset($_GET['end_year'])    && $_GET['start_year']  ? (int)$_GET['end_year']    : gbl::getYear();
//Since we're only allowing the user to choose a start and end day within the cur. context month 
//we can do this:  But note: we can't remove the variables altogether, or we'd need yet another set
//of functions to create the navcal calendars, and at some point we may want to allow more
//freestyle date choosing...
//$endMonth   = $startMonth;
//$endYear    = $startYear;

$startTimestamp = strtotime(gbl::getYear(). '/' . gbl::getMonth() . '/' . $startDay);
$endTimestamp   = strtotime(gbl::getYear() . '/' . gbl::getMonth()   . '/' . $endDay);
$endTimestamp2   = strtotime("+1 day",$endTimestamp);  //need last day to be inclusive...

//$startStr = date("Y-m-d H:i:s",$startTimestamp);
//$endStr = date("Y-m-d H:i:s",$endTimestamp2);
// calcuate the urls to select the first or second half of the month
$p1post="uid=$uid&amp;time_fmt=$time_fmt&amp;day=1";
$p2post="uid=$uid&amp;time_fmt=$time_fmt&amp;day=".date('t',gbl::getYear()."-".gbl::getMonth()."-15");


require_once('report.class.php');
$report = new Report();

$Location= Rewrite::getShortUri()."?uid=$uid&amp;time_fmt=$time_fmt&amp;day=$endDay";
gbl::setPost("uid=$uid&amp;time_fmt=$time_fmt");

if(!$export_excel) {
?>
	<script type="text/javascript">
	report = new Object();
	report.location = "<?php echo $Location;?>";
	</script>
	
	<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/reports.js"></script>
	<script type="text/javascript" src="<?php echo Config::getRelativeRoot()."/js/datetimepicker_css.js";?> "></script>
	<script type="text/javascript">
		function CallBack_WithNewDateSelected(strDate) {
			document.monthForm.submit();
		}
	</script>
<?php 
} 

if($export_excel){
	echo "<style type=\"text/css\"> ";
	include ("css/timesheet.css");
	echo "</style>";
}
echo "<title>".Config::getMainTitle()." | ".JText::_('USER_SUMMARY')." | ".gbl::getContextUser()."</title>";

PageElements::setHead(ob_get_contents());
ob_end_clean();
?>

<h1><?php echo JText::_('USER_SUMMARY'); ?></h1>

<?php

	if($print) {
		PageElements::setBodyOnLoad('window.print();');
	} 
	else if($export_excel) {
	} 
	else {
		require_once("include/tsx/navcal/navcal.class.php");
		$nav = new NavCal();
		$nav->navCalWithEndDates($startTimestamp,$endTimestamp,$startMonth);  
	}
?>

<?php if(!$export_excel) { ?>
<form name="monthForm" action="<?php print Rewrite::getShortUri(); ?>" method="get">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left">
							<b><?php echo JText::_('USER'); ?>:</b>&nbsp;
							<?php Common::user_select_droplist($uid, false); ?>
						</td>
						<td align="center" class="outer_table_heading">
					<?php Common::printDateSelector($mode, $startDate, $previousDate, $nextDate); ?>
						</td>
						<?php if (!$print): ?>
							<td align="right" width="10%">
								<input type="radio" name="time_fmt" value="decimal" onclick="this.form.submit()"
									<?php if($time_fmt == "decimal") print " checked=\"checked\""; ?> /> Hrs.dec&nbsp;<br />
								<input type="radio" name="time_fmt" value="hrsMins" onclick="this.form.submit()"
									<?php if($time_fmt != "decimal") print " checked=\"checked\""; ?> /> Hrs:Min&nbsp;
							</td>
							<td align="right">
								<a href="<?php echo Rewrite::getShortUri()."?".$p1post; ?>" class="outer_table_action"><?php echo JText::_('HALF_MONTH_1'); ?></a><br />
								<a href="<?php echo Rewrite::getShortUri()."?".$p2post; ?>" class="outer_table_action"><?php echo JText::_('HALF_MONTH_2'); ?></a>
							</td>
							<td  align="right" width="15%" >
								<button name="export_excel" onclick="reload2Export(this.form)"><img src="<?php echo Config::getRelativeRoot();?>/images/icon_xport-2-excel.gif" alt="Export to Excel"/></button> &nbsp;
								<button onclick="popupPrintWindow()"><img src="<?php echo Config::getRelativeRoot();?>/images/icon_printer.gif" alt="Print Report"  /></button>
							</td>
						<?php endif; ?>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>

<?php } // end if !export_excel
else {  //create Excel header
	list($fn,$ln) = get_users_name($uid);
	echo "<h4>Report for $ln, $fn<br />";
	echo date("F d", $startTimestamp)."  to  ";
	echo date("d", $endTimestamp);
	echo ", $endYear";
	echo "</h4>";
}
?>
<div id="monthly">
	<table class="monthTable">
		
<?php

// ==============================================================================================
// Fetch data records
list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, 0, gbl::getClientId());

if ($num == 0) {
	print "	<tr>\n";
	print "		<td align=\"center\">\n";
	print "			<i><br />No hours recorded.<br /><br /></i>\n";
	print "		</td>\n";
	print "	</tr>\n";
} else {
	$dayArray = array();
	while ($data = dbResult($qh)) {
		//if entry doesn't have an end time or duration, it's an incomplete entry
		//fixStartEndDuration returns a 0 if the entry is incomplete.
		if(!Common::fixStartEndDuration($data)) continue;

		//Since we're allowing entries that may span date boundaries, this complicates
		//our life quite a lot.  We need to "pre-process" the results to split those
		//entries that do span date boundaries into multiple entries that stop and then
		//re-start on date boundaries.
		//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
		$orderby="project";
		Common::split_data_into_discrete_days($data,$orderby,$dayArray,0);
	}

	ksort($dayArray);
	unset($data);

?>
<thead>
		<tr class="table_head" width="15%">
			<th>Client&nbsp;/ Project&nbsp;/ Task</th>
<?php 
	$daytotals=array();
	
	//print the days we are going to display
	
	$currentTime = $startTimestamp;
	for ($i = $startDay; $i <= $endDay; $i++) {
		//$currentDayStr = strftime("%a %d/%m/%y", $currentTime);
		$currentDayStr = strftime("%d/%m", $currentTime);
		echo "<th align=\"center\" width=\"5%\">$currentDayStr</th>\n";
		
		$dayStamp = mktime(0,0,0,$startMonth,$i,$startYear);
		$stamp_to_day_array[$dayStamp]=$i;
		$daytotals[$i]=0;
		
		$currentTime = strtotime(date("d M Y H:i:s",$currentTime) . " +1 days"); // increment date to next day
	}
	
	//ppr($daytotals);
	unset($currentTime);
  //ppr($stamp_to_day_array); 
?>
		<th align="right" width="7%">
			Totals
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$completeArray=array();
		//ppr($dayArray,'darry');
		
		foreach($dayArray as $dary){
			foreach($dary as $data){
				//need to make sure date is in range of what we want...
				if($data["start_stamp"] < $startTimestamp) continue;
				if($data["start_stamp"] >= $endTimestamp2) continue;

				$rowName = $data["clientName"]."&nbsp;/ ".$data["projectTitle"]."&nbsp;/ ".$data["taskName"];
				
				//for each rowName that does not yet exist, create it and
				//add each day in at the same time
				if(!array_key_exists($rowName,$completeArray)) {
					for ($mm = $startDay; $mm <= $endDay; $mm++) {
						$completeArray[$rowName][$mm]=0;
					}
				}

				$found = false;
				//work out which day the start_stamp is from
				foreach($stamp_to_day_array as $currentTime=>$dayFromStamp){
					if($data["start_stamp"] <= $currentTime){
						$completeArray[$rowName][$dayFromStamp]+=$data["duration"];
						$daytotals[$dayFromStamp]+=$data["duration"];
						$found = true;
						break;
					}
					continue;
				}
				if(!$found && $data['start_stamp'] <= strtotime(date("d M Y H:i:s",$currentTime) . " +1 days")){
					$completeArray[$rowName][$dayFromStamp]+=$data["duration"];
					$daytotals[$dayFromStamp]+=$data["duration"];
					$found = true;
				}
				else if(!$found) {
					ppr('no match found',$data['start_stamp']);
				}
			}
		}
		//ppr($completeArray,'cptarray');
		unset($rowName);
		unset($dayFromStamp);

		$grandtotal=0;
		foreach($completeArray as $rowName => $rowDayArray){
			echo "<tr>";
			echo "<td class=\"calendar_cell_right\">".$rowName."</td>";
			$tasktotal=0;

			foreach($rowDayArray as $day => $duration) {
				$tasktotal+=$duration;
				$grandtotal+=$duration;
				$time = $report->format_time($duration,$time_fmt);
				echo "<td class=\"calendar_cell_right\" align=\"right\" width=\"50\">".$time."</td>";
			} //end foreach $rowDayArray

			echo "<td class=\"calendar_cell_right\" align=\"right\" style=\"font-weight: bold;\">";
			echo $report->format_time($tasktotal,$time_fmt);
			echo"</td>";
			echo"</tr>";
		} //end foreach $completeArray ?>

					<tr>
						<td class="calendar_cell_right" style="font-weight: bold;  border-bottom: 0px;">
							Totals
						</td>
	<?php
		for ($mm = $startDay; $mm <= $endDay; $mm++) {
	?>
						<td class="calendar_cell_right" align="right" style="font-weight: bold; border-bottom: 0px;">
							<?php echo $report->format_time($daytotals[$mm],$time_fmt); ?>
						</td>
	<?php }?>
						<td class="calendar_cell_right" align="right" style="font-weight: bold; border-bottom: 0px;">
							<?php print $report->format_time($grandtotal,$time_fmt); ?>
						</td>
					</tr>
<?php } //end if $num==0 ?>
				</table>
			</td>
		</tr>
	</table>

<?php if(!$export_excel) { ?>

		</td>
	</tr>
</table>

</form>
<?php 
} //end if !export_excel 
?>
