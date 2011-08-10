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
$startDay =  strtotime(date("d",$todayStamp) . " -$daysToMinus days");
$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");
LogFile::write("\n\n\n\n\n\n\n\n\n\nStart of new execution. startDate: ". $todayStamp. "\n\n");
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
echo "var None_option = '".JText::_('VALUE_NONE')."';";
echo"</script>";
echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/js/simple.js\"></script>";

PageElements::setHead(ob_get_contents());
PageElements::setTheme('newcss');
ob_end_clean();
PageElements::setBodyOnLoad('populateExistingSelects();');

/*=======================================================================
==================== Function PrintFormRow =============================
=======================================================================*/

// taskId = $matchedPair->value1, daysArray = $matchedPair->value2

// usage: provide an index to generate an empty row or ALL parameters to prefill the row
function printFormRow($rowIndex, $layout, $data) {
	// print project, task and optionally work description
	//LogFile::write("printFormRow Layout: ". $layout);
	?>
	<tr id="row<?php echo $rowIndex; ?>">

			<?php
				switch ($layout) {
					case "no work description field":
						?>
						<td align="left" style="width:33%;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:33%;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:33%;">
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
						</td>
						<?php
						break;

					case "big work description field":
						// big work description field
						?>
						<td align="left" style="width:100px;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:160px;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
							<br/>
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:auto;">
							<textarea rows="2" style="width:100%;" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onKeyUp="onChangeWorkDescription(this.id);"><?php echo $data['log_message']; ?></textarea>
						</td>
						<?php
						break;

					case "small work description field":
					default:
						// small work description field = default layout
						?>
						<td align="left" style="width:100px;">
							<input type="hidden" id="client_row<?php echo $rowIndex; ?>" name="client_row<?php echo $rowIndex; ?>" value="<?php echo $data['client_id']; ?>" />
							<select id="clientSelect_row<?php echo $rowIndex; ?>" name="clientSelect_row<?php echo $rowIndex; ?>" onChange="onChangeClientSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:100px;">
							<input type="hidden" id="project_row<?php echo $rowIndex; ?>" name="project_row<?php echo $rowIndex; ?>" value="<?php echo $data['proj_id']; ?>" />
							<select id="projectSelect_row<?php echo $rowIndex; ?>" name="projectSelect_row<?php echo $rowIndex; ?>" onChange="onChangeProjectSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:140px;">
							<input type="hidden" id="task_row<?php echo $rowIndex; ?>" name="task_row<?php echo $rowIndex; ?>" value="<?php echo $data['task_id']; ?>" />
							<select id="taskSelect_row<?php echo $rowIndex; ?>" name="taskSelect_row<?php echo $rowIndex; ?>" onChange="onChangeTaskSelect(this.id);" style="width: 100%;" />
						</td>
						<td align="left" style="width:auto;">
							<input type="text" id="description_row<?php echo $rowIndex; ?>" name="description_row<?php echo $rowIndex; ?>" onChange="onChangeWorkDescription(this.id);" value="<?php echo $data['log_message']; ?>" style="width: 100%;" />
						</td>
						<?php
						break;
				}

	
	} // End function printFormRow
	
	function printSpaceColumn() {
		print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>";
	}
	function getAllTasksDayTotals(){
    	return $allTasksDayTotals;
  }
  function setAllTasksDayTotals($a){
    $allTasksDayTotals = $a;
  }
  
  	function finishRow($rowIndex, $colIndex, $rowTotal, $disable) {
		$allTasksDayTotals =null;
  		if($disable == "yes" )
  			$disabled = 'disabled="disabled" ';
  		else 
  			$disabled = '';
  		for ($currentDay = $colIndex; $currentDay < 8; $currentDay++) {
			//open the column
			print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";
				//while we are printing times set the style
			print "<span class=\"task_time_small\">";
				//create a string to be used in form input names
			$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
		
			print "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"0\" />";
			print "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\"". $disabled . "/>".JText::_('HR')."</span>";
			print "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\"". $disabled . "/>".JText::_('MN')."</span>";
				//close the times class
			print "</span>";
				//end the column
			print "</td>";

		}
		printSpaceColumn();
		//print the total column
		$weeklyTotalStr = Common::formatMinutes($rowTotal);
		print "<td class=\"calendar_totals_line_weekly\" valign=\"bottom\" align=\"right\" class=\"subtotal\">";
		print "<span class=\"calendar_total_value_weekly\" align=\"right\" id=\"subtotal_row" . $rowIndex . "\">$weeklyTotalStr</span></td>";
		
		printSpaceColumn();
		// print delete button
		print "<td class=\"calendar_delete_cell\" class=\"subtotal\">";
		print "<a id=\"delete_row$rowIndex\" href=\"#\" onclick=\"onDeleteRow(this.id); return false;\">x</a></td>";
	
		//end the row
		print "</tr>";
	}

	function printTime($rowIndex, $currentDay, $trans_num, $hours, $minutes) {
  		
		//open the column
		print "<td class=\"calendar_cell_middle\" valign=\"top\" align=\"left\">";

		//while we are printing times set the style
		print "<span class=\"task_time_small\">";

		//create a string to be used in form input names
		$rowCol = "_row" . $rowIndex . "_col" . ($currentDay);
		if ($trans_num ==  -1)
			$disabled = 'disabled="disabled" ';
		else 
			$disabled = '';

		print "<input type=\"hidden\" id=\"tid".$rowCol."\" name=\"tid".$rowCol."\" value=\"$trans_num\" />";
		if ($trans_num != 0) { //print a valid field 
			print "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"$hours\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('HR')."</span>";
			print "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"$minutes\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('MN')."</span>";
		}
		else { // print an empty field
			print "<span><input type=\"text\" id=\"hours" . $rowCol . "\" name=\"hours" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('HR')."</span>";
			print "<span><input type=\"text\" id=\"mins" . $rowCol . "\" name=\"mins" . $rowCol . "\" size=\"1\" value=\"\" onchange=\"recalculateRowCol(this.id)\" onkeydown=\"setDirty()\" $disabled />".JText::_('MN')."</span>";
		}
		//close the times class
		print "</span>";

		//end the column
		print "</td>";
	}
	
