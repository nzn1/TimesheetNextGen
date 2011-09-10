<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

define("NR_FIELDS", 9); // number of fields to iterate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclExpenses'))return;
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

// changed to show use of new css styles

//load local vars from request/post/get
if (isset($_REQUEST['uid']))
	$uid = $_REQUEST['uid'];
else
	$uid = gbl::getContextUser();

if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

//get the context date
$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
LogFile::write("Date values are from gbl: ".gbl::getMonth().", ". gbl::getDay().", ". gbl::getYear()."\n");
$todayDateValues = getdate($todayDate);
$ymdStr = "&amp;year=".$todayDateValues["year"] . "&amp;month=".$todayDateValues["mon"] . "&amp;day=".$todayDateValues["mday"];
$mode = gbl::getMode();
$proj_id = gbl::getProjId();
$client_id = gbl::getClientId();
$year = $todayDateValues["year"];
$month = $todayDateValues["mon"];
$day = $todayDateValues["mday"];

LogFile::write("Date values are: $year : $month : $day\n");

if ($mode == "all") $mode = "monthly";
if ($mode == "monthly") {
	$startDate = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());

	$startStr = date("Y-m-d H:i:s",$startDate);

	$endDate = Common::getMonthlyEndDate(getdate ($startDate));
	$endStr = date("Y-m-d H:i:s",$endDate);
	LogFile::write("Startdate/str=$startStr\n");
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 month");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 month");	
	
}
if ($mode == "weekly") {
	$startDate = mktime(0,0,0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
	
	list($startDate,$endDate) = Common::getWeeklyStartEndDates($startDate);

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
	$nextDate = strtotime(date("d M Y H:i:s",$startDate) . " +1 week");
	$prevDate = strtotime(date("d M Y H:i:s",$startDate) . " -1 week");	
}

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

//$subcl->setTimeFmt($time_fmt);
//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";

//LogFile::write("calling get_time_records($startStr, $endStr, $uid, $proj_id, $client_id)\n");
//LogFile::write("day = $day, month = $month, year = $year, stDt = $startDate, eDt = $endDate\n");

if($orderby == "date") {
	$subtotal_label[]=JText::_('CLIENT');
	$colVar[]="client";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]=JText::_('PROJECT');
	$colVar[]="project";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="date";
	$colWid[]="width=\"7%\"";
	$colAlign[]=""; $colWrap[]="";

	// start and stop times field
	$colVar[]="description";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	// start and stop times field
	$colVar[]="amount";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="billable";
	$colWid[]="width=\"20%\"";
	$colAlign[]=""; $colWrap[]="";
	
// add status field
	$colVar[]="category";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="status";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";
	
	// submission
	$colVar[]="submit";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
}

if($orderby == "client") {
	$subtotal_label[]=JText::_('CLIENT');
	$colVar[]="client";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]=JText::_('PROJECT');
	$colVar[]="project";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$colVar[]="date";
	$colWid[]="width=\"7%\"";
	$colAlign[]=""; $colWrap[]="";

	// start and stop times field
	$colVar[]="description";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	// start and stop times field
	$colVar[]="amount";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="billable";
	$colWid[]="width=\"20%\"";
	$colAlign[]=""; $colWrap[]="";
	
// add status field
	$colVar[]="category";
	$colWid[]="width=\"5%\"";
	$colAlign[]=""; $colWrap[]="";
	
	$colVar[]="status";
	$colWid[]="width=\"10%\"";
//	$colAlign[]="align=\"right\"";
	$colAlign[]="";
	$colWrap[]="";
	
	// submission
	$colVar[]="submit";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
}

$Location=Rewrite::getShortUri()."?uid=$uid$ymdStr&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode";
gbl::setPost("uid=$uid&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode");

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
//require_once("include/language/datetimepicker_lang.inc");
?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">

<?php if(!$export_excel) { ?>
<!--
function popupPrintWindow() {
	window.open("<?php echo "$Location&amp;print=yes"; ?>", "PopupPrintWindow", "location=0,status=no,menubar=no,resizable=1,width=800,height=450");
}
//-->
<?php } //end if !export_excel ?>
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

function CallBack_WithNewDateSelected(strDate) 
{
	document.subexpenses.submit();
}
</script>

<?php 
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EXPENSE_LIST')." | ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('txsheet2');
ob_end_clean();

?>

