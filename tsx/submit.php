<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

define("NR_FIELDS", 9); // number of fields to iterate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclDaily'))return;
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));
	
include('submit.class.php');
$subcl = new SubmitClass();

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
	LogFile::write("Startdate/str=$startStr\n");

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

$subcl->setTimeFmt($time_fmt);
//Setup the variables so we can let the user choose how to order things...
$orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"]: "project";

//LogFile::write("calling get_time_records($startStr, $endStr, $uid, $proj_id, $client_id)\n");
//LogFile::write("day = $day, month = $month, year = $year, stDt = $startDate, eDt = $endDate\n");

//Since we have to pre-process the data, it really doesn't matter what order the data 
//is in at this point...
list($num, $qh) = Common::get_time_records($startStr, $endStr, $uid, $proj_id, $client_id);

if($orderby == "project") {
	$subtotal_label[]=JText::_('PROJECT_TOTAL');
	$colVar[]="projectTitle";
	$colWid[]="width=\"15%\"";
	//$colWid[]="";
	$colAlign[]=""; $colWrap[]="nowrap";

	$subtotal_label[]=JText::_('TASK_TOTAL');
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
	
	// submission
	$colVar[]="submit";
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
		
	// submission
	$colVar[]="submit";
	$colWid[]="width=\"3%\"";
	$colAlign[]="";
	$colWrap[]="";
}

$Location="$_SERVER[PHP_SELF]?uid=$uid$ymdStr&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode";
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
	document.subtimes.submit();
}
</script>

<title><?php echo Config::getMainTitle()." - ".ucfirst(JText::_('SUBMIT_TIMES')). " " . gbl::getContextUser();?></title>
<?php 
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('SUBMIT_TIMES')." | ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('newcss');
ob_end_clean();

?>

<?php if(!$export_excel) { ?>
<form action="<?php echo Config::getRelativeRoot();?>/submit_action" method="post" name="subtimes" >
<input type="hidden" name="orderby" value="<?php echo $orderby; ?>">
<input type="hidden" name="year" value="<?php echo $year; ?>">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="day" value="<?php echo $day; ?>">
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
		<input id="date1" name="date1" type="hidden" value="<?php echo date('d-m-Y', $startDate); ?>" />
			&nbsp;&nbsp;&nbsp;<?php echo JText::_('SELECT_OTHER_WEEK').": "; ?>
			<img style="cursor: pointer;" onclick="javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow')" alt="" src="images/cal.gif" />
			
			</td>
	<?php if (!$print): ?>
		<td>
			<a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $_SERVER["QUERY_STRING"];?>&amp;export_excel=1" class="export"><img src="images/export_data.gif" name="esporta_dati" border=0><br />&rArr;&nbsp;Excel </a>
		</td>
		<td>
			<?php print "<button onClick=\"popupPrintWindow()\">" .ucfirst(JText::_('PRINT_REPORT'))."</button></td>\n"; ?>
		</td>
	<?php endif; ?>
	<?php
		// add submission and check all button
		if (!$print): ?>
		<td>
			<input type="submit" name="Submit" value="<?php echo JText::_('CONFIRM')?>" > 
		</td>
		<td>
			<input type="checkbox" name="Check All" onclick="submitAll(document.subtimes['sub[]']);">
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
			<th><?php echo JText::_('CONFIRM');?></td>
		</tr>
	</thead>
<?php
	$dati_total=array();
	$darray=array();

	$grand_total_time = 0;

	if ($num == 0) {
		print "	<tr>\n";
		print "		<td align=\"center\">\n";
		print "			<i><br />".JText::_('NO_TIME_RECORDED') ." ".date("Y-m-d",$startDate).".<br /><br /></i>\n";
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
						$formatted_time = $subcl->format_time($level_total[1]);
						print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">". 
							$subtotal_label[1]." </td><td class=\"texttotal\">".$formatted_time."</td><td>&nbsp;</td></tr>\n";
					}
					$level_total[1]=0;
				}
				if(isset($subtotal_label[0]) && ($last_colVar[0] != $data[$colVar[0]])) {
					if($grand_total_time) {
						$formatted_time = $subcl->format_time($level_total[0]);
						print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">" .
							$subtotal_label[0].": </td><td class=\"texttotal\">".$formatted_time."</td><td>&nbsp;</td></tr>\n";
					}
					$level_total[0]=0;
					$last_colVar[1]="";
				}

				print "<tr>";
				// max value equals number of columns plus 1 to print
				for($i=0; $i<NR_FIELDS; $i++) {
					print "<td>";
					if($i<2) {
						if($last_colVar[$i] != $data[$colVar[$i]]) {
							$subcl->printInfo($colVar[$i],$data);
							$last_colVar[$i]=$data[$colVar[$i]];
						} else
							print "&nbsp;";
					} else
							$subcl->printInfo($colVar[$i],$data);
					print "</td>";
				}
				print "</tr>";

				$level_total[0] += $data["duration"];
				$level_total[1] += $data["duration"];
				$grand_total_time += $data["duration"];
			}
		}

		if (isset($subtotal_label[1]) && $level_total[1]) {
			$formatted_time = $subcl->format_time($level_total[1]);
			print "<tr class=\"totalr\"><td class=\"texttotal\" colspan=\"7\">" .
				$subtotal_label[1].": </td><td class=\"texttotal\">$formatted_time</td><td>&nbsp;</td></tr>\n";
		}
		if (isset($subtotal_label[0]) && $level_total[0]) {
			$formatted_time = $subcl->format_time($level_total[0]);
			print "<tr class=\"totalr\"><td class=\"textproject\" colspan=\"7\">" .
				$subtotal_label[0].": </td><td class=\"texttotal\">$formatted_time</td><td>&nbsp;</td></tr>\n";
		}
		$formatted_time = $subcl->format_time($grand_total_time);
	}

?>
			</tr>

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
		<td>&nbsp;</td></td>
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
