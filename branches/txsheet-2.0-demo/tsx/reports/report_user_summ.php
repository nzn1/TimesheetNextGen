<?php

if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;

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

//What bi-monthly period is the context date in?
if(gbl::getDay() < 16) {
	$sday=1; 
	$eday=15;
} else {
	$sday=16;
	$eday=date('t',strtotime(gbl::getYear()."-".gbl::getMonth()."-15"));
}

//get start and end dates for the calendars
$start_day   = isset($_GET['start_day'])   && $_GET['start_day']   ? (int)$_GET['start_day']   : $sday;
$startMonth = isset($_GET['start_month']) && $_GET['start_month'] ? (int)$_GET['start_month'] : gbl::getMonth();
$startYear  = isset($_GET['start_year'])  && $_GET['start_year']  ? (int)$_GET['start_year']  : gbl::getYear();

$end_day     = isset($_GET['end_day'])     && $_GET['start_day']   ? (int)$_GET['end_day']     : $eday;
//$end_month   = isset($_GET['end_month'])   && $_GET['start_month'] ? (int)$_GET['end_month']   : gbl::getMonth();
//$end_year    = isset($_GET['end_year'])    && $_GET['start_year']  ? (int)$_GET['end_year']    : gbl::getYear();
//Since we're only allowing the user to choose a start and end day within the cur. context month 
//we can do this:  But note: we can't remove the variables altogether, or we'd need yet another set
//of functions to create the navcal calendars, and at some point we may want to allow more
//freestyle date choosing...
$end_month   = $startMonth;
$end_year    = $startYear;

$startTimestamp = strtotime($startYear . '/' . $startMonth . '/' . $start_day);
$end_time   = strtotime($end_year   . '/' . $end_month   . '/' . $end_day);
$end_time2   = strtotime("+1 day",$end_time);  //need last day to be inclusive...

$startStr = date("Y-m-d H:i:s",$startTimestamp);
$endStr = date("Y-m-d H:i:s",$end_time2);

$orderby="project";
function make_index($data,$order) {
	$index=sprintf("%05d-%05d-%05d",$data["client_id"], $data["proj_id"], $data["task_id"]);
	$index.="-".$data["start_stamp"];
	return $index;
}

require_once('report.class.php');
$report = new Report();

$Location= Rewrite::getShortUri()."?uid=$uid&amp;time_fmt=$time_fmt&amp;start_year=$startYear&amp;start_month=$startMonth&amp;start_day=$start_day&amp;end_year=$end_year&amp;end_month=$end_month&amp;end_day=$end_day";
gbl::setPost("uid=$uid&amp;time_fmt=$time_fmt");

if(!$export_excel) {
	require("report_javascript.inc");
?>
<script type="text/javascript">
<!--
/*
* Setup Javascript events for date drop-down lists. Again, this should be included in
* an external js file, but for now I want this report to be self-contained
*/

//run init function on window load
window.onload = init;

//apply auto-submit behaviour when changing date values
function init(){
	var start_day   = getObjectByName('start_day');
	//var start_month = getObjectByName('start_month');
	//var start_year  = getObjectByName('start_year');
	var end_day     = getObjectByName('end_day');
	//var end_month   = getObjectByName('end_month');
	//var end_year    = getObjectByName('end_year');

	start_day.onchange   = function (){this.form.submit();};
	//start_month.onchange = function (){this.form.submit();};
	//start_year.onchange  = function (){this.form.submit();};
	end_day.onchange     = function (){this.form.submit();};
	//end_month.onchange   = function (){this.form.submit();};
	//end_year.onchange    = function (){this.form.submit();};
}
//-->
</script>
<?php } //end if !export_excel ?>

<?php 
	if(!$export_excel) ;
	else {
		print "<style type=\"text/css\"> ";
		include ("css/timesheet.css");
		print "</style>";
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
	  $nav->navCalWithEndDates($startTimestamp,$end_time,$startMonth);  
	}

?>

