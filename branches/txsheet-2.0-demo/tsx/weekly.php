<h1><?php echo JText::_('WEEKLY_TIMESHEET'); ?></h1>
<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclWeekly'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//check that project id is valid
if (gbl::getProjId() == 0)
	gbl::setTaskId(0);

// Check project assignment.
if (gbl::getProjId() != 0 && gbl::getClientId() != 0) { // id 0 means 'All Projects'

	//make sure project id is valid for client. If not then choose another.
	if (!Common::isValidProjectForClient(gbl::getProjId(), gbl::getClientId())) {
		gbl::setProjId(Common::getValidProjectForClient(gbl::getClientId()));
	}
}
else
	gbl::setTaskId(0);

//get the context date
$startDayOfWeek = Common::getWeekStartDay();  //needed by NavCalendar
$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
$dateValues = getdate($todayDate);

list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

$startStr = date("Y-m-d H:i:s",$startDate);
$endStr = date("Y-m-d H:i:s",$endDate);

//get the timeformat
$CfgTimeFormat = Common::getTimeFormat();

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('WEEKLY_TIMESHEET')." | ".gbl::getContextUser()."</title>");
ob_start();

include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();

PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');

//WARNING - IF POPUP IS SET THEN doOnLoad() won't be run

if (isset($popup)){
	$str = "window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
	PageElements::setBodyOnLoad($str);
}	
	
	require_once("include/tsx/navcal/navcal.class.php");
	$nav = new NavCal();
	$nav->navCalClockOnOff($todayDate,false);
	//require_once("include/language/datetimepicker_lang.inc");
?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">

function CallBack_WithNewDateSelected(strDate) 
{
	document.subtimes.submit();
}
</script>
<form action="<?php echo Rewrite::getShortUri(); ?>" method="post" name="subtimes">
<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
<input type="hidden" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />


<!-- date selection table -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr>
		<td width="30%" align="center" class="outer_table_heading">
			<?php
				$sdStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$startDate));
				//just need to go back 1 second most of the time, but DST 
				//could mess things up, so go back 6 hours...
				$edStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$endDate - 6*60*60));
				echo JText::_('CURRENT_WEEK').': <span style="color:#00066F;">'.$sdStr.' - '.$edStr.'</span>';
			?>
		</td>
		<td width="15%" align="center">
  		<input id="date1" name="date1" type="hidden" value="<?php echo date('d-m-Y', $startDate); ?>" />
  			&nbsp;&nbsp;&nbsp;<?php echo JText::_('SELECT_OTHER_WEEK').": "; ?>
  			<img style="cursor: pointer;" onclick="javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow')" alt="" src="images/cal.gif" />
		</td>
		<td width="5%" align="right"><?php echo JText::_('FILTER')?>:</td>

		<td  align="left">
		<?php 
      echo ucfirst(JText::_('CLIENT')).':</td><td width="25%" align="left">';
      Common::client_select_list(gbl::getClientId(), gbl::getContextUser(), false, false, true, false, "submit();"); ?>
		</td>
		<td  align="left">
      <?php 
      echo ucfirst(JText::_('PROJECT')).':</td><td width="25%" align="left">';
      Common::project_select_list(gbl::getClientId(), false, gbl::getProjId(), gbl::getContextUser(), false, true, "submit();");
      ?>
    </td>
	</tr>
</table><!-- end date selection table -->

<div>&nbsp;</div>
 <!--  data table -->
	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr class="inner_table_head">
			<td class="inner_table_column_heading" align="left" width="22%">
<?php
// print heading row including days of week

			if (gbl::getClientId() == 0)
				print ucfirst(JText::_('CLIENT'))." / ";

			if (gbl::getProjId() == 0)
				print ucfirst(JText::_('PROJECT'))." / ";

			print ucfirst(JText::_('Task'));
