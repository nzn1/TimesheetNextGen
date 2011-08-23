<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclDaily'))return;

// Note supervisor form uses the same functions as the submit form.
include('submit.class.php');
$sc = new SubmitClass();

if (isset($_REQUEST['uid']))
	$uid = gbl::getUid();
else {
	// need to find the first user managed by this supervisor, contextuser, otherwise we display the supervisor's times
	//$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getuserTable()." " .
	//		" WHERE (select uid from ts1_user s WHERE s.username = 'peter') = supervisor ORDER BY status DESC, last_name, first_name";
	list($qh, $num) = Common::get_users_for_supervisor(gbl::getContextUser());
	if ($num > 0) {
		$data = dbResult($qh);
		$uid = $data['uid'];
	}
	else
	// no user
		$uid = "0";
}

if (isset($_REQUEST['print']))
	$print = true;
else
	$print = false;

//get the context date
$todayDate = mktime(0, 0, 0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
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
}
if ($mode == "weekly") {
	list($startDate,$endDate) = Common::getWeeklyStartEndDates($todayDate);

	$startStr = date("Y-m-d H:i:s",$startDate);
	$endStr = date("Y-m-d H:i:s",$endDate);
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

//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";

//LogFile::->write("calling get_time_records($startStr, $endStr, $uid, $proj_id, $client_id)\n");
//LogFile::->write("day = $day, month = $month, year = $year, stDt = $startDate, eDt = $endDate\n");

//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...
list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, $proj_id, $client_id);

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

	$colVar[]="start_stamp";
	$colWid[]="width=\"7%\"";
	$colAlign[]=""; $colWrap[]="";

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

