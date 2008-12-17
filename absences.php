<?php

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclAbsences')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclAbsences'));
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

if (!$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR))
	$disableChangeUser = 'true';
else
	$disableChangeUser = 'false';

//load local vars from superglobals
$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: (int)date("Y");
$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: (int)date("m");
$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: (int)date("d");

if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = $contextUser;

$action = 0;

//a useful constant
define("A_DAY", 24 * 60 * 60);

//get the passed date (context date)
$todayDate = mktime(0, 0, 0, $month, $day, $year);
$todayYear = date("Y", $todayDate);
$todayMonth = date("n", $todayDate);
$todayDay = date("j", $todayDate);
$dateValues = getdate($todayDate);
$todayDayOfWeek = $dateValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = getWeekStartDay();

//work out the start date by minusing enough seconds to make it the start day of week
$startDate = mktime(0,0,0, $month, 1, $year);
$startYear = date("Y", $startDate);
$startMonth = date("n", $startDate);
$startDay = date("j", $startDate);

// Calculate the previous month.
$last_month = $month - 1;
$last_year = $year;
if (!checkdate($last_month, 1, $last_year)) {
	$last_month += 12;
	$last_year --;
}

//calculate the next month
$next_month = $month+1;
$next_year = $year;
if (!checkdate($next_month, 1, $next_year)) {
	$next_year++;
	$next_month -= 12;
}

//define the command menu
include("timesheet_menu.inc");

//run the query
list($qh,$num) = get_absences($month, $year, $uid);
$ihol = 0;

//define working variables
$last_day = get_last_day($month, $year);

?>
<html>
<head>
<title>Timesheet Absence Entry</title>
<? include ("header.inc"); ?>
<script language="Javascript">

	function onSubmit() {
		//set the action
		document.getElementById('action').value = 1;
		document.theForm.submit();
	}


</script>
</head>
<?
echo "<body width=\"100%\" height=\"100%\"";
include ("body.inc");
echo ">\n";

include ("banner.inc");
?>
<form name="theForm" id="theForm" action="absences_action.php" method="post">
<input type="hidden" name="month" value=<? echo $month; ?>>
<input type="hidden" name="day" value=<? echo $day; ?>>
<input type="hidden" name="year" value=<? echo $year; ?>>
<input type="hidden" name="last_day" value=<? echo $last_day; ?>>
<input type="hidden" name="action" id="action" value=<? echo $action; ?>>
<input type="hidden" name="origin" value="<? echo $_SERVER["PHP_SELF"]; ?>">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap>
							<table width="60%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td>User:</td>
												<td width="50%"><? user_select_droplist($uid,$disableChangeUser); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading">
							<? echo date('F Y',mktime(0,0,0,$month, 1, $year)); ?>
						</td>
						<td align="right" nowrap>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?client_id=<? echo $client_id; ?>&proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $last_year ?>&month=<? echo $last_month ?>&day=<? echo $todayDay; ?>&uid=<? echo $uid; ?>" class="outer_table_action">Prev</a>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?client_id=<? echo $client_id; ?>&proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $next_year ?>&month=<? echo $next_month ?>&day=<? echo $todayDay; ?>&uid=<? echo $uid; ?>" class="outer_table_action">Next</a>
						</td>
						<td align="right">
							<input type="button" value="Save Changes" name="save" id="save" onClick="onSubmit();">
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
					<td align="center" class="calendar_cell_disabled_right"><b>Day</b></td>
					<td align="center" class="calendar_cell_disabled_right" colspan=2 ><b>Morning</b></td>
					<td align="center" class="calendar_cell_disabled_right" colspan=2 ><b>Afternoon</b></td>
				</tr>
		<tr>
<?
	for ($i=1;$i<=$last_day;$i++) {
		$day = mktime(0,0,0,$month,$i,$year);
		$dow = strftime("%a", $day);
		$daystyle = "calendar_cell_middle";
		if ((date('w', $day) == 6)||(date('w', $day) == 0)) {
			$daystyle = "calendar_cell_holiday_middle";
		}
		$AM_text = "";
		$PM_text = "";
		$AM_type = '';
		$PM_type = '';
		$AMstyle = $daystyle;
		$PMstyle = $daystyle;

		// first try for AM absences
		if ($ihol<$num) {
			$absdata = dbResult($qh,$ihol);
			if ($i==$absdata['day_of_month']) {
				if ($absdata['AM_PM']=='AM')
				{
					$AM_text = urldecode($absdata['subject']);
					$AM_type = $absdata['type'];
					$AMstyle = "calendar_cell_holiday_middle";
					$ihol++;
				}
				else if ($absdata['AM_PM']=='day')
				{
					$AM_text = urldecode($absdata['subject']);
					$AM_type = $absdata['type'];
					$AMstyle = "calendar_cell_holiday_middle";
					// don't increment
				}
			}
		}
		// second try for PM absences
		if ($ihol<$num) {
			$absdata = dbResult($qh,$ihol);
			if ($i==$absdata['day_of_month']) {
				if ($absdata['AM_PM']=='PM')
				{
					$PM_text = urldecode($absdata['subject']);
					$PM_type = $absdata['type'];
					$PMstyle = "calendar_cell_holiday_middle";
					$ihol++;
				}
				else if ($absdata['AM_PM']=='day')
				{
					$PM_text = urldecode($absdata['subject']);
					$PM_type = $absdata['type'];
					$PMstyle = "calendar_cell_holiday_middle";
					// now increment
					$ihol++;
				}
			}
		}

		if (($AM_type=='Public')&&($PM_type=='Public')) {
			$daystyle = "calendar_cell_holiday_middle";
		}
		if ($daystyle == "calendar_cell_holiday_middle")
			$disabled = 'true';
		else
			$disabled = 'false';

?>
			<td align="center" class="<? echo $daystyle; ?>"><? echo $dow; ?></td>
			<td align="center" class="<? echo $daystyle; ?>"><? echo $i; ?></td>
			<td align="right" class="<? echo $AMstyle; ?>"><? absence_select_droplist($AM_type, $disabled, "AMtype".$i); ?></td>
			<td align="left" class="<? echo $AMstyle; ?>"><input type="text" id="<? echo "AMtext",$i; ?>" name="<? echo "AMtext",$i; ?>" class="<? echo $AMstyle; ?>" value="<? echo $AM_text; ?>" <? if ($disabled=='true') echo "readonly"; ?>></td>
			<td align="right" class="<? echo $PMstyle; ?>"><? absence_select_droplist($PM_type, $disabled, "PMtype".$i); ?></td>
			<td align="left" class="<? echo $PMstyle; ?>"><input type="text" id="<? echo $i,"_PMtext"; ?>" name="<? echo "PMtext",$i; ?>" class="<? echo $PMstyle; ?>" value="<? echo $PM_text; ?>" <? if ($disabled=='true') echo "readonly"; ?>></td>
		</tr>
<?
	}
?>
						</tr>
					</td>
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
</body>
</html>
