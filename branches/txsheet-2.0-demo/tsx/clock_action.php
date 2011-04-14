<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
trigger_error('WARNING - LOTS OF STUFF IN clock_action has not be converted to OO!');
trigger_error('lots of globals are in here. this is a problem!');

// Authenticate
//require(Config::getDocumentRoot()."/include/tsx/debuglog.php");
if (!Site::getAuthenticationManager()->isLoggedIn()) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['POST_URI']));
	exit;
}


$GLOBALS["simple_debug"]=true;
if($GLOBALS["simple_debug"]) {


	$test=http_build_query($_POST);
	$tsize = strlen($test);

	LogFile::write("post size is $tsize\n");
} else
	$GLOBALS["debug"]=0;

if($GLOBALS["simple_debug"]) {
	LogFile::write(print_r($_POST, TRUE));
}

// Oh, bother, in OO mode, we're not at the "root" level of anything here, so, none of these
// variables below are global in scope...
// So, what do we really need?

/**
 * Updated by robsearles 26 Jan 2008
 * To enable the "stop" link to work in the "Daily Timesheet" page
 * set a few default values in the list of local vars below
 */
//load local vars from request/post/get
$month = isset($_POST['month']) ? $_POST['month'] : false;
$day = isset($_POST['day']) ? $_POST['day'] : false;
$year = isset($_POST['year']) ? $_POST['year'] : false;
$client_id = $_POST['client_id'];
$proj_id = $_POST['proj_id'];
$task_id = $_POST['task_id'];
$origin = isset($_POST['origin']) ? $_POST['origin'] : 'daily';
$destination = isset($_POST['destination']) ? $_POST['destination'] : 'daily';
$fromPopupWindow = isset($_POST['fromPopupWindow']) ? $_POST['fromPopupWindow']: false;
$clockonoff = isset($_POST['clockonoff']) ? $_POST['clockonoff']: "";
$clock_on_time_hour = isset($_POST['clock_on_time_hour']) ? $_POST['clock_on_time_hour']: 0;
$clock_on_time_min = isset($_POST['clock_on_time_min']) ? $_POST['clock_on_time_min']: 0;
$clock_off_time_hour = isset($_POST['clock_off_time_hour']) ? $_POST['clock_off_time_hour']: 0;
$clock_off_time_min = isset($_POST['clock_off_time_min']) ? $_POST['clock_off_time_min']: 0;
$log_message = isset($_POST['log_message']) ? $_POST['log_message']: "";
$log_message_presented = isset($_POST['log_message_presented']) ? $_POST['log_message_presented']: false;
$clock_on_check = isset($_POST['clock_on_check']) ? $_POST['clock_on_check']: "";
$clock_off_check = isset($_POST['clock_off_check']) ? $_POST['clock_off_check']: "";
$clock_on_radio = isset($_POST['clock_on_radio']) ? $_POST['clock_on_radio']: "";
$clock_off_radio = isset($_POST['clock_off_radio']) ? $_POST['clock_off_radio']: "";

if($fromPopupWindow == 'false')
	$fromPopupWindow = false;


//LogFile::write("destination = \"$destination\"\n");
//LogFile::write("fromPopupWindow = \"$fromPopupWindow\"\n");

/**
 * @todo the &var= stuff needs to be changed to &amp;var= BUT 
 * if the link is used in javascript then this must be carefully checked
 * as I think javascript doesn't like &amp;  
 */ 
//set the return location
$Location = "$destination?month=$month&amp;year=$year&amp;day=$day&amp;destination=$destination";
if ($destination == "stopwatch" || $destination == "daily")
	$Location = "$destination?client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id&amp;month=$month&amp;year=$year&amp;day=$day&amp;destination=$destination";

//determine the action
if (empty($clockonoff)) {
	if (!empty($clock_on_check) && !empty($clock_off_check))
		$clockonoff = "clockonandoff";
	else if (!empty($clock_on_check)) {
		if ($clock_on_radio == "now")
			$clockonoff = "clockonnow";
		else
			$clockonoff = "clockonat";
	} else if (!empty($clock_off_check)) {
		if ($clock_off_radio == "now")
			$clockonoff = "clockoffnow";
		else
			$clockonoff = "clockoffat";
	} else
		Common::errorPage("You must select at least one checkbox to indicate your action: clock on, clock off, or both.", $fromPopupWindow);
}

if ($clock_on_radio == "now" && $clock_off_radio == "now")
	Common::errorPage("You cannot clock on and off at with the same clock-on and clock-off time.", $fromPopupWindow);

