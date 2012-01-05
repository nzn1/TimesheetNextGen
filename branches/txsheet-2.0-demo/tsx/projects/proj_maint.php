<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclProjects'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));


//set seleced project status to started when nothing is chosen in the dropdown list
$proj_status = isset($_REQUEST['proj_status']) ? $_REQUEST['proj_status'] : "Started";

//set up query
$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, p.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"p.proj_status, http_link, proj_leader ".
					"FROM  ".tbl::getProjectTable()." p,  ".tbl::getClientTable()." c,  ".tbl::getUserTable()."  ".
					"WHERE ";

if (gbl::getClientId() != 0)
	$query .= "p.client_id = ".gbl::getClientId()." AND ";
	
if ($proj_status !== 0 and $proj_status !== 'All' )
	$query .= " p.proj_status = '$proj_status' AND ";

//$query .= "p.proj_id > 0 AND c.client_id = p.client_id ".  "ORDER BY p.title";
$query .= " p.proj_id > 0 ORDER BY p.title";

if (isset($_POST['page']) && $_POST['page'] != 0) { 
  $page  = $_POST['page']; 
} 
else { 
  $page=1; 
}
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
					"FROM  ".tbl::getProjectTable()." p,  ".tbl::getClientTable()." c,  ".tbl::getUserTable()."  ".
					"WHERE ";
if (gbl::getClientId() != 0)
	$query2 .= "p.client_id = ".gbl::getClientId()." AND ";
	
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

ob_start();

?>

<script type="text/javascript" type="text/javascript">
function delete_project(clientId, projectId) {
	if (confirm("<?php echo JText::_('JS_CONFIRM_DELETE_PROJECT')?>"))
		location.href = 'proj_action?client_id=' + clientId + '&proj_id=' + projectId + '&action=delete';
}
	
function change_page(newPageValue) {
	document.projectFilter.page.value = newPageValue;
	document.projectFilter.submit(); 
}
</script>

<?php
 $head = ob_get_contents();
 PageElements::setHead($head);
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('PROJECTS')."</title>");
PageElements::setTheme('txsheet2');
?>

<div id ="simple">
<form method="post" name="projectFilter" action="<?php echo Rewrite::getShortUri(); ?>">
	<input type="hidden" name="page" value="English" />
	

<h1><?php echo JText::_('PROJECTS'); ?></h1>

	<table class="simpleTable">
		<tr>
			<td>&nbsp;<?php echo JText::_('CLIENT')?>:&nbsp;</td>
			<td><?php Common::client_select_list(gbl::getClientId(), 0, false, false, true, false, "submit();", false); ?></td>
			<td>&nbsp;<?php echo JText::_('STATUS')?>:&nbsp;</td><td><?php Common::proj_status_list_filter('proj_status', $proj_status, "submit();"); ?></td>
			</td>
			<td>
				<?php writePageLinks($page, $results_per_page, $num2);?>
			</td>
			<td>

				<a href="proj_add?client_id=<?php echo gbl::getClientId(); ?>"><?php echo JText::_('ADD_NEW_PROJECT')?></a>
			</td>
		</tr>
	</table>
</form>

<table class="simpleTable">

	<tbody>

