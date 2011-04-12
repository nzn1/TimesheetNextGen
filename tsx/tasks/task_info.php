<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

//load local vars from superglobals
$task_id = $_REQUEST['task_id'];


//build query
$query_task = "SELECT DISTINCT task_id, name, description,status, ".
				"DATE_FORMAT(assigned, '%M %d, %Y') as assigned,".
				"DATE_FORMAT(started, '%M %d, %Y') as started,".
				"DATE_FORMAT(suspended, '%M %d, %Y') as suspended,".
				"DATE_FORMAT(completed, '%M %d, %Y') as completed ".
			"FROM  ".tbl::getTaskTable()." ".
			"WHERE task_id=$task_id ".
					"ORDER BY task_id";

//get the proj_id for this task
if (!isset($proj_id)) {
	list($qh, $num) = $proj_id = dbQuery("SELECT proj_id FROM  ".tbl::getTaskTable()."  WHERE task_id='$task_id'");
	$results = dbResult($qh);
	$proj_id = $results["proj_id"];
}

$query_project = "SELECT DISTINCT title, description,".
			"DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
			"DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
			"proj_status, proj_leader ".
		"FROM  ".tbl::getProjectTable()."  ".
		"WHERE proj_id=$proj_id";

?>
<html>
<head>
<title><?php echo Config::getMainTitle();?> - Task Information </title>

</head>

<table border="0" width="100%" height="100%" align="center" valign="center">
<?php

		list($qh, $num) = dbQuery($query_task);
		if ($num > 0) {
			$data_task = dbResult($qh);

?>
		<tr>
			<td>
				<table width="100%" border="0" class="section_body">
					<tr>
						<td valign="center">
							<span class="project_title"><?php echo stripslashes($data_task["name"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data_task["status"]; ?>&gt;</span><br />
								<?php echo stripslashes($data_task["description"]); ?>
						</td>
					</tr>
					<tr>
						<td align="left" colspan="2" align="top">
							<span class="label">Assigned persons:</span><br />
<?php
			//get assigned users
			list($qh3, $num_3) = dbQuery("SELECT username, task_id FROM  ".tbl::getTaskAssignmentsTable()."  WHERE task_id=$data_task[task_id]");
			if ($num_3 > 0) {
				while ($data_3 = dbResult($qh3)) {
					print "$data_3[username] ";
				}
			}
			else {
				print "<i>None</i>";
			}
?>
						</td>
					<tr>
				</table>
			</td>
		</tr>

<?php
	}
?>