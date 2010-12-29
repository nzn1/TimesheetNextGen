<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_edit.php,v 1.6 2004/07/02 14:15:56 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclSimple'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	}
	
	exit;
}
$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");
	
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$task_id = $_REQUEST['task_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand("Back", true, "javascript:history.back()"));
Site::getCommandMenu()->add(new TextCommand("&nbsp; &nbsp; &nbsp;", false, ""));
Site::getCommandMenu()->add(new TextCommand("Copy Projects/Tasks between users", true, "user_clone.php"));

//query database for existing task values

$TASK_ASSIGNMENTS_TABLE = tbl::getTaskAssignmentsTable();
$TASK_TABLE = tbl::getTaskTable();

list($qh, $num) = dbQuery("SELECT task_id, proj_id, name, description, status FROM $TASK_TABLE WHERE task_id = $task_id ");
$data = dbResult($qh);

list($qh, $num) = dbQuery("SELECT username FROM $TASK_ASSIGNMENTS_TABLE WHERE proj_id = $data[proj_id] AND task_id = $task_id");
$selected_array = array();
$i = 0;
while ($datanext = dbResult($qh)) {
	$selected_array[$i] = $datanext["username"];
	$i++;
}

?>
<html>
<head>
	<title>Edit Task</title>
</head>


<form action="task_action.php" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="proj_id" value="<?php echo $data["proj_id"]; ?>" />
<input type="hidden" name="task_id" value="<?php echo $data["task_id"]; ?>" />
<div id="inputArea">
<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="outer_table_heading">
			<h1>Edit Task: <?php echo $data["name"]; ?> </h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right">Task Name:</td>
		<td><input type="text" name="name" size="42" value="<?php echo $data["name"]; ?>" style="width: 100%" /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Description:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></td>
	</tr>
	<tr>
		<td align="right">Status:</td>
		<td><?php Common::proj_status_list("task_status", $data["status"]); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top">Assignments:</td>
		<td><?php Common::multi_user_select_list("assigned[]",$selected_array); ?></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" value="Update" />
		</td>
	</tr>
</table>
</div>
</form>
