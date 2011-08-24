<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

// Two potential problems exist, both are caused by this file not being able to 
// easily modify data that does not belong to the current context week.
//
// Setup: Time is entered using daily/popup screens; a task's time begins 
//        during week 1, but ends during week2.
//
// Example: worked some Sunday from 10 PM till 3 AM Monday
//
// Bug 1:  Week 1 is edited with the simple form, the two hours for Sunday
//         will be continue to exist as it should, but the 3 hours for Monday
//         will have disappeared.
//
// Bug 2:  Week 2 is edited with the simple form, the 3 hours for Monday will
//		   continuously be added to that task for Monday every time the "Save"
//         button is clicked.
//
// The problem cannot occur if only daily or simple forms are able to be
// used.  So, disable one type of entry or the other.
//
// Other Possible Resolutions if that's not acceptable to you:
//    a) total rewrite of this to update affected entries instead of trying
//       to delete all the records and re-creating them
//    b) rework entire system and automatically split all tasks that span
//       days into multiple discrete day tasks.
//    c} figure out logic to split up tasks that would be tickled by this bug
//       and save and update the extra day's times as needed.

error_reporting(E_ALL);
//ini_set('display_errors', false);

$simple_debug=true;
if($simple_debug) {
	$test=http_build_query($_REQUEST);
	$tsize = strlen($test);
	LogFile::write("post size is $tsize\n");
}


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

$totalRows = $_REQUEST["totalRows"];

//$startDate = $_REQUEST["startStamp"];
$startStr = date("Y-m-d H:i:s",$startDate);

//$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");
$endStr = date("Y-m-d H:i:s",$endDate);

if($simple_debug) {
	LogFile::write(print_r($_REQUEST, TRUE));
	LogFile::write("startStr = \"$startStr\"");
	LogFile::write("  endStr = \"$endStr\"\n");

	LogFile::write("  totalRows = \"$totalRows\"\n");
	LogFile::write("  totalRows = \"".$_REQUEST["totalRows"]."\"\n");
	LogFile::write("  existingRows = \"".$_REQUEST["existingRows"]."\"\n");
}