?>
						</td>
						<td align="center">&nbsp;</td>
						<?php
						//print the days of the week
						$currentDate = $startDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%A %d", $currentDate);
							print "<td class=\"inner_table_column_heading\" align=\"center\" width=\"10%\">$currentDayStr</td>\n";
							$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days"); // increment date to next day
						}
						?>
						<td align="center">&nbsp;</td>
						<td class="inner_table_column_heading" align="center"><?php echo ucfirst(JText::_('TOTAL'));?></td>
					</tr>
					<!--<tr>-->
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";

	require("include/tsx/taskinfo.class.php");

	// Get the Weekly data.
	$order_by_str = "".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name";
	list($num3, $qh3) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), gbl::getProjId(), gbl::getClientId(), $order_by_str);

	//print "<p>Query: $query </p>";
	//print "<p>there were $num3 results</p>";


	//we're going to put the data into an array of
	//different (unique) TASKS
	//which has an array of DAYS (7) which has
	//and array of size 4:
	// -index 0 is task entries array for tasks which started on a previous day and finish on a following day
	// -index 1 is task entries array for tasks which started on a previous day and finish today
	// -index 2 is task entreis array for tasks which started and finished today
	// -index 3 is task entries array for tasks which started today and finish on a following day

	$structuredArray = array();
	$previousTaskId = -1;
	$currentTaskId = -1;

	//iterate through results
	for ($i=0; $i<$num3; $i++) {
		//get the record for this task entry
		$data = dbResult($qh3,$i);
		
		//LogFile::write("\nweekly.php qh3 tuple: ". var_export($data, true)."\n");
		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		Common::fixStartEndDuration($data);

		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_stamp"];
		$currentTaskEndDate = $data["end_stamp"];
		$currentTaskName = $data["taskName"];
		$currentProjectTitle = $data["projectTitle"];
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription =  $data['log_message'];
		$currentClientName = $data["clientName"];
		$currentClientId = $data["client_id"];

		//find the current task id in the array
		$taskCount = count($structuredArray);
		unset($matchedPair);
		for ($j=0; $j<$taskCount; $j++) {
			//does its value1 (the task id) match?
			if ($structuredArray[$j]->value1 == $currentTaskId) {
				//store the pair we matched with
				$matchedPair = &$structuredArray[$j];

				//debug
				//print "<p> found existing matched pair so adding to that one </p>";

				//break since it matched
				break;
			}
		}

		//was it not matched
		if (!isset($matchedPair)) {

			//debug
			//print "<p> creating a new matched pair for this task </p>";

			//create a new days array
			$daysArray = array();

			//put an array in each day (this internal array will be of size 4)
			for ($j=0; $j<7; $j++) {
				//create a task event types array
				$taskEventTypes = array();

				//add 4 arrays to it
				for ($k=0; $k<4; $k++)
					$taskEventTypes[] = array();

				//add the task event types array to the days array for this day
				$daysArray[] = $taskEventTypes;
			}

			//create a new pair
			$matchedPair = new TaskInfo($currentTaskId,
											$daysArray,
											$currentProjectId,
											$currentProjectTitle,
											$currentTaskName,
											$currentClientName,
											$currentClientId,
											$currentWorkDescription);
LogFile::write("\nweekly.php new matched pair: ". var_export($matchedPair, true)."\n");
			//add the matched pair to the structured array
			$structuredArray[] = $matchedPair;

			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$structuredArray[count($structuredArray)-1];

			//print "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}

		//iterate through the days array
		$currentDate = $startDate;
		for ($k=0; $k<7; $k++) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");

			//work out some booleans
			$startsToday = (($currentTaskStartDate >= $currentDate ) && ( $currentTaskStartDate < $tomorrowDate ));
			$endsToday =   (($currentTaskEndDate > $currentDate) && ($currentTaskEndDate <= $tomorrowDate));
			$startsBeforeToday = ($currentTaskStartDate < $currentDate);
			$endsAfterToday = ($currentTaskEndDate > $tomorrowDate);

			if ($startsBeforeToday && $endsAfterToday)
				$matchedPair->value2[$k][0][] = $data;
			else if ($startsBeforeToday && $endsToday)
				$matchedPair->value2[$k][1][] = $data;
			else if ($startsToday && $endsToday)
				$matchedPair->value2[$k][2][] = $data;
			else if ($startsToday && $endsAfterToday)
				$matchedPair->value2[$k][3][] = $data;

			$currentDate = $tomorrowDate;
		}
	}

	//by now we should have our results structured in such a way that it's easy to output it

	//set vars
	$previousProjectId = -1;
	$allTasksDayTotals = array(0,0,0,0,0,0,0); //totals for each day

