<?php

// Authenticate
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	if(!class_exists('Site')){
		Header("Location: login?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));	
	}
	else{
		Header("Location: login?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	}
	
	exit;
}

// Connect to database.

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
        errorPage("Could not determine the logged in user");

if (empty($contextUser))
        errorPage("Could not determine the context user");

//load local vars from superglobals
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = $contextUser;

//load local vars from superglobals
$month = gbl::getMonth();
$day = gbl::getDay(); 
$year = gbl::getYear();
$last_day = isset($_REQUEST['last_day']) ? $_REQUEST['last_day']: "31";
$action = isset($_REQUEST['action']) ? $_REQUEST['action']: 0;

//set the return location
$Location = "absences?month=$month&year=$year&day=$day&uid=$uid";

if ($action!=0) {
	$endMonth = $month + 1;
	$endYear = $year;
	if ($endMonth > 12) {
		$endMonth = 1;
		$endYear++;

	}
	$ABSENCE_TABLE = tbl::getAbsenceTable();
	//clear the absences for this user in the month
	dbQuery("DELETE FROM $ABSENCE_TABLE WHERE user='$uid' AND ".
				"date >= '$year-$month-01 00:00:00' AND ".
				"date < '$endYear-$endMonth-01 00:00:00'");

	for ($i=1; $i<=$last_day; $i++) {
		$AMtype = $_POST["AMtype".$i];
		$AMtext = urlencode($_POST["AMtext".$i]);
		$PMtype = $_POST["PMtype".$i];
		$PMtext = urlencode($_POST["PMtext".$i]);

		if (($AMtype!='')&&($AMtype!='Public')) {
			dbquery("INSERT INTO $ABSENCE_TABLE VALUES ".
				"(0,'$year-$month-$i 00:00:00','AM','$AMtext','$AMtype','$uid')");
		}
		if (($PMtype!='')&&($PMtype!='Public')) {
			dbquery("INSERT INTO $ABSENCE_TABLE VALUES ".
				"(0,'$year-$month-$i 00:00:00','PM','$PMtext','$PMtype','$uid')");
		}
	}
}
Header("Location: $Location");
?>
