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
  // NOTE:  The session cache limiter and the excel stuff must appear before the session_start call, or the export to excel won't work in IE
  session_cache_limiter('public');
	
  header('Expires: 0');
	header('Cache-control: public');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");

	// When exporting data to excel, ensure the numbers written in the spreadsheet 
	// are in H.F format rather than HH:MI  
	$time_fmt = "decimal";
} else{
	$time_fmt = "time";
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

if (gbl::getClientId() == 0){
	//get the first project
	gbl::setClientId(Common::getFirstClient());
}

//I think UID must be set to '' as we want all client data - not user specific
$uid=''; 

$dateValues = gbl::getContextDate();
$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];

if (gbl::getMode() == "all" || gbl::getMode() == "monthly"){
  $mode = "monthly";
}
else{
  $mode = "weekly";
}

if ($mode == "monthly") {
	$startDate = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());
	$startStr = date("Y-m-d H:i:s",$startDate);

	$endDate = Common::getMonthlyEndDate($dateValues);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 month");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 month");		
}
else if ($mode == "weekly") {
	list($startDate,$endDate) = Common::getWeeklyStartEndDates(gbl::getContextTimestamp());

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 week");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 week");	
	


}

//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";

//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...
list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, gbl::getProjId(), gbl::getClientId());

if($orderby == "project") {
	$subtotal_label[]="Project total";
	$colVar[]="projectTitle";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap=\"nowrap\"";

	$subtotal_label[]="Task total";
	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap=\"nowrap\"";

	$colVar[]="start_stamp";
	$colWid[]="width=\"10%\"";
	$colAlign[]=""; $colWrap[]="";

	$colVar[]="log";
	$colWid[]="width=\"35%\"";
	$colAlign[]=""; $colWrap[]="";

  // add status field
	$colVar[]="status";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";

	$colVar[]="duration";
	$colWid[]="width=\"10%\"";
	$colAlign[]="align=\"right\"";
	$colWrap[]="";
}

if($orderby == "date") {
	$subtotal_label[]="Day's total";
	$colVar[]="start_stamp";
	$colWid[]="width=\"10%\"";
	$colAlign[]=""; $colWrap[]="";

//	$subtotal_label[]="Project total";
	$colVar[]="projectTitle";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap=\"nowrap\"";

	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap=\"nowrap\"";

	$colVar[]="log";
	$colWid[]="width=\"35%\"";
	$colAlign[]=""; $colWrap[]="";

// add status field
	$colVar[]="status";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="duration";
	$colWid[]="width=\"10%\"";
	$colAlign[]="align=\"right\"";
	$colWrap[]="";
}

require_once('report.class.php');
$report = new Report();

$Location= Rewrite::getShortUri()."?$ymdStr&amp;orderby=$orderby&amp;client_id=".gbl::getClientId()."&amp;mode=$mode";
gbl::setPost("&amp;orderby=$orderby&amp;client_id=".gbl::getClientId()."&amp;mode=$mode");

if(!$export_excel){ 

?>
<script type="text/javascript">
report = new Object();
report.location = "<?php echo $Location;?>";
</script>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/reports.js"></script>

<?php

}

echo"<title>".Config::getMainTitle()." | ".JText::_('CLIENT_REPORT')." | ".gbl::getContextUser()."</title>";
 
if($export_excel){
  echo "<style type=\"text/css\"> ";
	 include ("css/timesheet.css");
echo "</style>";
	}
PageElements::setHead(ob_get_contents());
ob_end_clean();
?>

<h1><?php echo JText::_('CLIENT_REPORT'); ?></h1>

<?php
	if($print) {
     PageElements::setBodyOnLoad('window.print();');
	} 
  else if($export_excel) {
	} 
  else {
		require_once("include/tsx/navcal/navcal.class.php");
	  $nav = new NavCal();
	
		if($mode=='weekly'){
      $nav->navCalNormal();
    }
		else{
      $nav->navCalMonthly();
    }
	}

?>

