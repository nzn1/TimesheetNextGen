<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclECategories'))return;

// Note supervisor form uses the same functions as the submit form.
require_once(Config::getDocumentRoot().'/tsx/submit.class.php'); 
$sc = new SubmitClass();

if (isset($_REQUEST['uid'])) {
	$uid = gbl::getUid();
	LogFile::write("\nsupervisore uid: ". $uid."\n");
}
else {
	// need to find the first user managed by this supervisor, contextuser, otherwise we display the supervisor's times
	//$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getuserTable()." " .
	//		" WHERE (select uid from ts1_user s WHERE s.username = 'peter') = supervisor ORDER BY status DESC, last_name, first_name";
	list($num, $qh) = Common::get_users_for_supervisor(gbl::getContextUser());
	if ($num > 0) {
		$data = dbResult($qh);
		$uid = $data['uid'];
	}
	else
	// no user
		$uid = gbl::getContextUser();
}
$uid = gbl::getContextUser();
	LogFile::write("\nsupervisore uid final: ". $uid."\n");
if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

//get the context date
$todayDate = strtotime(date("d M Y",gbl::getContextTimestamp()));
//$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$todayDateValues = getdate($todayDate);
$ymdStr = "&amp;year=".$todayDateValues["year"] . "&amp;month=".$todayDateValues["mon"] . "&amp;day=".$todayDateValues["mday"];
$mode = gbl::getMode();
$proj_id = gbl::getProjId();
$client_id = gbl::getClientId();
$year = $todayDateValues["year"];
$month = $todayDateValues["mon"];
$day = $todayDateValues["mday"];

$day = $todayDateValues["mday"];

if ($mode == "all") $mode = "monthly";
if ($mode == "monthly") {
	$startDate = mktime(0,0,0, $todayDateValues["mon"], 1, $todayDateValues["year"]);
	$startStr = date("Y-m-d H:i:s",$startDate);

	$endDate = Common::getMonthlyEndDate($todayDateValues);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 month");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 month");	
}
if ($mode == "weekly") {
	list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 week");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 week");
}

//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";
//export data to excel (or not)
$export_excel = isset($_GET["export_excel"]) ? (bool)$_GET["export_excel"] : false;

// if exporting data to excel, print appropriate headers. Ensure the numbers written in the spreadsheet
// are in H.F format rather than HH:MI
if($export_excel){
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: attachment; filename=\"Timesheet_" . date("Y-m").".xls" . "\"");
	header("Pragma: no-cache"); 
	$time_fmt = 'decimal';
} else
	$time_fmt = 'time';


	// list the expense data, change the order depending on orderby

	$query = "SELECT eid, title as project, organisation as client, billable, amount, e.description, " .
		" DATE(e.date) as date, e.status, t.description as category, x.first_name, x.last_name FROM ".
		tbl::getExpenseTable(). " e , " . tbl::getProjectTable(). " p, " . tbl::getClientTable()." c, " . tbl::getExpenseCategoryTable().
		 " t , " .tbl::getUserTable(). " s, ".tbl::getUserTable(). " x WHERE s.username = '".$uid."' AND s.uid = x.supervisor AND x.username = e.user_id ".
		 " AND p.proj_id = e.proj_id AND c.client_id = e.client_id ".
		 " AND e.date >= '". $startStr . "' AND e.date < '". $endStr . "' ".
		 " AND e.cat_id = t.cat_id ORDER BY ";
	if ($orderby == "project")
		$query .= "e.proj_id, e.client_id, e.date";
	else 
		$query .= "e.date, e.proj_id, e.client_id";

	LogFile::write("\nSupervisore\t $query\n");
	list($qh, $num) = dbQuery($query);

if($orderby == "project") {
	$subtotal_label[]= JText::_('PROJECT_TOTAL');
	$colVar[]="projectTitle";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]= JText::_('TASK_TOTAL');
	$colVar[]="taskName";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	// start and stop times field
	$colVar[]="start_time";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="date";
	$colWid[]="width=\"20%\"";
	$colAlign[]=""; $colWrap[]="";
	
// add status field
	$colVar[]="status";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="description";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";
	
	$colVar[]="billable";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";
		
	$colVar[]="category";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";
		
	$colVar[]="amount";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";	
	// approve
	$colVar[]="approve";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
	// reject
	$colVar[]="reject";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
}

