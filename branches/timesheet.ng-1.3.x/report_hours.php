<?php

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn()) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]");
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

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
	if ($seconds < 0) {
		$temp = 0 - $seconds;
		$sign = '-';
	}
	else {
		$sign = '';
	}
	$hour = (int) ($temp / (60*60));

	if ($hour < 10)
		$hour = '0'. $hour;

	$temp -= (60*60)*$hour;
	$minutes = (int) ($temp / 60);

	if ($minutes < 10)
		$minutes = '0'. $minutes;

	$temp -= (60*$minutes);
	$sec = $temp;

	if ($sec > 30)
		$minutes += 1;

//	if ($sec < 10)
//		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
//						// be a space after the . operator for this to work.
//	return "$hour:$minutes:$sec";
	return "$sign$hour:$minutes";
}

?>
<html>
<head>
<title>Timesheet Report: User Hours</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="report_hours.php" method="get">
<input type="hidden" name="month" value="<? echo $month; ?>">
<input type="hidden" name="year" value="<? echo $year; ?>">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

			<table width="100%" border="0">
				<tr>
					<td align="left" nowrap width="200">
						<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
							<tr>
								<td align="right" width="0" class="outer_table_heading">User:</td>
								<td align="left" width="100%">
									<? user_select_droplist($uid, false); ?>
								</td>
							</tr>
						</table>
					</td>
					<td align="center" nowrap class="outer_table_heading">
			  			<?	echo date('Y',$time); ?>
					</td>
					<td align="right" nowrap>
						<?
						printPrevNext($next_week, $prev_week, $next_month, $prev_month, "uid=$uid", 'yearly');
						?>
					</td>
				</tr>
			</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
				<tr>
					<td class="calendar_cell_disabled_right">&nbsp</td>
<?
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$currentMonDate = mktime(0,0,0,$currentMonth,1,$year);
		$currentMonStr = strftime("%b", $currentMonDate);
		print "<td align=\"center\" class=\"calendar_cell_disabled_right\"><b>$currentMonStr</b></td>";
	}
?>
					<td class="calendar_cell_disabled_right">&nbsp</td>
				</tr>
				<tr><td class="calendar_cell_middle"><b>Hours in month</b></td>
