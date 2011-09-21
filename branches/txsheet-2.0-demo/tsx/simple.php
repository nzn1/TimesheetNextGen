<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

require_once('simple.class.php');
$simple = new SimplePage();

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));
	
		$uid = gbl::getContextUser();
define("A_WEEK", 60 * 60 * 24 * 7); // seconds per week
		
//get the passed date (context date)
$todayStamp = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$todayValues = getdate($todayStamp);
$curDayOfWeek = $todayValues["wday"];
$year = gbl::getYear();
$month = gbl::getMonth();
$day = gbl::getDay();

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = Common::getWeekStartDay();

$daysToMinus = $curDayOfWeek - $startDayOfWeek;
if ($daysToMinus < 0)
	$daysToMinus += 7;

$startDate = strtotime(date("d M Y H:i:s",$todayStamp) . " -$daysToMinus days");
$startDay =  strtotime(date("d",$todayStamp) . " -$daysToMinus days");
$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");

$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -7 days");
$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");

// if required to copy the previous week's tasks and times, calculate the date
$copyprev = isset($_REQUEST["copyprev"]) ? $_REQUEST["copyprev"]: "";
//LogFile::write("\n\nsimple.php\ncopyprev = \"$copyprev\"" . " \n");
if (empty($copyprev)) {
    $copyStartDate = 0;
    $copyEndDate = 0;
}
else
{
    $daysToMinus += 7; // subtract a further 7 days to go a week earlier
    $copyStartDate = strtotime(date("d M Y H:i:s",$todayStamp) . " -$daysToMinus days");
    $copyEndDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");
}

//LogFile::write("\n\n\n\n\n\n\n\n\n\nStart of new Simple.php execution. startDate: ". $todayStamp. "\t context user: ". $uid. "\n\n");
//get the configuration of timeformat and layout
//list($qh2, $numq) = dbQuery("SELECT simpleTimesheetLayout FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
//$configData = dbResult($qh2);
$layout = Common::getLayout();

//$post="";

