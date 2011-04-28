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

//load local vars from request/post/get
if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "username";

$uid=''; $proj_id=0; $client_id=0;

//get the context date


// from report_user
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
}
if ($mode == "weekly") {
	list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
}



//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...
list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, $proj_id, $client_id);

if($orderby == "username") {
	$subtotal_label[]="User total";
	$colVar[]="uid";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]="Project total";
	$colVar[]="proj_id";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

//	$subtotal_label[]="Task total";
	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="duration";
	$colWid[]="width=\"10%\"";
	$colAlign[]="align=\"right\"";
	$colWrap[]="";
}

else if($orderby == "project") {
	$subtotal_label[]="Project total";
	$colVar[]="proj_id";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="";

	$subtotal_label[]="Task total";
	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

//	$subtotal_label[]="User total";
	$colVar[]="uid";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="duration";
	$colWid[]="width=\"10%\"";
	$colAlign[]="align=\"right\"";
	$colWrap[]="";
}

else if($orderby == "task") {
	$subtotal_label[]="Task total";
	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

//	$subtotal_label[]="Project total";
	$colVar[]="proj_id";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="";

//	$subtotal_label[]="User total";
	$colVar[]="uid";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

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
	print "<a href=\"javascript:void(0)\" onclick=window.open(\"" . $script .
		"?$variable=$info\",\"$title\",\"location=0,directories=no,status=no,scrollbar=yes," .
		"menubar=no,resizable=1,width=500,height=200\")>";
}

function make_user_link($uid, $string) {
	global $ymdStr;
	echo "<a href=\"".Config::getRelativeRoot()."/report_user?" . $ymdStr . "&amp;uid=$uid&amp;mode=monthly\">" . 
		$string .  "</a>&nbsp;"; 
}

function printInfo($type, $data) {
	global $ymdStr, $time_fmt;	

	if($type == "uid") {
		make_user_link($data["uid"],$data["uid"]);
		print "</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print $data["first_name"]."&nbsp;</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print $data["last_name"]."&nbsp;\n";
	} else if($type == "proj_id") {
		jsPopupInfoLink(Config::getRelativeRoot()."/client_info", "client_id", $data["client_id"], "Client_Info");
		print stripslashes($data["clientName"])."</a> / ";
		jsPopupInfoLink(Config::getRelativeRoot()."/proj_info", "proj_id", $data["proj_id"], "Project_Info");
		print stripslashes($data["projectTitle"])."</a>&nbsp;\n";
	} else if($type == "taskName") {
		jsPopupInfoLink(Config::getRelativeRoot()."/task_info", "task_id", $data["task_id"], "Task_Info");
		print stripslashes($data["taskName"])."</a>&nbsp;\n";
	} else if($type == "duration") {
		print format_time($data["duration"],$time_fmt);
	} else print "type unknown: $type &nbsp;";
}

function printBlanks($type) {
	if($type == "uid") {
		print "&nbsp;</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print "&nbsp;</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print "&nbsp;";
	} else if($type == "proj_id") {
		print "&nbsp;";
	} else if($type == "taskName") {
		print "&nbsp;";
	} else if($type == "duration") {
		print "&nbsp;";
	} else print "type unknown: $type &nbsp;";
}

function make_index($data,$order) {
	if($order == "username") {
		$index=sprintf("%-25.25s",$data["uid"]) .  sprintf("-%05d-",$data["proj_id"]) . sprintf("-%05d-",$data["task_id"]);
	} else if($order == "project") {
		$index=sprintf("-%05d",$data["proj_id"]) . sprintf("-%05d",$data["task_id"]) . sprintf("%-25.25s",$data["uid"]);
	} else {
		$index=sprintf("-%-25.25s",$data["taskName"]) . sprintf("-%05d",$data["proj_id"]) . sprintf("%-25.25s",$data["uid"]);
	}
	return $index;
}

$Location="$_SERVER[PHP_SELF]?$ymdStr&amp;orderby=$orderby";
gbl::setPost("&amp;orderby=$orderby");

if(!$export_excel) 
	require("report_javascript.inc");
