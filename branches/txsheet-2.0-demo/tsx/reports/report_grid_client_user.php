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








//get start and end dates for the calendars
$startDay   = isset($_GET['start_day'])   && $_GET['start_day']   ? (int)$_GET['start_day']   : 1;
$startMonth = isset($_GET['start_month']) && $_GET['start_month'] ? (int)$_GET['start_month'] : gbl::getMonth();
$startYear  = isset($_GET['start_year'])  && $_GET['start_year']  ? (int)$_GET['start_year']  : gbl::getYear();

$endDay     = isset($_GET['end_day'])     && $_GET['start_day']   ? (int)$_GET['end_day']     : date('t',strtotime(gbl::getYear()."-".gbl::getMonth()."-15"));
$endMonth   = isset($_GET['end_month'])   && $_GET['start_month'] ? (int)$_GET['end_month']   : gbl::getMonth();
$endYear    = isset($_GET['end_year'])    && $_GET['start_year']  ? (int)$_GET['end_year']    : gbl::getYear();

if(!checkdate($endMonth,$endDay,$endYear)) {
	$endDay=Common::get_last_day($endMonth,$endYear);
}

//define working variables
$last_proj_id = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;

$startTimestamp = strtotime($startYear . '/' . $startMonth . '/' . $startDay);
$endTimestamp   = strtotime($endYear   . '/' . $endMonth   . '/' . $endDay);
$endTimestamp2   = strtotime("+1 day",$endTimestamp);  //need last day to be inclusive...

$startStr = date("Y-m-d H:i:s",$startTimestamp);
$endStr = date("Y-m-d H:i:s",$endTimestamp2);

$ymdStr = "&amp;start_year=$startYear&amp;start_month=$startMonth&amp;start_day=$startDay".
		  "&amp;end_year=$endYear&amp;end_month=$endMonth&amp;end_day=$endDay";
$Location= Rewrite::getShortUri()."?$ymdStr&amp;client_id=".gbl::getClientId();

require_once('report.class.php');
$report = new Report();

if(!$export_excel){ 
?>
<script type="text/javascript">
report = new Object();
report.location = "<?php echo $Location;?>";
</script>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/reports.js"></script>

<script type="text/javascript">
	   /*
		* Setup Javascript events for date drop-down lists. Again, this should be included in
		* an external js file, but for now I want this report to be self-contained
		*/

		//run init function on window load
		window.onload = init;

		//apply auto-submit behaviour when changing date values
		function init(){
			var start_day   = getObjectByName('start_day');
			var start_month = getObjectByName('start_month');
			var start_year  = getObjectByName('start_year');
			var end_day     = getObjectByName('end_day');
			var end_month   = getObjectByName('end_month');
			var end_year    = getObjectByName('end_year');

			start_day.onchange   = function (){this.form.submit();};
			start_month.onchange = function (){this.form.submit();};
			start_year.onchange  = function (){this.form.submit();};
			end_day.onchange     = function (){this.form.submit();};
			end_month.onchange   = function (){this.form.submit();};
			end_year.onchange    = function (){this.form.submit();};
		}
	</script>
<?php	
}

if($export_excel){
  echo "<style type=\"text/css\"> ";
	 include ("css/timesheet.css");
  echo "</style>";
}
echo"<title>".Config::getMainTitle()." | Report: Timesheet Summary, ".date('F Y')."</title>";

PageElements::setHead(ob_get_contents());
ob_end_clean();
?>

