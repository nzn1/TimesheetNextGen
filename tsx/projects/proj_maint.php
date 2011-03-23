<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclProjects'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));


$client_id = gbl::getClientId();

$CLIENT_TABLE = tbl::getClientTable();
$PROJECT_TABLE = tbl::getProjectTable();
$TASK_TABLE = tbl::getTaskTable();
$USER_TABLE = tbl::getUserTable();
$TIMES_TABLE = tbl::getTimesTable();
$ASSIGNMENTS_TABLE = tbl::getAssignmentsTable();
$RATE_TABLE = tbl::getRateTable();
//set seleced project status to started when nothing is chosen in the dropdown list
$proj_status = isset($_REQUEST['proj_status']) ? $_REQUEST['proj_status'] : "Started";

//set up query
$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, p.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"p.proj_status, http_link, proj_leader ".
					"FROM  ".$PROJECT_TABLE." p,  ".$CLIENT_TABLE." c,  ".$USER_TABLE."  ".
					"WHERE ";

if ($client_id != 0)
	$query .= "p.client_id = $client_id AND ";
	
if ($proj_status !== 0 and $proj_status !== 'All' )
	$query .= " p.proj_status = '$proj_status' AND ";

//$query .= "p.proj_id > 0 AND c.client_id = p.client_id ".  "ORDER BY p.title";
$query .= " p.proj_id > 0 ORDER BY p.title";

if (isset($_POST['page']) && $_POST['page'] != 0) { $page  = $_POST['page']; } else { $page=1; }; 
$results_per_page = Common::getProjectItemsPerPage();
$start_from = ($page-1) * $results_per_page; 
$query .= " LIMIT $start_from, $results_per_page";
//$rs_result = mysql_query ($sql, $connection);  
//execute the query
list($qh, $num) = dbQuery($query);

//build query for determining number of pages
$query2 = "SELECT DISTINCT p.title, p.proj_id, p.client_id, p.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"p.proj_status, http_link, proj_leader ".
					"FROM  $PROJECT_TABLE p,  $CLIENT_TABLE c,  $USER_TABLE  ".
					"WHERE ";
if ($client_id != 0)
	$query2 .= "p.client_id = $client_id AND ";
	
if ($proj_status !== 0 and $proj_status !== 'All' ) {
	$query2 .= " p.proj_status = '$proj_status' AND ";
}

//$query2 .= "p.proj_id > 0 AND c.client_id = p.client_id ".  "ORDER BY p.title";
$query2 .= " p.proj_id > 0 ORDER BY p.title";

list($qh2, $num2) = dbQuery($query2);

				
function writePageLinks($page, $results_per_page, $num2) {
	if (($num2/$results_per_page) == (int)($num2/$results_per_page))
		$numberOfPages = ($num2/$results_per_page);
	else 
		$numberOfPages = 1+(int)($num2/$results_per_page);
	if($numberOfPages > 1 && $num2 != 0)
	{
		//echo '<td width="16em" align="right">';
		if($page > 1)
			echo '<a href="javascript:change_page(\''.($page-1).'\')">'.JText::_('PREV_PAGE').'</a>';
		else 
			echo JText::_('PREV_PAGE');
		echo ' / ';
		//echo '</td><td width="19em>"';
		if ($numberOfPages > $page)
			echo '<a href="javascript:change_page('.($page+1).')">'.JText::_('NEXT_PAGE').'</a>';
		else 
			echo JText::_('NEXT_PAGE');
		echo ' ( <b>';
		echo $page." ".JText::_('OF')." ";
		echo $numberOfPages;
		echo '</b> )';
	}
}
?>
<html>
<head>
<title>Projects</title>

<script type="text/javascript" type="text/javascript">
function delete_project(clientId, projectId) {
	if (confirm("<?php echo JText::_('CONFIRM_DELETE_PROJECT')?>"))
		location.href = 'proj_action?client_id=' + clientId + '&proj_id=' + projectId + '&action=delete';
}
	
function change_page(newPageValue) {
	document.projectFilter.page.value = newPageValue;
	document.projectFilter.submit(); 
}
</script>
</head>

<form method="post" name="projectFilter" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
	<input type="hidden" name="page" value="English" />
	<table width="100%" border="0" class="section_body">
		<tr>
			<td align="center" nowrap class="outer_table_heading">
				<?php echo ucwords(JText::_('PROJECTS'))?>
			</td>
			<td align="right">&nbsp;<?php echo ucwords(JText::_('CLIENT'))?>:&nbsp;</td>
			<td><?php Common::client_select_list($client_id, 0, false, false, true, false, "submit();", false); ?></td>
			<td align="right">&nbsp;<?php echo ucwords(JText::_('STATUS'))?>:&nbsp;</td><td><?php Common::proj_status_list_filter('proj_status', $proj_status, "submit();"); ?></td>
			</td>
			<td>
				<?php writePageLinks($page, $results_per_page, $num2);?>
			</td>
			<td align="right" nowrap>
				<a href="proj_add?client_id=<?php echo $client_id; ?>"><?php echo JText::_('ADD_NEW_PROJECT')?></a>
			</td>
		</tr>
	</table>
</form>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">

