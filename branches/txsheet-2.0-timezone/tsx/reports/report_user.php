<?php

if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;

// NOTE:  The session cache limiter and the excel stuff must appear before the session_start call,
//        or the export to excel won't work in IE
session_cache_limiter('public');

//export data to excel (or not) (IE is broken with respect to buttons, so we have to do it this way)
$export_excel=false;
if (isset($_GET["export_excel"]))
	if($_GET["export_excel"] == "1")
		$export_excel=true;

//Create the excel headers now, if needed
if($export_excel){
	header('Expires: 0');
	header('Cache-control: public');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");

	// When exporting data to excel, ensure the numbers written in the spreadsheet 
	// are in H.F format rather than HH:MI  
	$time_fmt = "decimal";
} else
	$time_fmt = "time";

//define the command menu & we get these variables from $_REQUEST:
//  $month $day $year $client_id $proj_id $task_id



//load local vars from request/post/get
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = gbl::getContextUser();

if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

//get the context date
$proj_id = gbl::getProjId();
$client_id =  gbl::getClientId();
$year = gbl::getYear();
$month = gbl::getMonth();
$day = gbl::getDay();

$todayDate = mktime(0, 0, 0, $month, $day, $year);
$dateValues = getdate($todayDate);
$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];

if (gbl::getMode() == "all" || gbl::getMode() == "monthly") $mode = "monthly";
	else $mode = "weekly";
if ($mode == "monthly") {
	$startDate = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());
	$startStr = date("Y-m-d H:i:s",$startDate);

	$endDate = Common::getMonthlyEndDate($dateValues);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 month");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 month");	
	
}
if ($mode == "weekly") {
	list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 week");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 week");	
	
}

//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";

//LogFile::write("calling get_time_records($startStr, $endStr, $uid, $proj_id, $client_id)\n");
//LogFile::write("day = $day, month = $month, year = $year, stDt = $startDate, eDt = $endDate\n");

//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...

list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, $proj_id, $client_id);

if($orderby == "project") {
	$subtotal_label[]="Project total";
	$colVar[]="projectTitle";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]="Task total";
	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

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
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

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

function format_time($time,$time_fmt) {
	if($time > 0) {
		if($time_fmt == "decimal")
			return Common::minutes_to_hours($time);
		else 
			return Common::format_minutes($time);
	} else 
		return "-";
}

function jsPopupInfoLink($script, $variable, $info, $title = "Info") {
	print "<a href=\"javascript:void(0)\" onclick=\"window.open('" . $script .
		"?$variable=$info','$title','location=0,directories=no,status=no,scrollbar=yes," .
		"menubar=no,resizable=1,width=500,height=200')\">";
}

function make_daily_link($ymdStr, $proj_id, $string) {
	echo "<a href=\"".Config::getRelativeRoot()."/daily?" .  $ymdStr .  "&amp;proj_id=$proj_id\">" . 
		$string .  "</a>&nbsp;"; 
}

function printInfo($type, $data) {
	global $time_fmt;
	if($type == "projectTitle") {
		jsPopupInfoLink(Config::getRelativeRoot()."/client_info", "client_id", $data["client_id"], "Client_Info");
		print stripslashes($data["clientName"])."</a> / ";
		jsPopupInfoLink("proj_info.php", "proj_id", $data["proj_id"], "Project_Info");
		print stripslashes($data["projectTitle"])."</a>&nbsp;\n";
	} else if($type == "taskName") {
		jsPopupInfoLink(Config::getRelativeRoot()."/task_info", "task_id", $data["task_id"], "Task_Info");
		print stripslashes($data["taskName"])."</a>&nbsp;\n";
	} else if($type == "duration") {
		//jsPopupInfoLink(Config::getRelativeRoot()."/trans_info", "trans_num", $data["trans_num"], "Time_Entry_Info");
		print format_time($data["duration"],$time_fmt);
	} else if($type == "start_stamp") {
		$dateValues = getdate($data["start_stamp"]);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$formattedDate = sprintf("%04d-%02d-%02d",$dateValues["year"],$dateValues["mon"],$dateValues["mday"]); 
		make_daily_link($ymdStr,0,$formattedDate); 
	} else if($type == "log") {
		if ($data['log_message']) print nl2br(stripslashes($data['log_message']));
		else print "&nbsp;";
	} else if($type == "status") {
		if ($data['status']) print stripslashes($data['status']);
		else print "&nbsp;";
	} else print "&nbsp;";
}

function make_index($data,$order) {
	if($order == "date") {
		$index=$data["start_stamp"] . sprintf("-%05d",$data["proj_id"]) . 
			sprintf("-%05d",$data["task_id"]);
	} else {
		$index=sprintf("%05d",$data["proj_id"]) .  sprintf("-%05d-",$data["task_id"]) .
			$data["start_stamp"];
	}
	return $index;
}

$Location= Rewrite::getShortUri()."?uid=$uid$ymdStr&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode";
gbl::setPost("uid=$uid&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode");

if(!$export_excel) 
	require("report_javascript.inc");

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('USER_REPORT')." | ".gbl::getContextUser()."</title>");

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");
?>
<?php 
	//if(!$export_excel) include ("header.inc");
	//else {
		print "<style type=\"text/css\"> ";
		include ("css/timesheet.css");
		print "</style>";
	//}
?>
</head>
<h1><?php echo JText::_('USER_REPORT'); ?></h1>

