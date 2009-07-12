<?php
// $Header: /cvsroot/tsheet/timesheet.php/report_specific_user.php,v 1.11 2005/05/23 10:42:46 vexil Exp $ modified by ivan_nz 25/08/08

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclReports')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclReports'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = $contextUser;

//define the command menu
include("timesheet_menu.inc");

// Set default months
setReportDate($year, $month, $day, $next_week, $prev_week, $next_month, $prev_month, $time);

function format_seconds($seconds) {
	$temp = $seconds;
	$hour = (int) ($temp / (60*60));

	if ($hour < 10)
		$hour = '0'. $hour;

	$temp -= (60*60)*$hour;
	$minutes = (int) ($temp / 60);

	if ($minutes < 10)
		$minutes = '0'. $minutes;

	$temp -= (60*$minutes);
	$sec = $temp;

	if ($sec < 10)
		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
								// be a space after the . operator for this to work.
	return "$hour:$minutes:$sec";
}

// Change the date-format for internationalization...
if ($mode == "all") $mode = "monthly";
if ($mode == "weekly") {
	$query = "SELECT $TIMES_TABLE.proj_id, ".
				"$TIMES_TABLE.task_id, ".
				"$TIMES_TABLE.log_message, " .
				"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
				"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, ".
				"$PROJECT_TABLE.title, ".
				"$CLIENT_TABLE.organisation, ".
				"$TASK_TABLE.name, ".
				"date_format(start_time, '%Y/%m/%d') as start_date, ".
				"trans_num ".
			"FROM $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $CLIENT_TABLE, $TASK_TABLE ".
			"WHERE $TIMES_TABLE.uid=$USER_TABLE.username AND ".
				"end_time > 0 AND ".
				"$TIMES_TABLE.uid='$uid' ".
				"AND start_time >= '$year-$month-$day' AND ".
				"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND ".
				"$CLIENT_TABLE.client_id = $PROJECT_TABLE.client_id AND ".
				"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
				"end_time < '".date('Y-m-d',$next_week)."' ".
			"ORDER BY proj_id, task_id, start_time";
} else {
	$query = "SELECT $TIMES_TABLE.proj_id, ".
				"$TIMES_TABLE.task_id, ".
				"$TIMES_TABLE.log_message, " .
				"sec_to_time(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff_time, ".
				"(unix_timestamp(end_time) - unix_timestamp(start_time)) as diff, ".
				"$PROJECT_TABLE.title, ".
				"$CLIENT_TABLE.organisation, ".
				"$TASK_TABLE.name, ".
				"date_format(start_time, '%Y/%m/%d') as start_date, ".
				"trans_num ".
			"FROM $USER_TABLE, $TIMES_TABLE, $PROJECT_TABLE, $CLIENT_TABLE, $TASK_TABLE ".
			"WHERE $TIMES_TABLE.uid=$USER_TABLE.username AND ".
				"end_time > 0 AND ".
				"$TIMES_TABLE.uid='$uid' ".
				"AND start_time >= '$year-$month-1' AND ".
				"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND ".
				"$CLIENT_TABLE.client_id = $PROJECT_TABLE.client_id AND ".
				"$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
				"end_time < '".date('Y-m-1',$next_month)."' ".
			"ORDER BY proj_id, task_id, start_time";
}
//run the query
list($qh,$num) = dbQuery($query);

//define working variables
$last_proj_id = -1;
$last_task_id = -1;
$total_time = 0;
$grand_total_time = 0;

?>
<html>
<head>
<title>Report: Hours for a specific user</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="report_specific_user.php" method="get">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="year" value="<?php echo $year; ?>">
<input type="hidden" name="day" value="<?php echo $day; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap width="200">
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td align="right" width="0" class="outer_table_heading">User:</td>
									<td align="left" width="100%">
											<?php user_select_droplist($uid, false); ?>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading">
						<?php if ($mode == "weekly")
								echo date('F d, Y',$time);
							else
								echo date('F Y',$time);
						?>
						</td>
						<td  align="center" >
						<a href="#" onclick="javascript:esporta('user')" ><img src="images/export_data.gif" name="esporta_dati" border=0></a>
						</td>
						<td align="right" nowrap>
						<?php
							printPrevNext($next_week, $prev_week, $next_month, $prev_month, "uid=$uid", $mode);
						?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
