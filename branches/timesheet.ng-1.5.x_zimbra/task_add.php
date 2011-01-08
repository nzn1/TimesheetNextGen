<?php
// $Header: /cvsroot/tsheet/timesheet.php/task_add.php,v 1.6 2004/07/02 14:15:56 vexil Exp $
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasAccess('aclTasks')) {
	Header("Location: login.php?redirect=$_SERVER[PHP_SELF]&amp;clearanceRequired=" . get_acl_level('aclTasks'));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$proj_id = $_REQUEST['proj_id'];

//define the command menu
$commandMenu->add(new TextCommand("Back", true, "javascript:history.back()"));

// get all the standard tasks
$query = "SELECT task_id, name, description from $STD_TASK_TABLE ORDER BY name";

list($stdtasks, $numtasks) = dbQuery($query);

?>
<html>
<head>
	<title>Add New Task</title>
<?php include ("header.inc"); ?>
</head>
<body <?php include ("body.inc"); ?> >
<?php include ("banner.inc"); ?>

<form action="task_action.php" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="proj_id" value="<?php echo $proj_id ?>" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Add New Task:
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
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
						<td><?php proj_status_list("task_status", "Started"); ?></td>
					</tr>
					<tr>
						<td align="right" valign="top">Assignments:</td>
						<td><?php multi_user_select_list("assigned[]"); ?></td>
					</tr>
				</table>
		<?php
		// add standard tasks to the selection list
		if($numtasks > 0) {

			print("<h3>Add Existing Standard Tasks</h3><table><tr><td>Add</td><td>Task Name</td><td>Description</td><td>Status</td><td>Assignments</td></tr>");
			for($i = 0; $i < $numtasks; $i++) {
				$data = dbResult($stdtasks, $i);
				$task_id = $data["task_id"];
				print("<tr><td><input type=\"checkbox\" name=\"add".$i."\" size=\"4\" style=\"width: 100%\"></td><td>".$data["name"]."</td><td>".$data["description"]."</td>");
				print("<td><input type=\"hidden\" name=\"id$i\" value=\"$task_id\"></td><td>");
				proj_status_list("task_status$i", "Pending");
				print("<td>");
				user_select_list("stdassigned".$i."[]");
				print("</td>	</tr>");
			}
		}
	
	?>
	</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Add New Task" />
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
<?php
// vim:ai:ts=4:sw=4
?>