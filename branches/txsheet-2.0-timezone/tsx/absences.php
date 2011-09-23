<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ABSENCE_ENTRY')." | ".gbl::getContextUser()."</title>");


$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");


if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_MANAGER))
	$canChangeUser = true;
else
	$canChangeUser = false;

if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = gbl::getContextUser();

$mode = gbl::getMode();
$action = 0;

//run the query
$day = gbl::getDay();
$month = gbl::getMonth();
$year = gbl::getYear();
list($qh,$num) = Common::get_absences($month, $year, $uid);
$ihol = 0;

//define working variables
$last_day = Common::get_last_day($month, $year);
$startDate = mktime(0,0,0, $month, 1, $year);
$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 month");
$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 month");	
ob_start();
//require_once("include/language/datetimepicker_lang.inc");

?>


<script type="text/javascript">

	function onSubmit() {
		//set the action
		document.getElementById('action').value = 1;
		document.theForm.submit();
	}
	function CallBack_WithNewDateSelected(strDate) {
		document.theForm.submit();
	}

</script>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>

<?php
PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
?>



<form name="theForm" id="theForm" action="<?php echo Config::getRelativeRoot();?>/absences_action" method="post">
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="hidden" name="day" value="<?php echo $day; ?>" />
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="last_day" value="<?php echo $last_day; ?>" />
<input type="hidden" name="action" id="action" value="<?php echo $action; ?>" />
<input type="hidden" name="origin" value="<?php echo Rewrite::getShortUri(); ?>" />

<h1><?php echo JText::_('ABSENCE_ENTRY'); ?></h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!--	<?php print "$day $month $year"; ?> -->
		<?php if($canChangeUser) : ?>
			<td align="left" width="38%" class="outer_table_heading"><?php echo JText::_('USER'); ?>: &nbsp; <?php Common::user_select_droplist($uid); ?></td>
		<?php else : ?>
			<td width="38%"><?php echo JText::_('USER'); ?>: &nbsp; <?php echo "<b>$uid</b>"; ?></td>
		<?php endif; ?>
		<td align="center" class="outer_table_heading">
			<?php echo utf8_encode(strftime(JText::_('DFMT_MONTH_YEAR'), mktime(0,0,0,$month, 1, $year))); ?>
		</td>
		<td  class="outer_table_heading">
			<?php Common::printDateSelector("daily", $startDate, $prevDate, $nextDate); ?></td>
		
		<td align="right">
			<input type="button" value="<?php echo JText::_('SAVE_CHANGES')?>" name="save" id="save" onclick="onSubmit();" />
		</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
	</tr>
</table>

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
		<tr>
<!--		<td class="calendar_cell_disabled_right">&nbsp</td> -->
			<td align="center" colspan="2" rowspan="2" class="calendar_cell_disabled_right"><b><?php echo JText::_('DAY'); ?></b></td>
			<td align="center" class="calendar_cell_disabled_right" colspan="2" ><b><?php echo JText::_('MORNING'); ?></b></td>
			<td align="center" class="calendar_cell_disabled_right" colspan="2" ><b><?php echo JText::_('AFTERNOON'); ?></b></td>
		</tr>
		<tr>
			<td align="center" class="calendar_cell_disabled_right" width="16%"><b><?php echo JText::_('TYPE'); ?></b></td>
			<td align="center" class="calendar_cell_disabled_right" width="34%"><b><?php echo JText::_('DETAIL'); ?></b></td>
			<td align="center" class="calendar_cell_disabled_right" width="16%"><b><?php echo JText::_('TYPE'); ?></b></td>
			<td align="center" class="calendar_cell_disabled_right" width="34%"><b><?php echo JText::_('DETAIL'); ?></b></td>
		</tr>

<?php
	for ($i=1;$i<=$last_day;$i++) {
		$day = mktime(0,0,0,$month,$i,$year);
		$dow = strftime("%A", $day);
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
    <tr>
			<td align="center" class="<?php echo $daystyle; ?>"><?php echo $dow; ?></td>
			<td align="center" class="<?php echo $daystyle; ?>"><?php echo $i; ?></td>
			<td align="right" class="<?php echo $AMstyle; ?>"><?php Common::absence_select_droplist($AM_type, $disabled, "AMtype".$i); ?></td>
			<td align="left" class="<?php echo $AMstyle; ?>"><input type="text" id="<?php echo "AMtext",$i; ?>" name="<?php echo "AMtext",$i; ?>" class="<?php echo $AMstyle; ?>" value="<?php echo $AM_text; ?>" style="width: 100%;" <?php if ($disabled=='true') echo "disabled=\"disabled\""; ?> /></td>
			<td align="right" class="<?php echo $PMstyle; ?>"><?php Common::absence_select_droplist($PM_type, $disabled, "PMtype".$i); ?></td>
			<td align="left" class="<?php echo $PMstyle; ?>"><input type="text" id="<?php echo "PMtext",$i; ?>" name="<?php echo "PMtext",$i; ?>" class="<?php echo $PMstyle; ?>" value="<?php echo $PM_text; ?>" style="width: 100%;" <?php if ($disabled=='true') echo "disabled=\"disabled\""; ?> /></td>
		</tr>
<?php
	}
?>

	</table>

</form>