<?php if(!$export_excel) { ?>
<form action="<?php print Rewrite::getShortUri(); ?>" method="get">
<input type="hidden" name="start_month" value="<?php echo $startMonth; ?>" />
<input type="hidden" name="start_year" value="<?php echo $startYear; ?>" />
<input type="hidden" name="end_month" value="<?php echo $end_month; ?>" />
<input type="hidden" name="end_year" value="<?php echo $end_year; ?>" />

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
						<?php
							echo utf8_encode(strftime("%B", $startTimestamp))." ";
							Common::day_button("start_day",$startTimestamp);
							echo "&nbsp;&nbsp;-&nbsp;&nbsp;";
							Common::day_button("end_day",$end_time);
							echo " $end_year";
						?> 
						</td>
						<?php if (!$print): ?>
							<td align="right" width="10%">
								<input type="radio" name="time_fmt" value="decimal" onclick="this.form.submit()"
									<?php if($time_fmt == "decimal") print " checked=\"checked\""; ?> /> Hrs.dec&nbsp;<br />
								<input type="radio" name="time_fmt" value="hrsMins" onclick="this.form.submit()"
									<?php if($time_fmt != "decimal") print " checked=\"checked\""; ?> /> Hrs:Min&nbsp;
							</td>
							<td align="right">
							<?php
								$p1post="uid=$uid&amp;time_fmt=$time_fmt&amp;start_year=$startYear&amp;start_month=$startMonth&amp;start_day=1&amp;end_year=$end_year&amp;end_month=$end_month&amp;end_day=15";
								$p2post="uid=$uid&amp;time_fmt=$time_fmt&amp;start_year=$startYear&amp;start_month=$startMonth&amp;start_day=16&amp;end_year=$end_year&amp;end_month=$end_month&amp;end_day=".date('t',strtotime("$end_year-$end_month-15"));
							?>
								<a href="<?PHP print Rewrite::getShortUri()."?".$p1post; ?>" class="outer_table_action"><?php echo JText::_('HALF_MONTH_1'); ?></a><br />
								<a href="<?PHP print Rewrite::getShortUri()."?".$p2post; ?>" class="outer_table_action"><?php echo JText::_('HALF_MONTH_2'); ?></a>
							</td>
							<td  align="right" width="15%" >
								<button name="export_excel" onclick="reload2Export(this.form)"><img src="../images/icon_xport-2-excel.gif" alt="Export to Excel"/></button> &nbsp;
								<button onclick="popupPrintWindow()"><img src="../images/icon_printer.gif" alt="Print Report"  /></button>
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
	echo date("d", $end_time);
	echo ", $end_year";
	echo "</h4>";
}
?>
				<table width="100%" border="0" cellpadding="4" cellspacing="0" class="table_body">
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
		Common::split_data_into_discrete_days($data,$orderby,$dayArray,0);
	}

	ksort($dayArray);
	unset($data);

?>
					<!-- Table headers -->
					<tr>
						<td class="calendar_cell_disabled_right">Client&nbsp;/ Project&nbsp;/ Task</td>
						<!--td class="calendar_cell_disabled_right">Project</td>
						<td class="calendar_cell_disabled_right">Task</td-->
<?php 
	$daytotals=array();
	
	//print the days we are going to display
	
	$currentTime = $startTimestamp;
	for ($i = $start_day; $i <= $end_day; $i++) {
		//$currentDayStr = strftime("%a %d/%m/%y", $currentTime);
		$currentDayStr = strftime("%d/%m", $currentTime);
		echo "<th class=\"inner_table_column_heading\" align=\"center\" width=\"10%\">$currentDayStr</th>\n";
		
		$dayStamp = mktime(0,0,0,$startMonth,$i,$startYear);
		$stamp_to_day_array[$dayStamp]=$i;
		$daytotals[$i]=0;
		
		$currentTime = strtotime(date("d M Y H:i:s",$currentTime) . " +1 days"); // increment date to next day
	}
	
	//ppr($daytotals);
	unset($currentTime);
  //ppr($stamp_to_day_array); 
?>
    
		<td class="calendar_cell_disabled_right" align="right">
			Totals
		</td>
	</tr>
	<?php
		$completeArray=array();
		//ppr($dayArray,'darry');
		
		foreach($dayArray as $dary){
		  foreach($dary as $data){
		    
				//need to make sure date is in range of what we want...
				if($data["start_stamp"] < $startTimestamp) continue;
				if($data["start_stamp"] >= $end_time2) continue;

				$rowName = $data["clientName"]."&nbsp;/ ".$data["projectTitle"]."&nbsp;/ ".$data["taskName"];
				
        //for each rowName that does not yet exist, create it and
        //add each day in at the same time
        if(!array_key_exists($rowName,$completeArray)) {
					for ($mm = $start_day; $mm <= $end_day; $mm++) {
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
		for ($mm = $start_day; $mm <= $end_day; $mm++) {
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