<?php if(!$export_excel) { ?>
<form action="<?php echo Config::getRelativeRoot();?>/expenses/exp_submit_action" method="post" name="subexpenses" >
<input type="hidden" name="orderby" value="<?php echo $orderby; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">

<table>
	<tr>
		<td><?php echo JText::_('CLIENT').": "; ?></td>
		<td>
			<?php Common::client_select_list($client_id, $uid, false, false, true, false, "submit();"); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo JText::_('USER').": "; ?></td>
		<td>
			<?php Common::user_select_droplist($uid, false,"100%"); ?>
		</td>
		<td>
		<?php Common::printDateSelector($mode, $startDate, $prevDate, $nextDate); ?>
			
		</td>
	<?php if (!$print): ?>
		<td>
			<a href="<?php echo Rewrite::getShortUri();?>?<?php echo $_SERVER["QUERY_STRING"];?>&amp;export_excel=1" class="export"><img src="<?php echo Config::getRelativeRoot();?>/images/export_data.gif" name="esporta_dati" border=0><br>&rArr;&nbsp;Excel </a>
		</td>
		<td>
			<?php print "<button onClick=\"popupPrintWindow()\">" .ucfirst(JText::_('PRINT_REPORT'))."</button></td>\n"; ?>
		</td>
		<td>
			<?php print "<a href=\"exp_add.php\">" .ucfirst(JText::_('ADD_NEW_EXPENSE'))."</a>\n"; ?>
		</td>
	<?php endif; ?>
	<?php
		// add submission and check all button
		if (!$print): ?>
		<td>
			<input type="submit" name="Submit" value="<?php echo JText::_('SUBMIT')?>" > 
		</td>
		<td>
			<input type="checkbox" name="Check All" onclick="submitAll(document.subexpenses['sub[]']);">
		</td>
	<?php endif; ?>	
	</tr>
</table>
<table>
	<thead>
		<tr>

<?php } // end if !export_excel 

	$projPost="uid=$uid$ymdStr&amp;orderby=project&amp;client_id=$client_id&amp;mode=$mode";
	$datePost="uid=$uid$ymdStr&amp;orderby=date&amp;client_id=$client_id&amp;mode=$mode";
	if($orderby== 'project') { ?>
		<th><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>
		<th><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>

	<?php } else { ?>
		<th><a href="<?php echo Rewrite::getShortUri() . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>
		<th><a href="<?php echo Rewrite::getShortUri() . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>

	<?php } ?>
			<th><?php echo JText::_('DESCRIPTION');?></td>
		<th><?php echo ucfirst(JText::_('CATEGORY')); ?></th>
		<th><?php echo ucfirst(JText::_('AMOUNT')); ?></th>
					<th><?php echo JText::_('BILLABLE');?></td>
			<th><?php echo JText::_('STATUS');?></td>
			<th><?php echo JText::_('ACTIONS');?></td>
			</tr>
	</thead>
	<tbody>
		
<?php
	$query = "SELECT eid, title as project, organisation as client, billable, amount, e.description, " .
			" date, status, t.description as category FROM ".
			tbl::getExpenseTable(). " e , " . tbl::getProjectTable(). " p, " . tbl::getClientTable()." c, " . tbl::getExpenseCategoryTable().
			 " t WHERE user_id = '" . $uid . "' AND p.proj_id = e.proj_id AND c.client_id = e.client_id ".
			" AND e.date >= '$startStr' AND e.date < '$endStr' " .
			 " AND e.cat_id = t.cat_id ORDER BY e.proj_id, e.client_id, e.date";
			//"' AND p.proj_id = '" . $proj_id .   "' AND c.client_id = '" . $client_id .
			LogFile::write("\nexp_list db query: ". $query. "\n");
	list($qh, $num) = dbQuery($query);

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br>".JText::_('NO_EXPENSES_RECORDED') ." ".date("Y-m-d",$startDate).".<br><br></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	} else {
		//Setup for two levels of subtotals
		$last_colVar[0]='';
		$last_colVar[1]='';

		$level_total[0] = 0;
		$level_total[1] = 0;
		


		while ($data = dbResult($qh)) {
			print "<tr>";
			print "<td>" . $data['client']. " / " .  $data['project']. "</td>";
			print "<td>" . $data['date']. "</td>";
			print "<td>" . $data['description']. "</td>";
			print "<td>" . $data['category']. "</td>";
			print "<td>" . $data['amount']. "</td>";
			if ($data['billable']) {
				// print the different billable descriptions internationalised
				print "<td>";
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
			print "<td>";
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
			if ($data['status'] == "Open") 
				print "<td><input type=\"checkbox\" name=\"sub[]\" value=\"" . $data["eid"] . "\"></td>";
			else 
				print "<td>&nbsp;</td>";
			print "</tr>";
			$level_total[0] = $level_total[0] + $data['amount'];
		}
		
	}			
	if ($num > 0) {
?>

		<tr class="mtotalr">
		<td class="textproject" colspan="5">
<?php
	if ($mode == "weekly")
		echo JText::_('WEEKLY_TOTAL'). ": ";
	else
		echo JText::_('MONTHLY_TOTAL'). ": ";
	echo $level_total[0];
?>
		</td>
		<td colspan="3">&nbsp;</td>
		</tr>
	</table>
<?php
	}
?>
	
<?php if(!$export_excel) { ?>

		</td>
	</tr>
</table>
<?php if ($print): ?>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('EMPLOYEE_SIGNATURE') ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('MANAGER_SIGNATURE') ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
		<tr>
			<td width="30%"><table><tr><td><?php echo JText::_('CLIENT_SIGNATURE') ?>:</td></tr></table></td>
			<td width="70%"><img src="images/spacer.gif" width="150" height="1" /></td>
		</tr>
	</table>		
<?php endif; //end if($print) ?>

</form>

<?php } //end if !export_excel ?>
