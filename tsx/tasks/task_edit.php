<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");

//load local vars from request/post/get
//$task_id = gbl::getTaskId();
$task_id =  $_REQUEST['task_id'];
//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));
Site::getCommandMenu()->add(new TextCommand("&nbsp; &nbsp; &nbsp;", false, ""));
Site::getCommandMenu()->add(new TextCommand("Copy Projects/Tasks between users", true, Config::getRelativeRoot(). "/users/user_clone.php"));

//query database for existing task values

list($qh, $num) = dbQuery("SELECT task_id, proj_id, name, description, status FROM ".tbl::getTaskTable(). " WHERE task_id = $task_id ");
$data = dbResult($qh);

list($qh, $num) = dbQuery("SELECT username FROM ".tbl::getTaskAssignmentsTable()." WHERE proj_id = $data[proj_id] AND task_id = $task_id");
$selected_array = array();
$i = 0;
while ($datanext = dbResult($qh)) {
	$selected_array[$i] = $datanext["username"];
	$i++;
}

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_TASK') ." | ".$data["name"]."</title>");
PageElements::setTheme('newcss');
?>
<h1><?php echo JText::_('EDIT_TASK').": ".$data["name"]; ?> </h1>

<form action="<?php echo Config::getRelativeRoot(); ?>/tasks/task_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="proj_id" value="<?php echo $data["proj_id"]; ?>" />
<input type="hidden" name="task_id" value="<?php echo $data["task_id"]; ?>" />
<div id="inputArea">
<table class="noborder">
	<tbody class="nobground">
		<td colspan="2" class="outer_table_heading">
		</td>
	</tr>

	<tr>
		<td align="right"><label for="name"><?php echo JText::_('TASK_NAME'); ?>:</label></td>
		<td><input type="text" name="name" id="name" size="42" value="<?php echo $data["name"]; ?>"/></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('DESCRIPTION'); ?>:</td>
		<td><textarea name="description" rows="4" cols="40"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('STATUS'); ?>:</td>
		<td><?php Common::proj_status_list("task_status", $data["status"]); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('TASK_MEMBERS'); ?>:</td>
		<td><?php Common::multi_user_select_list("assigned[]",$selected_array); ?></td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="submit" value="<?php echo JText::_('CONFIRM'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>
