<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

require_once('simple.class.php');
$simple = new SimplePage();

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//get the passed date (context date)
$todayStamp = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$todayValues = getdate($todayStamp);
$curDayOfWeek = $todayValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = Common::getWeekStartDay();

$daysToMinus = $curDayOfWeek - $startDayOfWeek;
if ($daysToMinus < 0)
	$daysToMinus += 7;

$startDate = strtotime(date("d M Y H:i:s",$todayStamp) . " -$daysToMinus days");
$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");

//get the configuration of timeformat and layout
//list($qh2, $numq) = dbQuery("SELECT simpleTimesheetLayout FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
//$configData = dbResult($qh2);
$layout = Common::getLayout();

//$post="";

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");

	
	
ob_start();	
?>
<title><?php echo Config::getMainTitle();?> - Simple Weekly Timesheet for <?php echo gbl::getContextUser();?></title>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">
	//define the hash table
	var projectTasksHash = {};
	
<?php
$PROJECT_TABLE = tbl::getProjectTable();
$CLIENT_TABLE = tbl::getClientTable();
$TASK_TABLE = tbl::getTaskTable();
//get all of the projects and put them into the hashtable
$getProjectsQuery = "SELECT $PROJECT_TABLE.proj_id, " .
							"$PROJECT_TABLE.title, " .
							"$PROJECT_TABLE.client_id, " .
							"$CLIENT_TABLE.client_id, " .
							"$CLIENT_TABLE.organisation " .
						"FROM $PROJECT_TABLE, " .tbl::getAssignmentsTable(). ", $CLIENT_TABLE " .
						"WHERE $PROJECT_TABLE.proj_id=" .tbl::getAssignmentsTable().".proj_id AND ".
							"" .tbl::getAssignmentsTable(). ".username='".gbl::getContextUser()."' AND ".
							"$PROJECT_TABLE.client_id=$CLIENT_TABLE.client_id ".
						"ORDER BY $CLIENT_TABLE.organisation, $PROJECT_TABLE.title";

list($qh3, $num3) = dbQuery($getProjectsQuery);

//iterate through results
for ($i=0; $i<$num3; $i++) {
	//get the current record
	$data = dbResult($qh3, $i);
	print("projectTasksHash['" . $data["proj_id"] . "'] = {};\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['name'] = '". addslashes($data["title"]) . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['clientId'] = '". $data["client_id"] . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['clientName'] = '". addslashes($data["organisation"]) . "';\n");
	print("projectTasksHash['" . $data["proj_id"] . "']['tasks'] = {};\n");
}

//get all of the tasks and put them into the hashtable
$getTasksQuery = "SELECT $TASK_TABLE.proj_id, " .
						"$TASK_TABLE.task_id, " .
						"$TASK_TABLE.name " .
					"FROM $TASK_TABLE, " .tbl::getTaskAssignmentsTable(). " ".
					"WHERE $TASK_TABLE.task_id = " .tbl::getTaskAssignmentsTable().".task_id AND ".
						"".tbl::getTaskAssignmentsTable().".username='".gbl::getContextUser()."' ".
					"ORDER BY $TASK_TABLE.name";

list($qh4, $num4) = dbQuery($getTasksQuery);
//iterate through results
for ($i=0; $i<$num4; $i++) {
	//get the current record
	$data = dbResult($qh4, $i);
	print("if (projectTasksHash['" . $data["proj_id"] . "'] != null)\n");
	print("  projectTasksHash['" . $data["proj_id"] . "']['tasks']['" . $data["task_id"] . "'] = '" . addslashes($data["name"]) . "';\n");
}
echo"</script>";
echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/js/simple.js\"></script>\n";

PageElements::setHead(ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('populateExistingSelects();');
?>

<form name="simpleForm" action="<?php echo Config::getRelativeRoot(); ?>/simple_action" method="post">
<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
<input type="hidden" name="startStamp" value="<?php echo $startDate; ?>" />

<h1><?php echo JText::_('SIMPLE_WEEKLY_TIMESHEET'); ?></h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center" nowrap="nowrap" class="outer_table_heading">
			<?php
				$sdStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$startDate));
				//just need to go back 1 second most of the time, but DST
				//could mess things up, so go back 6 hours...
				$edStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$endDate - 6*60*60));
				echo JText::_('CURRENT_WEEK').': <span style="color:#00066F;">'.$sdStr.' - '.$edStr.'</span>';
			?>
		</td>
		<td nowrap="nowrap" class="outer_table_heading">
			<input id="date1" name="date1" type="hidden" value="<?php echo date('d-M-Y', $startDate); ?>" />
			&nbsp;&nbsp;&nbsp;<?php echo JText::_('SELECT_OTHER_WEEK').": "; ?>
			<img style="cursor: pointer;" onclick="javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow')" alt="" src="images/cal.gif">
			</td>
		<td align="right" nowrap="nowrap">
			<!--prev / next buttons used to be here -->
		</td>
		<td align="right" nowrap="nowrap">
			<input type="button" name="saveButton" id="saveButton" value="<?php echo JText::_('SAVE_CHANGES')?>" disabled="disabled" onclick="validate();" />
		</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
	</tr>