$Location="$_SERVER[PHP_SELF]?uid=$uid$ymdStr&orderby=$orderby&client_id=$client_id&mode=$mode";
gbl::setPost("uid=$uid$ymdStr&orderby=$orderby&client_id=$client_id&mode=$mode");

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
PageElements::setHead("<title>".Config::getMainTitle()." | Timesheet for ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('newcss');

PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');

 if(!$export_excel) { ?>
<form name="subtimes" action="<?php echo Config::getRelativeRoot(); ?>/supervisort_action" method="post">
<input type="hidden" name="orderby" value="<?php echo $orderby; ?>">
<input type="hidden" name="year" value="<?php echo $year; ?>">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="day" value="<?php echo $day; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">

<table>
		<tr>
			<td>
				<table>
					<tr>
						<td><?php echo (JText::_('CLIENT')).':'; ?></td>
						<td>
							<?php Common::client_select_list($client_id, $uid, false, false, true, false, "submit();"); ?>
						</td>
					</tr>
					<tr>
						<td><?php echo (JText::_('USER')).':'; ?></td>
						<td>
							<?php Common::supervised_user_select_droplist($uid, false,"100%"); ?>
						</td>
					</tr>
				</table>
			<td>	
				<input id="date1" name="date1" type="hidden" value="<?php echo date('d-m-Y', $startDate); ?>" />
				&nbsp;<?php echo ucfirst(JText::_('CHANGE_DATE')).':'; ?>&nbsp;
				<img style="cursor: pointer;" onclick="javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow')" alt="" src="images/cal.gif" />
			</td>
			<?php if (!$print): ?>
		<td  align="center" width="10%" >
			<a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $_SERVER["QUERY_STRING"];?>&export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0><br />&rArr;&nbsp;Excel </a>
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
	<table>
		<thead>
			<tr>
				<?php 
					$projPost="uid=$uid$ymdStr&orderby=project&client_id=$client_id&mode=$mode";
					$datePost="uid=$uid$ymdStr&orderby=date&client_id=$client_id&mode=$mode";
					if($orderby== 'project'): ?>
						<th><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>
						<th><?php echo JText::_('TASK');?></th>
						<th><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>
						<th><?php echo ucfirst(JText::_('START_TIME')); ?></th>
						<th><?php echo ucfirst(JText::_('END_TIME')); ?></th>
							
						<?php else: ?>
							<th><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $datePost ."\">" .JText::_('DATE');?></a></th>
							<th><a href="<?php echo $_SERVER["PHP_SELF"] . "?" . $projPost."\">".JText::_('CLIENT')." / ".JText::_('PROJECT')." / ";?></a></th>
						<th><?php echo JText::_('TASK');?></td>
						<th><?php echo ucfirst(JText::_('START_TIME')); ?></th>
						<th><?php echo ucfirst(JText::_('END_TIME')); ?></th>
						<?php endif; ?>
						<th><?php echo JText::_('WORK_DESCRIPTION');?></td>
						<th><?php echo JText::_('STATUS');?></td>
						<th><?php echo JText::_('DURATION');?></td>
						<th><?php echo JText::_('APPROVE');?></td>
						<th><?php echo JText::_('REJECT');?></td>
					</tr>
<?php
	$dati_total=array();
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br />".JText::_('NO_TIME_RECORDED') .date("Y-m-d",$startDate).".<br /><br /></i>\n";
		print "		</td>\n";
		print "	</tr>\n";
	} else {
		//Setup for two levels of subtotals
		$last_colVar[0]='';
		$last_colVar[1]='';

		$level_total[0] = 0;
		$level_total[1] = 0;

		while ($data = dbResult($qh)) {
			//if entry doesn't have an end time or duration, it's an incomplete entry
			//fixStartEndDuration returns a 0 if the entry is incomplete.
			
			if(!Common::fixStartEndDuration($data)) continue;
			
			array_push($dati_total,$data);

			//Since we're allowing entries that may span date boundaries, this complicates
			//our life quite a lot.  We need to "pre-process" the results to split those
			//entries that do span date boundaries into multiple entries that stop and then
			//re-start on date boundaries.
			//NOTE: there must be a make_index() function defined in this file for the following function to, well, function
			Common::split_data_into_discrete_days($data,$orderby,$darray,1);
		}

		ksort($darray);
		//sort($data, ksort($data));
		unset($data);

		foreach($darray as $dary){
			foreach($dary as $data){
				//need to make sure date is in range of what we want...
				if($data["start_stamp"] < $startDate) continue;
				if($data["start_stamp"] >= $endDate) continue;
			$dateValues = getdate($data["start_stamp"]);
			$strtDate = sprintf("%04d-%02d-%02d %02d:%02d",$dateValues["year"],$dateValues["mon"],$dateValues["mday"],
					$dateValues["hours"], $dateValues["minutes"]); 
			$dateValuese = getdate($data["end_stamp"]);
			$stopDate = sprintf("%04d-%02d-%02d %02d:%02d",$dateValuese["year"],$dateValuese["mon"],$dateValuese["mday"],
					$dateValuese["hours"], $dateValues["minutes"]); 
					
				if(isset($subtotal_label[1]) && (($last_colVar[1] != $data[$colVar[1]]) 
					|| ($last_colVar[0] != $data[$colVar[0]]))) {
					if($grand_total_time) {
						$formatted_time = $sc->format_time($level_total[1]);
						print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">". 
							$subtotal_label[1]." </td><td class=\"texttotal\">".$formatted_time."</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
											}
					$level_total[1]=0;
				}
				if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = $sc->format_time($level_total[0]);
						print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">" .
							$subtotal_label[0].": </td><td class=\"texttotal\">".$formatted_time."</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
					}
					$level_total[0]=0;
					$last_colVar[1]="";
				}

				print "<tr>";
				// max value equals number of columns plus 1 to print
				for($i=0; $i<10; $i++) {
					print "<td valign=\"top\" class=\"calendar_cell_right\" ".$colWid[$i]." ".$colAlign[$i]." ".$colWrap[$i].">";
					if($i<2) {
						if($last_colVar[$i] != $data[$colVar[$i]]) {
							$sc->printInfo($colVar[$i], $data);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							print "&nbsp;";
					} else
							$sc->printInfo($colVar[$i], $data);
					print "</td>";
				}
				print "</tr>";

				$level_total[0] += $data["duration"];
				$level_total[1] += $data["duration"];
				$grand_total_time += $data["duration"];
			}
		}

		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = $sc->format_time($level_total[1]);
			print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">" .
				$subtotal_label[1].": </td><td class=\"texttotal\">$formatted_time</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = $sc->format_time($level_total[0]);
			print "<tr class=\"totalr\"><td class=\"textproject\" colspan=\"7\">" .
				$subtotal_label[0].": </td><td class=\"texttotal\">$formatted_time</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		}
		$formatted_time = $sc->format_time($grand_total_time);
	}

?>

		</td>

<?php
	if ($num > 0) {
?>
		<tr class="mtotalr">
		<td class="textproject" colspan="7">
<?php
	if ($mode == "weekly")
		echo JText::_('WEEKLY_TOTAL'). ": ";
	else
		echo JText::_('MONTHLY_TOTAL'). ": ";
	echo "</td><td class=\"textproject\">" .$formatted_time; 
?>
						</td><td>&nbsp;</td><td>&nbsp;</td>
					</tr>
				</table>
			</td>
		</tr>
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