<?php if(!$export_excel) { ?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">
	function CallBack_WithNewDateSelected(strDate) 
	{
		document.subreport.submit();
	}
</script>
<form action="<?php echo Rewrite::getShortUri(); ?>" method="post" name="subreport">
<input type="hidden" name="orderby" value="<?php echo $orderby; ?>" />
<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
<input type="hidden" name="proj_id" value="<?php echo gbl::getProjId(); ?>" />
<input type="hidden" name="mode" value="<?php echo $mode; ?>" />

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
							</table>
						</td>
						<td align="center" class="outer_table_heading">
						<?php Common::printDateSelector($mode, $startDate, $prevDate, $nextDate); ?>
						</td>
						
						<?php //}
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

<?php } // end if !export_excel
else {  //create Excel header
	$cn = get_client_name(gbl::getClientId());
	echo "<h4>Report for $cn<br />";
	if ($mode == "weekly") {
		$sdStr = date("M d, Y",$startDate);
		//just need to go back 1 second most of the time, but DST 
		//could mess things up, so go back 6 hours...
		$edStr = date("M d, Y",$endDate - 6*60*60); 
		echo "Week: $sdStr&nbsp;&nbsp;-&nbsp;&nbsp;$edStr"; 
	} 
  else{
		echo "Month of ".date('F, Y',$startDate);
	}
	echo "</h4>";
}
?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<!-- Table header line -->
					<tr class="inner_table_head">
					<?php 
						$projPost="$ymdStr&amp;orderby=project&amp;client_id=".gbl::getClientId()."&amp;mode=$mode";
						$datePost="$ymdStr&amp;orderby=date&amp;client_id=".gbl::getClientId()."&amp;mode=$mode";
						if($orderby== 'project'){ ?>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost; ?>" class="inner_table_column_heading"><?php echo JText::_('PROJECT'); ?></a></td>
							<td class="inner_table_column_heading"><?php echo JText::_('TASK'); ?></td>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost; ?>" class="inner_table_column_heading"><?php echo JText::_('DATE'); ?></a></td>
						<?php 
						}
            else {
            ?>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost; ?>" class="inner_table_column_heading"><?php echo JText::_('DATE'); ?></a></td>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost; ?>" class="inner_table_column_heading"><?php echo JText::_('PROJECT'); ?></a></td>
							<td class="inner_table_column_heading"><?php echo JText::_('TASK'); ?></td>
						<?php
            }
            ?>
						<td class="inner_table_column_heading"><?php echo JText::_('DESCRIPTION'); ?></td>
						<td class="inner_table_column_heading"><?php echo JText::_('STATUS'); ?></td>
            <td class="inner_table_column_heading"><?php echo JText::_('DURATION'); ?></td>
					</tr>
<?php
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
	 ?>
		<tr>
      <td align="center">
        <i><br />No hours recorded.<br /><br /></i>
      </td>    
    </tr>
		<?php
	} else {
		//Setup for two levels of subtotals
		$last_colVar[0]='';
		$last_colVar[1]='';

		$level_total[0] = 0;
		$level_total[1] = 0;

		while ($data = dbResult($qh)) {
			//if entry doesn't have an end time or duration, it's an incomplete entry
			//fixStartEndDuration returns a 0 if the entry is incomplete.
			if(!Common::fixStartEndDuration($data)) continue;

			//Since we're allowing entries that may span date boundaries, this complicates
			//our life quite a lot.  We need to "pre-process" the results to split those
			//entries that do span date boundaries into multiple entries that stop and then
			//re-start on date boundaries.
			//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
			Common::split_data_into_discrete_days($data,$orderby,$darray,1);
		}

		ksort($darray);
		unset($data);

		foreach($darray as $dary){
			foreach($dary as $data){
				//need to make sure date is in range of what we want...
				if($data["start_stamp"] < $startDate) continue;
				if($data["start_stamp"] >= $endDate) continue;

				if(isset($subtotal_label[1]) && (($last_colVar[1] != $data[$colVar[1]]) || ($last_colVar[0] != $data[$colVar[0]]))) {
					if($grand_total_time) {
						$formatted_time = $report->format_time($level_total[1],$time_fmt);
						echo "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td></tr>\n";
					}
					$level_total[1]=0;
				}
				
        if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = $report->format_time($level_total[0],$time_fmt);
						echo "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td></tr>\n";
					}
					$level_total[0]=0;
					$last_colVar[1]="";
				}

				echo "<tr>";
				for($i=0; $i<6; $i++) {
					echo "<td valign=\"top\" class=\"calendar_cell_right\" ".$colWid[$i]." ".$colAlign[$i]." ".$colWrap[$i].">";
					if($i<2) {
						if($last_colVar[$i] != $data[$colVar[$i]]) {
							$report->printInfo($colVar[$i], $data, $time_fmt);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							echo "&nbsp;";
					} else
							$report->printInfo($colVar[$i], $data, $time_fmt);
					echo "</td>";
				}
				echo "</tr>";

				$level_total[0] += $data["duration"];
				$level_total[1] += $data["duration"];
				$grand_total_time += $data["duration"];
			}
		}

		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = $report->format_time($level_total[1],$time_fmt);
			echo "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				//$subtotal_label[1].": <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = $report->format_time($level_total[0],$time_fmt);
			echo "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				//$subtotal_label[0].": <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td></tr>\n";
		}
		$formatted_time = $report->format_time($grand_total_time,$time_fmt);
	}

?>
				</table>
			</td>
		</tr>
<?php
	if ($num > 0) {
?>
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_bottom_panel">
					<tr>
						<td align="right" class="report_grand_total">
<?php
	if ($mode == "weekly"){
		echo "Weekly total: ".$formatted_time;
	}
	else{
		echo "Monthly total: ".$formatted_time;
	}
?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
	}
?>
	</table>

<?php if(!$export_excel) { ?>

		</td>
	</tr>
</table>
<?php if ($print) {
  $report->displaySignature(true,true,true);
} 
?>

</form>
<?php
} //end if !export_excel 

if($export_excel){
exit();
}
?>