<?php
	//are there any results?
	if ($num == 0) {
		if ($client_id != 0) {
			print "<tr><td align=\"center\"><br />".JText::_('NO_PROJECTS_FOR_CLIENT')." &nbsp; ";
			print "<a href=\"proj_add?client_id=$client_id\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br><br></td></tr>";
		} else {
			print "<tr><td align=\"center\"><br />".JText::_('NO_PROJECTS')." &nbsp; ";
			print "<a href=\"proj_add?client_id=$client_id\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br><br></td></tr>";
		}
	} else {
		//iterate through results
		for($j=0; $j<$num; $j++) {
			//get the current record
			$data = dbResult($qh);

			//strip slashes
			$data["title"] = stripslashes($data["title"]);
			if($data['client_id'] == 0)
				$data['organisation']=JText::_('PROJECT_NOT_ASSIGNED_TO_A_CLIENT');
			else
				$data["organisation"] = stripslashes(Common::get_client_name($data['client_id']));
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
			<table width="100%" border="0">
				<tr>
					<td width="45%" valign="top">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\">";

			print "<span class=\"project_title\">".$data['title']."</span>";
			if ($data["http_link"] != "")
				print "</a>";

			print "<br><span class=\"project_title\">".ucwords(JText::_('CLIENT')).": </span>".$data['organisation'];
?>
					</td>
					<td align="right" valign="top" nowrap>
						<?php print "<span class=\"project_status\">&lt;".ucwords(JText::_("$data[proj_status]"))."&gt;&nbsp;&nbsp;&nbsp;</span>" ?>
					</td>
					<td width="25%" nowrap>
<?php
			if (isset($data["start_date"]) && $data["start_date"] != '' && $data["deadline"] != '')
				print "<span class=\"label\">".JText::_('START_DATE').":</span> $data[start_date]<br /><span class=\"label\">".JText::_('DUE_DATE').":</span> $data[deadline]";
			else
				print "&nbsp;";
?>
					</td>
					<td width="30%"align="right" valign="top" nowrap>
						<span class="label">
							<?php echo ucwords(JText::_('ACTIONS'))?>:
						</span>
						<a href="proj_edit?client_id=<?php echo $client_id; ?>&amp;proj_id=<?php echo $data["proj_id"]; ?>"><?php echo ucwords(JText::_('EDIT'))?></a>,
						<a href="project_user_rates_action?proj_id=<?php echo $data["proj_id"]; ?>&amp;action=show_users"><?php echo JText::_('CHG_BILL_RATES')?></a>,
						<a href="javascript:delete_project(<?php echo $client_id; ?>,<?php echo $data["proj_id"]; ?>);"><?php echo ucwords(JText::_('DELETE'))?></a>
					</td>
				</tr>
				<?php if(trim($data['description']) != '') { ?>
				<tr>
					<td valign="middle" height="20" colspan="3">
						<span class="label">
							<?php echo ucwords(JText::_('DESCRIPTION')).": "?>
						</span>
						<?php echo $data['description'] ?><br>
					</td>
				</tr>
				<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0"<?php if ($j+1<$num) print "class=\"section_body\""; ?>>
				<tr>
					<td width="60%">
						<table border="0">
							<tr>
								<td>
									<span class="label">
										<?php echo JText::_('TOTAL_TIME')?>:
									</span> 
									<?php echo (isset($bill_data["total_time"]) ? Common::formatSeconds($bill_data["total_time"]): "0h 0m"); ?><br />
									<span class="label">
										<?php echo JText::_('TOTAL_BILL')?>:
									</span> 
									<b>$<?php echo (isset($bill_data["billed"]) ? sprintf("%01.2f",$bill_data["billed"]): "0.00"); ?></b>
								</td>
							</tr>
							<tr><td>&nbsp;</td></tr>
							<tr>
								<td>
									<span class="label">
										<?php echo JText::_('PROJECT_LEADER')?>:
									</span> 
									<?php echo $data['proj_leader'] ?>
									<br>
	<?php
				//display assigned users
				list($qh2, $num_workers) = dbQuery("SELECT DISTINCT username FROM $ASSIGNMENTS_TABLE WHERE proj_id = $data[proj_id]");
				if ($num_workers == 0) {
					print "<font size=\"-1\">".JText::_('NO_USERS_FOR_PROJECT')."</font>\n";
				} else {
					$workers = '';
					print "<span class=\"label\">".ucwords(JText::_('ASSIGNED_USERS')).":</span> ";
					for ($k = 0; $k < $num_workers; $k++) {
						$worker = dbResult($qh2);
						$workers .= "$worker[username], ";
					}

					$workers = preg_replace("/, $/", "", $workers);
					print $workers;
				}
	?>
								</td>
							</tr>
						</table>
					</td>
					<td width="40%">
						<div class="project_task_list">
							<a href="../tasks/task_maint?proj_id=<?php echo $data["proj_id"]; ?>"><span class="label"><?php echo ucwords(JText::_('TASKS'))?>:</span></a>&nbsp; &nbsp;<br />

<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("SELECT name, task_id FROM $TASK_TABLE WHERE proj_id=$data[proj_id] order by name");

			//are there any tasks?
			if ($num_tasks > 0) {
				$taskList = "";
				while ($task_data = dbResult($qh3)) {
					$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					$taskList .= "<a href=\"javascript:void(0)\" onclick=window.open(\"../tasks/task_info?proj_id=$data[proj_id]&amp;task_id=$task_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220\")>$taskName</a>, ";
				}
				$taskList = preg_replace("/, $/", "", $taskList);
				print $taskList;
			} else
				print JText::_('NO_TASKS_FOR_PROJECT');
?>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?php
		}
	}
?>

	<tr>
		<td>
			<center>
				<?php writePageLinks($page, $results_per_page, $num2);?>
			</center>
		</td>
	</tr>
</table>
