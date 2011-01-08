<?php

if(!class_exists('Site'))die('Restricted Access');

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclReports')) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&amp;clearanceRequired=" . Common::get_acl_level('aclReports'));
	exit;
}

//load local vars from superglobals
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:gbl::getContextUser();

$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$ymdStr = "&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()."";


Common::setMotd(0);

//include ("navcalnew/navcalendars.inc");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							Reports
						</td>
						<td align="left" nowrap class="outer_table_heading">
							<?php echo date('F d, Y',$todayDate) ?>
						</td>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Report Description</td>
						<td class="inner_table_column_heading">Actions</td>
					</tr>

<?php
function echo_TR_Class($val='') {
//if you want "highlighting" on the first row, make this zero instead of one
static $row = 1;

if(!($val === ''))
	$row=$val;

	if($row%3)
		echo "<tr>";
	else
		echo "<tr class=\"diff\">";

	$row++;
}
?>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">User report</td>
						<td class="calendar_cell_right">
							<a href="report_user?<?php print $ymdStr; ?>&amp;&mode=monthly">Generate monthly</a> /
							<a href="report_user?<?php print $ymdStr; ?>&amp;mode=weekly">Generate weekly</a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">User summary</td>
						<td class="calendar_cell_right">
							<a href="report_user_summ?<?php print $ymdStr; ?>">Bi-monthly</a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">Project report</td>
						<td class="calendar_cell_right">
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=monthly">Generate monthly</a> /
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=weekly">Generate weekly</a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">Client report</td>
						<td class="calendar_cell_right">
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=monthly">Generate monthly</a> /
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=weekly">Generate weekly</a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">Client / User - grid report</td>
						<td class="calendar_cell_right">
							<a href="report_grid_client_user?<?php print $ymdStr; ?>&amp;mode=monthly">Generate monthly</a>
						</td>
					</tr>
<?php if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) { ?>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">All users & All projects report</td>
						<td class="calendar_cell_right">
							<a href="report_all?<?php print $ymdStr; ?>&amp;mode=monthly">Generate monthly</a>
						</td>
					</tr>
<?php } ?>
<?php if (Site::getAuthenticationManager()->hasAccess('aclAbsences')) { ?>
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Attendance Reports</td>
						<td class="inner_table_column_heading">Actions</td>
					</tr>
					<?php echo_TR_Class(1);?>
						<td class="calendar_cell_middle">Absence Report</td>
						<td class="calendar_cell_right">
							<a href="report_absences?<?php print $ymdStr; ?>">Generate monthly</a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">Yearly User Report</td>
						<td class="calendar_cell_right">
							<a href="report_hours?<?php print $ymdStr; ?>">Generate yearly</a>
						</td>
					</tr>
<?php } ?>
					</table>
			</td>
		</tr>
	</table>

		</td>
	</tr>
</table>