if($orderby == "date") {
	$subtotal_label[]= JText::_('DAYS_TOTAL');
	$colVar[]="start_stamp";
	$colWid[]="width=\"10%\"";
	$colAlign[]=""; $colWrap[]="";

//	$subtotal_label[]="Project total";
	$colVar[]="projectTitle";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="taskName";
//	$colWid[]="width=\"15%\"";
	$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";
	
	// start and stop times field
	$colVar[]="start_time";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	// start and stop times field
	$colVar[]="stop_time";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
		
	$colVar[]="log";
	$colWid[]="width=\"20%\"";
	$colAlign[]=""; $colWrap[]="";
// add status field
	$colVar[]="status";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";

	$colVar[]="duration";
	$colWid[]="width=\"7%\"";
	$colAlign[]="";
	$colWrap[]="";
		
	// approve
	$colVar[]="approve";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
	// reject
	$colVar[]="reject";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
}

function make_index($data,$order) {
	if($order == "date") {
		$index=$data["start_stamp"] . sprintf("-%05d",$data["proj_id"]) . 
			sprintf("-%05d",$data["task_id"]);
	} else {
		$index=sprintf("%05d",$data["proj_id"]) .  sprintf("-%05d-",$data["task_id"]) .
			$data["start_stamp"];
	}
	return $index;
}

$Location= Rewrite::getShortUri()."?uid=$uid$ymdStr&orderby=$orderby&client_id=$client_id&mode=$mode";
gbl::setPost("uid=$uid$ymdStr&orderby=$orderby&client_id=$client_id&mode=$mode");
//require_once("include/language/datetimepicker_lang.inc");
?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>

<?php if(!$export_excel) { ?>
<script type="text/javascript">
<!--
function popupPrintWindow() {
	window.open("<?php echo "$Location&print=yes"; ?>", "PopupPrintWindow", "location=0,status=no,menubar=no,resizable=1,width=800,height=450");
}
submitall=false;
function submitAll (chk) {
	if (submitall == false) {
		submitall = true
	}
	else {
		submitall = false
	}
	for (var i =0; i < chk.length; i++) 
		{
			chk[i].checked = submitall;
		}
}
<?php } //end if !export_excel ?>
function CallBack_WithNewDateSelected(strDate) 
{
	document.subtimes.submit();
}
//-->
</script>
<?php 
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('SUPERVISE_EXPENSES') . " " .JText::_('FOR'). " ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('txsheet2');

PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();


 if(!$export_excel) { ?>

 <div id ="simple">
<form name="subtimes" action="<?php echo Config::getRelativeRoot(); ?>/expenses/supervisore_action" method="post">
<input type="hidden" name="orderby" value="<?php echo $orderby; ?>">
<input type="hidden" name="year" value="<?php echo $year; ?>">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="day" value="<?php echo $day; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
 <h1> <?php echo JText::_('SUPERVISE_EXPENSES') . " " .JText::_('FOR'). " " .gbl::getContextUser() ; ?></h1>
<table>
		<tr>
			<td>
				<table>
					<tr>
						<td class="outer_table_heading"><?php echo (JText::_('CLIENT')).':'; ?></td>
						<td class="outer_table_heading">
							<?php Common::client_select_list($client_id, $uid, false, false, true, false, "submit();"); ?>
						</td>
					</tr>
					<tr>
						<td class="outer_table_heading"><?php echo (JText::_('USER')).':'; ?></td>
						<td class="outer_table_heading">
							<?php Common::supervised_user_select_droplist($uid, false,"100%"); ?>
						</td>
					</tr>
				</table>
			<td class="outer_table_heading">	
				<?php Common::printDateSelector($mode, $startDate, $prevDate, $nextDate); ?>
			</td>
			<?php if (!$print): ?>
		<td  align="center" width="10%" >
			<a href="<?php echo Rewrite::getShortUri();?>?<?php echo $_SERVER["QUERY_STRING"];?>&export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0><br />&rArr;&nbsp;Excel </a>
		</td>
		<td align="center">
			<?php 
				print "<button onClick=\"popupPrintWindow()\">". ucfirst(JText::_('PRINT_REPORT'))."</button></td>\n"; 
		 endif; 
		// add submit button
		if (!$print): ?>
		<td align="right">
		</td>
		<td align="center">
			<input type="submit" name="Modify" value="<?php echo ucfirst(JText::_('SAVE_CHANGES')); ?>"> 
		</td>
		<td align="right">
			<?php echo ucfirst(JText::_('APPROVE')); ?>: <input type="checkbox" name="Check Appr" onclick="submitAll(document.subtimes['approve[]']);">
			<?php echo ucfirst(JText::_('REJECT')); ?><input type="checkbox" name="Check Rej" onclick="submitAll(document.subtimes['reject[]']);">
		</td>
			<?php endif; ?>	
		
	</tr>
</table>



	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>

<?php } // end if !export_excel ?>
	<table  class="simpleTable">
		<thead>
			<tr  class="table_head">
				<?php 
					$projPost="uid=$uid$ymdStr&orderby=project&client_id=$client_id&mode=$mode";
					$datePost="uid=$uid$ymdStr&orderby=date&client_id=$client_id&mode=$mode";
					if($orderby== "project") { ?>
						<th><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>

						<th><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>
							
						<?php }
						else { ?>
							<th><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>
							<th><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>
						<?php } ?>
						<th><?php echo JText::_('USER');?></th>
						<th><?php echo JText::_('DESCRIPTION');?></th>
						<th><?php echo ucfirst(JText::_('EXPENSE_CATEGORY')); ?></th>
						<th><?php echo ucfirst(JText::_('AMOUNT')); ?></th>
						<th><?php echo JText::_('BILLABLE');?></th>
						<th><?php echo JText::_('STATUS');?></th>
						<th><?php echo JText::_('APPROVE');?></th>
						<th><?php echo JText::_('REJECT');?></th>
			</tr>
		</thead>