//get the clock on/off "now" dates and times
//We're forced to recalculate the date as well as the time because the dialog
//box that says "now" could have been opened during the previous day
//(ie. clock on at 11:30pm, clock off at 2:15am)
if ($clock_on_radio == "now" || $clockonoff == "clockonnow") {
	$realToday = getdate(time());
	$month = $realToday['mon']; 
	$day = $realToday['mday'];
	$year = $realToday['year'];
	$clock_on_time_hour = $realToday["hours"];
	$clock_on_time_min = $realToday["minutes"];
}

if ($clock_off_radio == "now" || $clockonoff == "clockoffnow") {
	$realToday = getdate(time());
	$month = $realToday['mon']; 
	$day = $realToday['mday'];
	$year = $realToday['year'];
	$clock_off_time_hour = $realToday["hours"];
	$clock_off_time_min = $realToday["minutes"];
}

$info['onStamp']= mktime($clock_on_time_hour, $clock_on_time_min, 0, $month, $day, $year);
$info['offStamp'] = mktime($clock_off_time_hour, $clock_off_time_min, 0, $month, $day, $year);

if($GLOBALS["simple_debug"]) {
	LogFile::write("onStamp = $onStamp");
	LogFile::write("offStamp = $offStamp");
}

//call appropriate functions
if ($clockonoff == "clockonandoff")
	clockonandoff();
else if ($clockonoff == "clockonat") {
	clockon();
} else if ($clockonoff == "clockoffat") {
	clockoff();
} else if ($clockonoff == "clockonnow") {
	//if we're coming from the popup window then set the return location to the origin
	if ($fromPopupWindow == "true")
		//set the return location
		$Location = "$origin?client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id&amp;month=$month&amp;year=$year&amp;day=$day&amp;destination=$destination";

	clockon();
} else if ($clockonoff == "clockoffnow") {
	clockoff();
} else	 //redirects to a page where the user can enter the log message. Then returns here.
	Common::errorPage("Could not determine the clock on/off action. Please report this as a bug", $fromPopupWindow);

//This is functionally the end of this file...

function getLogMessage() {
	//import global vars
	global $year, $month, $day, $task_id, $proj_id, $client_id, $Location;
	global $origin, $destination, $clock_on_time_hour, $clock_off_time_hour,
						$clock_on_time_min, $clock_off_time_min, $clockonoff;
	global $log_message, $log_message_presented, $fromPopupWindow;

	if ($log_message_presented == false) {
		$targetWindowLocation = Config::getRelativeRoot()."/log_message".
					"?origin=$origin&amp;destination=$destination".
					"&amp;clock_on_time_hour=$clock_on_time_hour".
					"&amp;clock_off_time_hour=$clock_off_time_hour".
					"&amp;clock_on_time_min=$clock_on_time_min".
					"&amp;clock_off_time_min=$clock_off_time_min".
					"&amp;year=$year".
					"&amp;month=$month".
					"&amp;day=$day".
					"&amp;client_id=$client_id".
					"&amp;proj_id=$proj_id".
					"&amp;task_id=$task_id".
					"&amp;clockonoff=$clockonoff";

		if ($fromPopupWindow == "true") {
			//close this popup window and load the log message page in the main window.
			loadMainPageAndCloseWindow($targetWindowLocation);
		}
		else {
			gotoLocation($targetWindowLocation);
			exit;
		}
	}
}

function clockon() {
	include("table_names.inc");

	//import global vars
	global $onStamp, $task_id, $proj_id, $Location, $fromPopupWindow;

	if (empty($Location))
		Common::errorPage("failed sanity check, location empty");

	//check that we are not already clocked on
	$querystring = "SELECT times.start_time, tt.name FROM ".
			"".tbl::getTimesTable()." timest, ".tbl::getTaskTable()." tt WHERE ".
			"uid='".gbl::getContextUser()."' AND ".
			"end_time='0' AND ".
			//"start_time>='$year-$month-$day' AND ".
			//"start_time<='$year-$month-$day 23:59:59' AND ".
			"timest.task_id=$task_id AND ".
			"timest.proj_id=$proj_id AND ".
			"tt.task_id=$task_id AND ".
			"tt.proj_id=$proj_id";

	list($qh,$num) = dbQuery($querystring);
	$resultset = dbResult($qh);

	if ($num > 0)
		Common::errorPage("You have already clocked on for task '$resultset[name]' at $resultset[start_time].  Please clock off first.", $fromPopupWindow);

	$onStr = strftime("%Y-%m-%d %H:%M:%S", $onStamp);

	//now insert the record for this clock on
	$querystring = "INSERT INTO ".tbl::getTimesTable()." (uid, start_time, proj_id,task_id) ".
			"VALUES ('".gbl::getContextUser()."','$onStr', $proj_id, $task_id)";
	list($qh,$num) = dbQuery($querystring);

	//now output an ok page, the redirect back
	print "<html>\n";
	print "	<head>\n";
	print " 	<script language=\"javascript\">\n";
	print "				function alertAndLoad()\n";
	print "				{\n";
	print "					alert('Clocked on successfully');\n";
	if ($fromPopupWindow)
		print "					window.opener.location.reload();\n";
	print "					window.location=\"$Location\";\n";
	print "				}\n";
	print "			</script>\n";
	print "		</head>\n";
	print "		<body onLoad=\"javascript:alertAndLoad();\">\n";
	print "		</body>\n";
	print "	</html>\n";
	exit;
}

