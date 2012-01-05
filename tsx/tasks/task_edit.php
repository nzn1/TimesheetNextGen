<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTasks'))return;

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

list($qh, $num) = dbQuery("SELECT task_id, t.proj_id, title, t.name, t.description, status, c.organisation FROM ".tbl::getTaskTable(). 
	" t, " .tbl::getProjectTable(). " p, " .tbl::getClientTable().
	" c WHERE t.task_id = $task_id AND t.proj_id = p.proj_id AND p.client_id = c.client_id");
$data = dbResult($qh);

list($qh, $num) = dbQuery("SELECT username FROM ".tbl::getTaskAssignmentsTable()." WHERE proj_id = $data[proj_id] AND task_id = $task_id");
$selected_array = array();
$i = 0;
while ($datanext = dbResult($qh)) {
	$selected_array[$i] = $datanext["username"];
	$i++;
}

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_TASK') ." | ".$data["name"]."</title>");
PageElements::setTheme('txsheet2');
?>
<h1><?php echo JText::_('EDIT_TASK'). "<i>  " . $data["name"]."</i>  ".JText::_('FOR_PROJECT').
			    "  <i>" .$data['title']."</i>  ". JText::_('FOR_CLIENT'). " <i>".$data['organisation']. "</i>"; ?>:</h1>

<form action="<?php echo Config::getRelativeRoot(); ?>/tasks/task_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="proj_id" value="<?php echo $data["proj_id"]; ?>" />
<input type="hidden" name="task_id" value="<?php echo $data["task_id"]; ?>" />
<div id="inputArea">

<div><label><?php echo (JText::_('TASK_NAME')) ?>:</label><input type="text" name="name" size="42" value="<?php echo $data['name']?>"/></div>
<div><label><?php echo JText::_('DESCRIPTION'); ?>:</label><textarea name="description" rows="4" cols="40"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></div>
<div><label><?php echo JText::_('STATUS'); ?>:</label><?php Common::proj_status_list("task_status", $data["status"]); ?></div>
<div><label><?php echo JText::_('TASK_MEMBERS'); ?>:</label><?php Common::multi_user_select_list("assigned[]",$selected_array); ?></div>
<div><label></label><input type="submit" value="<?php echo JText::_('CONFIRM'); ?>" /></div>
</div>
</form>
