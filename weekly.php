<?php
//$Header: /cvsroot/tsheet/timesheet.php/weekly.php,v 1.6 2005/05/23 05:39:39 vexil Exp $
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclWeekly')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&amp;clearanceRequired=" . get_acl_level('aclWeekly'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&amp;clearanceRequired=" . Common::get_acl_level('aclWeekly'));
	}
	
	exit;
}

//include('weekly.class.php');
//$wc = new WeeklyClass();

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");
//check that project id is valid
if (gbl::getProjId() == 0)
	gbl::setTaskId(0);

// Check project assignment.
if (gbl::getProjId() != 0 && gbl::getClientId() != 0) { // id 0 means 'All Projects'

	//make sure project id is valid for client. If not then choose another.
	if (!isValidProjectForClient($proj_id, gbl::getClientId())) {
		$proj_id = getValidProjectForClient(gbl::getClientId());
	}
}
else
	$task_id = 0;


//get the context date
$startDayOfWeek = Common::getWeekStartDay();  //needed by NavCalendar
$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
$dateValues = getdate($todayDate);

list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

$startStr = date("Y-m-d H:i:s",$startDate);
$endStr = date("Y-m-d H:i:s",$endDate);

//get the timeformat
$CfgTimeFormat = Common::getTimeFormat();

//$post="proj_id=$proj_id&amp;amp;task_id=$task_id&amp;amp;client_id=$client_id";
?>
//<html>
//<head>
//<title>Weekly Timesheet for <?php echo "$contextUser" ?></title>
<?php
//include ("header.inc");
PageElements::setHead("<title>".Config::getMainTitle()." - Timesheet for ".$contextUser."</title>");
ob_start();
?>
//</head>
<?php
echo "<body width=\"100%\" height=\"100%\"";
include ("body.inc");
if (isset($popup))
	echo "onLoad=window.open(\"clock_popup.php?proj_id=$proj_id&amp;task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
echo ">\n";

//include ("banner.inc");
//include("navcal/navcalendars.inc");
	$currentDate = $todayDate;
	$fromPopup = "false";
	include("navcalnew/navcal+clockOnOff.inc"); 
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="month" value=<?php echo $month; ?> />
<input type="hidden" name="year" value=<?php echo $year; ?> />
<input type="hidden" name="day" value=<?php echo $day; ?> />
<input type="hidden" name="task_id" value=<?php echo $task_id; ?> />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap>
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Client:</td></tr></table></td>
												<td width="100%"><?php client_select_list($client_id, $contextUser, false, false, true, false, "submit();"); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
											</tr>
										</table>
									</td>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Project:</td></tr></table></td>
												<td width="100%"><?php project_select_list($client_id, false, $proj_id, $contextUser, false, true, "submit();"); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap class="outer_table_heading">
							Week Start: <?php echo date('D F j, Y',$startDate); ?>
						</td>
						<td align="right" nowrap>
							<!--prev / next buttons used to be here -->
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading" align="left" width="22%">
<?php

						if ($client_id == 0)
							print "Client / ";

						if ($proj_id == 0)
							print "Project / ";

						print "Task";
