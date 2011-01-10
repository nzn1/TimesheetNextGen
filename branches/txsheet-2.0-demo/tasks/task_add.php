<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
		gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	exit;
}

//load local vars from superglobals
$proj_id = $_REQUEST['proj_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand("Back", true, "javascript:history.back()"));

?>
<title><?php echo Config::getMainTitle();?> - Task Add</title>

<form action="<?php echo Config::getRelativeRoot(); ?>/tasks/task_action" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>" />
<div id="inputArea">
<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="outer_table_heading">
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
</div>
</form>