if (isset($popup)){
	PageElements::setBodyOnLoad("window.open('".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&amp;task_id=$task_id','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205');");
}
	
ob_start();	
?>
<title><?php echo Config::getMainTitle()." - Simple Weekly Timesheet for ".gbl::getContextUser();?></title>

<script type="text/javascript" src="<?php echo Config::getRelativeRoot()."/js/datetimepicker_css.js";?> "></script>

<script type="text/javascript">
	//define the hash table
	var projectTasksHash = {};
	
<?php
$PROJECT_TABLE = tbl::getProjectTable();
$CLIENT_TABLE = tbl::getClientTable();
$TASK_TABLE = tbl::getTaskTable();
//get all of the projects for this context user and put them into the hashtable
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
//LogFile::write("\nList of new projects Num: ". $num3. "\n");

//iterate through results
for ($i=0; $i<$num3; $i++) {
	//get the current record
	$data = dbResult($qh3, $i);
	echo("projectTasksHash['" . $data["proj_id"] . "'] = {};\n");
	echo("projectTasksHash['" . $data["proj_id"] . "']['name'] = '". addslashes($data["title"]) . "';\n");
	echo("projectTasksHash['" . $data["proj_id"] . "']['clientId'] = '". $data["client_id"] . "';\n");
	echo("projectTasksHash['" . $data["proj_id"] . "']['clientName'] = '". addslashes($data["organisation"]) . "';\n");
	echo("projectTasksHash['" . $data["proj_id"] . "']['tasks'] = {};\n");
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
LogFile::write("\nList of tasks Num: ". $num4. "\n");
//iterate through results
for ($i=0; $i<$num4; $i++) {
	//get the current record
	$data = dbResult($qh4, $i);
	echo("if (projectTasksHash['" . $data["proj_id"] . "'] != null)\n");
	echo("  projectTasksHash['" . $data["proj_id"] . "']['tasks']['" . $data["task_id"] . "'] = '" . addslashes($data["name"]) . "';\n");
}
echo "var None_option = '".JText::_('VALUE_NONE')."';";
?>
</script>

<?php
echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/js/simple.js\"></script>";

PageElements::setHead(ob_get_contents());
//PageElements::setTheme('newcss');
ob_end_clean();
PageElements::setBodyOnLoad('populateExistingSelects();');

	?>
<form name="simpleForm" action="<?php echo Config::getRelativeRoot();?>/simple_action" method="post">
<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
<input type="hidden" name="startStamp" value="<?php echo $startDate; ?>" />

<h1><?php echo JText::_('SIMPLE_WEEKLY_TIMESHEET'); ?></h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center"  class="outer_table_heading">
			<?php
				$sdStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$startDate));
				//just need to go back 1 second most of the time, but DST
				//could mess things up, so go back 6 hours...
				$edStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$endDate - 6*60*60));
				echo JText::_('CURRENT_WEEK').': <span style="color:#00066F;">'.$sdStr.' - '.$edStr.'</span>';
			?>
		</td>
		<td  class="outer_table_heading">
		<?php Common::printDateSelector("weekly", $startDate, $prevDate, $nextDate); ?>
		</td>
		<td width="15%" style="font-size: 11"><a href="<?php echo Rewrite::getShortUri();?>?&amp;year=<?php echo $year?>&amp;month=<?php echo $month?>&amp;day=<?php echo $day?>&amp;copyprev=1">Copy Previous</a></td>
          <td width="15%" align="right">
      <?php
          if($copyprev) { // if copy the previous week is set, then enable the save changes nutton
      ?>
          <input type="button" name="saveButton" id="saveButton" value="Save Changes" onclick="validate();" />
      <?php
          }
          else {
      ?>
              <input type="button" name="saveButton" id="saveButton" value="Save Changes" disabled="disabled" onclick="validate();" />
      <?php
          }
      ?>
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
			<th class="inner_table_column_heading" align="center" width="20%">
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

				echo
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
				<th class="inner_table_column_heading" align="center" width="6%">
				<?php echo ucfirst(JText::_('TOTAL')) ?>
			</th>
			<th align="center" width="2">&nbsp;</th>
				<th class="inner_table_column_heading" align="center" width="50">
				<?php echo ucfirst(JText::_('STATUS')) ?>
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
	// if copy previous data is selected, take data from last week
	if (!$copyStartDate) { // normal data retrieval 
		$startStr = date("Y-m-d H:i:s",$startDate);
		$endStr = date("Y-m-d H:i:s",$endDate);
		$dayAdjust = 0;
	}
	else { // copy previous week
		$startStr = date("Y-m-d H:i:s",$copyStartDate);
		$endStr = date("Y-m-d H:i:s",$copyEndDate);
		$dayAdjust = A_WEEK; // since we are retrieving records from last week, adjust date by 7
		// days to bring it into the current week
	}
	//LogFile::write("\nStartStr = " . $startStr ." endStr = " .$endStr.  "\n");
	$order_by_str = "".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name, ".
	//	"".tbl::getTimesTable().".start_time, " .tbl::getTimesTable().".log_message";
		"".tbl::getTimesTable().".log_message, " .tbl::getTimesTable().".start_time";
	list($num5, $qh5) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), 0, 0, $order_by_str);

	$previousDay = date("d",$startDate);
	$currentTaskId = -1;
	$previousClientProjTaskDesc = "-1";
	$rowIndex = 0;
	$colIndex = 1;
	$prevColIndex = 0;
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
		$currentTaskStartDate = $data["start_stamp"] + $dayAdjust;
		$currentDay = date("d",$currentTaskStartDate);
		$currentDayDate = $startDate;
		$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
		$tomorrowDay = strtotime(date("d",$currentDayDate) . " +1 days");
		$currentTaskEndDate = $data["end_stamp"] + $dayAdjust;
		$currentTaskName = stripslashes($data["taskName"]);
		$currentProjectTitle = stripslashes($data["projectTitle"]);
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription = $data["log_message"];
		$hours = floor($data['duration'] / 60 );
		$minutes = $data['duration'] - ($hours * 60);
		switch ($data['subStatus']) {
			case "Open":
				$status = JText::_('STATUS_OPEN');
				break;
			case "Submitted":
				$status = JText::_('STATUS_SUBMITTED');
				break;
			case "Approved":
				$status =JText::_('STATUS_APPROVED');
				break;
		}
	
		// calculate current change key
		$currentClientProjTaskDesc = $data['client_id'].$data['proj_id']. $data['task_id'].$data['log_message'];
		if ($previousClientProjTaskDesc == -1) { // if this is the first time through
			LogFile::write("first time through colIndex: ". $colIndex. " currentDay: " . $currentDay ." previousDay: ". $previousDay."\n");
			$previousClientProjTaskDesc = $currentClientProjTaskDesc; // make the keys the same so we don't force a new row
			$previousDay = $currentDay - 1;
			$simple->printFormRow($rowIndex, $layout, $data); // print client/proj/task etc
		}
		
		if (($currentClientProjTaskDesc > $previousClientProjTaskDesc) or ($colIndex > 7)) { // or ($currentDay <= $previousDay) change of data start new row
			//LogFile::write("Changed proj key or duplicate day. Start new row \n");
			LogFile::write("\nBreak in key\n"); 
			if($colIndex != 1) { // close off previous row 
				LogFile::write("\nClosing previous row". $rowIndex. " colIndex: ". $colIndex. "\n"); 
				$simple->finishRow($rowIndex, $colIndex, $rowTotal, $status, "no"); // "no" means no disabled input fields
				$rowTotal = 0;
				$colIndex = 1; // reset column index
				$prevColIndex = 0; // reset column index
				$rowIndex++; // count no rows
				$previousDay = $currentDay; // reset previous day
				$previousClientProjTaskDesc = $currentClientProjTaskDesc; 
			}
			$simple->printFormRow($rowIndex, $layout, $data); // print client/proj/task etc
		}
		
			// set colIndex to match the day of the incoming record
		for ($col = $colIndex; $col <8; $col++) {
			if ($currentDay == $daysOfWeek[$col-1]) {
				LogFile::write("\nFound matching day currentDay: ". $currentDay . " daysofweek: ". $daysOfWeek[$col-1] ." break loop \n");
				break; 
			}
			//LogFile::write("Not equal, inc colIndex" . $colIndex ." \n");
			$simple->printEmpty($rowIndex, $colIndex); // print empty day's times
			$colIndex++;
		}		

		LogFile::write("\nbefore test colIndex: ". $colIndex. " prevcolindex: ".$prevColIndex ." rowindex: ". $rowIndex. " currentDay: " . $currentDay ." previousDay: ". $previousDay. "\n");
		// if new record client/project/task/workdescription has changed, start a new row
		if (($colIndex >  $prevColIndex) and ($colIndex <= 7)) {
			if ($copyStartDate) // if we are copying previous data, set the transaction number so that the data appears to be new
				$simple->printTime($rowIndex, $colIndex, "n", $hours, $minutes); // print this column's data as new data
			else 
				$simple->printTime($rowIndex, $colIndex, $data["trans_num"], $hours, $minutes); // print this column's data
			$prevColIndex = $colIndex;
			LogFile::write("\ncontinue same row ". $rowIndex. " colIndex: ". $colIndex. "\n");
		}
		else if (($colIndex <= $prevColIndex) or ($colIndex > 7)) { // finish row and start new row
			$simple->finishRow($rowIndex, $colIndex, $rowTotal, $status, "no"); // "no" means no disabled input fields
			$rowTotal = 0;
			$colIndex = 1; // reset column index
			$prevColIndex = 0; // reset column index
			$rowIndex++; // count no rows
			$previousDay = $currentDay; // reset previous day
			$simple->printFormRow($rowIndex, $layout, $data); // print client/proj/task etc
			$previousClientProjTaskDesc = $currentClientProjTaskDesc; 
			LogFile::write("\nstarting new row ". $rowIndex. " colIndex: ". $colIndex. "\n");
			for ($col = 1; $col < $colIndex; $col++) { // now print empty cells until the space for the data
				$simple->printEmpty($rowIndex, $col); // print empty day's times
			}	
			if ($copyStartDate) // if we are copying previous data, set the transaction number so that the data appears to be new
				$simple->printTime($rowIndex, $colIndex, "n", $hours, $minutes); // print this column's data as new data
			else 
				$simple->printTime($rowIndex, $colIndex, $data["trans_num"], $hours, $minutes); // print this column's data
		}

			
		//	printFormRow($rowIndex, $layout, $data); // print client/proj/task etc
			// set colIndex to match the day of the incoming record
		//	for ($col = $colIndex; $col <8; $col++) {
		//		LogFile::write("After closing previous row, inside loop colIndex: ". $colIndex. " col: ". $col . " currentDay: " . $currentDay ." previousDay: ". $previousDay. " daysofweek: ". $daysOfWeek[$col-1]."\n");
		//		if ($currentDay == $daysOfWeek[$col-1]) {
		//			LogFile::write("Equal, break loop \n");
		//			break; 
		//		}
		//		LogFile::write("Not equal, print blank col and inc colIndex " . $colIndex ." \n");
		//		printTime($rowIndex, $colIndex, 0, -1, -1); // print empty day's times
		//		$colIndex++;
		//	}

		//	$hours = floor($data['duration'] / 60 );
		//	$minutes = $data['duration'] - ($hours * 60);
			// establish which column we should be in

		//	printTime($rowIndex, $colIndex, $data['trans_num'], $hours, $minutes);
		//	$rowTotal += $data['duration'];
		//	$allTasksDayTotals[$colIndex-1] += $data['duration']; 
		//	$colIndex++; 			
		//	LogFile::write("Print value. colIndex++: ". $colIndex. "\n"); 
		//}
		//else {
			// continue existing row
			//print hours and minutes input field for new day, and blanks in between
			// ignore blanks in between for now
			
		//	$hours = floor($data['duration'] / 60 );
		//	$minutes = $data['duration'] - ($hours * 60);
		//	printTime($rowIndex, $colIndex, $data['trans_num'], $hours, $minutes);
			$rowTotal += $data['duration'];
			$allTasksDayTotals[$colIndex-1] += $data['duration']; 
			$prevColIndex = $colIndex; 
			$colIndex++; 
			LogFile::write("Continue existing row ". $rowIndex. " colIndex++: ". $colIndex. "\n"); 
			
		
		
		//LogFile::write("end of tuple: previous key: ". $previousClientProjTaskDesc . " current key: " . $currentClientProjTaskDesc."\n");
		//LogFile::write("end of tuple: previous date: ". $previousDay . " current date: " . $currentDay."\n");
		$previousClientProjTaskDesc = $currentClientProjTaskDesc; // update key
		$previousDay = $currentDay; 
	

	}
	// finished all database records, so finish off row
	if($colIndex != 1) { // close off previous row
		LogFile::write("colIndex on last row: ". $colIndex. "\n"); 
		$simple->finishRow($rowIndex, $colIndex, $rowTotal, $status, "no"); // "no" means no disabled input fields;
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
	$simple->printFormRow($rowIndex, $layout, $data);
	$colIndex = 1;
	$rowTotal = 0;
	$simple->finishRow($rowIndex, $colIndex, $rowTotal, "", "yes"); // "yes" means fields will be disabled

	//create a new totals row
	echo "<tr id=\"totalsRow\">\n";
	echo "<td class=\"calendar_cell_disabled_middle\" align=\"right\">";
  	//store a hidden form field containing the number of existing rows
	echo "<input type=\"hidden\" id=\"existingRows\" name=\"existingRows\" value=\"" . $rowIndex . "\" />";

	//store a hidden form field containing the total number of rows
	echo "<input type=\"hidden\" id=\"totalRows\" name=\"totalRows\" value=\"" . ($rowIndex+1) . "\" /></td>";
	echo "<td class=\"calendar_cell_disabled_middle\" align=\"right\" colspan=\"3\">". JText::_('TOTAL_HOURS') .":</td>\n";
	
	//iterate through day totals for all tasks
	$grandTotal = 0;
	$col = 0;
	for ($colIndex=1; $colIndex<8; $colIndex++) {
			
		echo "<td class=\"calendar_totals_line_weekly_right\" align=\"right\">\n";
		echo "<span class=\"calendar_total_value_weekly\" id=\"subtotal_col" . $col . "\">" .Common::formatMinutes($allTasksDayTotals[$colIndex-1])."</span></td>";
		$grandTotal += $allTasksDayTotals[$colIndex-1];
	}

	//print grand total
	echo "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	echo "<td class=\"calendar_totals_line_monthly\" align=\"right\">\n";
	echo "<span class=\"calendar_total_value_monthly\" id=\"grand_total\">" .Common::formatMinutes($grandTotal)."</span></td>";
	echo "</tr>";
	
?>

			</table>


</form>