?>
						</td>
						<td align="center">&nbsp;</td>
						<?php
						//print the days of the week
						$currentDate = $startDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%A %d", $currentDate);
							print "	<td class=\"inner_table_column_heading\" align=\"center\" width=\"10%\">$currentDayStr</td>";
							$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");
						}
						?>
						<td align="center">&nbsp;</td>
						<td class="inner_table_column_heading" align="center">Total</td>
					</tr>
					<tr>
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";

	class TaskInfo extends Pair {
		var $projectId;
		var $projectTitle;
		var $taskName;
		var $clientName;
		var $clientId;

		function TaskInfo($value1, $value2, $projectId, $projectTitle, $taskName, $clientName, $clientId) {
			parent::Pair($value1, $value2);
			$this->projectId = $projectId;
			$this->projectTitle = $projectTitle;
			$this->taskName = $taskName;
			$this->clientName = $clientName;
			$this->clientId = $clientId;
		}
	}

	// Get the Weekly data.
	$order_by_str = "$CLIENT_TABLE.organisation, $PROJECT_TABLE.title, $TASK_TABLE.name";
	list($num3, $qh3) = get_time_records($startStr, $endStr, $contextUser, $proj_id, $client_id, $order_by_str);

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
											$currentClientId);

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

		//open the column for client name, project title, task name
		print "<td  class=\"calendar_cell_middle\" valign=\"top\">";

		//should we print the client name?
		if ($client_id == 0)
			print "<span class=\"client_name_small\">$matchedPair->clientName / </span>";

		//print the project title
		if ($proj_id == 0)
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
						$todaysTotal += get_duration($currentDate, $tomorrowDate);
						break;
					case 1: //tasks which started on a previous day and finish today
						print "...-" . $formattedEndTime;
						$todaysTotal += get_duration($currentDate, $currentTaskEntry["end_stamp"]);
						break;
					case 2: //tasks which started and finished today
						print $formattedStartTime . "-" . $formattedEndTime;
						$todaysTotal += $currentTaskEntry["duration"];
						break;
					case 3: //tasks which started today and finish on a following day
						print $formattedStartTime . "-...";
						$todaysTotal += get_duration($currentTaskEntry["start_stamp"],$tomorrowDate);
						break;
					default:
						print "error";
					}
				}
			}

			//Put a popup link in the cell
			$dateValues = getdate($currentDate);
			$popup_href = "javascript:void(0)\" onclick=window.open(\"clock_popup.php".
				"?client_id=$matchedPair->clientId".
				"&amp;proj_id=$matchedPair->projectId".
				"&amp;task_id=$matchedPair->value1".
				"&amp;year=".$dateValues["year"].
				"&amp;month=".$dateValues["mon"].
				"&amp;day=".$dateValues["mday"].
				"&amp;destination=$_SERVER[PHP_SELF]".
				"\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310\") dummy=\"";
			print "<a href=\"$popup_href\" class=\"action_link\">".
				"<img src=\"images/add.gif\" width=\"11\" height=\"11\" border=\"0\" alt=\"\" />".
				"</a>";

			//close the times class
			print "</span>";

			if (!$emptyCell) {
				//print todays total
				$todaysTotalStr = formatMinutes($todaysTotal);
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
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";

		//format the weekly total
		$weeklyTotalStr = formatMinutes($weeklyTotal);

		//print the total column
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\">$weeklyTotalStr</span></td>";

		//end the row
		print "</tr>";

		//store the previous task and project ids
		$previousTaskId = $currentTaskId;
		$previousProjectId = $matchedPair->projectId;
	}

	//create an actions row
	print "<tr>\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Actions:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	$currentDate = $startDate;
	for ($i=0; $i<7; $i++) {
		$dateValues = getdate($currentDate);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$popup_href = "javascript:void(0)\" onclick=window.open(\"clock_popup.php".
											"?client_id=$client_id".
											"&amp;proj_id=$proj_id".
											"&amp;task_id=$task_id".
											"$ymdStr".
											"&amp;destination=$_SERVER[PHP_SELF]".
											"\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310\") dummy=\"";
		print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
		print "<a href=\"$popup_href\" class=\"action_link\">Add</a>,";
		print "<a href=\"daily.php?$ymdStr\">Edit</a></td>\n";
		$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");
	}
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_cell_disabled_right\">&nbsp;</td>\n";
	print "</tr>";


	//create a new totals row
	print "<tr>\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Total Hours:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = formatMinutes($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		//print "<td class=\"calendar_totals_line_weekly\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\">$formattedTotal</span></td>";
	}

	//print grand total
	$formattedGrandTotal = formatMinutes($grandTotal);
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\">$formattedGrandTotal</span></td>";
	print "</tr>";

?>

				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>

