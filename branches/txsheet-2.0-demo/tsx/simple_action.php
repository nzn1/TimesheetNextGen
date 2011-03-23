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
ini_set('display_errors', true);

$simple_debug=true;
if($simple_debug) {
	require(Config::getDocumentRoot()."/include/tsx/debuglog.php");
	$debug = new logfile();
}

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

$totalRows = $_POST["totalRows"];

//$startDate = $_POST["startStamp"];
$startStr = date("Y-m-d H:i:s",$startDate);

//$endDate = strtotime(date("d M Y H:i:s",$startDate) . " +7 days");
$endStr = date("Y-m-d H:i:s",$endDate);

if($simple_debug) {
	$debug->write(print_r($_POST, TRUE));
	$debug->write("startStr = \"$startStr\"");
	$debug->write("  endStr = \"$endStr\"\n");

	$debug->write("  totalRows = \"$totalRows\"\n");
	$debug->write("  totalRows = \"".$_POST["totalRows"]."\"\n");
	$debug->write("  existingRows = \"".$_POST["existingRows"]."\"\n");
}

function delete_time_entries($uid, $btime, $etime, $proj_id, $task_id, $descr) {
	global $debug, $simple_debug;

//clear the tasks which start on this week
	$TIMES_TABLE = tbl::getTimesTable();
	$queryString = "DELETE FROM $TIMES_TABLE " . 
						"WHERE uid='$uid' AND " .
							"start_time >= '$btime' AND ".
							"start_time < '$etime' AND ".
							"proj_id=$proj_id AND task_id=$task_id AND ".
							"log_message='$descr'";

// to prevent deleteion when nothing is to be inserted
//if (isset($_POST["totalRows"]) && gbl::getContextUser() != "") {

	if($simple_debug)
		$debug->write("   Query = \"$queryString\"\n");

	dbQuery($queryString);
}

$TIMES_TABLE = tbl::getTimesTable();

//TODO: write meaningful todos ;)
for ($i=0; $i<$totalRows; $i++) {
	if($simple_debug)
		$debug->write("i=$i ". $_POST["projectSelect_row".$i]."  ".$_POST["taskSelect_row".$i]."\n");

	$projectId = $_POST["projectSelect_row" . $i];
	$oprojectId = $_POST["project_row" . $i];
	if ($projectId < 1)
		continue;
	$taskId = $_POST["taskSelect_row" . $i];
	$otaskId = $_POST["task_row" . $i];
	if ($taskId < 1)
		continue;
	$workDescription = '';
	if (array_key_exists("description_row" . $i, $_POST)) {
		// does not exist if simple timesheet layout = "no work description field"!
		$workDescription = mysql_real_escape_string($_POST["description_row" . $i]);
		$oworkDescription = mysql_real_escape_string($_POST["odescription_row" . $i]);
	} else {
		$workDescription = '';
		$oworkDescription = '';
	}

	if($simple_debug)
		$debug->write("proj=$projectId  task=$taskId  log=$workDescription\n");

	$curDaysTimestamp = $startDate;
	for ($j=1; $j<=7; $j++) {
		//get the timestamp for this day
		$nextDaysTimestamp = strtotime(date("d M Y H:i:s",$curDaysTimestamp) . " +1 days");

		//Create the starting timestamp string
		$stsStr = strftime("%Y-%m-%d %H:%M:%S", $curDaysTimestamp);
		$etsStr = strftime("%Y-%m-%d %H:%M:%S",$nextDaysTimestamp);

		//get the number of hours and minutes entered into the form
		$ohours = $_POST["ohours_row" . $i . "_col" . $j];
		$omins = $_POST["omins_row" . $i . "_col" . $j];

		$hours = $_POST["hours_row" . $i . "_col" . $j];
		$mins = $_POST["mins_row" . $i . "_col" . $j];

		if($ohours != $hours || $omins != $mins || $oprojectId != $projectId || $otaskId != $taskId || $oworkDescription != $workDescription) {
			//something changed for this entry
			if($simple_debug)
				$debug->write(" changing pid=$projectId tid=$taskId date=$stsStr  hours=$hours  mins=$mins \n");

			//don't delete anything if this was a new set of entries
			if($oprojectId != '' && $otaskId != '') 
				delete_time_entries(gbl::getContextUser(), $stsStr, $etsStr, $oprojectId, $otaskId, $oworkDescription);

			if ((!empty($hours) && $hours != 0) || (!empty($mins) && $mins != 0)) {

				//fix by Tyler Schacht
				if (empty($hours) || $hours == "") $hours = "00";

				//change hours & minutes into total number of minutes
				$minutes = $hours*60 + $mins;

				//calculate and set ending timestamp string
				$ets = strtotime(date("d M Y H:i:s",$curDaysTimestamp) . " +$minutes Minutes");
				$etsStr = strftime("%Y-%m-%d %H:%M:%S", $ets);
				
				//add to database
				$queryString = "INSERT INTO $TIMES_TABLE (uid, start_time, end_time, duration, proj_id, task_id, log_message) ".
										"VALUES ('".gbl::getContextUser()."','$stsStr', ".
										"'$etsStr', ".
										"'$minutes', ".
										"$projectId, $taskId, '$workDescription')";

				if($simple_debug)
					$debug->write("   Query = \"$queryString\"\n");

				list($qh,$num) = dbQuery($queryString);
			}
		}
		$curDaysTimestamp = $nextDaysTimestamp;
	}
}

$Location = Config::getRelativeRoot()."/simple?year=$year&amp;month=$month&amp;day=$day";

gotoLocation($Location);
exit;
// vim:ai:ts=4:sw=4
?>
