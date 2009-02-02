<?
// $Header: /cvsroot/tsheet/timesheet.php/simple_action.php,v 1.2 2005/03/09 01:40:46 vexil Exp $

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclSimple')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclSimple'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//get key variables from form
$totalRows = $_POST["totalRows"];
$year = $_POST["year"];
$month = $_POST["month"];
$day = $_POST["day"];
$startYear = $_POST["startYear"];
$startMonth = $_POST["startMonth"];
$startDay = $_POST["startDay"];

//convert the start date to a timestamp
$startDate = mktime(0, 0, 0,$startMonth, $startDay, $startYear);

//calculate end date
$endDate = $startDate + A_DAY * 7;
$endYear = date("Y", $endDate);
$endMonth = date("n", $endDate);
$endDay = date("j", $endDate);

//clear the tasks which start on this week
$queryString = "DELETE FROM $TIMES_TABLE WHERE ".
								"uid='$contextUser' AND " .
								"start_time >= '$startYear-$startMonth-$startDay 00:00:00' AND ".
								"start_time < '$endYear-$endMonth-$endDay 00:00:00'";
dbQuery($queryString);

//TODO: write meaningful todos ;)
for ($i=0; $i<$totalRows; $i++) {
	$projectId = $_POST["projectSelect_row" . $i];
	if ($projectId < 1)
		continue;
	$taskId = $_POST["taskSelect_row" . $i];
	if ($taskId < 1)
		continue;
	$workDescription = '';
	if (array_key_exists("description_row" . $i, $_POST)) {
		// does not exist if simple timesheet layout = "no work description field"!
		$workDescription = mysql_real_escape_string($_POST["description_row" . $i]);
	}

	for ($j=1; $j<=7; $j++) {
		//get the timestamp for this day
		$todaysTimestamp = $startDate + ($j-1) * A_DAY;

		//get the year, month and day for the timestamp
		$todayYear = date("Y", $todaysTimestamp);
		$todayMonth = date("n", $todaysTimestamp);
		$todayDay = date("j", $todaysTimestamp);

		//get the number of hours and minutes entered into the form
		$hours = $_POST["hours_row" . $i . "_col" . $j];
		$mins = $_POST["mins_row" . $i . "_col" . $j];

		if ((!empty($hours) && $hours != 0) || (!empty($mins) && $mins != 0)) {

			//fix by Tyler Schacht
			if (empty($hours) || $hours == "") $hours = "00";

			//add to database
			$queryString = "INSERT INTO $TIMES_TABLE (uid, start_time, end_time, proj_id, task_id, log_message) ".
									"VALUES ('$contextUser','$todayYear-$todayMonth-$todayDay 00:00:00', ".
									"'$todayYear-$todayMonth-$todayDay $hours:$mins:00', ".
									"$projectId, $taskId, '$workDescription')";
			list($qh,$num) = dbQuery($queryString);
		}
	}
}

$Location = "simple.php?year=$year&month=$month&day=$day";

Header("Location: $Location");
exit;