</table>

<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
				<tr class="inner_table_head">
					<td class="inner_table_column_heading" align="center">
							<?php
								echo ucwords(JText::_('CLIENT')." / ".JText::_('PROJECT')." / ".JText::_('TASK'));
								if(strstr($layout, 'no work description') == '')
									echo ' / '.ucwords(JText::_('WORK_DESCRIPTION'));
							?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<?php
						//print the days of the week
						$currentDayDate = $startDate;
						$dstadj=array();
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%a", $currentDayDate);
							$dst_adjustment = Common::get_dst_adjustment($currentDayDate);
							$dstadj[]=$dst_adjustment;
							$minsinday = ((24*60*60) - $dst_adjustment)/60;

							print
								"<td class=\"inner_table_column_heading\" align=\"center\" width=\"65\">"								
								  ."<input type=\"hidden\" id=\"minsinday_".($i+1)."\" value=\"$minsinday\" />"
									."$currentDayStr<br />" .
									//Output the numerical date in the form of day of the month
									date("d", $currentDayDate) . 
								"</td>";
							$currentDayDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
						}
						?>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">
							<?php echo ucfirst(JText::_('TOTAL')) ?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">
							<?php echo ucfirst(JText::_('DELETE')) ?>
						</td>
					</tr>
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";










	// Get the Weekly user data.
	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$order_by_str = "".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name";
	list($num5, $qh5) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), 0, 0, $order_by_str);

	//we're going to put the data into an array of
	//different (unique) TASKS
	//which has an array of DAYS (7) which has
	//an array of task durations for that day

	$structuredArray = array();
	$previousTaskId = -1;
	$currentTaskId = -1;

	//iterate through results
	for ($i=0; $i<$num5; $i++) {
		//get the record for this task entry
		$data = dbResult($qh5,$i);

		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		Common::fixStartEndDuration($data);

		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_stamp"];
		$currentTaskEndDate = $data["end_stamp"];
		$currentTaskName = stripslashes($data["taskName"]);
		$currentProjectTitle = stripslashes($data["projectTitle"]);
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription = $data["log_message"];

		//debug
		//print "<p>taskId:$currentTaskId '$data[taskName]', start time:$data[start_time_str], end time:$data[end_time_str]</p>";

		// Combine multiple entries for a given project/task & description into a single line
		// look for the current task id in the array
		$taskCount = count($structuredArray);
		unset($matchedPair);
		for ($j=0; $j<$taskCount; $j++) {
			// does(taskID [value1] && workDescription) match?
			if ($structuredArray[$j]->value1 == $currentTaskId && $structuredArray[$j]->workDescription == $currentWorkDescription) {
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

			for ($j=0; $j<7; $j++) {
				//create a task event types array
				$taskEventTypes = array();

				//add the task event types array to the days array for this day
				$daysArray[] = $taskEventTypes;
			}

			//create a new pair
			$matchedPair = new TaskInfo($currentTaskId, $daysArray,
										$currentProjectId, $currentProjectTitle,
										$currentTaskName, $currentWorkDescription
										);

			//add the matched pair to the structured array
			$structuredArray[] = $matchedPair;

			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$structuredArray[count($structuredArray)-1];

			//print "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}

		//iterate through the days array
		$currentDayDate = $startDate;
		for ($k=0; $k<7; $k++) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");

			$duration = 0;
			if(isset($data["duration"]) && ($data["duration"] > 0) ) {
				$duration = $data["duration"];
			}

			$startsToday = (($currentTaskStartDate >= $currentDayDate ) && ( $currentTaskStartDate < $tomorrowDate ));
			$endsToday =   (($currentTaskEndDate > $currentDayDate) && ($currentTaskEndDate <= $tomorrowDate));
			$startsBeforeToday = ($currentTaskStartDate < $currentDayDate);
			$endsAfterToday = ($currentTaskEndDate > $tomorrowDate);

			if($startsToday && $endsToday ) {
				$matchedPair->value2[$k][] = $duration;
			} else if($startsToday && $endsAfterToday) {
				$matchedPair->value2[$k][] = Common::get_duration($currentTaskStartDate, $tomorrowDate);
			} else if( $startsBeforeToday && $endsToday ) {
				$matchedPair->value2[$k][] = Common::get_duration($currentDayDate, $currentTaskEndDate);
			} else if( $startsBeforeToday && $endsAfterToday ) {
				$matchedPair->value2[$k][] = Common::get_duration($currentDayDate, $tomorrowDate);
			}

			$currentDayDate = $tomorrowDate;
		}
	}

	//by now we should have our results structured in such a way that it it easy to output it

	//set vars
	$previousProjectId = -1;
	$simple->setAllTasksDayTotals(array(0,0,0,0,0,0,0)); //totals for each day

/*	$previousTaskId = -1;
	$thisTaskId = -1;
	$columnDay = -1;
	$columnStartDate = $startDate;*/


//iterate through the structured array
	$count = count($structuredArray);
	unset($matchedPair);
	for ($rowIndex = 0; $rowIndex<$count; $rowIndex++) {
		$matchedPair = &$structuredArray[$rowIndex];


		$simple->printFormRow($rowIndex, $layout,$matchedPair->projectId,$matchedPair->value1,$matchedPair->workDescription,$startDate,$matchedPair->value2);

		//store the previous task and project ids
		$previousTaskId = $matchedPair->value1;
		$previousProjectId = $matchedPair->projectId;
	}

	/////////////////////////////////////////
	//add an extra row for new data entry
	/////////////////////////////////////////

	$simple->printFormRow($count, $layout, -1, -1);

	////////////////////////////////////////////////////
	//Changes reequired to enter data on form -define 10 entry rows

//	for ($i=0; $i<10; $i

	////////////////////////////////////////////////////

	//create a new totals row
	print "<tr id=\"totalsRow\">\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
  	//store a hidden form field containing the number of existing rows
	print "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"" . $count . "\" />";

	//store a hidden form field containing the total number of rows
	print "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($count+1) . "\" />";
  print ucwords(JText::_('TOTAL_HOURS')).":</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	foreach ($simple->getAllTasksDayTotals() as $currentAllTasksDayTotal) {
		$col++;
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = Common::formatMinutes($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">$formattedTotal</span></td>";
	}

	//print grand total
	$formattedGrandTotal = Common::formatMinutes($grandTotal);
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\" id=\"grand_total\">$formattedGrandTotal</span></td>";
	print "</tr>";

?>

			</table>
		</td>
	</tr>
</table>

</form>
