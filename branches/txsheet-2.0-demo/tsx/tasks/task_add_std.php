<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_add_std.php,v 1.0 2010/04/26 09:00:00 vexil Exp $
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTasks'))return;

$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$proj_id = 0;

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

//build the database query
$query = "SELECT task_id, name, description from " .tbl::getStdTaskTable(). " ORDER BY name";

list($stdtasks, $numtasks) = dbQuery($query);
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('STANDARD_TASKS')."</title>");
PageElements::setTheme('txsheet2');
?>
<!-- form action="task_test.php" method="post" -->

<form action="task_std_action" method="post">
<input type="hidden" name="action" value="add">


<h1><?php echo JText::_('ADD_NEW_STANDARD_TASK') ?></h1>
<?php echo JText::_('STANDARD_TASK') ?>

<div id ="simple">
<table class="simpleTable">
	<thead class="table_head">
<tr>
	<th>Add</th>
	<th>Task Name</th>
	<th>Description</th>
</tr>
</thead>
<tbody>

<?php 
	for ($row = 0; $row < 10; $row++) {
		
		print "<tr>\n";
		print "<td class=\"calendar_cell_middle\"><input type=\"checkbox\" name=\"add" .$row. "\" size=\"4\"></td>";
		print "<td class=\"calendar_cell_middle\"><input type=\"text\" name=\"task" .$row . "\" size=\"42\"></td>";
		print "<td class=\"calendar_cell_middle\"><input type=\"text\" name=\"descr" . $row. "\" size=\"42\"></td>";
		print "</tr>"; // end this row
	}
?>
	<tr>
		<td align="center" colspan="3">
			<input type="submit" value="<?php echo JText::_('ADD_DEL_STD_TASKS')?>">
		</td>
	</tr>
</table>
<h1><?php echo JText::_('DELETE_EXISTING_STD_TASKS');?> </h1>
<table class="simpleTable">
	<thead class="table_head">
	<tr>
	<th><?php echo JText::_('DELETE');?></th>
	<th><?php echo JText::_('TASK_NAME');?></th>
	<th><?php echo JText::_('DESCRIPTION');?></th>
</tr>
</thead>
<tbody>

<?php
	if($numtasks > 0) {
		// continue row count from previous input rows
		for($i = 0; $i < $numtasks; $i++) {
			$data = dbResult($stdtasks, $i);
			if(($i % 2) ==1)
				print "<tr class=\"diff\">\n";
			else
				print "<tr>\n";
			print("<td class=\"calendar_cell_middle\"><input type=\"checkbox\" name=\"del".$row."\" size=\"4\" >" .
				"<input type=\"hidden\" name=\"task".$row. "\" value=\"".$data["task_id"]."\">" .
				"</td><td class=\"calendar_cell_middle\">".$data["name"]."</td><td class=\"calendar_cell_middle\">".$data["description"]."</td></tr>");
			$row++;
		}
	}
	else {
	}
?>

	</table>

</form>
