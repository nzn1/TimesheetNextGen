<?php
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
//require("debuglog.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=Administrator");
	exit;
}

// Connect to database.
$dbh = dbConnect();

//define the command menu & we get these variables from $_REQUEST:
//  $month $day $year $client_id $proj_id $task_id
include("timesheet_menu.inc");

$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "username";

$uid=''; $proj_id=0; $client_id=0;

//get the context date
$todayDate = mktime(0, 0, 0,$month, $day, $year);
$dateValues = getdate($todayDate);
$ymdStr = "&year=".$dateValues["year"] . "&month=".$dateValues["mon"] . "&day=".$dateValues["mday"];

$startDate = mktime(0,0,0, $month, 1, $year);
$startStr = date("Y-m-d H:i:s",$startDate);

$endDate = getMonthlyEndDate($dateValues);
$endStr = date("Y-m-d H:i:s",$endDate);

//$debug = new logfile();

//export data to excel (or not)
$export_excel = isset($_GET["export_excel"]) ? (bool)$_GET["export_excel"] : false;

// if exporting data to excel, print appropriate headers. Ensure the numbers written in the spreadsheet
// are in H.F format rather than HH:MI
if($export_excel){
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");
	header("Pragma: no-cache"); 
	$time_fmt = 'decimal';
} else
	$time_fmt = 'time';

//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...
list($num, $qh) = get_time_records($startStr, $endStr, $uid, $proj_id, $client_id);

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

function format_time($time) {
	global $time_fmt;
	if($time > 0) {
		if($time_fmt == "decimal")
			return minutes_to_hours($time);
		else 
			return format_minutes($time);
	} else 
		return "-";
}

function jsPopupInfoLink($script, $variable, $info, $title = "Info") {
	print "<a href=\"javascript:void(0)\" ONCLICK=window.open(\"" . $script .
		"?$variable=$info\",\"$title\",\"location=0,directories=no,status=no,scrollbar=yes," .
		"menubar=no,resizable=1,width=500,height=200\")>";
}

function make_user_link($uid, $string) {
	global $ymdStr;
	echo "<a href=\"report_user.php?" . $ymdStr . "&uid=$uid&mode=&monthly\">" . 
		$string .  "</a>&nbsp;"; 
}

