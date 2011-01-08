<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
		gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=" . Common::get_acl_level('aclSimple'));

	exit;
}
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");


$client_id = gbl::getClientId();

//set seleced project status to started when nothing is chosen in the dropdown list
$proj_status = isset($_REQUEST['proj_status']) ? $_REQUEST['proj_status'] : "Started";

$PROJECT_TABLE = tbl::getProjectTable();
$CLIENT_TABLE = tbl::getClientTable();
$USER_TABLE = tbl::getUserTable();
$TIMES_TABLE = tbl::getTimesTable();
$ASSIGNMENTS_TABLE = tbl::getAssignmentsTable();
$RATE_TABLE = tbl::getRateTable();
$TASK_TABLE = tbl::getTaskTable();

//set up query
$query = "SELECT DISTINCT $PROJECT_TABLE.title, $PROJECT_TABLE.proj_id, $PROJECT_TABLE.client_id, ".
						"$CLIENT_TABLE.organisation, $PROJECT_TABLE.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"$PROJECT_TABLE.proj_status, http_link, proj_leader ".
					"FROM $PROJECT_TABLE, $CLIENT_TABLE, $USER_TABLE ".
					"WHERE ";
if ($client_id != 0)
	$query .= "$PROJECT_TABLE.client_id = $client_id AND ";
	
if ($proj_status !== 0 and $proj_status !== 'All' ) {
	$query .= " $PROJECT_TABLE.proj_status = '$proj_status' AND ";
}

$query .= "$PROJECT_TABLE.proj_id > 0 AND $CLIENT_TABLE.client_id = $PROJECT_TABLE.client_id ".
						"ORDER BY $PROJECT_TABLE.title";

if (isset($_POST['page']) && $_POST['page'] != 0) { $page  = $_POST['page']; } else { $page=1; }; 
$results_per_page = Common::getProjectItemsPerPage();
$start_from = ($page-1) * $results_per_page; 
$query .= " LIMIT $start_from, $results_per_page";
//$sql = �SELECT * FROM students ORDER BY name ASC LIMIT $start_from, 20�; 
//$rs_result = mysql_query ($sql, $connection);  
//execute the query
list($qh, $num) = dbQuery($query);

//build query for determining number of pages
$query2 = "SELECT DISTINCT $PROJECT_TABLE.title, $PROJECT_TABLE.proj_id, $PROJECT_TABLE.client_id, ".
						"$CLIENT_TABLE.organisation, $PROJECT_TABLE.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"$PROJECT_TABLE.proj_status, http_link, proj_leader ".
					"FROM $PROJECT_TABLE, $CLIENT_TABLE, $USER_TABLE ".
					"WHERE ";
if ($client_id != 0)
	$query2 .= "$PROJECT_TABLE.client_id = $client_id AND ";
	
if ($proj_status !== 0 and $proj_status !== 'All' ) {
	$query2 .= " $PROJECT_TABLE.proj_status = '$proj_status' AND ";
}

$query2 .= "$PROJECT_TABLE.proj_id > 0 AND $CLIENT_TABLE.client_id = $PROJECT_TABLE.client_id ".
						"ORDER BY $PROJECT_TABLE.title";
