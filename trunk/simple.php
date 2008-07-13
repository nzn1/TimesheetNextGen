<?php
//$Header: /cvsroot/tsheet/timesheet.php/simple.php,v 1.7 2005/05/23 05:39:39 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
require("class.Pair.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclSimple')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclSimple'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

//define the command menu
include("timesheet_menu.inc");

//bug fix - we must display all projects
$proj_id = 0;

// Check project assignment.
if ($proj_id != 0) { // id 0 means 'All Projects'
	list($qh, $num) = dbQuery("SELECT * FROM $ASSIGNMENTS_TABLE WHERE proj_id='$proj_id' AND username='$contextUser'");
	if ($num < 1)
		errorPage("You cannot access this project, because you are not assigned to it.");
}
else
	$task_id = 0;

//a useful constant
define("A_DAY", 24 * 60 * 60);

//get the passed date (context date)
$todayDate = mktime(0, 0, 0,$month, $day, $year);
$todayYear = date("Y", $todayDate);
$todayMonth = date("n", $todayDate);
$todayDay = date("j", $todayDate);
$dateValues = getdate($todayDate);
$todayDayOfWeek = $dateValues["wday"];

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = getWeekStartDay();

$daysToMinus = $todayDayOfWeek - $startDayOfWeek;
if ($daysToMinus < 0)
	$daysToMinus += 7;

//work out the start date by minusing enough seconds to make it the start day of week
$startDate = $todayDate - $daysToMinus * A_DAY;
$startYear = date("Y", $startDate);
$startMonth = date("n", $startDate);
$startDay = date("j", $startDate);

//work out the end date by adding 7 days
$endDate = $startDate + 7 * A_DAY;
$endYear = date("Y", $endDate);
$endMonth = date("n", $endDate);
$endDay = date("j", $endDate);

// Calculate the previous week
$previousWeekDate = $todayDate - A_DAY * 7;
$previousWeekYear = date("Y", $previousWeekDate);
$previousWeekMonth = date("n", $previousWeekDate);
$previousWeekDay = date("j", $previousWeekDate);

//calculate next week
$nextWeekDate = $todayDate + A_DAY * 7;
$nextWeekYear = date("Y", $nextWeekDate);
$nextWeekMonth = date("n", $nextWeekDate);
$nextWeekDay = date("j", $nextWeekDate);

//get the configuration of timeformat and layout 
list($qh2, $numq) = dbQuery("SELECT timeformat, simpleTimesheetLayout FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qh2);
$layout = $configData['simpleTimesheetLayout'];

//build the database query
$query = "SELECT date_format(start_time,'%d') AS day_of_month, ";

if ($configData["timeformat"] == "12")
	$query .= "date_format(end_time, '%l:%i%p') AS endd, date_format(start_time, '%l:%i%p') AS start, ";
else
	$query .= "date_format(end_time, '%k:%i') AS endd, date_format(start_time, '%k:%i') AS start, ";

$query .= "unix_timestamp(end_time) - unix_timestamp(start_time) AS diff_sec, ".
						"end_time AS end_time_str, ".
						"start_time AS start_time_str, ".
						"unix_timestamp(start_time) AS start_time, ".
						"unix_timestamp(end_time) AS end_time, ".
						"$PROJECT_TABLE.title AS projectTitle, " .
						"$TASK_TABLE.name AS taskName, " .
						"$TIMES_TABLE.proj_id, " .
						"$TIMES_TABLE.task_id, " .
						"$TIMES_TABLE.log_message " .

						"FROM $TIMES_TABLE, $TASK_TABLE, $PROJECT_TABLE WHERE " .
						"$PROJECT_TABLE.proj_id = $TIMES_TABLE.proj_id AND " .
						"uid='$contextUser' AND ";

if ($proj_id > 0) //otherwise want all records no matter what project
	$query .= "$TIMES_TABLE.proj_id=$proj_id AND ";

$query .= "$TASK_TABLE.task_id = $TIMES_TABLE.task_id AND ".
			"((start_time >= '$startYear-$startMonth-$startDay 00:00:00' AND " .
			"start_time < '$endYear-$endMonth-$endDay 00:00:00') ".
			"OR (end_time >= '$startYear-$startMonth-$startDay 00:00:00' AND " .
			"end_time < '$endYear-$endMonth-$endDay 00:00:00') ".
			"OR (start_time < '$startYear-$startMonth-$startDay 00:00:00' AND end_time >= '$endYear-$endMonth-$endDay 00:00:00')) ".
			"ORDER BY proj_id, start_time";

?>
<html>
<head>
<title>Simple Weekly Timesheet for <? echo "$contextUser" ?></title>
<?
include ("header.inc");
?>
<script language="Javascript">
	//define the hash table
	var projectTasksHash = {};
<?php
//get all of the projects and put them into the hashtable
$getProjectsQuery = "SELECT $PROJECT_TABLE.proj_id, $PROJECT_TABLE.title, $PROJECT_TABLE.client_id, ".
													"$CLIENT_TABLE.client_id, $CLIENT_TABLE.organisation FROM ".
													"$PROJECT_TABLE, $ASSIGNMENTS_TABLE, $CLIENT_TABLE WHERE ".
													"$PROJECT_TABLE.proj_id=$ASSIGNMENTS_TABLE.proj_id AND ".
													"$ASSIGNMENTS_TABLE.username='$contextUser' AND ".
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
$getTasksQuery = "SELECT $TASK_TABLE.proj_id, $TASK_TABLE.task_id, $TASK_TABLE.name FROM ".
											"$TASK_TABLE, $TASK_ASSIGNMENTS_TABLE WHERE ".
											"$TASK_TABLE.task_id = $TASK_ASSIGNMENTS_TABLE.task_id AND ".
											"$TASK_ASSIGNMENTS_TABLE.username='$contextUser'";

list($qh4, $num4) = dbQuery($getTasksQuery);
//iterate through results
for ($i=0; $i<$num4; $i++) {
	//get the current record
	$data = dbResult($qh4, $i);
	print("if (projectTasksHash['" . $data["proj_id"] . "'] != null)\n");
	print("  projectTasksHash['" . $data["proj_id"] . "']['tasks']['" . $data["task_id"] . "'] = '" . addslashes($data["name"]) . "';\n");
}

?>

	//function to populate existing rows with project and task names and select the right one in each
	function populateExistingSelects() {

		//get the number of existing rows
		var existingRows = parseInt(document.getElementById('existingRows').value);
		//alert('There are ' + existingRows + ' existing rows');

		//iterate to plus one to do the additional row
		for (var i=0; i<=existingRows; i++) {
			//alert('existing row ' + i);

			//get the project and task id for this row
			var projectId = document.getElementById('project_row' + i).value;
			var taskId = document.getElementById('task_row' + i).value;

			//get the selects
			var projectSelect = document.getElementById('projectSelect_row' + i);
			var taskSelect = document.getElementById('taskSelect_row' + i);

			//add None to the selects
			projectSelect.options[projectSelect.options.length] = new Option('None', '-1');
			taskSelect.options[taskSelect.options.length] = new Option('None', '-1');

			//add the projects
			var clientId = -1;
			for (var key in projectTasksHash) {
				if (projectTasksHash[key]['clientId'] != clientId) {
					projectSelect.options[projectSelect.options.length] = new Option('[' + projectTasksHash[key]['clientName'] + ']', -1);
					clientId = projectTasksHash[key]['clientId'];
				}

				projectSelect.options[projectSelect.options.length] = new Option(String.fromCharCode(160, 160) + projectTasksHash[key]['name'], key);

				// Add for select last project like default project
				if (projectId == -1)
					projectId=key;
				if (key == projectId)
					projectSelect.options[projectSelect.options.length-1].selected = true;
			}

			if (projectId != -1) {
				//add the tasks
				var thisProjectTasks = projectTasksHash[projectId]['tasks'];
				for (taskKey in thisProjectTasks) {
					taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

					if (taskKey == taskId)
						taskSelect.options[taskSelect.options.length-1].selected = true;
				}
			}
		}
	}

	function populateTaskSelect(row, projectId, selectedTaskId) {
		//get the task select for this row
		var taskSelect = document.getElementById('taskSelect_row' + row);

		//add the tasks
		var thisProjectTasks = projectTasksHash[projectId]['tasks'];
		for (var taskKey in thisProjectTasks) {
			taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

			if (taskKey == selectedTaskId)
				taskSelect.options[taskSelect.options.length-1].selected = true;
		}
	}

	function clearTaskSelect(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		for (var i=1; i<taskSelect.options.length; i++)
			taskSelect.options[i] = null;

		//set the length back to 1
		taskSelect.options.length = 1;

		//select the 'None' option
		taskSelect.options[0].selected = true;

		onChangeTaskSelectRow(row);
	}

	function clearWorkDescriptionField(row) {	
		descField = document.getElementById("description_row" + row);
		descField.value = "";
	}

	function rowFromIdStr(idStr) {
		var pos1 = idStr.indexOf("row") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function colFromIdStr(idStr) {
		var pos1 = idStr.indexOf("col") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function onChangeProjectSelect(idStr) {
		row = rowFromIdStr(idStr);
		clearTaskSelect(row);

		//get the project id
		var projectSelect = document.getElementById('projectSelect_row' + row);
		var projectId = projectSelect.options[projectSelect.selectedIndex].value;

		if (projectId != -1)
			//populate the select with tasks for this project
			populateTaskSelect(row, projectId);

			setDirty();
	}

	function onChangeTaskSelect(idStr) {
		onChangeTaskSelectRow(rowFromIdStr(idStr));
	}

	function onChangeTaskSelectRow(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		if (taskSelect.options[0].selected == true) {
			//disable fields
			for (var i=1; i<=7; i++) {
				document.getElementById('hours_row' + row + '_col' + i).disabled = true;
				document.getElementById('mins_row' + row + '_col' + i).disabled = true;
			}
		}
		else {
			//get the total number of rows
			var totalRows = parseInt(document.getElementById('totalRows').value);
			//alert('change task droplist on row ' + row + ', totalRows=' + totalRows);

			if (row == (totalRows-1)) {
				//get the row to copy
				var tempNode = document.getElementById('row' + row);

				//clone the row
				var newNode = tempNode.cloneNode(true);

				//setup the pattern to match
				var rowRegex = new RegExp("row(\\d+)");

				//iterate through with dom and replace all name and id attributes with regexp
				replaceIdAndNameAttributes(newNode, rowRegex, totalRows);

				//increment totalRows by one
				//alert('totalRows was ' + document.getElementById('totalRows').value);
				document.getElementById('totalRows').value = parseInt(document.getElementById('totalRows').value) + 1;
				//alert('totalRows is now ' + document.getElementById('totalRows').value);

				//get the totals node
				var totalsNode = document.getElementById('totalsRow');

				//insert the new node before the totals node
				totalsNode.parentNode.insertBefore(newNode, totalsNode);

				//clear the task select
				clearTaskSelect(totalRows);

				// clear the work description field
				clearWorkDescriptionField(totalRows);
				
				//select default project
				var oldProjectSelect = document.getElementById('projectSelect_row' + row);
				var newProjectSelect = document.getElementById('projectSelect_row' + (row+1));
				newProjectSelect.options[oldProjectSelect.selectedIndex].selected = true;

				//repopulate task
				var projectId = newProjectSelect.options[newProjectSelect.selectedIndex].value;
				populateTaskSelect(row+1, projectId);
				
			}

			//enable fields
			for (var i=1; i<=7; i++) {
				document.getElementById('hours_row' + row + '_col' + i).disabled = false;
				document.getElementById('mins_row' + row + '_col' + i).disabled = false;
			}
		}
		setDirty();
	}

	function onChangeWorkDescription(idStr) {
		setDirty();
	}
	
	//clear row and make it invisible
	function onDeleteRow(idStr) {
		var row = rowFromIdStr(idStr);
		var tr = document.getElementById('row' + row)
		
		// clear the task select				
		clearTaskSelect(row);

		// clear the work description field
		clearWorkDescriptionField(row);
				
		// clear hours and minutes
		for (var i=1; i<=7; i++) {
			document.getElementById("hours_row" + row + "_col" + i).value = "";
			document.getElementById("mins_row" + row + "_col" + i).value = "";
			recalculateCol(i);
		}
		
		tr.style.display = "none";
	}
	
	function replaceIdAndNameAttributes(node, rowRegex, rowNumber) {
		while (node != null) {
			if (node.getAttribute != null && node.getAttribute("id") != null)
				node.setAttribute("id", node.getAttribute("id").replace(rowRegex, "row" + rowNumber));
			if (node.getAttribute != null && node.getAttribute("name") != null)
				node.setAttribute("name", node.getAttribute("name").replace(rowRegex, "row" + rowNumber));

			//call this function recursively for children
			// did not to work recursely with if statement like it was:
			// if (node.firstChild != null && node.firstChild.tagName != null)
			if (node.firstChild != null)
				replaceIdAndNameAttributes(node.firstChild, rowRegex, rowNumber);

			//do the same for the next sibling
			node = node.nextSibling;
		}
	}

	function recalculateRowCol(idStr) {
		recalculateRow(rowFromIdStr(idStr));
		recalculateCol(colFromIdStr(idStr),idStr);
		setDirty();
	}

	function recalculateRow(row) {
		var totalMins = 0;
		for (i=1; i<=7; i++) {
			if (((hours=parseInt(document.getElementById("hours_row" + row + "_col" + i).value)) > 23) ) {
				alert("Too many hours...");
				document.getElementById("hours_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("mins_row" + row + "_col" + i).select();  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				return false;
			}
			hours = parseInt(document.getElementById("hours_row" + row + "_col" + i).value);
			if (!isNaN(hours))
				totalMins += hours * 60;
			if ((mins = parseInt(document.getElementById("mins_row" + row + "_col" + i).value)) > 59) {
				alert("Too many minutes...");
				document.getElementById("mins_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				document.getElementById("mins_row" + row + "_col" + i).select();  //=true;
				return false;
			}
			mins = parseInt(document.getElementById("mins_row" + row + "_col" + i).value);
			if (!isNaN(mins))
				totalMins += mins;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the total cell
		var totalCell = document.getElementById("subtotal_row" + row);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function recalculateCol(col,idStr) {
		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);

		var totalMins = 0;
		var row="";
		for (var i=0; i<totalRows; i++) {
			hours = parseInt(document.getElementById("hours_row" + i + "_col" + col).value);
			if (!isNaN(hours))
				totalMins += hours * 60;
			mins = parseInt(document.getElementById("mins_row" + i + "_col" + col).value);
			if (!isNaN(mins))
				totalMins += mins;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);
		if (hours >24) {
			alert("Too many hours... Check your column");
			row=rowFromIdStr(idStr);
			document.getElementById("hours_row" + row + "_col" + col).value="";  //=true;
			document.getElementById("mins_row" + row + "_col" + col).select();  //=true;
			document.getElementById("hours_row" + row + "_col" + col).select();  //=true;
			recalculateRow(rowFromIdStr(idStr));
		return false;
		}

		//get the total cell
		var totalCell = document.getElementById("subtotal_col" + col);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';

		recalculateGrandTotal();
	}

	function recalculateGrandTotal() {
		var totalMins = 0;
		for (var i=1; i<=7; i++) {
			var currentInnerHTML = document.getElementById("subtotal_col" + i).innerHTML;
			//get the hours
			hPos = currentInnerHTML.indexOf('h');
			hours = parseInt(currentInnerHTML.substring(0, hPos));
			if (!isNaN(hours))
				totalMins += hours * 60;

			//get the minutes
			mPos = currentInnerHTML.indexOf('m');
			mins = parseInt(currentInnerHTML.substring(hPos+7, mPos));
			if (!isNaN(mins))
				totalMins += mins;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the grand total cell
		var grandTotalCell = document.getElementById("grand_total");
		grandTotalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function setDirty() {
		document.getElementById("saveButton").disabled = false;
	}

	function validate() {
		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);

		//iterate through rows
		for (var i=0; i<totalRows; i++) {
			//iterate through cols
			for (var j=1; j<=7; j++) {
				hours = parseInt(document.getElementById("hours_row" + i + "_col" + j).value);
				if (document.getElementById("hours_row" + i + "_col" + j).value != "" && isNaN(hours) || hours > 23) {
					alert('The hours field in row ' + i + ' column ' + j + ' must be a number between 0 and 23.');
					document.getElementById("hours_row" + i + "_col" + j).focus();
					return;
				}

				mins = parseInt(document.getElementById("mins_row" + i + "_col" + j).value);
				if (document.getElementById("mins_row" + i + "_col" + j).value != "" && isNaN(mins) || mins > 59) {
					alert('The minutes field in row ' + i + ' column ' + j + ' must be a number between 0 and 59.');
					document.getElementById("mins_row" + i + "_col" + j).focus();
					return;
				}
			}
		}

		document.theForm.submit();
	}

</script>
</head>
<?php
echo "<body width=\"100%\" height=\"100%\" onLoad=\"populateExistingSelects();\"";
include ("body.inc");
if (isset($popup))
	echo "onLoad=window.open(\"popup.php?proj_id=$proj_id&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
echo ">\n";

include ("banner.inc");
?>
<form name="theForm" action="simple_action.php" method="post">
<input type="hidden" name="year" value=<? echo $year; ?>>
<input type="hidden" name="month" value=<? echo $month; ?>>
<input type="hidden" name="day" value=<? echo $day; ?>>
<input type="hidden" name="startYear" value=<? echo $startYear; ?>>
<input type="hidden" name="startMonth" value=<? echo $startMonth; ?>>
<input type="hidden" name="startDay" value=<? echo $startDay; ?>>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading">
							Week Start: <? echo date('D F j, Y',mktime(0,0,0,$startMonth, $startDay, $startYear)); ?>
						</td>
						<td align="right" nowrap>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?proj_id=<?echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<?echo $previousWeekYear ?>&month=<? echo $previousWeekMonth ?>&day=<? echo $previousWeekDay ?>" class="outer_table_action">Prev</a>
							<a href="<? echo $_SERVER["PHP_SELF"]; ?>?proj_id=<? echo $proj_id; ?>&task_id=<? echo $task_id; ?>&year=<? echo $nextWeekYear ?>&month=<? echo $nextWeekMonth ?>&day=<? echo $nextWeekDay ?>" class="outer_table_action">Next</a>
						</td>
						<td align="right" nowrap>
							<input type="button" name="saveButton" id="saveButton" value="Save Changes" disabled="true" onClick="validate();" />
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<? include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading" align="center">
							Project / Task<?php if(strstr($layout, 'no work description') == '') echo ' / Work Description'; ?>
						</td>
						<td align="center" width="2">&nbsp;</td>
						<?php
						//print the days of the week
						$currentDayDate = $startDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%a", $currentDayDate);
							$currentDayDate += A_DAY;
							print
								"<td align=\"center\" width=\"65\">" .
								"<table width=\"65\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>" .
								"<td class=\"inner_table_column_heading\" align=\"center\">" .
								"$currentDayStr" .
								"</td></tr></table></td>\n";
						}
						?>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">Total</td>
						<td align="center" width="2">&nbsp;</td>
						<td class="inner_table_column_heading" align="center" width="50">delete</td>
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
	
	// taskId = $matchedPair->value1, daysArray = $matchedPair->value2
	// $allTasksDayTotals = int[7] and sums up the minutes for all tasks at one day 
	// usage: provide an index to generate an empty row or ALL parameters to prefill the row
	function printFormRow($rowIndex, $layout, $projectId = "", $taskId = "", $workDescription = "", $startDate = null, $daysArray = null, &$allTasksDayTotals = null) { 
	
		// print project, task and optioinally work description

		?>
		<tr id="row<?php echo $rowIndex; ?>">
			<td class="calendar_cell_middle" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<?php 
					switch ($layout) {
						case "no work description field":
							?>
							<td align="left" style="width:50%;">
								<input type="hidden" id="project_row<? echo $rowIndex; ?>" name="project_row<? echo $rowIndex; ?>" value="<? echo $projectId; ?>" />
								<select id="projectSelect_row<? echo $rowIndex; ?>" name="projectSelect_row<? echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:50%;">
								<input type="hidden" id="task_row<? echo $rowIndex; ?>" name="task_row<? echo $rowIndex; ?>" value="<? echo $taskId; ?>" />
								<select id="taskSelect_row<? echo $rowIndex; ?>" name="taskSelect_row<? echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<?php
							break;
						
						case "big work description field":
							// big work description field
							?>
							<td align="left" style="width:160px;">
								<input type="hidden" id="project_row<? echo $rowIndex; ?>" name="project_row<? echo $rowIndex; ?>" value="<? echo $projectId; ?>" />
								<select id="projectSelect_row<? echo $rowIndex; ?>" name="projectSelect_row<? echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
								<br/>
								<input type="hidden" id="task_row<? echo $rowIndex; ?>" name="task_row<? echo $rowIndex; ?>" value="<? echo $taskId; ?>" />
								<select id="taskSelect_row<? echo $rowIndex; ?>" name="taskSelect_row<? echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<textarea rows="2" style="width:100%;" id="description_row<? echo $rowIndex; ?>" name="description_row<? echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><? echo $workDescription; ?></textarea>
							</td>
							<?php
							break;
							
						case "small work description field":
						default:
							// small work description field = default layout
							?>
							<td align="left" style="width:100px;">
								<input type="hidden" id="project_row<? echo $rowIndex; ?>" name="project_row<? echo $rowIndex; ?>" value="<? echo $projectId; ?>" />
								<select id="projectSelect_row<? echo $rowIndex; ?>" name="projectSelect_row<? echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:160px;">
								<input type="hidden" id="task_row<? echo $rowIndex; ?>" name="task_row<? echo $rowIndex; ?>" value="<? echo $taskId; ?>" />
								<select id="taskSelect_row<? echo $rowIndex; ?>" name="taskSelect_row<? echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
							</td>
							<td align="left" style="width:auto;">
								<input type="text" id="description_row<? echo $rowIndex; ?>" name="description_row<? echo $rowIndex; ?>" onChange="onChangeWorkDescription(this.id);" value="<? echo $workDescription; ?>" style="width: 100%;" />
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
		
		// print hours and minutes input field for each day
 
		for ($currentDay = 0; $currentDay < 7; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";

			//while we are printing times set the style
			print "<span class=\"task_time_small\">";

			//create a flag for empty cell
			$emptyCell = true;
			
			//declare todays vars
//			$currentDay++;
			$todaysTotal = 0;
			$todaysHours = "";
			$todaysMinutes = "";

			// if there is an $daysArray calculate todays minutes and hours

			if (!$isEmptyRow) {
				$currentDayArray = $daysArray[$currentDay];
				$todaysStartTime = $startDate + $currentDay * A_DAY;
				$todaysEndTime = $startDate + ($currentDay + 1) * A_DAY;
				
				for ($j=0; $j<4; $j++) {
					$currentTaskEntriesArray = $currentDayArray[$j];
	
					//print "C" . count($currentTaskEntriesArray) . " ";
	
					//iterate through the task entries
					foreach ($currentTaskEntriesArray as $currentTaskEntry) {
						//is the cell empty?
						if ($emptyCell)
							//the cell is not empty since we found a task entry
							$emptyCell = false;
						//else
							//print a break for the next entry
							//print "<br>";
	
						//format printable times
						$formattedStartTime = $currentTaskEntry["start"];
						$formattedEndTime = $currentTaskEntry["endd"];
	
						switch($j) {
						case 0: //tasks which started on a previous day and finish on a following day
							//print "...-...";
							$todaysTotal += A_DAY;
							break;
						case 1: //tasks which started on a previous day and finish today
							//print "...-" . $formattedEndTime;
							$todaysTotal += $currentTaskEntry["end_time"] - $todaysStartTime;
							break;
						case 2: //tasks which started and finished today
							//print $formattedStartTime . "-" . $formattedEndTime;
							$todaysTotal += $currentTaskEntry["end_time"] - $currentTaskEntry["start_time"];
							break;
						case 3: //tasks which started today and finish on a following day
							//print $formattedStartTime . "-...";
							$todaysTotal += $todaysEndTime - $currentTaskEntry["start_time"];
							break;
						default:
							print "error";
						}
					}
				}

				$todaysHours = floor($todaysTotal / 60 / 60);
				$todaysMinutes = ($todaysTotal - ($todaysHours * 60 * 60)) / 60;
			}
			
			// write summary and totals of this row
			
			//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay+1);
			$disabled = $isEmptyRow?'disabled="true" ':'';
			
			print "<span nowrap><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$todaysHours\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled/>h</span>";
			print "<span nowrap><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$todaysMinutes\" onChange=\"recalculateRowCol(this.id)\" onKeyDown=\"setDirty()\" $disabled/>m</span>";

			//close the times class
			print "</span>";

			/*if (!$emptyCell) {
				//print todays total
				$todaysTotalStr = formatSeconds($todaysTotal);
				print "<br><span class=\"task_time_total_small\">$todaysTotalStr</span>";
			}*/

			//end the column
			print "</td>";

			//add this days total to the weekly total
			$weeklyTotal += $todaysTotal;

			// add this days total to the all tasks total for this day 
			// if an array is provided by the caller
			if ($allTasksDayTotals != null) {
				$allTasksDayTotals[$currentDay] += $todaysTotal;
			}
		}

		printSpaceColumn();
		
		//format the weekly total
		$weeklyTotalStr = formatSeconds($weeklyTotal);

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

	// Get the Weekly data.
	list($qh5, $num5) = dbQuery($query);

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
	for ($i=0; $i<$num5; $i++) {
		//get the record for this task entry
		$data = dbResult($qh5,$i);

		//Due to a bug in mysql with converting to unix timestamp from the string,
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$data["start_time"] = strtotime($data["start_time_str"]);
		$data["end_time"] = strtotime($data["end_time_str"]);

		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_time"];
		$currentTaskEndDate = $data["end_time"];
		$currentTaskName = stripslashes($data["taskName"]);
		$currentProjectTitle = stripslashes($data["projectTitle"]);
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription = $data["log_message"];

		//debug
		//print "<p>taskId:$currentTaskId '$data[taskName]', start time:$data[start_time_str], end time:$data[end_time_str]</p>";


		//find the current task id in the array
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
			$matchedPair = new TaskInfo($currentTaskId, $daysArray,
										$currentProjectId, $currentProjectTitle,
										$currentTaskName, $currentWorkDescription);

			//add the matched pair to the structured array
			$structuredArray[] = $matchedPair;

			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$structuredArray[count($structuredArray)-1];

			//print "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}

		//iterate through the days array
		for ($k=0; $k<7; $k++) {

			//$dayStart = strftime("%D %T", $startDate + $k * A_DAY);
			//$dayEnd = strftime("%D %T", $startDate + ($k + 1) * A_DAY);
			//print "<p>DAY start: $dayStart, DAY end: $dayEnd</p>";

			//work out some booleans
			$startsOnPreviousDay = ($currentTaskStartDate < ($startDate + $k * A_DAY));
			$endsOnFollowingDay = ($currentTaskEndDate >= ($startDate + ($k + 1) * A_DAY));
			$startsToday = ($currentTaskStartDate >= ($startDate + $k * A_DAY) &&
													$currentTaskStartDate < ($startDate + ($k + 1) * A_DAY));
			$endsToday = ($currentTaskEndDate >= ($startDate + $k * A_DAY) &&
													$currentTaskEndDate < ($startDate + ($k + 1) * A_DAY));

			//$currentTaskStartDateStr = strftime("%D %T", $currentTaskStartDate);
			//$currentTaskEndDateStr = strftime("%D %T", $currentTaskEndDate);
			//print "<p>task start: $currentTaskStartDateStr task end: $currentTaskEndDateStr</p>";

			//print "<p>startsOnPreviousDay=$startsOnPreviousDay, endsOnFollowingDay=$endsOnFollowingDay" .
			//	", startsToday=$startsToday, endsToday=$endsToday</p>";

			//does it start before this day and end after this day?
			if ($startsOnPreviousDay && $endsOnFollowingDay)
				//add this task entry to the array for index 0
				$matchedPair->value2[$k][0][] = $data;
			//does it start before this day and end on this day?
			else if ($startsOnPreviousDay && $endsToday)
				//add this task entry to the arry for index 1
				$matchedPair->value2[$k][1][] = $data;
			//does it start and end on this day?
			else if ($startsToday && $endsToday)
				//add this task entry to the array for index 2
				$matchedPair->value2[$k][2][] = $data;
			//does it start on this day and end on a following day
			else if ($startsToday && $endsOnFollowingDay)
				//add this task entry to the array for index 3
				$matchedPair->value2[$k][3][] = $data;
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
	
	printFormRow($count, $layout, 1, -1);
	
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
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">Total Hours:</td>\n";
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";

	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
		$col++;
		$grandTotal += $currentAllTasksDayTotal;
		$formattedTotal = formatSeconds($currentAllTasksDayTotal);
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">$formattedTotal</span></td>";
	}

	//print grand total
	$formattedGrandTotal = formatSeconds($grandTotal);
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\" id=\"grand_total\">$formattedGrandTotal</span></td>";
	print "</tr>";

?>

				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<? include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>
<?
include ("footer.inc");
?>
</body>
</html>