<h1><?php echo JText::_('GRID_CLIENT_REPORT'); ?></h1>

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
<form action="<?php echo Rewrite::getShortUri(); ?>" method="get">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="face_padding_cell">

        <table width="100%" border="0">
					<tr>
						<td align="left" width="35%">
							<table width="100%" border="0" cellpadding="1" cellspacing="2">
                <tr>
									<td align="right" width="0" class="outer_table_heading"><?php echo JText::_('CLIENT'); ?>:</td>
									<td align="left" width="100%">
											<?php Common::client_select_droplist(gbl::getClientId(), false, !$print); ?>
									</td>									
								</tr>
								<tr>
									<td align="right" width="0" class="outer_table_heading"><?php echo JText::_('USER'); ?>:</td>
									<td align="left" width="100%">
											<?php Common::user_select_droplist($uid, false, "100%"); ?>
									</td>
								</tr>
							</table>
						</td>
						<td width="25%"><!-- <td align="center" class="outer_table_heading">-->
							<table border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td align="right" width="0" class="outer_table_heading">Start Date:</td>
									<td align="left"><?php Common::day_button("start_day",$startTimestamp); Common::month_button("start_month",$startMonth); Common::year_button("start_year",$startYear); ?></td>
								</tr>
								<tr>
									<td align="right" width="0" class="outer_table_heading">End Date:</td>
									<td align="left"><?php Common::day_button("end_day",$endTimestamp); Common::month_button("end_month",$endMonth); Common::year_button("end_year",$endYear); ?></td>
								</tr>
							</table>
						</td>
            <?php						
						if (!$print){ ?>
						<td  align="right" width="15%" >
							<button name="export_excel" onclick="reload2Export(this.form)"><img src="<?php echo Config::getRelativeRoot();?>/images/icon_xport-2-excel.gif" alt="Export to Excel" /></button> &nbsp;
							<button onclick="popupPrintWindow()"><img src="<?php echo Config::getRelativeRoot();?>/images/icon_printer.gif" alt="Print Report" /></button>
						</td>
					<?php }?>
				</tr>
			</table>

			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
				<tr>
					<td>
<?php
} //end if(!$export_excel)
else {  //create Excel header
	list($fn,$ln) = get_users_name($uid);
	$cn = get_client_name(gbl::getClientId());
	echo "<h4>Report for $cn<br />";
	echo "User: $ln, $fn<br />";
	$sdStr = date("M d, Y",$startTimestamp);
	//just need to go back 1 second most of the time, but DST 
	//could mess things up, so go back 6 hours...
	$edStr = date("M d, Y",$endTimestamp2 - 6*60*60); 
	echo "$sdStr&nbsp;&nbsp;-&nbsp;&nbsp;$edStr"; 
	echo "</h4>";
}

// ==========================================================================================================
// FETCH REPORT DATA AND DISPLAY

list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, 0, gbl::getClientId());