function printInfo($type) {
	global $data,$ymdStr;	

	if($type == "uid") {
		make_user_link($data["uid"],$data["uid"]);
		print "</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print $data["first_name"]."&nbsp;</td>";
		print "<td valign=\"top\" class=\"calendar_cell_middle\" nowrap>";
		print $data["last_name"]."&nbsp;\n";
	} else if($type == "proj_id") {
		jsPopupInfoLink("client_info.php", "client_id", $data["client_id"], "Client_Info");
		print stripslashes($data["clientName"])."</a> / ";
		jsPopupInfoLink("proj_info.php", "proj_id", $data["proj_id"], "Project_Info");
		print stripslashes($data["projectTitle"])."</a>&nbsp;\n";
	} else if($type == "taskName") {
		jsPopupInfoLink("task_info.php", "task_id", $data["task_id"], "Task_Info");
		print stripslashes($data["taskName"])."</a>&nbsp;\n";
	} else if($type == "duration") {
		print format_time($data["duration"]);
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

$Location="$_SERVER[PHP_SELF]?$ymdStr&orderby=$orderby";
$post="&orderby=$orderby";

?>

<?php if(!$export_excel) { ?>
<script type="text/javascript">
<!--
function popupPrintWindow() {
	window.open("<?php echo "$Location&print=yes"; ?>", "PopupPrintWindow", "location=0,status=no,menubar=no,resizable=1,width=800,height=450");
}
//-->
</script>
<?php } //end if !export_excel ?>

<html>
<head>
<title>Timesheet.php Report: All hours this month</title>
<?php 
	if(!$export_excel) include ("header.inc");
	else {
		print "<style type=\"text/css\"> ";
		include ("css/timesheet.css");
		print "</style>";
	}
?>
</head>
<?php 
	if($print) {
		echo "<body width=\"100%\" height=\"100%\"";
		include ("body.inc");

		echo "onLoad=window.print();";
		echo ">\n";
	} else if($export_excel) {
		echo "<body ";
		include ("body.inc");
		echo ">\n";
	} else {
		echo "<body ";
		include ("body.inc");
		echo ">\n";
		include ("banner.inc");
		$MOTD = 0;  //don't want the MOTD printed
		include("navcal/navcal_monthly.inc");
	}
?>

<?php if(!$export_excel) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php if(!$print) include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" width="34%" nowrap class="outer_table_heading">
						<?php echo date('F Y',mktime(0,0,0,$month,1,$year)) ?>
						</td>
						<?php if (!$print): ?>
							<td  align="center" width="10%" >
							<a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $_SERVER["QUERY_STRING"];?>&export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0><br>&rArr;&nbsp;Excel </a>
							</td>
							<td  align="center" width="33%">
							<?php 
								print "<button onClick=\"popupPrintWindow()\">Print Report</button></td>\n"; 
							?>
							</td>
						<?php endif; ?>
						<td align="right" width="33%" nowrap>&nbsp;
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php if(!$print) include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>

<?php } // end if !export_excel ?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<tr class="inner_table_head">
					<?php
						$userPost="$ymdStr&orderby=username";
						$projPost="$ymdStr&orderby=project";
						$taskPost="$ymdStr&orderby=task";
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
			"$USER_TABLE.username, ".
			"$PROJECT_TABLE.title, ".
			"$PROJECT_TABLE.proj_id, ".
			"$TASK_TABLE.name, ".
			"$TASK_TABLE.task_id ".
		"FROM $USER_TABLE, $PROJECT_TABLE, $TASK_TABLE, $ASSIGNMENTS_TABLE, $TASK_ASSIGNMENTS_TABLE ".
		"WHERE $ASSIGNMENTS_TABLE.proj_id = $PROJECT_TABLE.proj_id AND ".
			"$TASK_ASSIGNMENTS_TABLE.task_id = $TASK_TABLE.task_id AND ".
			"$PROJECT_TABLE.proj_id = $TASK_TABLE.proj_id AND ".
			"$ASSIGNMENTS_TABLE.username = $USER_TABLE.username AND ".
			"$USER_TABLE.username NOT IN ('admin','guest') ".
		"ORDER BY $orderby";


$query = "SELECT $TIMES_TABLE.proj_id, ".
		"$TIMES_TABLE.task_id, ".
		"$TIMES_TABLE.log_message, " .
		"end_time AS end_time_str, ".
		"start_time AS start_time_str, ".
		"timediff(end_time, start_time) as diff_time, ".
		"unix_timestamp(end_time) as end_stamp, ".
		"unix_timestamp(start_time) as start_stamp, ".
		"$PROJECT_TABLE.title, ".
		"$TASK_TABLE.name, ".
		"first_name, last_name, ".
		"$TIMES_TABLE.uid as username, ".
		"date_format(start_time, '%Y/%m/%d') as start_date, ".
		"trans_num ".
	"FROM $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $TASK_TABLE ".
	"WHERE $TIMES_TABLE.uid=$USER_TABLE.username AND ".
		"end_time > 0 AND ".
		//"$TIMES_TABLE.uid='$uid' AND ".
		"start_time >= '$year-$month-1' AND ".
		"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND ".
		"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
		"end_time < '".date('Y-m-1',$next_month)."' ".
	"ORDER BY $orderby";

//$debug->write("Query: $query\n");

	list ($qh,$num) = dbQuery($query);
*/
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>No hours recorded.<br><br></i>\n";
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
			if(!fixStartEndDuration($data)) continue;

			//Since we're allowing entries that may span date boundaries, this complicates
			//our life quite a lot.  We need to "pre-process" the results to split those
			//entries that do span date boundaries into multiple entries that stop and then
			//re-start on date boundaries.
			//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
			split_data_into_discrete_days($data,$orderby,$darray,0);
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
						$formatted_time = format_time($level_total[2]);
						print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[2].": <span class=\"report_sub_total2\">$formatted_time</span></td></tr>\n";
					}
					$level_total[2]=0;
				}
				if(isset($subtotal_label[1]) && (($last_colVar[1] != $data[$colVar[1]]) || ($last_colVar[0] != $data[$colVar[0]]))) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[1]);
						print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td>";
						print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
					}
					$level_total[1]=0;
					$last_colVar[2]="";
				}
				if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = format_time($level_total[0]);
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
							printInfo($colVar[$i]);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							printBlanks($colVar[$i]);
					} else
						printInfo($colVar[$i]);
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
			$formatted_time = format_time($level_total[2]);
			print "<tr><td colspan=\"6\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[2].": <span class=\"report_sub_total2\">$formatted_time</span></td></tr>\n";
		}
		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = format_time($level_total[1]);
			print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[1].": <span class=\"report_sub_total1\">$formatted_time</span></td>";
			print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = format_time($level_total[0]);
			print "<tr><td colspan=\"5\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
				$subtotal_label[0].": <span class=\"report_total\">$formatted_time</span></td>";
			print "<td class=\"calendar_totals_line_weekly_right\">&nbsp;</td></tr>\n";
		}
		$formatted_time = format_time($grand_total_time);
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
<!-- include the timesheet face up until the end -->
<?php if(!$print) include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

<?php if (!$print) include ("footer.inc"); ?>
<?php } //end if !export_excel ?>
</BODY>
</HTML>
<?php
// vim:ai:ts=4:sw=4
?>
