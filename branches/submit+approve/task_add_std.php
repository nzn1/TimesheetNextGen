<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_add_std.php,v 1.0 2010/04/26 09:00:00 vexil Exp $
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclTasks')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . get_acl_level('aclTasks'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$proj_id = 0;

//define the command menu
$commandMenu->add(new TextCommand("Back", true, "javascript:history.back()"));

//build the database query
$query = "SELECT task_id, name, description from $STD_TASK_TABLE ORDER BY name";

list($stdtasks, $numtasks) = dbQuery($query);
?>
<html>
<head>
	<title>Add New Standard Tasks</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<!-- form action="task_test.php" method="post" -->

<form action="task_std_action.php" method="post">
<input type="hidden" name="action" value="add">
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>">

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Add New Standard Tasks:
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>
<h3>Add New Standard Tasks</h3>
<table>
<tr>
	<td>Add</td>
	<td>Task Name</td>
	<td>Description</td>
</tr>
<tr>
	<td><input type="checkbox" name="add1" size="4" style="width: 100%"></td>
	<td><input type="text" name="task1" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr1" size="42" style="width: 100%"></td>
</tr>
<tr>
	<td><input type="checkbox" name="add2" size="4" style="width: 100%"></td>
	<td><input type="text" name="task2" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr2" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add3" size="4" style="width: 100%"></td>
	<td><input type="text" name="task3" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr3" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add4" size="4" style="width: 100%"></td>
	<td><input type="text" name="task4" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr4" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add5" size="4" style="width: 100%"></td>
	<td><input type="text" name="task5" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr5" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add6" size="4" style="width: 100%"></td>
	<td><input type="text" name="task6" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr6" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add7" size="4" style="width: 100%"></td>
	<td><input type="text" name="task7" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr7" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add8" size="4" style="width: 100%"></td>
	<td><input type="text" name="task8" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr8" size="42" style="width: 100%"></td>
</tr><tr>
	<td><input type="checkbox" name="add9" size="4" style="width: 100%"></td>
	<td><input type="text" name="task9" size="42" style="width: 100%"></td>
	<td><input type="text" name="descr9" size="42" style="width: 100%"></td>
</tr>
</table>
<h3>Delete Existing Standard Tasks</h3>
<table>
<tr>
	<td>Del</td>
	<td>Task Name</td>
	<td>Description</td>
</tr>

<?php
	if($numtasks > 0) {
		for($i = 0; $i < $numtasks; $i++) {
			$data = dbResult($stdtasks, $i);
	print("<tr><td><input type=\"checkbox\" name=\"del".$data["task_id"]."\" size=\"4\" style=\"width: 100%\"></td><td>".$data["name"]."</td><td>".$data["description"]."</td></tr>");
	}
	}
	else {
		print("No existing tasks");
		}
		?>
	</table>
<table>
		<tr>
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Add and Delete Selected New Standard Tasks">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>

<?php include("footer.inc"); ?>
</body>
</html>