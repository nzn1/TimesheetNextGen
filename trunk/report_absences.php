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

//run the query
list($qh,$num) = get_absences($month, $year, $uid);
$ihol = 0;

//define working variables
$last_day = get_last_day($month, $year);
$AM_text = "&nbsp";
$PM_text = "&nbsp";
$public_hol = 'N';

?>
<html>
<head>
<title>Timesheet Report: Monthly Absences</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="report_absences.php" method="get">
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
			  			<?	echo date('F Y',$time); ?>
					</td>
					<td align="right" nowrap>
						<?
						printPrevNext($next_week, $prev_week, $next_month, $prev_month, "uid=$uid", 'monthly');
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
					<td align="center" class="calendar_cell_disabled_right"><b>Day</b></td>
					<td align="center" class="calendar_cell_disabled_right"><b>Morning</b></td>
					<td align="center" class="calendar_cell_disabled_right"><b>Afternoon</b></td>
				</tr>
<?
	for ($i=1;$i<=$last_day;$i++) {
		$day = mktime(0,0,0,$month,$i,$year);
		$dow = strftime("%a", $day);
		$daystyle = "calendar_cell_middle";
		if ((date('w', $day) == 6)||(date('w', $day) == 0)) {
			$daystyle = "calendar_cell_holiday_middle";
		}
		$AM_text = "&nbsp";
		$PM_text = "&nbsp";
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
					$AM_text = $absdata['type'].": ".urldecode($absdata['subject']);
					$AM_type = $absdata['type'];
					$AMstyle = "calendar_cell_holiday_middle";
					$ihol++;
				}
				else if ($absdata['AM_PM']=='day')
				{
					$AM_text = $absdata['type'].": ".urldecode($absdata['subject']);
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
					$PM_text = $absdata['type'].": ".urldecode($absdata['subject']);
					$PM_type = $absdata['type'];
					$PMstyle = "calendar_cell_holiday_middle";
					$ihol++;
				}
				else if ($absdata['AM_PM']=='day')
				{
					$PM_text = $absdata['type'].": ".urldecode($absdata['subject']);
					$PM_type = $absdata['type'];
					$PMstyle = "calendar_cell_holiday_middle";
					// now increment
					$ihol++;
				}
			}
		}

		if (($AM_type=='Public')||($PM_type=='Public')) {
			$daystyle = "calendar_cell_holiday_middle";
		}
?>
			<td align="center" class="<? echo $daystyle; ?>"><? echo $dow; ?></td>
			<td align="center" class="<? echo $daystyle; ?>"><? echo $i; ?></td>
			<td align="left" class="<? echo $AMstyle; ?>"><? echo $AM_text; ?></td>
			<td align="left" class="<? echo $PMstyle; ?>"><? echo $PM_text; ?></td>
		</tr>
<?
	}

// Calculate the previous month.
$last_month = $month - 1;
$last_year = $year;
if (!checkdate($last_month, 1, $last_year)) {
	$last_month += 12;
	$last_year --;
}
$holidays_taken = count_absences_in_month($month, $year, $uid);
$holiday_allowance = get_balance(get_last_day($last_month, $last_year), $last_month, $last_year, $uid);
$holiday_remaining = $holiday_allowance - $holidays_taken;
$glidetime_allowance = get_balance(get_last_day($last_month, $last_year), $last_month, $last_year, $uid, 'glidetime');
$glidetime_paid = get_allowance(get_last_day($month, $year), $month, $year, $uid, 'glidetime') 
					- get_allowance(get_last_day($last_month, $last_year), $last_month, $last_year, $uid, 'glidetime');
$compensation_taken = count_absences_in_month($month, $year, $uid, 'Compensation');
$glidetime_remaining = get_balance(get_last_day($month, $year), $month, $year, $uid, 'glidetime') - $compensation_taken - $glidetime_paid;
?>
				<tr>
					<td colspan=3><br><br><b>Comments:</b><br><br><br></td>
					<td><br><br><b>Employee:</b><br>Signature/Date<br><br></td>
				</tr>
				<tr>
					<td colspan=3>
						Holiday Allowance: <? echo $holiday_allowance; ?><br>
						Holiday in Month: <? echo $holidays_taken; ?><br>
						Holiday Remaining: <? echo $holiday_remaining; ?><br><br>
					</td>
					<td><b>Manager:</b><br>Signature/Date<br><br></td>
				</tr>
				<tr>
					<td colspan=3>
						Glidetime Allowance: <? printf("%2.2f", $glidetime_allowance); ?><br>
						Paid-out in Month: <? echo $glidetime_paid; ?><br>
						Compensation Taken: <? echo $compensation_taken; ?><br>
						Glidetime Remaining: <? printf("%2.2f", $glidetime_remaining); ?><br>
					</td>
					<td><b>Bookkeeping:</b><br>Signature/Date<br><br></td>
				</tr>
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
</BODY>
</HTML>