/*=======================================================================
================ end Function PrintFormRow =============================
=======================================================================*/
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
			<input id="date1" name="date1" type="hidden" value="<?php echo date('d-m-Y', $startDate); ?>" />
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
	<thead>
		<tr>
			<th class="inner_table_column_heading" align="center">
				<?php 
					echo JText::_('CLIENT'); 
				?>
			</th>
			<th class="inner_table_column_heading" align="center">
			<?php 
				echo JText::_('PROJECT'); 
			?>
			</th>
			<th class="inner_table_column_heading" align="center">
				<?php 
					echo JText::_('TASK'); 
				?>
			</th>
			<th class="inner_table_column_heading" align="center" width=\"25%\">
				<?php 
					if(strstr($layout, 'no work description') == '')
						echo JText::_('WORK_DESCRIPTION');
				?>
			</th>
			<?php
			// save the days of the week for later comparison
			$daysOfWeek = array(0,0,0,0,0,0,0);
			//print the days of the week
			$currentDayDate = $startDate;
			$dstadj=array();
			for ($i=0; $i<7; $i++) {
				$currentDayStr = strftime("%a", $currentDayDate);
				$dst_adjustment = Common::get_dst_adjustment($currentDayDate);
				$dstadj[]=$dst_adjustment;
				$minsinday = ((24*60*60) - $dst_adjustment)/60;
				$daysOfWeek[$i] = date("d", $currentDayDate);

				print
					"<th class=\"inner_table_column_heading\" align=\"center\" width=\"5%\">"								
					  ."<input type=\"hidden\" id=\"minsinday_".($i+1)."\" value=\"$minsinday\" />"
						."$currentDayStr<br />" .
						//Output the numerical date in the form of day of the month
						date("d", $currentDayDate) . 
					"</th>";
				$currentDayDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
			}
			?>
			<th align="center" width="2">&nbsp;</th>
			<th class="inner_table_column_heading" align="center" width="50">
				<?php echo ucfirst(JText::_('TOTAL')) ?>
			</th>
			<th align="center" width="2">&nbsp;</th>
			<th class="inner_table_column_heading" align="center" width="50">
				<?php echo ucfirst(JText::_('DELETE')) ?>
			</th>
		</tr>
	</thead>
	<tbody>