<?php
	$dati_total=array();
	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>No hours recorded.<br><br></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	}
	else {
		while ($data = dbResult($qh)) {
			array_push($dati_total,$data);
			// New project, so print out last project total time and start a new table cell.
			if ($last_proj_id != $data["proj_id"]) {
				$last_proj_id = $data["proj_id"];
				if ($grand_total_time) {
					$formatted_time = format_seconds($total_time);
					print "<tr><td colspan=\"4\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
									"Total: <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>\n";
				}

				$current_project_title = stripslashes($data["title"]);
				$current_client_title = stripslashes($data["organisation"]);
				print "<tr><td valign=\"top\" colspan=\"4\" class=\"calendar_cell_disabled_right\">".
							"$current_client_title / <a href=\"javascript:void(0)\" ONCLICK=window.open(\"proj_info.php?proj_id=$data[proj_id]\",\"Info\",\"location=0,directories=no,status=no,menubar=no,resizable=1,scrollbar=yes,width=500,height=200\") class=\"outer_table_action\">$current_project_title</a>\n";
							"</td></tr>\n";
				$total_time = 0;
			}
		//	print "<tr><td align=\"right\" class=\"calendar_cell_middle\">\n";
			print "<tr>\n\t<td valign=\"top\" align=\"right\" width=\"50%\" class=\"calendar_cell_right\">\n\t";
			if ($last_task_id != $data["task_id"]) {
				$last_task_id = $data["task_id"];
				$current_task_name = stripslashes($data["name"]);
				print "<a href=\"javascript:void(0)\" ONCLICK=window.open(\"task_info.php?task_id=$data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,scrollbar=yes,width=500,height=200\")>$current_task_name</a>&nbsp;\n";
			}
			print "&nbsp;</td>\n\t<td valign=\"top\" align=\"left\" width=\"8%\" class=\"calendar_cell_right\">$data[start_date]:&nbsp;&nbsp;</td>\n\t";
			print "</td>\n\t<td valign=\"top\" align=\"left\" class=\"calendar_cell_right\">";
			if ($data['log_message']) print stripslashes($data['log_message']);
			else print "&nbsp;";
			print "</td>\n\t";
			print "<td valign=\"bottom\" align=\"right\" width=\"5%\" class=\"calendar_cell_right\">\n\t\t";
			print "<a href=\"javascript:void(0)\" ONCLICK=window.open(\"trans_info.php?trans_num=$data[trans_num]\",\"TransactionInfo\",\"location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200\")>$data[diff_time]</a>\n\t</td>\n</tr>\n";			

//			print "<a href=\"javascript:void(0)\" onclick=\"javascript:window.open(" .
//						"'trans_info.php?trans_num=$data[trans_num]'" . 
//						",'Task Event Info','location=0,directories=no" .
//						",status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">";
//			print "&nbsp;&nbsp;$data[diff_time]<a>\n\t</td>\n</tr>\n";

			//print "<a href=\"javascript:void(0)\" onclick=\"javascript:window.open('trans_info.php?trans_num=$data[trans_num]','Task Event Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$data[start_date]: $data[diff_time]</a>\n";
		//					"</tr></td>\n";

			$total_time += $data["diff"];
			$grand_total_time += $data["diff"];
		}

		if ($total_time) {
			$formatted_time = format_seconds($total_time);
			print "<tr><td colspan=\"4\" align=\"right\" class=\"calendar_totals_line_weekly_right\">" .
							"Total: <span class=\"calendar_total_value_weekly\">$formatted_time</span></td></tr>";
		}
		$formatted_time = format_seconds($grand_total_time);
	}
	$out_js="<SCRIPT language=javascript> function esporta() {";
		if (!empty($dati_total)) {
			$_SESSION['excel_data']=$dati_total;
			$out_js.="window.open('./export_timesheet.php?type=user')";
		}else {
			$out_js.="alert('No records to export')";
		}
	$out_js.="} </SCRIPT>";

	echo $out_js;

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
						<td align="right" class="calendar_totals_line_monthly">
<?php
	if ($mode == "weekly")
		print "Weekly";
	else
		print "Monthly";
?>
							total:
							<span class="calendar_total_value_monthly"><?php echo $formatted_time; ?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
<?php
	}
?>
	</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>
<?php
include ("footer.inc");
?>
</BODY>
</HTML>

