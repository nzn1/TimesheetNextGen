<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclAbsences'))return;

// Connect to database.

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");


//load local vars from request/post/get
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = gbl::getContextUser();
if (isset($_REQUEST['date1'])) {
	 $date1 = $_REQUEST["date1"];
	$newdate = explode("-", $date1);
	$year=$newdate[2];
	$month=$newdate[1];
	$day=$newdate[0];
	
}
else {
	$month = gbl::getMonth();
	$day = gbl::getDay(); 
	$year = gbl::getYear();
}
$startDayOfWeek = Site::Config()->get('weekstartday');  //needed by NavCalendar
$todayDate = mktime(0, 0, 0, $month, $day, $year);
$startDate = strtotime(date("d M Y",$todayDate));

$last_day = isset($_REQUEST['last_day']) ? $_REQUEST['last_day']: "31";
$action = isset($_REQUEST['action']) ? $_REQUEST['action']: 0;

if ($action!=0) {
	$endMonth = $month + 1;
	$endYear = $year;
	if ($endMonth > 12) {
		$endMonth = 1;
		$endYear++;

	}
		//clear the absences for this user in the month
	dbQuery("DELETE FROM ".tbl::getAbsenceTable()." WHERE user='$uid' AND ".
				"date >= '$year-$month-01 00:00:00' AND ".
				"date < '$endYear-$endMonth-01 00:00:00'");

	for ($i=1; $i<=$last_day; $i++) {
		$AMtype = mysql_real_escape_string(@$_POST["AMtype".$i]);
		$AMtext = urlencode(@$_POST["AMtext".$i]);
		$PMtype = mysql_real_escape_string(@$_POST["PMtype".$i]);
		$PMtext = urlencode(@$_POST["PMtext".$i]);


		if ($AMtype != '0' && $AMtype != null && $AMtype != 'Public') {
			$q = "INSERT INTO ".tbl::getAbsenceTable()." VALUES ".
				"(0,'$year-$month-$i 00:00:00','AM','$AMtext','$AMtype','$uid')";
				
			if(Debug::getSqlStatement())ppr($q);
			$ret['status'] = Site::Db()->query($q);
			$ret['ref'] = mysql_insert_id();

			if($ret['status'] == false && debug::getSqlError()==1){
				Debug::ppr(mysql_error(),'sqlError');
			}
		}

		if ($PMtype != '0' && $PMtype != null && $PMtype != 'Public') {
			$q = "INSERT INTO ".tbl::getAbsenceTable()." VALUES ".
				"(0,'$year-$month-$i 00:00:00','PM','$PMtext','$PMtype','$uid')";
				
			if(Debug::getSqlStatement())ppr($q);
			$ret['status'] = Site::Db()->query($q);
			$ret['ref'] = mysql_insert_id();

			if($ret['status'] == false && debug::getSqlError()==1){
				Debug::ppr(mysql_error(),'sqlError');
			}
		}
	}
}
//set the return location
$Location = Config::getRelativeRoot()."/absences?month=$month&amp;year=$year&amp;day=$day&amp;uid=$uid";
ppr($_POST);
gotoLocation($Location);
?>
