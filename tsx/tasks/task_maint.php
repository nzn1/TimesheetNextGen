<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;


if (empty($proj_id))
	$proj_id = 1;

//make sure the selected project is valid for this client
$client_id = gbl::getClientId();
if ($client_id != 0) {
	if (!Common::isValidProjectForClient($proj_id, $client_id))
		$proj_id = Common::getValidProjectForClient($client_id);
}

if (isset($_REQUEST['page']) && $_REQUEST['page'] != 0) { $page  = $_REQUEST['page']; } else { $page=1; };
$results_per_page = Common::getTaskItemsPerPage();
$start_from = ($page-1) * $results_per_page;

$query_task = "SELECT DISTINCT task_id, name, description,status, ".
			"DATE_FORMAT(assigned, '%M %d, %Y') as assigned,".
			"DATE_FORMAT(started, '%M %d, %Y') as started,".
			"DATE_FORMAT(suspended, '%M %d, %Y') as suspended,".
			"DATE_FORMAT(completed, '%M %d, %Y') as completed ".
		"FROM ". tbl::getTaskTable(). " t " .
		"WHERE t.proj_id=$proj_id ".
		"ORDER BY t.task_id ".
		"LIMIT $start_from, $results_per_page";

$query_project = "SELECT DISTINCT title, description,".
			"DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
			"DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
			"proj_status, proj_leader ".
		"FROM " .tbl::getProjectTable(). " p ".
		"WHERE p.proj_id=$proj_id";

function writePageLinks($page, $results_per_page, $num_task_page)
{
	//echo "num_task_page: ".$num_task_page.", results_per_page: ".$results_per_page.", page: ".$page;
	if (($num_task_page/$results_per_page) == (int)($num_task_page/$results_per_page))
		$numberOfPages = ($num_task_page/$results_per_page);
	else
		$numberOfPages = 1+(int)($num_task_page/$results_per_page);
	//echo $numberOfPages." =< 1 or ".$num_task_page." equals 0";
	if($numberOfPages > 1 && $num_task_page != 0)
	{
		//echo '<td width="16em" align="right">';
		if($page > 1)
			echo '<a href="javascript:change_page(\''.($page-1).'\')">Previous Page</a>';
		else
			echo 'Previous Page';
		echo ' / ';
		//echo '</td><td width="19em>"';
		if ($numberOfPages > $page)
		 	echo '<a href="javascript:change_page('.($page+1).')">Next Page</a>';
		else
			echo 'Next Page';
		echo ' ( <b>';
		echo $page." of ";
		echo $numberOfPages;
		echo '</b> )';
		//echo '</td>';
	}
}

$query_task_page = "SELECT DISTINCT task_id, name, description,status, ".
			"DATE_FORMAT(assigned, '%M %d, %Y') as assigned,".
			"DATE_FORMAT(started, '%M %d, %Y') as started,".
			"DATE_FORMAT(suspended, '%M %d, %Y') as suspended,".
			"DATE_FORMAT(completed, '%M %d, %Y') as completed ".
		"FROM " .tbl::getTaskTable() ." t ".
		"WHERE t.proj_id=$proj_id ".
		"ORDER BY t.task_id ";

list($qh_task_page, $num_task_page) = dbQuery($query_task_page);
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('TASKS')."</title>");

?>

<html>
<head>

<script type="text/javascript">

	function delete_task(projectId, taskId) {
		if (confirm('Deleting a task which has been used in the past will make those timesheet ' +
				'entries invalid, and may cause errors. This action is not recommended. ' +
				'Are you sure you want to delete this task?'))
			location.href = 'task_action?proj_id=' + projectId + '&task_id=' + taskId + '&action=delete';
	}

	function change_page(newPageValue)
	{
		document.changeForm.page.value = newPageValue;
		document.changeForm.submit();
	}
</script>
</head>

<form name="changeForm" action="<?php echo $_SERVER["PHP_SELF"]; ?>" style="margin-bottom: 0px;">
<input type="hidden" name="page" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
							<td align="center" class="outer_table_heading" nowrap>
							<?php echo (JText::_('TASKS')) ?>
						</td>

	</tr>
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap>
							<table width="100%" height="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td><?php echo (JText::_('CLIENT')) ?>:</td></tr></table></td>
												<td width="100%"><?php Common::client_select_list($client_id, 0, false, false, true, false, "submit();", false); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
											</tr>
										</table>
									</td>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td><?php echo (JText::_('PROJECT')) ?>:</td></tr></table></td>
												<td width="100%"><?php Common::project_select_list($client_id, false, $proj_id, 0, false, false, "submit();", false); ?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="images/spacer.gif" width="150" height="1" alt="" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td>
							<?php writePageLinks($page, $results_per_page, $num_task_page); ?>
						</td>
						<td align="right" nowrap>
							<?php if ($proj_id != 0) { ?>
							<a href="task_add?proj_id=<?php echo $proj_id; ?>"><?php echo (JText::_('ADD_NEW_TASK')) ?></a>
							<?php } else { ?>
								<span class="disabledLink"><?php echo JText::_('ADD_NEW_TASK') ?></span>
							<?php } ?>
							<br /><br />
							<a href="assign-proj-mbrs-to-all-tasks"><?php echo JText::_('ASSIGN_ALLTASKS_ALLPROJECTMEMBERS') ?></a>
						</td>
					</tr>
				</table>

			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
<?php
	//execute query
	list($qh_task, $num_task) = dbQuery($query_task);

	//were there any results
	if ($num_task == 0) {
		if ($proj_id == 0) {
			print "	<tr>\n";
			print "		<td align=\"center\">\n";
			print "			<i><br />Please select a client with projects, or 'All Clients'.<br /><br /></i>\n";
			print "		</td>\n";
			print "	</tr>\n";
		}
		else {
			print "	<tr>\n";
			print "		<td align=\"center\">\n";
			print "			<i><br />There are no tasks for this project.<br /><br /></i>\n";
			print "		</td>\n";
			print "	</tr>\n";
		}
	}
	else {
		//iterate through tasks
		for ($j=0; $j<$num_task; $j++) {
			$data_task = dbResult($qh_task);
			//start the row
?>
		<tr>
			<td>
				<table width="100%" border="0"<?php if ($j+1<$num_task) print "class=\"section_body\""; ?>>
					<tr>
						<td valign="center">
							<span class="project_title"><?php echo stripslashes($data_task["name"]); ?></span>
							&nbsp;<span class="project_status">&lt;<?php echo $data_task["status"]; ?>&gt;</span><br />
								<?php echo stripslashes($data_task["description"]); ?>
						</td>
						<td align="right" valign="top" nowrap>
							<span class="label">Actions:</span>
							<a href="task_edit?task_id=<?php echo $data_task["task_id"]; ?>"><?php echo (JText::_('EDIT')) ?></a>,
							<a href="javascript:delete_task(<?php echo $proj_id; ?>,<?php echo $data_task["task_id"]; ?>);"><?php echo (JText::_('DELETE')) ?></a>
						</td>
					</tr>
					<tr>
						<td align="left" colspan="2" align="top">
							<span class="label"><?php echo JText::_('TASK_MEMBERS'); ?>:</span><br />
<?php
			//get assigned users
			list($qh3, $num_3) = dbQuery("SELECT username, task_id FROM ".tbl::getTaskAssignmentsTable()." WHERE task_id=$data_task[task_id]");
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
	}
?>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<center><?php writePageLinks($page, $results_per_page, $num_task_page); ?></center>
			</td>
		</tr>
	</table>

		</td>
	</tr>
</table>

</form>