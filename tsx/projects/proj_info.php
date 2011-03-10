<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from superglobals
$proj_id = $_REQUEST['proj_id'];

	$query_project = "SELECT DISTINCT title, description,".
			"DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
			"DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
			"proj_status, proj_leader ".
		"FROM  ".tbl::getProjectTable()."  ".
		"WHERE p.proj_id=$proj_id";

	$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, c.organisation, ".
			"p.description, DATE_FORMAT(start_date, '%M %d, %Y') as start_date, DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
			"p.proj_status, http_link ".
		"FROM  ".tbl::getProjectTable()." ,  p".tbl::getClientTable()." c, ".tbl::getUserTable().
		"WHERE p.proj_id=$proj_id  ";

//set up query
$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, ".
						"c.organisation, p.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"p.proj_status, http_link, proj_leader ".
					"FROM  ".tbl::getProjectTable()." ,  ".tbl::getClientTable()." ,  ".tbl::getUserTable().
					"WHERE p.proj_id=$proj_id AND ".
						"c.client_id=p.client_id ".
					"ORDER BY p.proj_id";
?>
<html>
<head>
<title>Project Info</title>
<?php
include ("header.inc");
?>
</head>
<body width="100%" height="100%" style="margin: 0px;" <?php include ("body.inc"); ?> >
<table border="0" width="100%" height="100%" align="center" valign="center">
<?php
		list($qh, $num) = dbQuery($query);
		if ($num > 0) {

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
										<table width="100%" border="0" class="section_body">
											<tr>
												<td valign="center">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\"><span class=\"project_title\">$data[title]</span></a>";
			else
				print "<span class=\"project_title\">$data[title]</span>";

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
											</tr>
											<tr>
												<td><?php echo $data["description"]; ?><br /></td>
												<td valign="top" align="right"><span class="label">Client:</span> <?php echo $data["organisation"]; ?></td>
											</tr>
											<tr>
												<td colspan="2" width="100%">
													<table width="100%" border="0" cellpadding="0" cellspacing="0">
														<tr>
															<td width="70%">
																<table border="0" cellpadding="0" cellspacing="0">
																	<tr>
																		<td>
																			<span class="label">Total time:</span> <?php echo (isset($bill_data["total_time"]) ? formatSeconds($bill_data["total_time"]): "0h 0m"); ?>
																			<?php if ($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) { ?>
																			<br /><span class="label">Total bill:</span> <b>$<?php echo (isset($bill_data["billed"]) ? $bill_data["billed"]: "0.00"); ?></b>
																			<?php } ?>
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
																	<a href="<?php echo Config::getRelativeRoot(); ?>/task_maint?proj_id=<?php echo $data["proj_id"]; ?>"><span class="label">Tasks:</span></a>&nbsp; &nbsp;<br />
<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("SELECT name, task_id FROM $TASK_TABLE WHERE proj_id=$data[proj_id]");

			//are there any tasks?
			if ($num_tasks > 0) {
				while ($task_data = dbResult($qh3)) {
					$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					print "<a href=\"javascript:void(0)\" onclick=window.open(\"".Config::getRelativeRoot()."/task_info?proj_id=$data[proj_id]&amp;task_id=$task_data[task_id]\",\"TaskInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220\")>$taskName</a><br />";
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
?>
				</table>
</body>
</HTML>
<?php
// vim:ai:ts=4:sw=4
?>