//no records were found
if ($num == 0) {
  
	print '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">';
	print "	<tr>\n";
	print "		<td align=\"center\">\n";
	print "			<i><br />No hours recorded.<br /></i>\n";
	if($endTimestamp2 <= $startTimestamp)
		print "			<i><b><br /><font color=\"red\">End time is before Start time!</font><br /></b></i>\n";
	print "		<br /></td>\n";
	print "	</tr>\n";
	print " </table>\n";
} else {
	$dayArray = array();
	$crosstab = array();
	$projects = array();

	//=========================================================================================================================
	//sort the data into an array which we can more easily traverse in order to build the cross-tab report
	while ($data = dbResult($qh)) {
		//if entry doesn't have an end time or duration, it's an incomplete entry
		//fixStartEndDuration returns a 0 if the entry is incomplete.
		if(!Common::fixStartEndDuration($data)) continue;

		//Since we're allowing entries that may span date boundaries, this complicates
		//our life quite a lot.  We need to "pre-process" the results to split those
		//entries that do span date boundaries into multiple entries that stop and then
		//re-start on date boundaries.
		//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
		$orderby = 'date';
		Common::split_data_into_discrete_days($data,$orderby,$dayArray,0);
	}

	ksort($dayArray);
  unset($data);

/*
	//print the days we are going to display
	
	$currentTime = $startTimestamp;
	for ($i = $startDay; $i <= $endDay; $i++) {
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
   
		<td class="calendar_cell_disabled_right" align="right">
			Totals
		</td>
	</tr>
		$completeArray=array();
		//ppr($dayArray,'darry');
		*/

  foreach($dayArray as $dary){
		foreach($dary as $data){

			//need to make sure date is in range of what we want...
			if($data["start_stamp"] < $startTimestamp) continue;
			if($data["start_stamp"] >= $endTimestamp2) continue;

      //we need to extract just the day part of the stamp.  the minutes/seconds are unimportant      
      $startStampDate = getdate($data["start_stamp"]);
      //overwrite the startstamp with the start of day stamp
      $data['start_stamp'] = mktime(0, 0, 0,$startStampDate['mon'],$startStampDate['mday'],$startStampDate['year']);

			if(!isset($crosstab[$data['start_stamp']])){
				$crosstab[$data['start_stamp']] = array();
			}

			$crosstab[$data['start_stamp']][$data['task_id']] = $data['duration'];

			if(!isset($crosstab[$data['start_stamp']]['total'])){
				$crosstab[$data['start_stamp']]['total'] = 0;
			}

			$crosstab[$data['start_stamp']]['total'] += $data['duration'];

			if(!array_key_exists($data['proj_id'], $projects)){
				$projects[$data['proj_id']] = array('title' => $data['projectTitle'], 'total' => 0, 'tasks' => array());
			}

			if(!array_key_exists($data['task_id'], $projects[$data['proj_id']]['tasks'])){
				$projects[$data['proj_id']]['tasks'][$data['task_id']] = array('title' => $data['taskName'], 'total' => 0);
			}
			
			$projects[$data['proj_id']]['tasks'][$data['task_id']]['total'] += $data['duration'];
			$projects[$data['proj_id']]['total'] += $data['duration'];
		}
	}

	asort($projects);
  
  //ppr($projects,'projects');
  //ppr($crosstab,'crosstab');
  ?>
  
	<table border="1" cellpadding="0" cellspacing="0" class="table_body report">
	 <thead>
    <tr>
      <th class="first">&nbsp;</th>
      <th class="first">&nbsp;</th>
    <?php
		foreach($projects as $project_id => $project){
			echo '<th class="project" colspan="' . count($project['tasks']) . '">' . htmlentities($project['title']) . '</th>';
		}
		?>
	  </tr>
    <tr>
	   <th class="first">&nbsp;</th>
	   <th>Total</th>
     <?php
    	foreach($projects as $project_id => $project){
    		foreach($project['tasks'] as $task_id => $task)
    			echo '<th>' . htmlentities($task['title']) . '</th>';
    	}
    ?>
    </tr>
	</thead>
  <?php
	while($startTimestamp <= $endTimestamp){
	  //ppr($startTimestamp, 'startTimestamp');
    
		if(array_key_exists($startTimestamp,$crosstab))
			$data = $crosstab[$startTimestamp];
		else
			$data = array();
      
		if(in_array(date('N',$startTimestamp), array(6,7))){
			echo '<tr class="weekend">';
		}
    else{
			echo '<tr>';
    }
		echo '<td class="date"><strong>' . date('D, jS M, Y',$startTimestamp) . '</strong></td>';

		if($data){
			echo '<td><strong>' . $report->format_time($data['total'],$time_fmt) . '</strong></td>';
		}
		else{
			echo '<td>&nbsp;</td>';
		}

		foreach($projects as $project_id => $project){
			foreach($project['tasks'] as $task_id => $task){
				echo '<td class="cell">';
				if(array_key_exists($task_id, $data)){
					echo htmlentities($report->format_time($data[$task_id],$time_fmt));
					$grand_total_time += $data[$task_id];
				}
				else{
					echo '&nbsp;';
				}

				echo '</td>';
			}
		}

		echo '</tr>';

		$startTimestamp = strtotime(date('d-M-Y',$startTimestamp) . ' + 1 Day');
	}
  ?>
  
	<tr>
	   <td class="grandtotal"><strong>TOTAL</strong></td>
	   <td class="grandtotal"><strong> <?php echo $report->format_time($grand_total_time,$time_fmt);?></strong></td>
    <?php
  	foreach($projects as $project_id => $project){
  		foreach($project['tasks'] as $task_id => $task)
  			echo '<td class="total"><strong>' . htmlentities($report->format_time($task['total'],$time_fmt)) . '</strong></td>';
  	}
    ?>
  </tr>
</table>
<?php
}

if(!$export_excel) { ?>

		</td>
	</tr>
</table>
<?php if ($print) {
  $report->displaySignature(false,true,true);
} 
?>

  		</td>
	</tr>
</table>
</form>
<?php
} //end if !export_excel 

if($export_excel){
exit();
}
?>