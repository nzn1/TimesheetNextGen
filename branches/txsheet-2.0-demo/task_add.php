<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_add.php,v 1.6 2004/07/02 14:15:56 vexil Exp $
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

//load local vars from superglobals
$proj_id = $_REQUEST['proj_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand("Back", true, "javascript:history.back()"));

?>
<html>
<head>
	<title>Add New Task</title>
</head>


<form action="task_action.php" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
			<h1>Add New Task:</h1>
		</td>
	</tr>
	<tr>
		<td align="right">Task Name:</td>
		<td><input type="text" name="name" size="42" style="width: 100%" /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Description:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%"></textarea></td>
	</tr>
	<tr>
		<td align="right">Status:</td>
		<td><?php Common::proj_status_list("task_status", "Started"); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top">Assignments:</td>
		<td><?php Common::multi_user_select_list("assigned[]"); ?></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" value="Add New Task" />
		</td>
	</tr>

</table>

</form>