<?php
//$Header: /cvsroot/tsheet/timesheet.php/proj_action.php,v 1.8 2005/05/17 03:38:37 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclProjects')) {
	Header('Location: login.php?clearanceRequired=' . get_acl_level('aclProjects'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$action = $_REQUEST['action'];
if ($action == "add" || $action == "edit") {
	$assigned = isset($_REQUEST["assigned"]) ? $_REQUEST['assigned']: array();
	array_walk($assigned, $authenticationManager->escape_string);
	$end_month = mysqli_real_escape_string($dbh, $_REQUEST['end_month']);
	$end_day = mysqli_real_escape_string($dbh, $_REQUEST['end_day']);
	$end_year = mysqli_real_escape_string($dbh, $_REQUEST['end_year']);
	$start_month = mysqli_real_escape_string($dbh, $_REQUEST['start_month']);
	$start_day = mysqli_real_escape_string($dbh, $_REQUEST['start_day']);
	$start_year = mysqli_real_escape_string($dbh, $_REQUEST['start_year']);
	$title = mysqli_real_escape_string($dbh, $_REQUEST['title']);
	$description = mysqli_real_escape_string($dbh, $_REQUEST['description']);
	$url = mysqli_real_escape_string($dbh, $_REQUEST['url']);
	$proj_status = mysqli_real_escape_string($dbh, $_REQUEST['proj_status']);
	$project_leader = mysqli_real_escape_string($dbh, $_REQUEST['project_leader']);
}
$client_id = $_REQUEST['client_id'];
$proj_id = isset($_REQUEST['proj_id']) ? $_REQUEST['proj_id']: 0;

if (!isset($action)) {
//	Header("Location: $HTTP_REFERER");
	errorPage("ERROR: No action has been passed.  Please fix.\n");
}
elseif ($action == "add") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	if ((!checkdate($end_month, $end_day, $end_year)) || (!checkdate($start_month, $start_day, $start_year))) {
		if (($start_day != 0 && $start_month != 0 && $start_year != 0) || ($end_day != 0 && $end_month != 0 && $end_year != 0))
				errorPage("ERROR: Invalid date.  Please fix.\n");
	}

/*	$title = addslashes($title);
	$description = addslashes($description);
	$url = addslashes($url);*/

	list($qh, $num) = dbQuery("INSERT INTO $PROJECT_TABLE (title, client_id, description, start_date, deadline, http_link, proj_status, proj_leader) VALUES ".
						"('$title','$client_id','$description', '$start_year-$start_month-$start_day', ".
						"'$end_year-$end_month-$end_day','$url', '$proj_status', '$project_leader')");
	$proj_id = dbLastID($dbh);

	//create a time string for >>now<<
	$time_string = date("Y-m-d H:i:00");

	list($task_qh, $num) = dbQuery("INSERT INTO $TASK_TABLE (proj_id, name, description, assigned, started, status)\n VALUES ".
							"($proj_id, 'Default Task', '', '$time_string', '$time_string', 'Started')");
	$task_id = dbLastID($dbh);

	//flag for whether the leader was added to the assignments
	$leader_added = false;

	//check if the leader was added to the assignments
	while (list(,$username) = each($assigned)) {
		if ($username == $project_leader)
			$leader_added = true;
		dbQuery("INSERT INTO $ASSIGNMENTS_TABLE VALUES ($proj_id, '$username', 1)");
		dbQuery("INSERT INTO $TASK_ASSIGNMENTS_TABLE(proj_id, task_id, username) VALUES ($proj_id, $task_id, '$username')");
	}
	if (!$leader_added) {
		// Add the project leader.
		dbQuery("INSERT INTO $ASSIGNMENTS_TABLE VALUES ($proj_id, '$project_leader', 1)");
		dbQuery("INSERT INTO $TASK_ASSIGNMENTS_TABLE(proj_id, task_id, username) VALUES ($proj_id, $task_id, '$project_leader')");
	}

	// we're done adding the project so redirect to the maintenance page
	Header("Location: proj_maint.php?client_id=$client_id");

}
elseif ($action == "edit") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	if ((!checkdate($end_month, $end_day, $end_year)) || (!checkdate($start_month, $start_day, $start_year))) {
		if (($start_day != 0 && $start_month != 0 && $start_year != 0) || ($end_day != 0 && $end_month != 0 && $end_year != 0))
			errorPage("ERROR: Invalid date.  Please fix.\n");
	}

/*	$title = addslashes($title);
	$description = addslashes($description);
	$url = addslashes($url);*/

	$query = "UPDATE $PROJECT_TABLE set title='$title',client_id='$client_id',description='$description',".
				"start_date='$start_year-$start_month-$start_day', proj_status='$proj_status', proj_leader='$project_leader', ".
				"deadline='$end_year-$end_month-$end_day',http_link='$url' WHERE proj_id=$proj_id";

	list($qh,$num) = dbquery($query);

	if ($assigned) {
		dbQuery("DELETE FROM $ASSIGNMENTS_TABLE WHERE proj_id = $proj_id");
		while (list(,$username) = each($assigned)) {
			dbQuery("INSERT INTO $ASSIGNMENTS_TABLE VALUES ($proj_id, '$username', 1)");
		}
	}

	//we're done editing, so redirect back to the maintenance page
	Header("Location: proj_maint.php?client_id=$client_id");
}
elseif ($action == 'delete') {
	dbQuery("DELETE FROM $TASK_ASSIGNMENTS_TABLE WHERE proj_id = $proj_id");
	dbQuery("DELETE FROM $TASK_TABLE WHERE proj_id = $proj_id");
	dbQuery("DELETE FROM $PROJECT_TABLE WHERE proj_id=$proj_id");
	dbQuery("DELETE FROM $ASSIGNMENTS_TABLE WHERE proj_id=$proj_id");
	Header("Location: proj_maint.php?client_id=$client_id");
}

// vim:ai:ts=4:sw=4
?>
