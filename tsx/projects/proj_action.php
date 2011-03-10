<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;


//load local vars from superglobals
$action = $_REQUEST['action'];
if ($action == "add" || $action == "edit") {
	$assigned = isset($_REQUEST['assigned']) ? $_REQUEST['assigned']: array();
	$end_month = $_REQUEST['end_month'];
	$end_day = $_REQUEST['end_day'];
	$end_year = $_REQUEST['end_year'];
	$start_month = $_REQUEST['start_month'];
	$start_day = $_REQUEST['start_day'];
	$start_year = $_REQUEST['start_year'];
	$title = $_REQUEST['title'];
	$description = $_REQUEST['description'];
	$url = $_REQUEST['url'];
	$proj_status = $_REQUEST['proj_status'];
	$project_leader = $_REQUEST['project_leader'];
}
$client_id = $_REQUEST['client_id'];
$proj_id = isset($_REQUEST['proj_id']) ? $_REQUEST['proj_id']: 0;

if (!isset($action)) {
//	Header("Location: $HTTP_REFERER");
	Common::errorPage("ERROR: No action has been passed.  Please fix.\n");
}
elseif ($action == "add") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	if ((!checkdate($end_month, $end_day, $end_year)) || (!checkdate($start_month, $start_day, $start_year))) {
		if (($start_day != 0 && $start_month != 0 && $start_year != 0) || ($end_day != 0 && $end_month != 0 && $end_year != 0))
				Common::errorPage("ERROR: Invalid date.  Please fix.\n");
	}

/*	$title = addslashes($title);
	$description = addslashes($description);
	$url = addslashes($url);*/

	list($qh, $num) = dbQuery("INSERT INTO  ".tbl::getProjectTable()."  (title, client_id, description, start_date, deadline, http_link, proj_status, proj_leader) VALUES ".
						"('$title','$client_id','$description', '$start_year-$start_month-$start_day', ".
						"'$end_year-$end_month-$end_day','$url', '$proj_status', '$project_leader')");
	list($qhp, $nump) = dbQuery("SELECT proj_id FROM  ".tbl::getProjectTable()."  WHERE client_id = '$client_id' AND description = '$description' ".
					" AND start_date = '$start_year-$start_month-$start_day' ".
					" AND deadline = '$end_year-$end_month-$end_day' AND proj_status = '$proj_status' AND proj_leader = '$project_leader'");
	$data = dbResult($qhp);
	$proj_id = $data['proj_id'];	
	//$proj_id = dbLastID(MySQLDB::getConnection());

	//create a time string for >>now<<
	$time_string = date("Y-m-d H:i:00");

	list($task_qh, $num) = dbQuery("INSERT INTO  ".tbl::getTaskTable()."  (proj_id, name, description, assigned, started, status)\n VALUES ".
							"($proj_id, 'Default Task', '', '$time_string', '$time_string', 'Started')");
	list($task_qht, $numt) = dbQuery("SELECT task_id FROM  ".tbl::getTaskTable()."  WHERE proj_id = '$proj_id' ". 
						" AND name = 'Default Task' AND status= 'Started'");
	//$task_id = dbLastID(MySQLDB::getConnection());
	$data = dbResult($task_qht);
	$task_id = $data['task_id'];
	//flag for whether the leader was added to the assignments
	$leader_added = false;

	//check if the leader was added to the assignments
	while (list(,$username) = each($assigned)) {
		if ($username == $project_leader)
			$leader_added = true;
		/*
		 * Had to add '0.00' to make the query match up to the database
		 */
		dbQuery("INSERT INTO  ".tbl::getAssignmentsTable()."  VALUES ($proj_id, '$username', 1)");
		dbQuery("INSERT INTO  ".tbl::getTaskAssignmentsTable()." (proj_id, task_id, username) VALUES ($proj_id, $task_id, '$username')");
	}
	if (!$leader_added) {
		// Add the project leader.
		/*
		 * Had to add '0.00' to make the query match up to the database
		 */
		dbQuery("INSERT INTO  ".tbl::getAssignmentsTable()."  VALUES ($proj_id, '$project_leader', 1)");
		dbQuery("INSERT INTO  ".tbl::getTaskAssignmentsTable()." (proj_id, task_id, username) VALUES ($proj_id, $task_id, '$project_leader')");
	}

	// we're done adding the project so redirect to the maintenance page
	gotoLocation(Config::getRelativeRoot()."/projects/proj_maint?client_id=$client_id");

}
elseif ($action == "edit") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	if ((!checkdate($end_month, $end_day, $end_year)) || (!checkdate($start_month, $start_day, $start_year))) {
		if (($start_day != 0 && $start_month != 0 && $start_year != 0) || ($end_day != 0 && $end_month != 0 && $end_year != 0))
			Common::errorPage("ERROR: Invalid date.  Please fix.\n");
	}

/*	$title = addslashes($title);
	$description = addslashes($description);
	$url = addslashes($url);*/

	$query = "UPDATE  ".tbl::getProjectTable()."  set title='$title',client_id='$client_id',description='$description',".
				"start_date='$start_year-$start_month-$start_day', proj_status='$proj_status', proj_leader='$project_leader', ".
				"deadline='$end_year-$end_month-$end_day',http_link='$url' WHERE proj_id=$proj_id";

	list($qh,$num) = dbquery($query);

	if ($assigned) {
		dbQuery("DELETE FROM  ".tbl::getAssignmentsTable()."  WHERE proj_id = $proj_id");
		while (list(,$username) = each($assigned)) {
			dbQuery("INSERT INTO  ".tbl::getAssignmentsTable()."  VALUES ($proj_id, '$username', 1)");
		}
	}

	//we're done editing, so redirect back to the maintenance page
	gotoLocation(Config::getRelativeRoot()."/projects/proj_maint?client_id=$client_id");
}
elseif ($action == 'delete') {
	dbQuery("DELETE FROM  ".tbl::getTaskAssignmentsTable()."  WHERE proj_id = $proj_id");
	dbQuery("DELETE FROM  ".tbl::getTaskTable()."  WHERE proj_id = $proj_id");
	dbQuery("DELETE FROM  ".tbl::getProjectTable()."  WHERE proj_id=$proj_id");
	dbQuery("DELETE FROM  ".tbl::getAssignmentsTable()."  WHERE proj_id=$proj_id");
	gotoLocation(Config::getRelativeRoot()."/projects/proj_maint?client_id=$client_id");
}

// vim:ai:ts=4:sw=4
?>