<?php
	if ($num == 0) {
		print "	<tr class=\"calendar_cell_middle\">\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>".JText::_('NO_EXPENSES_RECORDED') ." ".date("Y-m-d",$startDate).".<br><br></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	} 
	else {
		//Setup for two levels of subtotals
		$last_colVar[0]='';
		$last_colVar[1]='';

		$level_total[0] = 0;
		$level_total[1] = 0;
		


		while ($data = dbResult($qh)) {
			print "<tr>";
			if($orderby == "project") {
				print "<td class=\"calendar_cell_middle\">" . $data['client']. " / " .  $data['project']. "</td>";
				print "<td class=\"calendar_cell_middle\">" . $data['date']. "</td>";
			}
			else {
				print "<td class=\"calendar_cell_middle\">" . $data['date']. "</td>";
				print "<td class=\"calendar_cell_middle\">" . $data['client']. " / " .  $data['project']. "</td>";
			}
			print "<td class=\"calendar_cell_middle\">" . $data['first_name']. " ".$data['last_name']."</td>";
			print "<td class=\"calendar_cell_middle\">" . $data['description']. "</td>";
			print "<td class=\"calendar_cell_middle\">" . $data['category']. "</td>";
			print "<td class=\"calendar_cell_middle\">" . $data['amount']. "</td>";
			if ($data['billable']) {
				// print the different billable descriptions internationalised
				print "<td class=\"calendar_cell_middle\">";
				switch($data['billable']) {
					case "Billable":
						print JText::_('BILLABLE');
						break;
					case "Internal":
						print JText::_('INTERNAL');
						break;
					case "Personal":
						print JText::_('PERSONAL');
						break;
				}
				print "</td>";
			}
			// print the different status descriptions internationalised
			print "<td class=\"calendar_cell_middle\">";
			switch($data['status']) {
				case "Open":
					print JText::_('STATUS_OPEN');
					break;
				case "Submitted":
					print JText::_('STATUS_SUBMITTED');
					break;
				case "Approved":
					print JText::_('STATUS_APPROVED');
					break;
			}
			print "</td>";
			if ($data['status'] == "Submitted") { // if expense has been submitted, then allow approval or rejection
				print "<td class=\"calendar_cell_middle\"><input type=\"checkbox\" name=\"approve[]\" value=\"" . $data["eid"] . "\"></td>";
				print "<td class=\"calendar_cell_middle\"><input type=\"checkbox\" name=\"reject[]\" value=\"" . $data["eid"] . "\"></td>";
			} else
			{
				print "<td class=\"calendar_cell_middle\">&nbsp;</td><td class=\"calendar_cell_middle\">&nbsp;</td>";
			}
			print "</tr>";
			$level_total[0] = $level_total[0] + $data['amount'];
		}
		
	}			
	if ($num > 0) {
?>

		<tr>
		<td  colspan="4">&nbsp;</a>
		<td class="calendar_total_value_weekly">
<?php
	if ($mode == "weekly")
		echo JText::_('WEEKLY_TOTAL'). ": ";
	else
		echo JText::_('MONTHLY_TOTAL'). ": ";
	echo $level_total[0];
?>
		</td>
		<td></td><td></td>
		</tr>
	</table>
<?php
	}
?>
	</table>

<?php if(!$export_excel) { ?>


		</td>
	</tr>
</table>
<?php if ($print): ?>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('EMPLOYEE_SIGNATURE')?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('MANAGER_SIGNATURE')?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('CLIENT_SIGNATURE')?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
	</table>		
<?php endif; //end if($print) ?>

</form>

<?php } //end if !export_excel ?>