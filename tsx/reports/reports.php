<h1><?php echo JText::_('REPORTS'); ?></h1>
<?php
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('REPORTS')."</title>");

if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;

//load local vars from request/post/get
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:gbl::getContextUser();

$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$ymdStr = "&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()."";

require_once("include/tsx/navcal/navcal.class.php");
$nav = new NavCal();
$nav->navCalMonthly($todayDate,false);
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" class="outer_table_heading">
							<?php echo JText::_('CURRENT_DATE').': '?><span style="color:#00066F;"><?php echo utf8_encode(strftime(JText::_('DFMT_WKDY_MONTH_DAY_YEAR'), $todayDate)); ?></span>
						</td>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading"><?php echo JText::_('ACTIVITY_REPORTS'); ?></td>
						<td class="inner_table_column_heading"><?php echo JText::_('PERIOD'); ?></td>
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
						<td class="calendar_cell_middle"><?php echo JText::_('USER_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_user?<?php print $ymdStr; ?>&amp;&mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_user?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle"><?php echo JText::_('USER_SUMMARY'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_user_summ?<?php print $ymdStr; ?>"><?php echo JText::_('HALF_MONTH'); ?></a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle"><?php echo JText::_('PROJECT_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle"><?php echo JText::_('CLIENT_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle"><?php echo JText::_('CLIENT_USER_GRID_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_grid_client_user?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
<?php if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) { ?>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle"><?php echo JText::_('ALL_USERS_ALL_PROJECTS_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_all?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
<?php } 
	if (Site::getAuthenticationManager()->hasAccess('aclAbsences')) { ?>
					<tr class="inner_table_head">
						<td class="inner_table_column_heading"><?php echo JText::_('ATTENDANCE_REPORTS'); ?></td>
						<td class="inner_table_column_heading"><?php echo JText::_('PERIOD'); ?></td>
					</tr>
					<?php echo_TR_Class(1);?>
						<td class="calendar_cell_middle"><?php echo JText::_('ABSENCE_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_absences?<?php print $ymdStr; ?>"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
					<?php echo_TR_Class();?>
						<td class="calendar_cell_middle">Yearly User Report</td>
						<td class="calendar_cell_right">
							<a href="report_hours?<?php print $ymdStr; ?>"><?php echo JText::_('YEAR'); ?></a>
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
