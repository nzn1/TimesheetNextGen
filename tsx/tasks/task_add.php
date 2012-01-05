<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTasks'))return;

//load local vars from request/post/get
$proj_id = gbl::getProjId(); //$_REQUEST['proj_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_TASK')."</title>");
PageElements::setTheme('txsheet2');

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/tasks/task_action" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>" />

<?php 
// get name of project and client name using project id

	$queryString = "SELECT title, organisation ".
				" FROM ".tbl::getProjectTable(). " p, ". tbl::getClientTable()." c " .
				" WHERE proj_id= '$proj_id' AND p.client_id = c.client_id";
	list($qh, $num) = dbQuery($queryString);
	$data = dbResult($qh);
	LogFile::write(" Get title organisation  Query = \"$queryString\" and rows returned is \"$num\"\n");
	
?>
<h1><?php echo JText::_('ADD_NEW_TASK_IN_PROJECT'). "<i>".$data['title']."</i>".
	 JText::_('FOR_CLIENT'). "<i>". $data['organisation'] ."</i>"; ?>:</h1>
<div><label><?php echo (JText::_('TASK_NAME')) ?>:</label><input type="text" name="name" size="42" /></div>
<div><label><?php echo (JText::_('DESCRIPTION')) ?>:</label><textarea name="description" rows="4" cols="40" wrap="virtual"></textarea></div>
<div><label><?php echo (JText::_('STATUS')) ?>:</label><?php Common::proj_status_list("task_status", "Started"); ?></div>
<div><label><?php echo (JText::_('TASK_MEMBERS')) ?>:</label><?php Common::multi_user_select_list("assigned[]"); ?></div>

<table class="noborder">
	<tbody class="nobground">
		<tr>
		<td align="center" colspan="2">
			<input type="submit" value="<?php echo (JText::_('ADD_NEW_TASK')) ?>" />
		</td>
		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	</tr>
	<tr>
		<td class="outer_table_heading">
			<h1><?php echo JText::_('ADD_STANDARD_TASKS_IN_PROJECT'). "<i>". $data['title']. "</i>". JText::_('FOR_CLIENT'). "<i>". $data['organisation']. "</i>"; ?>:</h1>
		</td>
	</tr>

</table>
	<?php 
		// add standard tasks to the selection list
		// get all the standard tasks
		$query = "SELECT task_id, name, description from " .tbl::getStdTaskTable()." ORDER BY name";

		list($stdtasks, $numtasks) = dbQuery($query);
		$noneSelected = array();
		$size = 4; // selects size of multi_user_select_list. Minimum of 4 to get a vert scroll bar
		if($numtasks > 0) {
	?>
<div id ="simple">
<table class="simpleTable">
	<thead class="table_head">
		<tr>
			<th><?php echo JText::_('SELECT_ADD'); ?></th>
			<th><?php echo JText::_('TASK_NAME'); ?></th>
			<th><?php echo JText::_('DESCRIPTION'); ?></th>
			<th><?php echo JText::_('STATUS'); ?></th>
			<th><?php echo JText::_('ASSIGNMENTS'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
		for($i = 0; $i < $numtasks; $i++) {
			$data = dbResult($stdtasks, $i);
			$task_id = $data["task_id"];
			if(($i % 2) ==1)
				print "<tr class=\"diff\">\n";
			else
				print "<tr>\n";
	?>
				<td class="calendar_cell_middle">
					<input type="checkbox" name="add<?php echo $i; ?>"></td>
				<td class="calendar_cell_middle"><?php echo $data["name"]; ?></td>
				<td class="calendar_cell_middle"><?php echo $data["description"]; ?></td>
	<?php 
			print("<td><input type=\"hidden\" name=\"id$i\" value=\"$task_id\">");
				Common::proj_status_list("task_status$i", "Pending");
			print("</td><td>");
				Common::multi_user_select_list("stdassigned".$i."[]", $noneSelected, $size );
			print("</td></tr>");
		}
	}
	
	?>


</table>
</div>
</form>