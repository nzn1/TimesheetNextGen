<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//bug fix - we must display all projects
$proj_id = 0;
$task_id = 0;

//get the passed date (context date)
$month = gbl::getMonth();
$day = gbl::getDay(); 
$year = gbl::getYear();
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
//list($qh2, $numq) = dbQuery("SELECT simpleTimesheetLayout FROM $CONFIG_TABLE WHERE config_set_id = '1'");
//$configData = dbResult($qh2);
$layout = Common::getLayout();

//$post="";

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");

	
	
ob_start();	
?>
<title><?php echo Config::getMainTitle()." - ".JText::_('TIMESHEET_FOR').gbl::getContextUser();?></title>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js">
	//define the hash table
	var projectTasksHash = {};
	
<?php //Notice: We're dynamically creating more javascript below
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


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="year" value=<?php echo $year; ?> />
<input type="hidden" name="month" value=<?php echo $month; ?> />
<input type="hidden" name="day" value=<?php echo $day; ?> />
<input type="hidden" name="startStamp" value=<?php echo $startDate; ?> />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							<?php echo JText::_('TIMESHEET'); ?>
						</td>
						<td align="middle" nowrap class="outer_table_heading">
							<?php
								$sdStr = date(JText::_('DFMT_MONTH_DAY_YEAR'),$startDate);
								//just need to go back 1 second most of the time, but DST 
								//could mess things up, so go back 6 hours...
								$edStr = date(JText::_('DFMT_MONTH_DAY_YEAR'),$endDate - 6*60*60);
								echo ucfirst(JText::_('WEEK')).": $sdStr - $edStr"; 
							?>
						</td>
						<td>
								<input id="date1" name="date1" type="text" size="25" onclick="javascript:NewCssCal('date1', 'ddmmmyyyy')" 
								value="<?php echo date("d-M-Y",$startDate); ?>" />
								</td>
								<td align="center" nowrap="nowrap" class="outer_table_heading">
								<input id="sub" type="submit" name="Change Date" value="<?php echo JText::_('CHANGE_DATE') ?>"></input>
								</td>
							
						</td>
						<td align="right" nowrap>
							<!--prev / next buttons used to be here -->
						</td>
						<td align="right" nowrap>
							<input type="button" name="saveButton" id="saveButton" value="<?php echo ucwords(JText::_('SAVE_CHANGES'))?>" disabled="true" onClick="validate();" />
						</td>
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
							print "<input type=\"hidden\" id=\"minsinday_".($i+1)."\" value=\"$minsinday\" />";
							print
								"<td align=\"center\" width=\"65\">" .
								"<table width=\"65\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>" .
								"<td class=\"inner_table_column_heading\" align=\"center\">" .
								"$currentDayStr<br>" .
								//Output the numerical date in the form of day of the month
								date("d", $currentDayDate) .
								"</td></tr></table></td>\n";
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
					<tr>