<?php
	if($print) {
		//echo "<body width=\"100%\" height=\"100%\"";
		//include ("body.inc");

		//echo "onLoad=window.print();";
		//echo ">\n";
	} else if($export_excel) {
		//echo "<body ";
		//include ("body.inc");
		//echo ">\n";
	} else {
		//echo "<body ";
		//include ("body.inc");
		//echo ">\n";
		//echo "<div id=\"header\">";

		require_once("include/tsx/navcal/navcal.class.php");
	  $nav = new NavCal();
	
		if($mode=='weekly'){
      $nav->navCalNormal();
    }
		else{
      $nav->navCalMonthly();
    }
		echo "</div>";
	}
//require_once("include/language/datetimepicker_lang.inc");

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
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="hidden" name="day" value="<?php echo $day; ?>" />
<input type="hidden" name="mode" value="<?php echo $mode; ?>" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" width="35%">
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<tr>
										<td align="right" width="0" class="outer_table_heading"><?php echo JText::_('CLIENT'); ?>:</td>
										<td align="left" width="100%">
											<?php Common::client_select_list($client_id, $uid, false, false, true, false, "submit();"); ?>
										</td>
									</tr>
									<td align="right" width="0" class="outer_table_heading"><?php echo JText::_('USER'); ?>:</td>
									<td align="left" width="100%">
											<?php Common::user_select_droplist($uid, false,"100%"); ?>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" class="outer_table_heading">
						<?php Common::printDateSelector($mode, $startDate, $prevDate, $nextDate); ?>
						</td>
						
						<?php //}
							if (!$print): ?>
							<td  align="right" width="15%" >
								<button name="export_excel" onclick="reload2Export(this.form)"><img src="../images/icon_xport-2-excel.gif" alt="Export to Excel" align="absmiddle" /></button> &nbsp;
								<button onclick="popupPrintWindow()"><img src="../images/icon_printer.gif" alt="Print Report" align="absmiddle" /></button>
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
	$cn = get_client_name($client_id);
	echo "<h4>Report for $ln, $fn<br />";
	echo "Client = $cn<br />";
	if ($mode == "weekly") {
		$sdStr = date("M d, Y",$startDate);
		//just need to go back 1 second most of the time, but DST 
		//could mess things up, so go back 6 hours...
		$edStr = date("M d, Y",$endDate - 6*60*60); 
		echo "Week: $sdStr&nbsp;&nbsp;-&nbsp;&nbsp;$edStr"; 
	} else
		echo "Month of ".date('F, Y',$startDate);
	echo "</h4>";
}
?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<!-- Table header line -->
					<tr class="inner_table_head">
					<?php 
						$projPost="uid=$uid$ymdStr&amp;orderby=project&amp;client_id=$client_id&amp;mode=$mode";
						$datePost="uid=$uid$ymdStr&amp;orderby=date&amp;client_id=$client_id&amp;mode=$mode";
						if($orderby== 'project'): ?>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost; ?>" class="inner_table_column_heading"><?php echo JText::_('CLIENT').'/'.JText::_('PROJECT'); ?></a></td>
							<td class="inner_table_column_heading"><?php echo JText::_('TASK'); ?></td>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost; ?>" class="inner_table_column_heading"><?php echo JText::_('DATE'); ?></a></td>
						<?php else: ?>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost; ?>" class="inner_table_column_heading"><?php echo JText::_('DATE'); ?></a></td>
							<td class="inner_table_column_heading"><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost; ?>" class="inner_table_column_heading"><?php echo JText::_('CLIENT').'/'.JText::_('PROJECT'); ?></a></td>
							<td class="inner_table_column_heading"><?php echo JText::_('TASK'); ?></td>
						<?php endif; ?>
						<td class="inner_table_column_heading"><?php echo JText::_('DESCRIPTION'); ?></td>
						<td class="inner_table_column_heading"><?php echo JText::_('STATUS'); ?></td>
						<td class="inner_table_column_heading"><?php echo JText::_('DURATION'); ?></td>
					</tr>
<?php
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br />No hours recorded.<br /><br /></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
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
						$formatted_time = format_time($level_total[1],$time_fmt);
						print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td></tr>\n";
					}
					$level_total[1]=0;
				}
				if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[0],$time_fmt);
						print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td></tr>\n";
					}
					$level_total[0]=0;
					$last_colVar[1]="";
				}

				print "<tr>";
				for($i=0; $i<6; $i++) {
					print "<td valign=\"top\" class=\"calendar_cell_right\" ".$colWid[$i]." ".$colAlign[$i]." ".$colWrap[$i].">";
					if($i<2) {
						if($last_colVar[$i] != $data[$colVar[$i]]) {
							printInfo($colVar[$i], $data);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							print "&nbsp;";
					} else
							printInfo($colVar[$i], $data);
					print "</td>";
				}
				print "</tr>";

				$level_total[0] += $data["duration"];
				$level_total[1] += $data["duration"];
				$grand_total_time += $data["duration"];
			}
		}

		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = format_time($level_total[1],$time_fmt);
			print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				//$subtotal_label[1].": <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = format_time($level_total[0],$time_fmt);
			print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				//$subtotal_label[0].": <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td></tr>\n";
		}
		$formatted_time = format_time($grand_total_time,$time_fmt);
	}

?>
						</tr>
					</td>
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
	if ($mode == "weekly")
		print "Weekly";
	else
		print "Monthly";
?>
							total:
							<?php echo $formatted_time; ?>
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
<?php if ($print) { ?>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('EMPLOYEE_SIGNATURE'); ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('MANAGER_SIGNATURE'); ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('CLIENT_SIGNATURE'); ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
		</tr>
	</table>		
<?php } //end if($print) ?>

</form>
<?php if (!$print) {
		//echo "<div id=\"footer\">"; 
		//include ("footer.inc"); 
		//echo "</div>";
	}
} //end if !export_excel 
?>

<?php
// vim:ai:ts=4:sw=4
?>