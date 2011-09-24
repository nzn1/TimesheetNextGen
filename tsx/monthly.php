<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setTheme('txsheet2');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;

include('monthly.class.php');
$mc = new MonthlyClass();

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

// Check project assignment.
if (gbl::getProjId() != 0) { // id 0 means 'All Projects'
	list($qh, $num) = dbQuery("SELECT * FROM ".tbl::getAssignmentsTable()." WHERE proj_id='".gbl::getProjId()."' AND username='".gbl::getContextUser()."'");
	if ($num < 1){
		Common::errorPage(JText::sprintf('NOT_ASSIGNED_TO_IT',JText::_('PROJECT')));
	}
} 
else{
	gbl::setTaskId(0);
}

//work out the start date by subtracting days to get to beginning of week
gbl::setDay(1);

$startDate = strtotime(date("d M Y",gbl::getContextTimestamp()));
$startStr = date("Y-m-d H:i:s",$startDate);
$endDate = Common::getMonthlyEndDate(gbl::getContextDate());
$endStr = date("Y-m-d H:i:s",$endDate);
//ppr($endDate,'end date');
//ppr($endStr,'end str');

$previousDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " -1 month");
$nextDate = strtotime(date("d M Y H:i:s",gbl::getContextTimestamp()) . " +1 month");

$mode="monthly";

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = Common::getWeekStartDay();
// Get day of week of 1st of month
$dowForFirstOfMonth = date('w',$startDate);

//get the number of lead in days
$leadInDays = $dowForFirstOfMonth - $startDayOfWeek;
if ($leadInDays < 0){
	$leadInDays += 7;
}

//get the first printed date
$firstPrintedDate = strtotime(date("d M Y H:i:s",$startDate) . " -$leadInDays days");



ob_start();
echo "<title>".Config::getMainTitle()." | ".JText::_('MONTHLY_TIMESHEET')." | ".gbl::getContextUser()."</title>";
?>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">
	function CallBack_WithNewDateSelected(strDate) {
		document.monthForm.submit();
	}
</script>
<?php
PageElements::setHead(ob_get_contents());
ob_end_clean();

if (isset($popup))
	PageElements::setBodyOnLoad("window.open('".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&task_id=$task_id',"
      ."'Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205');");
?>


<form name="monthForm" action="<?php echo Rewrite::getShortUri(); ?>" method="get">

<!-- Overall table covering month cells, client and project and date -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td ><h1 style="margin:0;padding:0;"><?php echo JText::_('MONTHLY_TIMESHEET'); ?></h1></td>

	</tr>
	<tr>
		<td >&nbsp;</td>
	</tr>
	<tr>
		<td align="center" class="outer_table_heading">
		<?php Common::printDateSelector($mode, $startDate, $previousDate, $nextDate); ?>
		</td>
		
		<td class="outer_table_heading"><?php echo JText::_('FILTER')?>:</td>
		<td class="outer_table_heading">
				<span style="color:#00066F;"><?php echo JText::_('CLIENT').': '; ?></span>
		</td>
		<td align="left">
			<?php Common::client_select_list(gbl::getClientId(), gbl::getContextUser(), false, false, true, false, "submit();"); ?>
		</td>
		<td >&nbsp;</td>
		<td class="outer_table_heading">
			<span style="color:#00066F;"><?php echo JText::_('PROJECT').': '; ?></span>
		</td>
		<td align="left"><?php Common::project_select_list(gbl::getClientId(), false, gbl::getProjId(), gbl::getContextUser(), false, true, "submit();"); ?></td>
		<td >&nbsp;</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
	</tr>
</table><!-- end of the client, project select table and the current month -->

<div id="monthly">
	<!-- table encompassing heading, days in month, weekly total and month total -->
	<table class="monthTable">
		<thead>
  		<tr class="table_head">
  		<?php
  			//print the days of the week
  			$currentDate = $firstPrintedDate;
  			for ($i=0; $i<7; $i++) {
  				$currentDayStr = strftime("%A", $currentDate);
  				$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 day");
  				echo "	<th align=\"center\">$currentDayStr</th>\n";
  			}
  		?>
  		</tr>
		</thead>
		<tr>
