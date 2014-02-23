<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTasks'))return;
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from post
$action = $_REQUEST['action'];

if (!isset($action)) {
//	Header("Location: $HTTP_REFERER");
	errorPage("ERROR: No action has been passed.  Please fix.\n");
}
elseif ($action == "add") {
	// Do add type things in here, then send back to proj_maint.php.
	// No error checking for now.
	//TODO Fix upper limit of for loop to the max no of rows
	for($i = 0; $i <30; $i++) {	
		if (isset($_REQUEST['add'.$i])) {
			$action = "add"; // add row?
		}
		elseif (isset($_REQUEST['del'.$i])) {
			$action = "del"; // delete row?
		}
		else $action = "none";
		if ($action == "add") {
			$rowAction = $_REQUEST['add'.$i];
			$task = $_REQUEST['task'.$i];
			$descr = $_REQUEST['descr'.$i];
			// debug echo "loop: ". $i ." Key: ".$key . " Value: ".$value."<br />";
			if($rowAction = "on") {
				// add new task
				list($qh, $num) = dbQuery("INSERT INTO ".tbl::getStdTaskTable(). " (`task_id`, `name`, `description`) VALUES (NULL, '$task' , '$descr')");
			}
		}
		elseif($action == "del") {
			$stdtaskno = $_REQUEST['task'.$i];
			list($qh, $num) = dbQuery("DELETE FROM ".tbl::getStdTaskTable(). " WHERE task_id = $stdtaskno ");
		}

	}

	//we're done editing, so redirect back to the maintenance page
	$Location = Config::getRelativeRoot()."/tasks/task_add_std";
	gotoLocation($Location);
}	
?>
