<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
        errorPage("Could not determine the logged in user");


//load local vars from request/post/get
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = gbl::getContextUser();

if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

//get the context date
$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$todayDateValues = getdate($todayDate);
$year = $todayDateValues["year"];
$month = $todayDateValues["mon"];
$day = $todayDateValues["mday"];
$todayDate = mktime(0, 0, 0,$month, $day, $year);
$dateValues = getdate($todayDate);
$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];

//run the query
list($qh,$num) = Common::get_absences($month, $year, $uid);
$ihol = 0;

//define working variables
$last_day = Common::get_last_day($month, $year);
$AM_text = "&nbsp";
$PM_text = "&nbsp";
$public_hol = 'N';

function make_index($data,$order) {
	if($order == "date") {
		$index=$data["start_stamp"] . sprintf("-%05d",$data["proj_id"]) . 
			sprintf("-%05d",$data["task_id"]);
	} else {
		$index=sprintf("%05d",$data["proj_id"]) .  sprintf("-%05d-",$data["task_id"]) .
			$data["start_stamp"];
	}
	return $index;
}

$Location= Rewrite::getShortUri()."?uid=$uid$ymdStr";
gbl::setPost("uid=$uid");

?>

<script type="text/javascript">
<!--
function popupPrintWindow() {
	window.open("<?php echo "$Location&print=yes"; ?>", "PopupPrintWindow", "location=0,status=no,menubar=no,resizable=1,width=800,height=450");
}
//-->
</script>


<title>Report: Monthly Absences</title>

</head>
<?php
	if($print) {
		echo "<body width=\"100%\" height=\"100%\"";
		
		echo "onLoad=window.print();";
		echo ">\n";
	} else {
		echo "<body ";
		
		echo ">\n";
		
		$motd = 0;  //don't want the motd printed
		require_once("include/tsx/navcal/navcal.class.php");
	  $nav = new NavCal();
    $nav->navCalMonthly();
	}


?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">
	function CallBack_WithNewDateSelected(strDate) 
	{
		document.subabsences.submit();
	}
</script>
<form action="<?php echo Config::getRelativeRoot(); ?>/reports/report_absences" method="post" name="subabsences" >
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="day" value="<?php echo $day; ?>" />


<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!--  td align="left" width="200" -->
				<!--  table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2" -->
			<td align="right" width="0" class="outer_table_heading">User:</td>
			<td align="left" > <?php Common::user_select_droplist($uid, false); ?>
			</td>
			<td align="center" class="outer_table_heading">
			<input id="date1" name="date1" type="hidden" value="<?php echo date('d-m-Y', $todayDate); ?>" />
			&nbsp;&nbsp;&nbsp;
			<?php
				echo JText::_('SELECT_OTHER_MONTH').": ";
			?>
				<img style="cursor: pointer;" onclick="javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow', 'false', '24', 'false', 'MONTH')" alt="" src="<?php echo Config::getRelativeRoot();?>/images/cal.gif">
				</td><td><td>	&nbsp;&nbsp;&nbsp;</td><td>
			<?php echo date('F Y',$todayDate); ?>
			</td>
			<?php if (!$print): 
				//<td  align="center" >
				//<a href="#" onclick="javascript:esporta('user')" ><img src="images/export_data.gif" name="esporta_dati" border="0" alt="" /></a>
				//</td>
			?>
			<td  align="center" >
			<?php 
				print "<button onclick=\"popupPrintWindow()\">Print Report</button></td>\n"; 
			?>
			</td>
			<?php endif; ?>
			<td align="right">
			</td>
	</tr>
	</table>

	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table_body">
		<tr class="table_body">
			<td class="calendar_cell_disabled_right">&nbsp;</td>
			<td align="center" class="calendar_cell_disabled_right"><b>Day</b></td>
			<td align="center" class="calendar_cell_disabled_right"><b>Morning</b></td>
			<td align="center" class="calendar_cell_disabled_right"><b>Afternoon</b></td>
		</tr>
<?php
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
			<td align="center" class="<?php echo $daystyle; ?>"><?php echo $dow; ?></td>
			<td align="center" class="<?php echo $daystyle; ?>"><?php echo $i; ?></td>
			<td align="left" class="<?php echo $AMstyle; ?>"><?php echo $AM_text; ?></td>
			<td align="left" class="<?php echo $PMstyle; ?>"><?php echo $PM_text; ?></td>
		</tr>
<?php
	}

// Calculate the previous month.
$last_month = $month - 1;
$last_year = $year;
if (!checkdate($last_month, 1, $last_year)) {
	$last_month += 12;
	$last_year --;
}
$holidays_taken = Common::count_absences_in_month($month, $year, $uid);
$holiday_remaining = Common::get_balance(Common::get_last_day($month, $year), $month, $year, $uid);
$holiday_allowance = $holiday_remaining + $holidays_taken;
$glidetime_allowance = Common::get_balance(Common::get_last_day($last_month, $last_year), $last_month, $last_year, $uid, 'glidetime');
$glidetime_paid = Common::get_allowance(Common::get_last_day($month, $year), $month, $year, $uid, 'glidetime') 
					- Common::get_allowance(Common::get_last_day($last_month, $last_year), $last_month, $last_year, $uid, 'glidetime');
$compensation_taken = Common::count_absences_in_month($month, $year, $uid, 'Compensation');
$worked_time = Common::count_worked_secs(1, $month, $year, Common::get_last_day($month, $year), $month, $year, $uid);
$working_time = Common::count_working_time(1, $month, $year, Common::get_last_day($month, $year), $month, $year, $uid);
$glidetime_remaining = $glidetime_allowance + $worked_time/SECONDS_PER_HOUR -$working_time - $compensation_taken - $glidetime_paid;
?>
		<tr>
			<td colspan=3><br /><br /><b>Comments:</b><br /><br /><br /></td>
			<td><br /><br /><b>Employee:</b><br />Signature/Date<br /><br /></td>
		</tr>
		<tr>
			<td colspan=3>
				Holiday Allowance: <?php echo $holiday_allowance; ?><br />
				Holiday in Month: <?php echo $holidays_taken; ?><br />
				Holiday Remaining: <?php echo $holiday_remaining; ?><br /><br />
			</td>
			<td><b>Manager:</b><br />Signature/Date<br /><br /></td>
		</tr>
		<tr>
			<td colspan=3>
				Glidetime Allowance: <?php echo Common::format_hours_minutes($glidetime_allowance*SECONDS_PER_HOUR); ?><br />
				Worked in Month: <?php echo Common::format_hours_minutes($worked_time); ?><br />
				Paid-out in Month: <?php echo Common::format_hours_minutes($glidetime_paid*SECONDS_PER_HOUR); ?><br />
				Compensation Taken: <?php echo Common::format_hours_minutes($compensation_taken*SECONDS_PER_HOUR); ?><br />
				Working-Time in Month: <?php echo Common::format_hours_minutes($working_time*SECONDS_PER_HOUR); ?><br />
				Glidetime Remaining: <?php echo Common::format_hours_minutes($glidetime_remaining*SECONDS_PER_HOUR); ?><br />
			</td>
			<td><b>Bookkeeping:</b><br />Signature/Date<br /><br /></td>
		</tr>
	</table>

		</td>
	</tr>
</table>

</form>