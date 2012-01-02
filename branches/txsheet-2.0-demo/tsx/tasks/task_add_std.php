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
PageElements::setTheme('newcss');
?>
<!-- form action="task_test.php" method="post" -->

<form action="task_std_action" method="post">
<input type="hidden" name="action" value="add">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">

					<tr>
						<td align="left" class="outer_table_heading" >
							<?php echo JText::_('ADD_NEW_STANDARD_TASK') ?>
						</td>
					</tr>
				</table>



<h3><?php echo JText::_('ADD_NEW_STANDARD_TASK') ?></h3>
<?php echo JText::_('STANDARD_TASK') ?>
<table>
<thead>
<tr>
	<th>Add</th>
	<th>Task Name</th>
	<th>Description</th>
</tr>
</thead>
<tbody>

<?php 
	for ($row = 0; $row < 10; $row++) {
		print "<tr>"; // start a new  row
		print "<td><input type=\"checkbox\" name=\"add" .$row. "\" size=\"4\"></td>";
		print "<td><input type=\"text\" name=\"task" .$row . "\" size=\"42\"></td>";
		print "<td><input type=\"text\" name=\"descr" . $row. "\" size=\"42\"></td>";
		print "</tr>"; // end this row
	}
?>

</table>
<h3>Delete Existing Standard Tasks</h3>
<table>
<thead>
<tr>
	<th>Del</th>
	<th>Task Name</th>
	<th>Description</th>
</tr>

<?php
	if($numtasks > 0) {
		// continue row count from previous input rows
		for($i = 0; $i < $numtasks; $i++) {
			$data = dbResult($stdtasks, $i);
			print("<tr><td><input type=\"checkbox\" name=\"del".$row."\" size=\"4\" >" .
				"<input type=\"hidden\" name=\"task".$row. "\" value=\"".$data["task_id"]."\">" .
				"</td><td>".$data["name"]."</td><td>".$data["description"]."</td></tr>");
			$row++;
		}
	}
	else {
		print("<tr><td>" . JText::_('NO_EXISTING_TASKS') . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>");
	}
?>
	<tr>
		<td align="center" colspan="3">
			<input type="submit" value="Add and Delete Selected New Standard Tasks">
		</td>
	</table>

</form>