list($qh2, $num2) = dbQuery($query2);

				
function writePageLinks($page, $results_per_page, $num2)
{
	if (($num2/$results_per_page) == (int)($num2/$results_per_page))
		$numberOfPages = ($num2/$results_per_page);
	else 
		$numberOfPages = 1+(int)($num2/$results_per_page);
	if($numberOfPages > 1 && $num2 != 0)
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
?>
<html>
<head>
<title>Projects</title>

<script type="text/javascript" type="text/javascript">

	function delete_project(clientId, projectId) {
				if (confirm('Deleting a project will also delete all tasks and assignments associated ' +
												'with that project. If any of these tasks have timesheet entries ' +
												'they will become invalid and may cause errors. This action is not recommended. ' +
												'Are you sure you want to delete this project?'))
					location.href = 'proj_action?client_id=' + clientId + '&proj_id=' + projectId + '&action=delete';
	}
	
	function change_page(newPageValue) 
	{
		document.projectFilter.page.value = newPageValue;
		document.projectFilter.submit(); 
	}
</script>
</head>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

		<form method="post" name="projectFilter" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
			<input type="hidden" name="page" value="English" />
			<table width="100%" border="0">
				<tr>
					<td width="40%">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><table width="50"><tr><td>Client:</td></tr></table></td>
								<td width="100%"><?php Common::client_select_list($client_id, 0, false, false, true, false, "submit();", false); ?></td>
								<td>&nbsp;Status:&nbsp;</td><td><?php Common::proj_status_list_filter('proj_status', $proj_status, "submit();"); ?></td>
							</tr>
						</table>
					</td>
					<td align="center" nowrap class="outer_table_heading">
						Projects
					</td>
					<td>
						<?php writePageLinks($page, $results_per_page, $num2);?>
					</td>
					<td align="right" nowrap>
						<a href="proj_add?client_id=<?php echo $client_id; ?>">Add new project</a>
					</td>
				</tr>
			</table>
		</form>

			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
				<tr>
					<td>
							<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">

<?php
	//are there any results?
	if ($num == 0) {
		if ($client_id != 0)
			print "<tr><td align=\"center\"><br />There are no projects for this client.<br /><br /></td></tr>";
		else
			print "<tr><td align=\"center\"><br />There are no projects.<br /><br /></td></tr>";
	}
	else {
		//iterate through results
		for($j=0; $j<$num; $j++) {
			//get the current record
			$data = dbResult($qh);

			//strip slashes
			$data["title"] = stripslashes($data["title"]);
			$data["organisation"] = stripslashes($data["organisation"]);
			$data["description"] = stripslashes($data["description"]);

			list($billqh, $bill_num) = dbquery(
					"SELECT sum(unix_timestamp(end_time) - unix_timestamp(start_time)) as total_time, ".
						"sum(bill_rate * ((unix_timestamp(end_time) - unix_timestamp(start_time))/(60*60))) as billed ".
						"FROM $TIMES_TABLE, $ASSIGNMENTS_TABLE, $RATE_TABLE ".
						"WHERE end_time > 0 AND $TIMES_TABLE.proj_id = $data[proj_id] ".
						"AND $ASSIGNMENTS_TABLE.proj_id = $data[proj_id] ".
						"AND $ASSIGNMENTS_TABLE.rate_id = $RATE_TABLE.rate_id ".
						"AND $ASSIGNMENTS_TABLE.username = $TIMES_TABLE.uid ");
			$bill_data = dbResult($billqh);

			//start the row
?>
								<tr>
									<td>
										<table width="100%" border="0"<?php if ($j+1<$num) print "class=\"section_body\""; ?>>
											<tr>
												<td valign="center">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\"><span class=\"project_title\">$data[organisation] / $data[title]</span></a>";
			else
				print "<span class=\"project_title\">$data[organisation] / $data[title]</span>";

			print "&nbsp;&nbsp;<span class=\"project_status\">&lt;$data[proj_status]&gt;</span>"
?>
												</td>
												<td align="right">
<?php
			if (isset($data["start_date"]) && $data["start_date"] != '' && $data["deadline"] != '')
				print "<span class=\"label\">Start:</span> $data[start_date]<br /><span class=\"label\">Deadline:</span> $data[deadline]";
			else
				print "&nbsp;";
?>
												</td>
												<td align="right" valign="top" nowrap>
													<span class="label">Actions:</span>
													<a href="proj_edit?client_id=<?php echo $client_id; ?>&amp;proj_id=<?php echo $data["proj_id"]; ?>">Edit</a>,
													<a href="project_user_rates_action?proj_id=<?php echo $data["proj_id"]; ?>&amp;action=show_users">Bill Rates</a>,
													<a href="javascript:delete_project(<?php echo $client_id; ?>,<?php echo $data["proj_id"]; ?>);">Delete</a>
												</td>
											</tr>
											<tr>
												<td colspan="2"><?php echo $data["description"]; ?><br /></td>
												<td valign="top" align="right"><span class="label">Client:</span> <?php echo $data["organisation"]; ?></td>
											</tr>
											<tr>
												<td colspan="3" width="100%">
													<table width="100%" border="0" cellpadding="0" cellspacing="0">
														<tr>
															<td width="70%">
																<table border="0" cellpadding="0" cellspacing="0">
																	<tr>
																		<td>
																			<span class="label">Total time:</span> <?php echo (isset($bill_data["total_time"]) ? Common::formatSeconds($bill_data["total_time"]): "0h 0m"); ?><br />
																			<span class="label">Total bill:</span> <b>$<?php echo (isset($bill_data["billed"]) ? sprintf("%01.2f",$bill_data["billed"]): "0.00"); ?></b>
																		</td>
																	</tr>
																	<tr><td>&nbsp;</td></tr>


<?php

			//display project leader
			print "<tr><td><span class=\"label\">Project Leader:</span> $data[proj_leader] </td></tr>";

			//display assigned users
			list($qh2, $num_workers) = dbQuery("SELECT DISTINCT username FROM $ASSIGNMENTS_TABLE WHERE proj_id = $data[proj_id]");
			if ($num_workers == 0) {
				print "<tr><td><font size=\"-1\">Nobody assigned to this project</font></td></tr>\n";
			}
			else {
				$workers = '';
				print "<tr><td><span class=\"label\">Assigned Users:</span> ";
				for ($k = 0; $k < $num_workers; $k++) {
					$worker = dbResult($qh2);
					$workers .= "$worker[username], ";
				}

				$workers = preg_replace("/, $/", "", $workers);
				print $workers;
				print "</td></tr>";
			}

?>


																</table>
															</td>
															<td width="30%">
																<div class="project_task_list">
																	<a href="task_maint?proj_id=<?php echo $data["proj_id"]; ?>"><span class="label">Tasks:</span></a>&nbsp; &nbsp;<br />
<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("SELECT name, task_id FROM $TASK_TABLE WHERE proj_id=$data[proj_id]");

			//are there any tasks?
			if ($num_tasks > 0) {
				while ($task_data = dbResult($qh3)) {
					$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					print "<a href=\"javascript:void(0)\" onclick=window.open(\"task_info?proj_id=$data[proj_id]&amp;task_id=$task_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220\")>$taskName</a><br />";
				}
			}
			else
				print "None.";
?>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
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
	</tr>
	<tr>
		<td>
			<center>
				<?php writePageLinks($page, $results_per_page, $num2);?>
			</center>
		</td>
	</tr>
			</table>

		</td>
</table>