PageElements::setHead("<title>".Config::getMainTitle()." | All hours this month</title>");

	if(!$export_excel) ; //include ("header.inc");
	else {
		print "<style type=\"text/css\"> ";
		include ("css/timesheet.css");
		print "</style>";
	}

	if($print) {
		echo "<body width=\"100%\" height=\"100%\"";
		//include ("body.inc");

		echo "onLoad=window.print();";
		echo ">\n";
	} else if($export_excel) {
		echo "<body ";
		//include ("body.inc");
		echo ">\n";
	} else {
		echo "<body ";
		//include ("body.inc");
		echo ">\n";
		echo "<div id=\"header\">";
		//include ("banner.inc");
		$motd = 0;  //don't want the motd printed
		require_once("include/tsx/navcal/navcal.class.php");
	  $nav = new NavCal();
		$nav->navCalMonthly();
		echo "</div>";
	}
?>

<?php if(!$export_excel) { ?>
<form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="get">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td width="15%">&nbsp;</td>
						<td align="left" nowrap class="outer_table_heading">
						<?php echo date('F Y',mktime(0,0,0,$month,1,$year)) ?>
						</td>
						<?php if (!$print): ?>
							<td  align="right" width="15%" nowrap >
								<button name="export_excel" onclick="reload2Export(this.form)"><img src="images/icon_xport-2-excel.gif" alt="Export to Excel" align="absmiddle" /></button> &nbsp;
								<button onclick="popupPrintWindow()"><img src="images/icon_printer.gif" alt="Print Report" align="absmiddle" /></button>
							</td>
						<?php endif; ?>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>

<?php } // end if !export_excel
else {  //create Excel header
	echo "<h4>Report Everything<br />";
	echo "Month of " . date("F, Y", $startDate);
	echo "</h4>";
}
?>

				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<tr class="inner_table_head">
					<?php
						$userPost="$ymdStr&amp;orderby=username";
						$projPost="$ymdStr&amp;orderby=project";
						$taskPost="$ymdStr&amp;orderby=task";
						if($orderby=='username'): ?>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $userPost; ?>" class="inner_table_column_heading">Username</a></td>
							<td class="inner_table_column_heading">First Name</td>
							<td class="inner_table_column_heading">Last Name</td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $projPost; ?>" class="inner_table_column_heading">Client / Project</a></td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $taskPost; ?>" class="inner_table_column_heading">Task</a></td>
						<?php elseif($orderby=='project'): ?>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $projPost; ?>" class="inner_table_column_heading">Client / Project</a></td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $taskPost; ?>" class="inner_table_column_heading">Task</a></td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $userPost; ?>" class="inner_table_column_heading">Username</a></td>
							<td class="inner_table_column_heading">First Name</td>
							<td class="inner_table_column_heading">Last Name</td>
						<?php elseif($orderby=='task'): ?>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $taskPost; ?>" class="inner_table_column_heading">Task</a></td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $projPost; ?>" class="inner_table_column_heading">Client / Project</a></td>
							<td class="inner_table_column_heading"><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $userPost; ?>" class="inner_table_column_heading">Username</a></td>
							<td class="inner_table_column_heading">First Name</td>
							<td class="inner_table_column_heading">Last Name</td>
						<?php else: ?>
							<td colspan="4" class="inner_table_column_heading">unknown orderby, so I don't know what the headers should look like</td>
						<?php endif; ?>
						<td class="inner_table_column_heading">Duration</td>
					</tr>
<?php