<?php

	//debug
	//$startDateStr = strftime("%D", $startDate);
	//$endDateStr = strftime("%D", $endDate);
	//print "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";
 require("include/tsx/class.Pair.php");

	class TaskInfo extends Pair {
		var $clientId;
		var $projectId;
		var $projectTitle;
		var $taskName;
		var $workDescription;

		function TaskInfo($value1, $value2, $projectId, $projectTitle, $taskName, $workDescription) {
			parent::Pair($value1, $value2);
			$this->projectId = $projectId;
			$this->projectTitle = $projectTitle;
			$this->taskName = $taskName;
			$this->workDescription = $workDescription;
		}
	}

	function printSpaceColumn() {
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}

	/*=======================================================================
	 ==================== Function PrintFormRow =============================
	 =======================================================================*/

	// taskId = $matchedPair->value1, daysArray = $matchedPair->value2
	// $allTasksDayTotals = int[7] and sums up the minutes for all tasks at one day
	// usage: provide an index to generate an empty row or ALL parameters to prefill the row
	function printFormRow($rowIndex, $layout, $projectId = "", $taskId = "", $workDescription = "", $startDate = null, $daysArray = NULL) {
		// print project, task and optionally work description
		global $allTasksDayTotals; //global because of PHP4 thing about passing by reference?
		$clientId="";
		?>
		<tr id="row<?php echo $rowIndex; ?>">
			<td class="calendar_cell_middle" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<?php
					switch ($layout) {
						case "no work description field":
							?>
							<td align="left" style="width:33%;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:33%;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:33%;">
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<?php
							break;

						case "big work description field":
							// big work description field
							?>
							<td align="left" style="width:100px;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:160px;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
								<br>
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo $workDescription; ?></textarea>
							</td>
							<?php
							break;

						case "small work description field":
						default:
							// small work description field = default layout
							?>
							<td align="left" style="width:100px;">
								<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $clientId; ?>" />
								<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:100px;">
								<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $projectId; ?>" />
								<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:140px;">
								<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $taskId; ?>" />
								<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<input type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onChange="onChangeWorkDescription(this.id);" value="<?php echo $workDescription; ?>" style="width: 100%;" />
							</td>
							<?php
							break;
					}

				?>
				</tr>
			</table>
		</td>
		<?php

		printSpaceColumn();

		$weeklyTotal = 0;
		$isEmptyRow = ($daysArray == null);

		//print_r($daysArray); print "<br>";

		//print hours and minutes input field for each day

		for ($currentDay = 0; $currentDay < 7; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";

			//while we are printing times set the style
			print "<span class=\"task_time_small\">";

			//declare current days vars
			$curDaysTotal = 0;
			$curDaysHours = "";
			$curDaysMinutes = "";

			// if there is an $daysArray calculate current day's minutes and hours

			if (!$isEmptyRow) {
				$currentDayArray = $daysArray[$currentDay];

				foreach ($currentDayArray as $taskDuration) {
					$curDaysTotal += $taskDuration;
				}
				$curDaysHours = floor($curDaysTotal / 60 );
				$curDaysMinutes = $curDaysTotal - ($curDaysHours * 60);
			}

			// write summary and totals of this row

			//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay+1);
			$disabled = $isEmptyRow?'disabled="disabled" ':'';

			print "<span nowrap><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$curDaysHours\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled />".JText::_('HR')."</span>";
			print "<span nowrap><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$curDaysMinutes\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled />".JText::_('MN')."</span>";

			//close the times class
			print "</span>";

			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $curDaysTotal;

			// add this days total to the all tasks total for this day
			// if an array is provided by the caller
			if ($allTasksDayTotals != null) {
				$allTasksDayTotals[$currentDay] += $curDaysTotal;
			}
		}

		printSpaceColumn();

		//format the weekly total
		$weeklyTotalStr = Common::formatMinutes($weeklyTotal);

		//print the total column
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";

		printSpaceColumn();

		// print delete button
		print "<td class=\"calendar_delete_cell\" class=\"subtotal\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>";

		//end the row
		print "</tr>";
	}

	/*=======================================================================
	 ================ end Function PrintFormRow =============================
	 =======================================================================*/

	// Get the Weekly user data.
	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$order_by_str = "$CLIENT_TABLE.organisation, $PROJECT_TABLE.title, $TASK_TABLE.name";
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
	$allTasksDayTotals = array(0,0,0,0,0,0,0); //totals for each day

/*	$previousTaskId = -1;
	$thisTaskId = -1;
	$columnDay = -1;
	$columnStartDate = $startDate;*/


//iterate through the structured array
	$count = count($structuredArray);
	unset($matchedPair);
	for ($rowIndex = 0; $rowIndex<$count; $rowIndex++) {
		$matchedPair = &$structuredArray[$rowIndex];

		printFormRow($rowIndex, $layout,
					 $matchedPair->projectId,
					 $matchedPair->value1,
					 $matchedPair->workDescription,
					 $startDate,
					 $matchedPair->value2,
					 $allTasksDayTotals);


		//store the previous task and project ids
		$previousTaskId = $matchedPair->value1;
		$previousProjectId = $matchedPair->projectId;
	}

	/////////////////////////////////////////
	//add an extra row for new data entry
	/////////////////////////////////////////

	printFormRow($count, $layout, -1, -1);

	//store a hidden form field containing the number of existing rows
	print "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"" . $count . "\" />";

	//store a hidden form field containing the total number of rows
	print "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($count+1) . "\" />";

	////////////////////////////////////////////////////
	//Changes reequired to enter data on form -define 10 entry rows

//	for ($i=0; $i<10; $i

	////////////////////////////////////////////////////

	//create a new totals row
	print "<tr id=\"totalsRow\">\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">".ucwords(JText::_('TOTAL')." ".JText::_('HOURS')).":</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
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

		</td>
	</tr>
</table>

</form>