<?php

	//define the variable dayCol
	$dayCol = 0;

	// Print last months' days spots.
	for ($i=0; $i<$leadInDays; $i++) {
		echo "<td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
		$dayCol++;
	}

  require(Config::getDocumentRoot().'/include/tsx/tsxtimelist.class.php');
  $tl = new TsxTimeList();
  // Get the Monthly data.
  $tl->search($startStr,$endStr);
  
	
	list($qhol, $holnum) = Common::get_absences(gbl::getMonth(), gbl::getYear(), gbl::getContextUser());

	$ihol = 0; $holtitle = "";
	if ($holnum>$ihol)
		$holdata = dbResult($qhol, $ihol);

	$trackFirstItem=0; 
  $trackLastItem=0; 
  $curDay = 1; 
  $monthlyTotal = 0; 
  $weeklyTotal = 0;

	while (checkdate(gbl::getMonth(), $curDay, gbl::getYear())) {
		$curStamp = mktime(0,0,0, gbl::getMonth(), $curDay, gbl::getYear());
		$tomorrowStamp = strtotime(date("d M Y H:i:s",$curStamp) . " +1 day");

		// New Week.
		if ((($dayCol % 7) == 0) && ($dowForFirstOfMonth != 0)) {
			echo "</tr>\n<!-- --><tr>\n";
			$mc->print_totals($weeklyTotal, "weekly", gbl::getYear(), gbl::getMonth(), $curDay);
			$weeklyTotal = 0;
			echo "</tr>\n<!-- --><tr>\n";
		} else
			$dowForFirstOfMonth = 1;

		//define subtable
		if (($dayCol % 7) == 6){
			echo "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_holiday_right\">\n";
		}
		else if (($dayCol % 7 ) == 5){
			echo "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_holiday_middle\">\n";
		}
		else {
			$cellstyle = 'calendar_cell_middle';
			if ($holnum>$ihol && $holdata['day_of_month']==$curDay) {
				$cellstyle = 'calendar_cell_holiday_middle';
				if ($holdata['user']=='') {
					$holtitle = urldecode($holdata['subject']);
					if (($holdata['AM_PM']=='AM')||($holdata['AM_PM']=='PM')){
						$holtitle .= " (".$holdata['AM_PM'].")";
					}
				} 
        else{
					$holtitle = $holdata['user'].": ".urldecode($holdata['type'])." ".$holdata['AM_PM'];
				}
				$ihol++;
				if ($holnum>$ihol){
					$holdata = dbResult($qhol, $ihol);
					if ($holdata['day_of_month']==$curDay) {
						if ($holdata['user']==''){
							$holtitle .= " ".urldecode($holdata['subject']);
							if (($holdata['AM_PM']=='AM')||($holdata['AM_PM']=='PM')){
								$holtitle .= " (".$holdata['AM_PM'].")";
							}
						} 
            else {
							if ($holtitle==$holdata['user'].": ".urldecode($holdata['type'])." AM"){
								$holtitle = $holdata['user'].": ".urldecode($holdata['type']);
							}
							else{
								$holtitle .= " ".$holdata['user'].": ".urldecode($holdata['type'])." ".$holdata['AM_PM'];
							}
						}
						$ihol++;
						if ($holnum>$ihol){
							$holdata = dbResult($qhol, $ihol);
						}
					}
				}
			}
			echo "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"".$cellstyle."\">\n";
		}

		// Print out date.
		$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
											"?client_id=".gbl::getClientId()."".
											"&amp;proj_id=".gbl::getProjId()."".
											"&amp;task_id=".gbl::getTaskId()."".
											"&amp;year=".gbl::getYear()."".
											"&amp;month=".gbl::getMonth()."".
											"&amp;day=".$curDay."".
											"&amp;destination=".urlencode(Rewrite::getShortUri()).
											"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
		
    $href = Config::getRelativeRoot()."/daily?&amp;year=".gbl::getYear() . "&amp;month=".gbl::getMonth() . "&amp;day=".$curDay.
			"&amp;client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId();
    ?>
        <div style="display:inline;">
          <?php echo "<a href=\"".$href."\">".$curDay."<span class=\"holiday_title_small\">&nbsp;$holtitle</span><!--shows holiday info--></a>";?>
        </div>	
        <div style="float:right; width:12px">
          <a href="<?echo $popup_href;?>" class="action_link">
				    <img src="{relativeRoot}/images/add.gif" alt="+" width="11" height="11" border="0" />
				  </a>
        </div>
        <div class="clearall"></div>
        
		

<?php  
		$data_seen = 0;
		$holtitle = ""; // reset

		//Ok, the logic is going to get a little thick here.  Previous version of code looped
		//through the entire set of the month's data entries for every day of the month.  That
		//works out to be O=N^2, ie. way inefficient.

		//Here we need to keep track of how far back we need to keep checking the time entries
		//and how far forward we need to check them, in variables $trackFirstItem and $trackLastItem respectively.
		//As tasks finish, $trackFirstItem can be incremented, as we check additional entries, $trackLastItem is
		//incremented.  If tasks are nested, ie. one starts and stops in the middle of another
		//task, we have to keep $trackFirstItem from being incremented until the end of the outer most nested
		//task is finished. This complicated logic changes the code to be O=2N at worst, and can
		//be very close to O=N at best. In either case, this is much more efficient than O=N^2.

		//(acutally, if every task is nested inside another thoughout the entire month, I think
		//it's O=N log N, but that's still better than N^2, and that's not exactly a valid real
		//world time card, in fact nesting tasks is probably a highly questionable practice.)

		//set data to the earliest set of data we need to check
		$i = $trackFirstItem;
    if($i >= $tl->getLength()){
      $data = new TsxTime();
    }
    else{
      $data = $tl->getTime($i);
    }
    
		$todaysTotal = 0;

		if($i<$tl->getLength()) {
			//set some booleans
			$data->setBooleans(mktime(0,0,0, gbl::getMonth(), $curDay, gbl::getYear()));

			$todaysData=array();

			$can_change_a = 1;

			// If the day has data, gather the info...
			while($i <= $trackLastItem ) {
				if(($data->startsBeforeToday && $data->endsAfterToday) || ($data->startsBeforeToday && $data->endsToday) ||
				  ($data->startsToday && $data->endsToday) || ($data->startsToday && $data->endsAfterToday) ) {

					// This day has data in it.  Therefore we want to print out a summary at the bottom of each day.
					$data_seen = 1;

					if ($data->startsBeforeToday && $data->endsAfterToday) {
						$todaysData[$data->clientName][$data->projectTitle][$data->taskName][]= "...-...";
						$todaysTotal += Common::get_duration($curStamp, $tomorrowStamp);
					} 
          else if ($data->startsBeforeToday && $data->endsToday) {
						$todaysData[$data->clientName][$data->projectTitle][$data->taskName][]= "...-" . $data->getFormattedEndTime();
						$todaysTotal += Common::get_duration($curStamp, $data->end_stamp);
					} 
          else if ($data->startsToday && $data->endsToday) {
						$todaysData[$data->clientName][$data->projectTitle][$data->taskName][]= $data->getFormattedStartTime() . "-" . $data->getFormattedEndTime();
						$todaysTotal += $data->duration;
					} 
          else if ($data->startsToday && $data->endsAfterToday) {
						$todaysData[$data->clientName][$data->projectTitle][$data->taskName][]= $data->getFormattedStartTime() . "-...";
						$todaysTotal += Common::get_duration($data->start_stamp,$tomorrowStamp);
					} 
          else {
						echo "Error: time booleans are in a confused state<br />\n";
					}


					if($can_change_a && $data->endsAfterToday) {
						$trackFirstItem=$i;
						$can_change_a = 0;
					}

					if($can_change_a  && $data->endsToday){
            $trackFirstItem=$i+1;
          }
					if($trackLastItem<=$i){
            $trackLastItem=$i+1;
          }

				}
				$i++;

				if($i< $tl->getLength()) {				
				  $data = $tl->getTime($i);
          $data->setBooleans(mktime(0,0,0, gbl::getMonth(), $curDay, gbl::getYear()));
				} 
        else {
					$data->startsToday=false;
					$data->endsToday=false;
					$data->startsBeforeToday=false;
					$data->endsAftertoday=true;
				} 
				
			}
		}

		$weeklyTotal += $todaysTotal;
		$monthlyTotal += $todaysTotal;

		if ($data_seen == 1) {
			//Print the entire day's worth of info we've gathered
			foreach($todaysData as $clientName => $clientArray) {
				echo "<span class=\"client_name_small\">$clientName</span>";
				foreach($clientArray as $projectName => $projectArray) {
					echo "<span class=\"project_name_small\">&nbsp;$projectName</span>";
					foreach($projectArray as $taskName => $taskArray) {
						echo "<span class=\"task_name_small\">&nbsp;&nbsp;$taskName</span>";
						foreach($taskArray as $taskStr) {
							echo "<span class=\"task_time_small\">&nbsp;&nbsp;&nbsp;$taskStr</span>";
						}
					}
				}
			}

			echo "<span class=\"task_time_total_small\">" . Common::formatMinutes($todaysTotal) ."</span>";

		} else {
			echo "<span>&nbsp;</span>";
		}

		//end subtable

		echo " </td>\n";

		$curDay++;
		$dayCol++;
	}
	// Print the rest of the calendar.
	while (($dayCol % 7) != 0) {
		if (($dayCol % 7) == 6)
			echo " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_right\">&nbsp;</td>\n ";
		else
			echo " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
		$dayCol++;
	}
	echo "</tr>\n<tr>\n";
	$mc->print_totals($weeklyTotal, "weekly", gbl::getYear(), gbl::getMonth(), $curDay);
	$weeklyTotal = 0;
	echo "</tr>\n<tr>\n";
	$mc->print_totals($monthlyTotal, "monthly", gbl::getYear(), gbl::getMonth(), $curDay);

?>
					</tr>
				</table>
  </div><!--end monthly div-->
</form>