/*$query =	"select distinct first_name, ".
			"last_name, ".
			"".tbl::getuserTable().".username, ".
			"pt.title, ".
			"pt.proj_id, ".
			"tt.name, ".
			"tt.task_id ".
		"FROM ".tbl::getUserTable()." ut, ".tbl::getProjectTable()." pt , ".tbl::getTaskTable()." tt , ".tbl::getAssignmentTable()." at, ".tbl::getTaskAssignmentsTable()." tat ".
		"WHERE at.proj_id = pt.proj_id AND ".
			"tat.task_id = tt.task_id AND ".
			"pt.proj_id = tt.proj_id AND ".
			"at.username = ut.username AND ".
			"ut.username NOT IN ('admin','guest') ".
		"ORDER BY $orderby";


$query = "SELECT tt.proj_id, ".
		"tt.task_id, ".
		"tt.log_message, " .
		"end_time AS end_time_str, ".
		"start_time AS start_time_str, ".
		"timediff(end_time, start_time) as diff_time, ".
		"unix_timestamp(end_time) as end_stamp, ".
		"unix_timestamp(start_time) as start_stamp, ".
		"pt.title, ".
		"tt.name, ".
		"first_name, last_name, ".
		"timest.uid as username, ".
		"date_format(start_time, '%Y/%m/%d') as start_date, ".
		"trans_num ".
	"FROM ".tbl::getUserTable()." ut, ".tbl::getTimesTable()." timest, ".tbl::getProjectTable()." pt, ".tbl::getTaskTable()." tt ".
	"WHERE timest.uid= ut.username AND ".
		"end_time > 0 AND ".
		//"timest.uid='$uid' AND ".
		"start_time >= '$year-$month-1' AND ".
		"pt.proj_id = timest.proj_id AND ".
		"tt.task_id = timest.task_id AND ".
		"end_time < '".date('Y-m-1',$next_month)."' ".
	"ORDER BY $orderby";

//LogFile::write("Query: $query\n");

	list ($qh,$num) = dbQuery($query);
*/
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br />No hours recorded.<br /><br /></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	} else {
		//Setup for three levels of subtotals
		$last_colVar[0]='';
		$last_colVar[1]='';
		$last_colVar[2]='';

		$level_total[0] = 0;
		$level_total[1] = 0;
		$level_total[2] = 0;

		while ($data = dbResult($qh)) {
			//if entry doesn't have an end time or duration, it's an incomplete entry
			//fixStartEndDuration returns a 0 if the entry is incomplete.
			if(!Common::fixStartEndDuration($data)) continue;

			//Since we're allowing entries that may span date boundaries, this complicates
			//our life quite a lot.  We need to "pre-process" the results to split those
			//entries that do span date boundaries into multiple entries that stop and then
			//re-start on date boundaries.
			//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
			Common::split_data_into_discrete_days($data,$orderby,$darray,0);
		}

		ksort($darray);
		unset($data);

		foreach($darray as $dary){
			foreach($dary as $data){
				//need to make sure date is in range of what we want...
				if($data["start_stamp"] < $startDate) continue;
				if($data["start_stamp"] >= $endDate) continue;

				if(isset($subtotal_label[2]) && (($last_colVar[2] != $data[$colVar[2]]) || ($last_colVar[1] != $data[$colVar[1]]) || ($last_colVar[0] != $data[$colVar[0]]))) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[2],$time_fmt);
						print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[2].": <span class=\"report_sub_total2\">$formatted_time</span></td></tr>\n";
					}
					$level_total[2]=0;
				}
				if(isset($subtotal_label[1]) && (($last_colVar[1] != $data[$colVar[1]]) || ($last_colVar[0] != $data[$colVar[0]]))) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[1],$time_fmt);
						print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td>";
						print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
					}
					$level_total[1]=0;
					$last_colVar[2]="";
				}
				if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[0],$time_fmt);
						print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td>";
						print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
					}
					$level_total[0]=0;
					$last_colVar[1]="";
					$last_colVar[2]="";
				}

				print "<tr>";
				for($i=0; $i<4; $i++) {
					if($i==3)
						print "<td valign=\"top\" class=\"calendar_cell_right\" ".$colWid[$i]." ".$colAlign[$i]." ".$colWrap[$i].">";
					else
						print "<td valign=\"top\" class=\"calendar_cell_middle\" ".$colWid[$i]." ".$colAlign[$i]." ".$colWrap[$i].">";
					if($i<3) {
						if($last_colVar[$i] != $data[$colVar[$i]]) {
							printInfo($colVar[$i], $data);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							printBlanks($colVar[$i]);
					} else
						printInfo($colVar[$i], $data);
					print "</td>";
				}
				print "</tr>";

				$level_total[0] += $data["duration"];
				$level_total[1] += $data["duration"];
				$level_total[2] += $data["duration"];
				$grand_total_time += $data["duration"];
			}
		}

		if (isset($subtotal_label[2]) && $level_total[2]) {
			$formatted_time = format_time($level_total[2],$time_fmt);
			print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[2].": <span class=\"report_sub_total2\">$formatted_time</span></td></tr>\n";
		}
		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = format_time($level_total[1],$time_fmt);
			print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td>";
			print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = format_time($level_total[0],$time_fmt);
			print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td>";
			print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
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
						Monthly	Grand total:
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

</form>
<?php if (!$print) {
		echo "<div id=\"footer\">"; 
		//include ("footer.inc"); 
		echo "</div>";
	}
} //end if !export_excel 
?>