<?php

	// Get the Weekly user data.
	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$order_by_str = "".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name, ".
	//	"".tbl::getTimesTable().".start_time, " .tbl::getTimesTable().".log_message";
		"".tbl::getTimesTable().".log_message, " .tbl::getTimesTable().".trans_num";
	list($num5, $qh5) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), 0, 0, $order_by_str);

	$previousDay = date("d",$startDate);
	$currentTaskId = -1;
	$previousClientProjTaskDesc = "";
	$rowIndex = 0;
	$colIndex = 1;
	$count = 0;
	$weeklyTotal = 0;
	$rowTotal = 0;
	$allTasksDayTotals = array(0,0,0,0,0,0,0); //totals for each day i.e.columns

	//iterate through results
	for ($i=0; $i<$num5; $i++) {
		//get the record for this task entry
		$data = dbResult($qh5,$i);
		
		LogFile::write("\nqh5 tuple: ". var_export($data, true)."\n");
		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		//Common::fixStartEndDuration($data);

		//get the current task properties
		$currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_stamp"];
		$currentDay = date("d",$currentTaskStartDate);
		$currentDayDate = $startDate;
		$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
		$tomorrowDay = strtotime(date("d",$currentDayDate) . " +1 days");
		$currentTaskEndDate = $data["end_stamp"];
		$currentTaskName = stripslashes($data["taskName"]);
		$currentProjectTitle = stripslashes($data["projectTitle"]);
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription = $data["log_message"];
		
		// calculate current change key
		$currentClientProjTaskDesc = $data['client_id'].$data['proj_id']. $data['task_id'].$data['log_message'];

		// set colIndex to match the day of the incoming record
		for ($col = $colIndex; $col <8; $col++) {
		LogFile::write("inside loop colIndex: ". $colIndex. " col: ". $col . " currentDay: " . $currentDay ." previousDay: ". $previousDay. " daysofweek: ". $daysOfWeek[$col-1]."\n");
			if ($currentDay == $daysOfWeek[$col-1]) {
				LogFile::write("Equal, break loop \n");
				break; 
			}
			LogFile::write("Not equal, inc colIndex" . $colIndex ." \n");
			printTime($rowIndex, $colIndex, 0, -1, -1); // print empty day's times
			$colIndex++;
		}		

		// on second thoughts, don't need an array, just output the values since the db query should be in the right order. 
		LogFile::write("before test colIndex: ". $colIndex. "currentDay: " . $currentDay ." previousDay: ". $previousDay. "\n");
		// if client/project/task/workdescription has changed, start a new row
		if (($currentClientProjTaskDesc > $previousClientProjTaskDesc) or ($currentDay <= $previousDay)) { // change of data start new row
			LogFile::write("Changed proj key or duplicate day. Start new row \n");
			
			if($colIndex != 1) { // close off previous row if this is not the first time through
				LogFile::write("Closing previous row. colIndex: ". $colIndex. "\n"); 
				finishRow($rowIndex, $colIndex, $rowTotal, "no"); // "no" means no disabled input fields
				$rowTotal = 0;
				$colIndex = 1; // reset column index
				$rowIndex++; // count no rows
				$previousDay = $currentDay; // reset previous day
			}
			
			printFormRow($rowIndex, $layout, $data); // print client/proj/task etc
			// set colIndex to match the day of the incoming record
			for ($col = $colIndex; $col <8; $col++) {
				LogFile::write("After closing previous row, inside loop colIndex: ". $colIndex. " col: ". $col . " currentDay: " . $currentDay ." previousDay: ". $previousDay. " daysofweek: ". $daysOfWeek[$col-1]."\n");
				if ($currentDay == $daysOfWeek[$col-1]) {
					LogFile::write("Equal, break loop \n");
					break; 
				}
				LogFile::write("Not equal, print blank col and inc colIndex " . $colIndex ." \n");
				printTime($rowIndex, $colIndex, 0, -1, -1); // print empty day's times
				$colIndex++;
			}

			$hours = floor($data['duration'] / 60 );
			$minutes = $data['duration'] - ($hours * 60);
			// establish which column we should be in

			printTime($rowIndex, $colIndex, $data['trans_num'], $hours, $minutes);
			$rowTotal += $data['duration'];
			$allTasksDayTotals[$colIndex-1] += $data['duration']; 
			$colIndex++; 			
			LogFile::write("Print value. colIndex++: ". $colIndex. "\n"); 
		}
		else {
			// continue existing row
			//print hours and minutes input field for new day, and blanks in between
			// ignore blanks in between for now
			
			$hours = floor($data['duration'] / 60 );
			$minutes = $data['duration'] - ($hours * 60);
			printTime($rowIndex, $colIndex, $data['trans_num'], $hours, $minutes);
			$rowTotal += $data['duration'];
			$allTasksDayTotals[$colIndex-1] += $data['duration']; 
			$colIndex++; 
			LogFile::write("Continue existing row colIndex++: ". $colIndex. "\n"); 
			
		}
		
		//LogFile::write("end of tuple: previous key: ". $previousClientProjTaskDesc . " current key: " . $currentClientProjTaskDesc."\n");
		//LogFile::write("end of tuple: previous date: ". $previousDay . " current date: " . $currentDay."\n");
		$previousClientProjTaskDesc = $currentClientProjTaskDesc; // update key
		$previousDay = $currentDay; 
	

	}
	// finished all database records, so finish off row
	if($colIndex != 1) { // close off previous row
		LogFile::write("colIndex on last row: ". $colIndex. "\n"); 
		finishRow($rowIndex, $colIndex, $rowTotal, "no"); // "no" means no disabled input fields;
		$rowIndex++;
	}
		/* $currentDayDate = $startDate;
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
	*/


	/////////////////////////////////////////
	//add an extra row for new data entry
	/////////////////////////////////////////

	$data['proj_id'] = -1;
	$data['client_id'] = -1;
	$data['task_id'] = -1;
	$data['log_message'] = "";
	printFormRow($rowIndex, $layout, $data);
	$colIndex = 1;
	$rowTotal = 0;
	finishRow($rowIndex, $colIndex, $rowTotal, "yes"); // "yes" means fields will be disabled

	//create a new totals row
	print "<tr id=\"totalsRow\">\n";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
  	//store a hidden form field containing the number of existing rows
	print "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"" . $rowIndex . "\" />";

	//store a hidden form field containing the total number of rows
	print "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($rowIndex+1) . "\" /></td>";
	print "<td class=\"calendar_cell_disabled_middle\" align=\"right\" colspan=\"3\">". JText::_('TOTAL_HOURS') .":</td>\n";
	
	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	for ($colIndex=1; $colIndex<8; $colIndex++) {
			
		print "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		print "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">" .Common::formatMinutes($allTasksDayTotals[$colIndex-1])."</span></td>";
		$grandTotal += $allTasksDayTotals[$colIndex-1];
	}

	//print grand total
	print "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	print "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	print "<span class=\"calendar_total_value_monthly\" id=\"grand_total\">" .Common::formatMinutes($grandTotal)."</span></td>";
	print "</tr>";
	
?>

			</table>
		</td>
	</tr>
</table>

</form>
