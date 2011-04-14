<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;

//load local vars from request/post/get
$action = $_REQUEST["action"];
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$proj_id = $_REQUEST["proj_id"];

if ($action == "add" || $action == "edit") {
	$name = $_REQUEST["name"];
	$description = $_REQUEST["description"];
	$assigned = isset($_REQUEST["assigned"]) ? $_REQUEST['assigned']: array();
	$task_status = $_REQUEST["task_status"];
}

//create a time string for >>now<<
$time_string = date("Y-m-d H:i:00");

if (!isset($action))
	gotoLocation($HTTP_REFERER);
elseif ($action == "add") {
	$name = addslashes($name);
	$description = addslashes($description);

	list($qh, $num) = dbQuery("INSERT INTO ".tbl::getTaskTable()." (proj_id, name, description, assigned, started, status) VALUES ".
						"('$proj_id', '$name','$description', ".
						"'$time_string', '$time_string', '$task_status')");
	//$task_id = dbLastID($dbh);
	list($qhqt, $numt) = dbQuery("SELECT task_id FROM ".tbl::getTaskTable().
				" WHERE proj_id= '$proj_id' AND name = '$name' AND description = '$description' ".
				" AND started = '$time_string' AND status= '$task_status'");
	$data = dbResult($qhqt);
	$task_id = $data['task_id'];		

	if (isset($assigned)) {
		while (list(,$username) = each($assigned))
			dbQuery("INSERT INTO ".tbl::getTaskAssignmentsTable()." (proj_id, task_id, username) VALUES ($proj_id, $task_id, '$username')");
	}

	// redirect to the task management page (we're done)
	gotoLocation(Config::getRelativeRoot()."/tasks/task_maint?proj_id=$proj_id");
} elseif ($action == "edit") {
	$name = addslashes($name);
	$description = addslashes($description);

	$query = "UPDATE ".tbl::getTaskTable()." SET name='$name',description='$description',".
				" status='$task_status' ";
	if ($task_status=='Complete') {
		$query .=	",completed='$time_string'";
	}
	$query .=		" WHERE task_id=$task_id";
	
	list($qh,$num) = dbquery($query);

	if ($assigned) {
		dbQuery("DELETE FROM ".tbl::getTaskAssignmentsTable()." WHERE task_id = $task_id");
			while (list(,$username) = each($assigned))
				dbQuery("INSERT INTO ".tbl::getTaskAssignmentsTable()." (proj_id, task_id, username) VALUES ($proj_id, $task_id, '$username')");
	}

		// we're done so redirect to the task management page
		gotoLocation(Config::getRelativeRoot()."/tasks/task_maint?proj_id=$proj_id");
	} elseif ($action == 'delete') {
		dbQuery("DELETE FROM ".tbl::getTaskTable()."  WHERE task_id = $task_id");
		dbQuery("DELETE FROM ".tbl::getTaskAssignmentsTable()."  WHERE task_id = $task_id");
		gotoLocation(Config::getRelativeRoot()."/tasks/task_maint?proj_id=$proj_id");
	}

// vim:ai:ts=4:sw=4
?>
