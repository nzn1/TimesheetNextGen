<?php
if(!class_exists('Site'))die('Restricted Access');

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
		gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=" . Common::get_acl_level('aclSimple'));

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
$task_id = gbl::getTaskId();

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

PageElements::setHead("<title>".Config::getMainTitle()." | Edit Task | ".$data["name"]."</title>");
?>

<form action="<?php echo Config::getRelativeRoot(); ?>/task_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="proj_id" value="<?php echo $data["proj_id"]; ?>" />
<input type="hidden" name="task_id" value="<?php echo $data["task_id"]; ?>" />
<div id="inputArea">
<table align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2" class="outer_table_heading">
			<h1>Edit Task: <?php echo $data["name"]; ?> </h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right"><label for="name">Task Name:</label></td>
		<td><input type="text" name="name" id="name" size="42" value="<?php echo $data["name"]; ?>" style="width: 100%" /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Description:</td>
		<td><textarea name="description" rows="4" cols="40" style="width: 100%"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></td>
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
		<td></td>
		<td>
			<input type="submit" value="Update" />
		</td>
	</tr>
</table>
</div>
</form>