/*	$previousTaskId = -1;
	$thisTaskId = -1;
	$columnDay = -1;
	$columnStartDate = $startDate;*/

	//iterate through the structured array
	$count = count($structuredArray);
	unset($matchedPair);
	for ($i=0; $i<$count; $i++) {
		$matchedPair = &$structuredArray[$i];

		//start the row
		print "<tr>";
LogFile::write("\nweekly.php structured matched pair: ". var_export($matchedPair, true)."\n");
		//open the column for client name, project title, task name
		print "<td  class=\"calendar_cell_middle\" valign=\"top\">";

		//should we print the client name?
		if (gbl::getClientId() == 0)
			print "<span class=\"client_name_small\">$matchedPair->clientName / </span>";

		//print the project title
		if (gbl::getProjId() == 0)
			print "<span class=\"project_name_small\">$matchedPair->projectTitle / </span>";

		//print the task name
		print "<span class=\"task_name_small\">$matchedPair->taskName</span>";
		print "</td>\n";

		//print the spacer column
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";

		//iterate through the days array
		$dayIndex = 0;
		$weeklyTotal = 0;

		$currentDate = $startDate;
		foreach ($matchedPair->value2 as $currentDayArray) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");

			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"right\">";

			//while we are printing times set the style
			print "<span class=\"task_time_small\">";

			//declare todays vars
			$todaysTotal = 0;

			//create a flag for empty cell
			$emptyCell = true;

			//iterate through the current day array
			for ($j=0; $j<4; $j++) {
				$currentTaskEntriesArray = $currentDayArray[$j];

				//print "C" . count($currentTaskEntriesArray) . " ";

				//iterate through the task entries
				foreach ($currentTaskEntriesArray as $currentTaskEntry) {
					//is the cell empty?
					if ($emptyCell)
						//the cell is not empty since we found a task entry
						$emptyCell = false;
					else
						//print a break for the next entry
						print "&nbsp;"; //"<br />";

					//format printable times
					if ($CfgTimeFormat == "12") {
						$formattedStartTime = date("g:iA",$currentTaskEntry["start_stamp"]);
						$formattedEndTime = date("g:iA",$currentTaskEntry["end_stamp"]);
					} else {
						$formattedStartTime = date("G:i",$currentTaskEntry["start_stamp"]);
						$formattedEndTime = date("G:i",$currentTaskEntry["end_stamp"]);
					}

					//Simple math will be wrong during Daylight savings time changes
					switch($j) {
					case 0: //tasks which started on a previous day and finish on a following day
						print "...-...";
						$todaysTotal += Common::get_duration($currentDate, $tomorrowDate);
						break;
					case 1: //tasks which started on a previous day and finish today
						print "...-" . $formattedEndTime;
						$todaysTotal += Common::get_duration($currentDate, $currentTaskEntry["end_stamp"]);
						break;
					case 2: //tasks which started and finished today
						print $formattedStartTime . "-" . $formattedEndTime;
						$todaysTotal += $currentTaskEntry["duration"];
						break;
					case 3: //tasks which started today and finish on a following day
						print $formattedStartTime . "-...";
						$todaysTotal += Common::get_duration($currentTaskEntry["start_stamp"],$tomorrowDate);
						break;
					default:
						print "error";
					}
				}
			}

			//Put a popup link in the cell
			$dateValues = getdate($currentDate);
			$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
				"?client_id=$matchedPair->clientId".
				"&amp;proj_id=$matchedPair->projectId".
				"&amp;task_id=$matchedPair->value1".
				"&amp;year=".$dateValues["year"].
				"&amp;month=".$dateValues["mon"].
				"&amp;day=".$dateValues["mday"].
				"&amp;destination=".Rewrite::getShortUri().
				"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
			print "<a href=\"$popup_href\" class=\"action_link\">".
				"<img src=\"images/add.gif\" width=\"11\" height=\"11\" border=\"0\" alt=\"\" />".
				"</a>";

			//close the times class
			print "</span>";

			if (!$emptyCell) {
				//print todays total
				$todaysTotalStr = Common::formatMinutes($todaysTotal);
				print "<br /><span class=\"task_time_total_small\">$todaysTotalStr</span>";
			}

			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $todaysTotal;

			//add this days total to the all tasks total for this day
			$allTasksDayTotals[$dayIndex] += $todaysTotal;
			$dayIndex++;
			$currentDate=$tomorrowDate;
		}

		//print the spacer column
		echo "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

		//format the weekly total
		$weeklyTotalStr = Common::formatMinutes($weeklyTotal);

		//print the total column
		echo "<td class=\"calendar_totals_line_weekly subtotal\" valign=\"bottom\" align=\"right\" >\n";
		echo "<span class=\"calendar_total_value_weekly\" style=\"text-align:right\">$weeklyTotalStr</span></td>\n";

		//end the row
		echo "</tr>\n";

		//store the previous task and project ids
		$previousTaskId = $currentTaskId;
		$previousProjectId = $matchedPair->projectId;
	}

	//create an actions row
	print "<tr><!--create an actions row-->\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">".ucfirst(JText::_('ACTIONS')).":</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	$currentDate = $startDate;
	for ($i=0; $i<7; $i++) {
		$dateValues = getdate($currentDate);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
											"?client_id=".gbl::getClientId()."".
											"&amp;proj_id=".gbl::getProjId()."".
											"&amp;task_id=".gbl::getTaskId()."".
											"$ymdStr".
											"&amp;destination=".Rewrite::getShortUri().
											"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
		print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
		print "<a href=\"$popup_href\" class=\"action_link\">".ucfirst(JText::_('ADD'))."</a>,";
		print "<a href=\"daily?$ymdStr\">".ucfirst(JText::_('EDIT'))."</a></td>\n";
		$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");
	}
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_cell_disabled_right\">&nbsp;</td>\n";
	print "</tr>";


	//create a new totals row
	print "<tr><!--new totals row-->\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">".ucwords(JText::_('TOTAL')." ".JText::_('HOURS')).":</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = Common::formatMinutes($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		//print "<td class=\"calendar_totals_line_weekly\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\">$formattedTotal</span></td>";
	}

	//print grand total
	$formattedGrandTotal = Common::formatMinutes($grandTotal);
	echo "<!-- print grand total-->";
  print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\">$formattedGrandTotal</span></td>";
	print "</tr>";

?>

  </table>
</form>