for ($i=0; $i<$totalRows; $i++) {
	if($simple_debug)
		LogFile::write("working on row $i proj_id ". $_REQUEST["projectSelect_row".$i]." task_id ".$_REQUEST["taskSelect_row".$i]."\n");

		//TODO does this handle change of client/project/task?
	$projectId = $_REQUEST["projectSelect_row" . $i];
	$oprojectId = $_REQUEST["project_row" . $i];
	if ($oprojectId < 1 && $projectId < 1) // when the projectId is < 1 indicates an unused blank last row: ignore
		continue;
	$taskId = $_REQUEST["taskSelect_row" . $i];
	$otaskId = $_REQUEST["task_row" . $i];
	if ($otaskId < 1 && $taskId < 1) // when the taskId is < 1 indicates an unused blank last row: ignore
		continue;
	$workDescription = '';
	//TODO handle change of description
	if (array_key_exists("description_row" . $i, $_REQUEST)) {
		// does not exist if simple timesheet layout = "no work description field"!
		$workDescription = mysql_real_escape_string($_REQUEST["description_row" . $i]);
		//$oworkDescription = mysql_real_escape_string($_REQUEST["odescription_row" . $i]); // get the old and new descriptions
	} else {
		$workDescription = '';
		$oworkDescription = '';
	}

	$curDaysTimestamp = $startDate;
	for ($j=1; $j<=7; $j++) {
		//get the timestamp for this day
		$nextDaysTimestamp = strtotime(date("d M Y H:i:s",$curDaysTimestamp) . " +1 days");

		//Create the starting timestamp string
		$stsStr = strftime("%Y-%m-%d %H:%M:%S", $curDaysTimestamp);
		$etsStr = strftime("%Y-%m-%d %H:%M:%S",$nextDaysTimestamp);
		$trans_num = $_REQUEST["tid_row" . $i . "_col" . $j];

		if($taskId == -1) {  //if this row was deleted, taskId is -1, 
			// delete the transactions in this row for this column
			if ($trans_num > 0) { // trans_num = 0 for empty cells, so skip them
				$delquery = "DELETE FROM ". tbl::getTimesTable(). " WHERE trans_num = '" .$trans_num. "'";
				LogFile::write("delete string: " .$delquery);
				list($del1, $numd) = dbQuery($delquery); // delete the times record
				//$data = dbResult($del1);
			}
		} 
		else {
			//get the number of hours and minutes entered into the form
			$hours = $_REQUEST["hours_row" . $i . "_col" . $j];
			$mins = $_REQUEST["mins_row" . $i . "_col" . $j];
			$minutes = $hours*60 + $mins;
			
			if($trans_num < 0) { 
				// trans_num has beeen made negative hence the time for this entry has changed
				//	LogFile::write(" changing opid=$oprojectId otid=$otaskId date=$stsStr\n");
				// get record, update new ending time, and update no of hours 
				$trans_num = - $trans_num; // make positive
				$qt1 = "SELECT start_time, end_time, duration FROM ". tbl::getTimesTable(). " WHERE trans_num = " .$trans_num;
				list($qh1, $num1) = dbQuery($qt1); // retrieve the times record
				$data = dbResult($qh1);
				// calculate new ending time
				$newEndTime = $data['start_time'] . " + $minutes Minutes";
				LogFile::write("new end time is " . $newEndTime);
				$newEndTime = strtotime($data['start_time'] . " + $minutes Minutes");
				// if new end time crossses midnight, split record into two
				$midnight = strtotime($data['start_time'] . " midnight");
				$etsStr = strftime("%Y-%m-%d %H:%M:%S", $newEndTime);
				//if ($newEndTime > $midnight) {
					// split record into two
				//LogFile::write("new end time: " . $newEndTime . " is beyond midnight: ". $midnight);
					
				//}
				//else {
					// update record with new duration and new end time
					$updateQuery = "UPDATE ". tbl::getTimesTable(). " SET end_time = '" . $etsStr . "', duration = " . $minutes .
						" WHERE trans_num = " . $trans_num;
					list($qu1, $num1) = dbQuery($updateQuery); // update the times record
					LogFile::write("new end time: " . $newEndTime . " and duration updated ". $minutes);
				//}
			}
			
			if($trans_num == 0) { 
				// insert new times record
				if ((!empty($hours) && $hours != 0) || (!empty($mins) && $mins != 0)) {
	
					//fix by Tyler Schacht
					if (empty($hours) || $hours == "") $hours = "00";
	
					//change hours & minutes into total number of minutes
					$minutes = $hours*60 + $mins;
	
					//calculate and set ending timestamp string
					$ets = strtotime(date("d M Y H:i:s",$curDaysTimestamp) . " +$minutes Minutes");
					$etsStr = strftime("%Y-%m-%d %H:%M:%S", $ets);
					
					//add to database
					$queryString = "INSERT INTO ".tbl::getTimesTable()." (uid, start_time, end_time, duration, proj_id, task_id, log_message) ".
									"VALUES ('".gbl::getContextUser()."','$stsStr', ".
									"'$etsStr', ".
									"'$minutes', ".
									"$projectId, $taskId, '$workDescription')";
	
					if($simple_debug)
						LogFile::write("   Query = \"$queryString\"\n");
	
					list($qh,$num) = dbQuery($queryString);
				}
			}
		}
		$curDaysTimestamp = $nextDaysTimestamp;
		
	}
}

// date1 contains the date redirection in the format dd-mm-yyyy

$date1 = $_REQUEST["date1"];
$newdate = explode("-", $date1);
$Location = Config::getRelativeRoot()."/simple?year=".$newdate[2]."&amp;month=".$newdate[1]."&amp;day=".$newdate[0];
gotoLocation($Location);
exit;
// vim:ai:ts=4:sw=4
?>