function clockoff() {
	include("table_names.inc");

  /************
   *
   *@TODO - THESE VARIABLES DON'T EXIST! - they need to be found!
   *globals have been removed completely   
   */        
	//import global vars
	global $year, $month, $day, $offStamp, $task_id, $proj_id, $Location;
	global $log_message, $log_message_presented, $fromPopupWindow;

	$offStr = strftime("%Y-%m-%d %H:%M:%S", $offStamp);

	//check that we are actually clocked on
	$querystring = "SELECT start_time, start_time < '$offStr' AS valid FROM ".tbl::getTimesTable()." WHERE ".
			"uid='".gbl::getContextUser()."' AND ".
			"end_time=0 AND ".
			//"start_time >= '$year-$month-$day' AND ".
			//"start_time <= '$year-$month-$day 23:59:59' AND ".
			"proj_id=$proj_id AND ".
			"task_id=$task_id";

	list($qh,$num) = dbQuery($querystring);
	$data = dbResult($qh);

	if ($num == 0)
		Common::errorPage("You are not currently clocked on. You must clock on before you can clock off. If you have just clocked on please wait at least one minute before clocking off", $fromPopupWindow);	
	//also check that the clockoff time is after the clockon time
	else if ($data["valid"] == 0)
		Common::errorPage("You must clock off <i>after</i> you clock on.", $fromPopupWindow);

	$onStamp = strtotime($data["start_time"]);
	$duration = ($offStamp-$onStamp)/60;

	//do we need to present the user with a log message screen?
	if ($log_message_presented == false)
		getLogMessage();

	//now insert the record for this clock off
	$log_message = addslashes($log_message);
	$querystring = "UPDATE ".tbl::getTimesTable()." SET log_message='$log_message', end_time='$offStr', duration='$duration' WHERE ".
			"uid='".gbl::getContextUser()."' AND ".
			"proj_id=$proj_id AND ".
			"end_time=0 AND ".
			//"start_time >= '$year-$month-$day' AND ".
			//"start_time < '$year-$month-$day 23:59:59' AND ".
			"task_id=$task_id";
	list($qh,$num) = dbQuery($querystring);
	gotoLocation($Location);
}

function clockonandoff() {
//	include("table_names.inc");

	//import global vars
	global $year, $month, $day, $task_id, $proj_id, $Location;
	global $destination, $clock_on_time_hour, $clock_off_time_hour, $clock_on_time_min, $clock_off_time_min;
	global $log_message, $log_message_presented, $onStamp, $offStamp;
	global $clock_on_radio, $clock_off_radio, $fromPopupWindow;
	global $simple_debug, $debug;

	if($simple_debug) {
		$debug->write("onStamp = $onStamp");
		$debug->write("offStamp = $offStamp");
	}

	//make sure we're not clocking on after clocking off
	if ($offStamp < $onStamp)
		Common::errorPage("You cannot have your clock on time ($clock_on_time_hour:$clock_on_time_min) ".
			"later than your clock off time ($clock_off_time_hour:$clock_off_time_min)", $fromPopupWindow);
	else if ($onStamp == $offStamp)
		//errorPage("You cannot clock on and off with the same time. ($clock_on_time_hour:$clock_on_time_min = $clock_off_time_hour:$clock_off_time_min)", $fromPopupWindow);
		Common::errorPage("You cannot clock on and off with the same time. ($onStamp == $offStamp)", $fromPopupWindow);

	if ($log_message_presented == false)
		getLogMessage();

	$duration=($offStamp - $onStamp)/60; //get duration in minutes
	$onStr = strftime("%Y-%m-%d %H:%M:%S", $onStamp);
	$offStr = strftime("%Y-%m-%d %H:%M:%S", $offStamp);
	
	$log_message = addslashes($log_message);
	$queryString = "INSERT INTO ".tbl::getTimesTable()." (uid, start_time, end_time, duration, proj_id, task_id, log_message) ".
			"VALUES ('".gbl::getContextUser()."','$onStr', '$offStr', '$duration', " .
			"$proj_id, $task_id, '$log_message')";
	list($qh,$num) = dbQuery($queryString);

	gotoLocation($Location);
	exit;
}
?>
<?php
// vim:ai:ts=4:sw=4
?>
