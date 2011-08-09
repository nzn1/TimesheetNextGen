<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from request/post/get
$proj_id = gbl::getProjId(); //$_REQUEST['proj_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_TASK')."</title>");
PageElements::setTheme('newcss');

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/tasks/task_action" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>" />
<div id="inputArea">
<?php 
// get name of project and client name using project id

	$queryString = "SELECT title, organisation ".
				" FROM ".tbl::getProjectTable(). " p, ". tbl::getClientTable()." c " .
				" WHERE proj_id= '$proj_id' AND p.client_id = c.client_id";
	list($qh, $num) = dbQuery($queryString);
	$data = dbResult($qh);
	LogFile::write(" Get title organisation  Query = \"$queryString\" and rows returned is \"$num\"\n");
	
?>
<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="outer_table_heading">
			<h3><?php echo (JText::_('ADD_NEW_TASK')). " ". JText::_('PROJECT'). $data['title']. JText::_('FOR_CLIENT'). $data['organisation'] ?>:</h3>
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo (JText::_('TASK_NAME')) ?>:</td>
		<td><input type="text" name="name" size="42" style="width: 100%" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo (JText::_('DESCRIPTION')) ?>:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo (JText::_('STATUS')) ?>:</td>
		<td><?php Common::proj_status_list("task_status", "Started"); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo (JText::_('TASK_MEMBERS')) ?>:</td>
		<td><?php Common::multi_user_select_list("assigned[]"); ?></td>
	</tr>
	<tr>
		<td class="outer_table_heading">
			<h3><?php echo (JText::_('ADD_STANDARD_TASKS')). " ". JText::_('PROJECT'). $data['title']. JText::_('FOR_CLIENT'). $data['organisation'] ?>:</h3>
		</td>
	</tr>
</table>
	<?php 
		// add standard tasks to the selection list
		// get all the standard tasks
		$query = "SELECT task_id, name, description from " .tbl::getTaskTable()." ORDER BY name";

		list($stdtasks, $numtasks) = dbQuery($query);
		$noneSelected = array();
		$size = 4; // selects size of multi_user_select_list. Minimum of 4 to get a vert scroll bar
		if($numtasks > 0) {
	?>
<table>
	<thead>
		<tr>
			<th><?php echo JText::_('SELECT_ADD'); ?></t>
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
	?>
			<tr>
				<td>
					<input type="checkbox" name="add<?php echo $i; ?>" size="4" style="width: 100%"></td>
				<td><?php echo $data["name"]; ?></td>
				<td><?php echo $data["description"]; ?></td>
	<?php 
			print("<td><input type=\"hidden\" name=\"id$i\" value=\"$task_id\">");
				Common::proj_status_list("task_status$i", "Pending");
			print("</td><td>");
				Common::multi_user_select_list("stdassigned".$i."[]", $noneSelected, $size );
			print("</td>	</tr>");
		}
	}
	
	?>
	<tr>
		<td align="center">
			<input type="submit" value="<?php echo (JText::_('ADD_NEW_TASK')) ?>" />
		</td>
	</tr>

</table>
</div>
</form>