<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;
PageElements::setTemplate('popup_template.php');
PageElements::setTheme('txsheet2');

//load local vars from request/post/get
$proj_id = $_REQUEST['proj_id'];

	$query_project = "SELECT DISTINCT title, description,".
			"DATE_FORMAT(start_date, '%M %d, %Y') as start_date,".
			"DATE_FORMAT(deadline, '%M %d, %Y') as deadline,".
			"proj_status, proj_leader ".
		" FROM  ".tbl::getProjectTable()."  ".
		" WHERE p.proj_id=$proj_id";

	$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, c.organisation, ".
			"p.description, DATE_FORMAT(start_date, '%M %d, %Y') as start_date, DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
			"p.proj_status, http_link ".
		" FROM  ".tbl::getProjectTable()." p, ".tbl::getClientTable()." c, ".tbl::getUserTable().
		" WHERE p.proj_id=$proj_id  ";

//set up query
$query = "SELECT DISTINCT p.title, p.proj_id, p.client_id, ".
						"c.organisation, p.description, " .
						"DATE_FORMAT(start_date, '%M %d, %Y') as start_date, " .
						"DATE_FORMAT(deadline, '%M %d, %Y') as deadline, ".
						"p.proj_status, http_link, proj_leader ".
					" FROM  ".tbl::getProjectTable()." p,  ".tbl::getClientTable()." c,  ".tbl::getUserTable().
					" WHERE p.proj_id=$proj_id AND ".
						"c.client_id=p.client_id ".
					" ORDER BY p.proj_id";

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('PROJECT_INFO')." | ".gbl::getContextUser()."</title>");
ob_start();


ob_end_clean();
?>
<h2><?php echo JText::_('PROJECT_INFO');?></h2>
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
			$proj_id = $data["proj_id"];

			list($billqh, $bill_num) = dbquery(
					"SELECT sum(unix_timestamp(end_time) - unix_timestamp(start_time)) as total_time, ".
						"format(sum(bill_rate * ((unix_timestamp(end_time) - unix_timestamp(start_time))/(60*60))),2) as billed ".
						"FROM ".tbl::getTimesTable()." tt, ".tbl::getAssignmentsTable()." at, ".tbl::getRateTable()." rt ".
						"WHERE end_time > 0 AND tt.proj_id = $proj_id ".
						"AND at.proj_id = $proj_id ".
						"AND at.rate_id = rt.rate_id ".
						"AND at.username = tt.uid ");
			$bill_data = dbResult($billqh);

			//start the row
?>
			<tr>
				<td valign="center" colspan="2" align="center">
<?php
			if ($data["http_link"] != "")
				print "<a href=\"$data[http_link]\"><span class=\"project_title\">$data[title]</span></a>";
			else
				print "<span class=\"project_title\">$data[title]</span>";

			print "</td></tr><tr><td><span class=\"label\">". JText::_('STATUS') ."</span></td>";
			print "<td>" .$data['proj_status']. "</td></tr>";
?>
			<tr>
				</td>
			<tr>
				<td align="right">
<?php
			if (isset($data["start_date"]) && $data["start_date"] != '' && $data["deadline"] != '') {
				print  JText::_('START_DATE'). "</td><td> $data[start_date]</td></tr>";
				print "<tr><td  align=\"right\">". JText::_('END_DATE') ."</td><td> $data[deadline]";
			}
			else
				print "&nbsp;";
?>
				</td>
			</tr>
			<tr>
				<td  align="right"><?php echo JText::_('CLIENT'); ?><br /></td>
				<td> <?php echo $data["organisation"]; ?></td>
			</tr>
			<tr>
				<td  align="right"><?php echo JText::_('DESCRIPTION'); ?><br /></td>
				<td><?php echo $data["description"] ; ?></td>
			</tr>
			<tr>
				<td align="right">
					<?php echo JText::_('TOTAL_TIME'); ?></td><td>
					<?php echo (isset($bill_data["total_time"]) ? Common::formatSeconds($bill_data["total_time"]): "0h 0m"); ?>
					</td></tr>
					<?php //TODO fix manager clearance
						 if (Auth::ACCESS_GRANTED == $this->requestPageAuth('aclSimple')) { ?>
							<tr><td align="right">
							<?php echo JText::_('TOTAL_BILL'); ?></td><td>$<?php echo (isset($bill_data["billed"]) ? $bill_data["billed"]: "0.00"); ?></b>
					<?php } ?>
				</td>
			</tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td></tr>

<?php

			//display project leader
			print "<tr><td>" .JText::_('PROJECT_LEADER'). ":</td><td> $data[proj_leader] </td></tr>";

			//display assigned users
			list($qh2, $num_workers) = dbQuery("SELECT DISTINCT username FROM ".tbl::getAssignmentsTable()." WHERE proj_id = $data[proj_id]");
			if ($num_workers == 0) {
				print "<td><font size=\"-1\">Nobody assigned to this project</font></td>\n";
			}
			else {
				$workers = '';
				print "<td align=\"right\">" . JText::_('TASK_MEMBERS'). "</td><td>";
				for ($k = 0; $k < $num_workers; $k++) {
					$worker = dbResult($qh2);
					$workers .= "$worker[username], ";
				}

				$workers = preg_replace("/, $/", "", $workers);
				print $workers;
				print "</td></tr>";
			}

?>

				</td>
					<td width="30%">
						<a href="<?php echo Config::getRelativeRoot(); ?>/task_maint?proj_id=<?php echo $data["proj_id"]; ?>"><?php echo JText::_('TASKS'); ?></a></td><td>
		<?php
			//get tasks
			list($qh3, $num_tasks) = dbQuery("SELECT name, task_id FROM ".tbl::getTaskTable()." WHERE proj_id=$data[proj_id]");

			//are there any tasks?
			if ($num_tasks > 0) {
				while ($task_data = dbResult($qh3)) {
					$taskName = str_replace(" ", "&nbsp;", $task_data["name"]);
					print "<a href=\"javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/task_info?proj_id=$data[proj_id]&amp;task_id=$task_data[task_id]','TaskInfo','location=0,directories=no,status=no,menubar=no,resizable=1,width=550,height=220')\">$taskName</a><br />";
				}
			}
			else
				print "None.";
?>
							</div>
						</td>
					</tr>
	
<?php
		}
?>
		</table>