<?
	$hours = array( "total" => array("working_hours" => 0));
	// Working hours
	$total = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$last_day = get_last_day($currentMonth, $year);
		$hours[$currentMonth]["working_hours"] = 0;
		$others = count_absences_in_month($currentMonth, $year, '', 'Other'); //Other absences without user are general exceptions
		$hours[$currentMonth]["working_hours"] -= $others;
		$public = count_absences_in_month($currentMonth, $year, '', 'Public'); //Public holidays are without user
		$hours[$currentMonth]["working_hours"] -= $public;
		for ($currentDay=1;$currentDay<=$last_day;$currentDay++) {
			$currentDate = mktime(0,0,0,$currentMonth,$currentDay,$year);
			if ((date('w', $currentDate) != 6)&&(date('w', $currentDate) != 0)) {
				$hours[$currentMonth]["working_hours"] += 8;
			}
		}
		$hourstr = format_seconds($hours[$currentMonth]["working_hours"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["working_hours"] += $hours[$currentMonth]["working_hours"];
	}
	$totalstr = format_seconds($hours["total"]["working_hours"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Worked hours
	print "<tr><td class=\"calendar_cell_middle\"><b>Total attendance</b></td>";
	$hours["total"]["attendance"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		list($num, $qh) = get_month_times($currentMonth, $year, $uid);
		$last_day = get_last_day($currentMonth, $year);
		$total_sec = 0;
		for ($currentEntry=0;$currentEntry<$num;$currentEntry++) {
			$data = DbResult($qh, $currentEntry);
			//Due to a bug in mysql with converting to unix timestamp from the string,
			//we are going to use php's strtotime to make the timestamp from the string.
			//the problem has something to do with timezones.
			$data["start_time"] = strtotime($data["start_time_str"]);
			$data["end_time"] = strtotime($data["end_time_str"]);

			if ($data["start_time"] < mktime(0,0,0,$currentMonth,1,$year))
				$total_sec += $data["end_time"] - mktime(0,0,0,$currentMonth,1,$year);
			elseif ($data["end_time"] > mktime(23,59,59,$currentMonth,$last_day,$year))
				$total_sec += mktime(23,59,59,$currentMonth,$last_day,$year) - $data["start_time"];
			else
				$total_sec += $data["end_time"] - $data["start_time"];

		}
		$hourstr = format_seconds($total_sec);
		$hours[$currentMonth]["attendance"] = $total_sec;
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["attendance"] += $total_sec;
	}
	$totalstr = format_seconds($hours["total"]["attendance"]);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Weekend worked hours
	print "<tr><td class=\"calendar_cell_middle\"><b>Weekend attendance</b></td>";
	$hours["total"]["weekend"] = 0;

	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		list($num, $qh) = get_month_times($currentMonth, $year, $uid);
		$last_day = get_last_day($currentMonth, $year);
		list($qhol, $holnum) = get_absences($currentMonth, $year);
		$ihol = 0;
		if ($holnum>$ihol)
			$holdata = dbResult($qhol, $ihol);
		$total_sec = 0;
		$currentDay = 1;
		for ($currentEntry=0;$currentEntry<$num;$currentEntry++) {
			$data = DbResult($qh, $currentEntry);
			//Due to a bug in mysql with converting to unix timestamp from the string,
			//we are going to use php's strtotime to make the timestamp from the string.
			//the problem has something to do with timezones.
			$data["start_time"] = strtotime($data["start_time_str"]);
			$data["end_time"] = strtotime($data["end_time_str"]);

			if ($data["start_time"] < mktime(0,0,0,$currentMonth,1,$year))
				$data["start_time"] = mktime(0,0,0,$currentMonth,1,$year);
			if ($data["end_time"] > mktime(23,59,59,$currentMonth,$last_day,$year))
				$data["end_time"] = mktime(23,59,59,$currentMonth,$last_day,$year);

			while ($data["start_time"] > mktime(23,59,59,$currentMonth,$currentDay,$year))
				$currentDay++;
			if ($holnum > $ihol) {
				while (($holdata["day_of_month"] < $currentDay)&&($holnum > $ihol)) {
					$ihol++;
					$holdata = dbResult($qhol, $ihol);
				}
			}

			//public holidays
			if ($holnum > $ihol) {
				if (($holdata["day_of_month"]==$currentDay)&&($holdata["AM_PM"]=='day'))
					$total_sec += $data["end_time"] - $data["start_time"];
			}
			//weekend
			if ((date('w', mktime(0,0,0,$currentMonth,$currentDay,$year)) == 6)
			   ||(date('w', mktime(0,0,0,$currentMonth,$currentDay,$year)) == 0))
				$total_sec += $data["end_time"] - $data["start_time"];

		}
		$hourstr = format_seconds($total_sec);
		$hours[$currentMonth]["weekend"] = $total_sec;
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["weekend"] += $total_sec;
	}
	$totalstr = format_seconds($hours["total"]["weekend"]);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Compensation taken
	print "<tr><td class=\"calendar_cell_middle\"><b>Compensation taken</b></td>";
	$hours["total"]["compensation"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid, 'Compensation');
		$hours[$currentMonth]["compensation"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["compensation"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["compensation"] += $hours[$currentMonth]["compensation"];
	}
	$totalstr = format_seconds($hours["total"]["compensation"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Training taken
	print "<tr><td class=\"calendar_cell_middle\"><b>Training taken</b></td>";
	$hours["total"]["training"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid, 'Training');
		$hours[$currentMonth]["training"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["training"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["training"] += $hours[$currentMonth]["training"];
	}
	$totalstr = format_seconds($hours["total"]["training"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Sick time
	print "<tr><td class=\"calendar_cell_middle\"><b>Sick</b></td>";
	$hours["total"]["sick"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid, 'Sick');
		$hours[$currentMonth]["sick"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["sick"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["sick"] += $hours[$currentMonth]["sick"];
	}
	$totalstr = format_seconds($hours["total"]["sick"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Military Service
	print "<tr><td class=\"calendar_cell_middle\"><b>Military Service</b></td>";
	$hours["total"]["military"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid, 'Military');
		$hours[$currentMonth]["military"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["military"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["military"] += $hours[$currentMonth]["military"];
	}
	$totalstr = format_seconds($hours["total"]["military"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Other absences
	print "<tr><td class=\"calendar_cell_middle\"><b>Other Absences</b></td>";
	$hours["total"]["other"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid, 'Other');
		$hours[$currentMonth]["other"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["other"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["other"] += $hours[$currentMonth]["other"];
	}
	$totalstr = format_seconds($hours["total"]["other"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Holiday taken
	print "<tr><td class=\"calendar_cell_middle\"><b>Holiday taken</b></td>";
	$hours["total"]["holiday"] = 0;
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = count_absences_in_month($currentMonth, $year, $uid);
		$hours[$currentMonth]["holiday"] = $holidays;
		$hourstr = format_seconds($hours[$currentMonth]["holiday"]*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$hourstr</td>";
		$hours["total"]["holiday"] += $hours[$currentMonth]["holiday"];
	}
	$totalstr = format_seconds($hours["total"]["holiday"]*60*60);
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$totalstr</b></td>";
	print "</tr>";

	// Holiday remaining
	print "<tr><td class=\"calendar_cell_middle\"><b>Holiday remaining</b></td>";
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$holidays = get_balance(1, $currentMonth, $year, $uid);
		$holiday_remaining = format_seconds(($holidays - $hours[$currentMonth]["holiday"])*60*60);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$holiday_remaining</td>";
	}
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$holiday_remaining</b></td>";
	print "</tr>";

	// glidetime remaining
	print "<tr><td class=\"calendar_cell_middle\"><b>Glidetime</b></td>";
	$remaining = get_balance(1, 1, $year, $uid, 'glidetime');
	for ($currentMonth=1;$currentMonth<=12;$currentMonth++) {
		$remaining = $hours[$currentMonth]["attendance"]
				- $hours[$currentMonth]["working_hours"]*60*60
				+ $hours[$currentMonth]["holiday"]*60*60
				+ $hours[$currentMonth]["sick"]*60*60
				+ $hours[$currentMonth]["training"]*60*60
				+ $hours[$currentMonth]["military"]*60*60
				+ $hours[$currentMonth]["other"]*60*60
				- $hours[$currentMonth]["compensation"]
				+ $remaining;
		$glidetime = format_seconds($remaining);
		print "<td align=\"right\" class=\"calendar_cell_middle\">$glidetime</td>";
	}
	print "<td align=\"right\" class=\"calendar_cell_disabled_right\"><b>$glidetime</b></td>";
	print "</tr>";

?>
				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>
<?
include ("footer.inc");
?>
</BODY>
</HTML>