<?php
	//are there any results?
	if ($num == 0) {
		if (gbl::getClientId() != 0) {
			print "<tr><td align=\"center\"><br />".JText::_('NO_PROJECTS_FOR_CLIENT')." &nbsp; ";
			print "<a href=\"proj_add?client_id=".gbl::getClientId()."\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br /><br /></td></tr>";
		} else {
			print "<tr><td align=\"center\"><br />".JText::_('NO_PROJECTS')." &nbsp; ";
			print "<a href=\"proj_add?client_id=".gbl::getClientId()."\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br /><br /></td></tr>";
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
						"FROM ".tbl::getTimesTable()." tt, ".tbl::getAssignmentsTable()." at, ".tbl::getRateTable()." rt ".
						"WHERE end_time > 0 AND tt.proj_id = $data[proj_id] ".
						"AND at.proj_id = $data[proj_id] ".
						"AND at.rate_id = rt.rate_id ".
						"AND at.username = tt.username ");
			$bill_data = dbResult($billqh);
			$billing_type="hourly";

			//start the row
?>
		<tr class="diff">
			<td width="45%"  class="project_title">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\"><span>$data[organisation] / $data[title]</span></a>";
			else
				print "<span>$data[organisation] / $data[title]</span>";

			print "&nbsp;&nbsp;<span>&lt;$data[proj_status]&gt;</span>"
?>
			</td>
			<td align="right">
<?php
			if (isset($data["start_date"]) && $data["start_date"] != '' && $data["deadline"] != '')
				print "<span class=\"label\">". JText::_('START_DATE'). ":</span> $data[start_date]<br><span class=\"label\">Deadline:</span> $data[deadline]";
			else
				print "&nbsp;";
?>
							</td>
							<td align="right" valign="top" nowrap>
								<span class="label"><?php echo JText::_('ACTIONS')?>:</span>
								<a href="proj_edit?client_id=<?php echo gbl::getClientId(); ?>&amp;proj_id=<?php echo $data["proj_id"]; ?>"><?php echo ucwords(JText::_('EDIT'))?></a>,
								<a href="project_user_rates_action?proj_id=<?php echo $data["proj_id"]; ?>&amp;action=show_users"><?php echo JText::_('CHG_BILL_RATES')?></a>,
								<a href="javascript:delete_project(<?php echo gbl::getClientId(); ?>,<?php echo $data["proj_id"]; ?>);"><?php echo ucwords(JText::_('DELETE'))?></a>
								
							</td>
						</tr>
						<tr>
							<td colspan="2"><?php echo JText::_('DESCRIPTION'). ": ".$data["description"]; ?><br></td>
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
														<span class="label"><?php echo JText::_('BILLING_TYPE')?>:</span> <?php echo $billing_type; ?><br>
														<span class="label"><?php echo JText::_('TOTAL_TIME_WORKED')?>:</span> <?php echo (isset($bill_data["total_time"]) ? Common::formatSeconds($bill_data["total_time"]): "0h 0m"); ?><br>
														<span class="label"><?php echo JText::_('TOTAL_TIME_BILLED')?>::</span> 
														<?php 
														if ($billing_type == 'hourly') {
															echo (isset($bill_data["total_time"]) ? Common::formatSeconds($bill_data["total_time"]): "0h 0m"); 
														}
														else{
															echo (isset($bill_days["days"]) ? Common::formatSeconds($bill_days["days"] * 28800): "0h 0m"); 
														}
														?><br>
														<span class="label"><?php echo JText::_('TOTAL_COST')?>:</span> <b><?php echo JText::_('CURRENCY').(isset($bill_data["cost"]) ? printf("%8.2f", $bill_data["cost"]): "0.00"); ?></b><br>
														<span class="label"><?php echo JText::_('TOTAL_BILL')?>:</span> <b><?php echo JText::_('CURRENCY').(isset($bill_data["billed"]) ? printf("%8.2f", $bill_data["billed"]): "0.00"); ?></b>
													</td>
												</tr>
												<tr><td>&nbsp;</td></tr>


	<tr>
		<td>
				<table>
				<tr>

		<?php 
			//display project leader
			print "<tr><td><span class=\"label\">".JText::_('PROJECT_LEADER').":</span> $data[proj_leader] </td></tr>";

			//display assigned users
			list($qh2, $num_workers) = dbQuery("SELECT DISTINCT username FROM " .tbl::getAssignmentsTable(). " WHERE proj_id = $data[proj_id]");
			if ($num_workers == 0) {
				print "<tr><td><font size=\"-1\">".JText::_('NO_USERS_FOR_PROJECT')."</font></td></tr>\n";
			}
			else {
				$workers = '';
				print "<tr><td>" .ucwords(JText::_('PROJECT_MEMBERS'))."</td><td>";
				for ($k = 0; $k < $num_workers; $k++) {
					$worker = dbResult($qh2);
					$workers .= "$worker[username], ";
				}

				$workers = preg_replace("/, $/", "", $workers);
				print $workers;
				print "</td></tr>";
			}

?>
<div class="project_task_list">
			</table>
				<tr>
				<td width="50%">
				
				<a href="../tasks/task_maint?proj_id=<?php echo $data["proj_id"]; ?>"><span class="label"><?php echo ucwords(JText::_('TASKS'))?>:</span></a>&nbsp; &nbsp;</td></tr>
<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("SELECT name, task_id FROM ".tbl::getTaskTable()." WHERE proj_id=$data[proj_id]");

			//are there any tasks?
			if ($num_tasks > 0) {
				while ($task_data = dbResult($qh3)) {
					//$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					print "<tr><td><a href=\"javascript:void(0)\" onclick=window.open(\"task_info.php?proj_id=$data[proj_id]&task_id=$task_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220\")>".$task_data['name']."</a></td></tr>";
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
</table>
