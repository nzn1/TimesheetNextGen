<h1><?php echo JText::_('REPORTS'); ?></h1>
<?php
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('REPORTS')."</title>");
PageElements::setTheme('txsheet2');
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;

//load local vars from request/post/get
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid']:gbl::getContextUser();

$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$ymdStr = "&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()."";
$startDate = strtotime(date("d M Y",gbl::getContextTimestamp()));
$prevDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " -1 month");
$nextDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " +1 month");

require_once("include/tsx/navcal/navcal.class.php");
$nav = new NavCal();
$nav->navCalMonthly($todayDate,false);
?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot()."/js/datetimepicker_css.js";?> "></script>

<table width="100%" border="0" cellspacing="0" cellpadding="0"  class="simpleTable">
	<tr>
		<td align="left" class="outer_table_heading">
			<?php echo JText::_('CURRENT_DATE').': '?><span style="color:#00066F;"><?php echo utf8_encode(strftime(JText::_('DFMT_WKDY_MONTH_DAY_YEAR'), $todayDate)); ?></span>
		</td>
		<td>
		<?php Common::printDateSelector("weekly", $startDate, $prevDate, $nextDate); ?>
		<td>
	</tr>
</table>
<div id="monthly">
	<table class="monthTable">
		<thead>
			<tr class="table_head">
			<th><?php echo JText::_('ACTIVITY_REPORTS'); ?></th>
			<th><?php echo JText::_('PERIOD'); ?></th>
		</tr>
		</thead>
		<tbody>


					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('USER_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_user?<?php print $ymdStr; ?>&amp;&mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_user?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('USER_SUMMARY'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_user_summ?<?php print $ymdStr; ?>"><?php echo JText::_('HALF_MONTH'); ?></a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('PROJECT_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_project?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('CLIENT_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a> /
							<a href="report_client?<?php print $ymdStr; ?>&amp;mode=weekly"><?php echo JText::_('WEEK'); ?></a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('CLIENT_USER_GRID_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_grid_client_user?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
<?php if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) { ?>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('ALL_USERS_ALL_PROJECTS_REPORT'); ?></td>
						<td class="calendar_cell_right">
							<a href="report_all?<?php print $ymdStr; ?>&amp;mode=monthly"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
<?php } 
	if (Site::getAuthenticationManager()->hasAccess('aclAbsences')) { ?>
		</tbody>
		</table><br>
		<table class="monthTable">
		<thead>
		<tr class="table_head">
			<th><?php echo JText::_('ATTENDANCE_REPORTS'); ?></th>
			<th><?php echo JText::_('PERIOD'); ?></th>
		</tr>

		</thead>
		<tbody>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('ABSENCE_REPORT'). "&nbsp;"; ?></td>
						<td class="calendar_cell_right">
							<a href="report_absences?<?php print $ymdStr; ?>"><?php echo JText::_('MONTH'); ?></a>
						</td>
					</tr>
					<tr>
						<td class="calendar_cell_middle"><?php echo JText::_('YEARLYUSERREPT'). "&nbsp;"; ?></td>
						<td class="calendar_cell_right">
							<a href="report_hours?<?php print $ymdStr; ?>"><?php echo JText::_('YEAR'); ?></a>
						</td>
					</tr>
<?php } ?>

	</table>

