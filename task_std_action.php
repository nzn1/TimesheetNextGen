<?php
//$Header: /cvsroot/tsheet/timesheet.php/task_std_action.php,v 1.0 2010/04/26 12:12:00 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclProjects')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclProjects'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from post
$action = $_REQUEST['action'];
$client_id = $_REQUEST['client_id'];

if (!isset($action)) {
//	Header("Location: $HTTP_REFERER");
	errorPage("ERROR: No action has been passed.  Please fix.\n");
}
elseif ($action == "add") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	
//	while (list($key,$value) = each($_POST)){
//		echo "Key: ".$key . "Value: ".$value."<br />";
//	}
	
	for($i = 0; $i <30; $i++) {	
		list($key, $value) = each($_POST);
		// debug echo "loop: ". $i ." Key: ".$key . " Value: ".$value."<br />";
		$action = substr($key, 0, 3);
		if($action == "add")  {
		
				list($key, $tn) = each($_POST);
				list($key, $td) = each($_POST);
				if($value == "on") {
					list($qh, $num) = dbQuery("INSERT INTO $STD_TASK_TABLE (`task_id`, `name`, `description`) VALUES (NULL, '$tn' , '$td')");
				}
		}
		elseif($action == "del") {
					
				if ($value == "on") {
					$tn = substr($key, 3, strlen($key) - 2);
					list($qh, $num) = dbQuery("DELETE FROM $STD_TASK_TABLE WHERE task_id = $tn");
					}

	}

	//we're done editing, so redirect back to the maintenance page
	Header("Location: proj_maint.php?client_id=$client_id");
	}
}
// p.ridge {border-style:ridge;border-width:15px;border-color:#98bf21;} -moz-border-radius, round corners
?>




