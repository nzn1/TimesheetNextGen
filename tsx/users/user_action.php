<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclReports'))return;

//load local vars from request/post/get
$action = $_REQUEST["action"];
$uid = $_REQUEST["uid"];
$first_name = $_REQUEST["first_name"];
$last_name = $_REQUEST["last_name"];
$username = $_REQUEST["username"];
$email_address = $_REQUEST["email_address"];
$password = $_REQUEST["password"];
$isAdministrator = isset($_REQUEST["isAdministrator"]) ? $_REQUEST["isAdministrator"]: "false";
$isManager = isset($_REQUEST["isManager"]) ? $_REQUEST["isManager"]: "false";
$status = isset($_REQUEST["isActive"]) ? ($_REQUEST["isActive"]=="true" ? "ACTIVE" : "INACTIVE") : "ACTIVE";

//LogFile::->write("status = \"$status\"  isActive=\"".$_REQUEST["isActive"]."\"\n");

//print "<p>isAdministrator='$isAdministrator'</p>";


if ($action == "delete") {
	dbquery("DELETE FROM ".tbl::getUserTable()." WHERE uid='$uid'");
	dbquery("DELETE FROM ".tbl::getAssignmentsTable()." WHERE username='$username'");
	dbquery("DELETE FROM ".tbl::getTaskAssignmentsTable()." WHERE username='$username'");
} else if ($action == "addupdate") {
	//set the level
	if ($isAdministrator == "true")
		$level = 11;
	else if ($isManager == "true")
		$level = 6;
	else
		$level = 1;

	//check whether the user exists, and get his encrypted password.
	list($qh,$num) = dbQuery("SELECT username, password FROM ".tbl::getuserTable()." WHERE uid='$uid'");

	//if there is a match
	if ($data = dbResult($qh)) {

		//has the username changed
		if ($data["username"] != $username) {
			//update the assignments
			dbQuery("UPDATE ".tbl::getAssignmentsTable()." SET username='$username' WHERE username='$data[username]'");
			dbQuery("UPDATE ".tbl::getTaskAssignmentsTable()." SET username='$username' WHERE username='$data[username]'");
			dbQuery("UPDATE ".tbl::getProjectTable()." SET proj_leader='$username' WHERE proj_leader='$data[username]'");
			dbQuery("UPDATE ".tbl::getTimesTable()." SET uid='$username' WHERE uid='$data[username]'");
		}

		if ($data["password"] == $password) {
			//then we are not updating the password
			dbquery("UPDATE ".tbl::getuserTable()." SET first_name='$first_name', last_name='$last_name', ".
								"status='$status', " .
								"username='$username', " .
								"email_address='$email_address', ".
								"level='$level' ".
								"WHERE uid='$uid'");
		} else {
			//set the password as well
			dbquery("UPDATE ".tbl::getuserTable()." SET first_name='$first_name', last_name='$last_name', ".
								"status='$status', " .
								"username='$username', " .
								"email_address='$email_address', ".
								"level='$level', ".
								"password=".config::getDbPwdFunction()."('$password') " .
								"WHERE uid='$uid'");
		}
	} else {
		// a new user
		dbquery("INSERT INTO ".tbl::getuserTable()." (username, level, password, first_name, ".
							"last_name, email_address, time_stamp, status) " .
						"VALUES ('$username',$level,".config::getDbPwdFunction()."('$password'),'$first_name',".
							"'$last_name','$email_address',0,'$status')");
		dbquery("INSERT INTO ".tbl::getAssignmentsTable()." VALUES (1,'$username', 1)"); // add default project.
		dbquery("INSERT INTO ".tbl::getTaskAssignmentsTable()." VALUES (1,'$username', 1)"); // add default task
		//create a time string for >>now<<
		$today_stamp = date("Y-m-d H:i:00");
		dbquery("INSERT INTO ".tbl::getAllowanceTable()." VALUES (NULL,'$username', '$today_stamp', 0, 0.0)"); // add default allowance
	}
}

//redirect back to the user management page
gotoLocation(Config::getRelativeRoot()."/user_maint");

?>
