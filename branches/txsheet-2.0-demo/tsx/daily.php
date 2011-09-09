<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclDaily'))return;
PageElements::setTheme('txsheet2');
include('daily.class.php');
$dc = new DailyClass();

//define the command menu & we get these variables from $_REQUEST:
//  gbl::getMonth() gbl::getDay() gbl::getYear() gbl::getClientId() gbl::getProjId() gbl::getTaskId()

//check that project id is valid
if (gbl::getProjId() == 0){
	gbl::setTaskId(0);
}

$startDayOfWeek = Common::getWeekStartDay();  //needed by NavCalendar
$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
$startDate = strtotime(date("d M Y",$todayDate));

$tomorrowDate = strtotime(date("d M Y H:i:s",$todayDate) . " +1 days");
$yesterdayDate = strtotime(date("d M Y H:i:s",$todayDate) . " -1 days");
//get the timeformat
$CfgTimeFormat = Common::getTimeFormat();

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('DAILY_TIMESHEET')." | ".gbl::getContextUser()."</title>");
ob_start();

include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();

?>
<script type="text/javascript">
//<![CDATA[
	function delete_entry(transNum) {
		if (confirm("<?php echo JText::_('JS_CONFIRM_DELETE_TIME'); ?>"))
			location.href = '<?php echo Config::getRelativeRoot()."/delete?month=".gbl::getMonth()
      ."&year=".gbl::getYear()."&day=".gbl::getDay()
      ."&client_id=".gbl::getClientId()."&proj_id=".gbl::getProjId()
      ."&task_id=".gbl::getTaskId();?> &trans_num=' + transNum;
	}
	//]]>
	function CallBack_WithNewDateSelected(strDate) {
		document.dayForm.submit();
	}

</script>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<?php 
PageElements::setHead(PageElements::getHead().ob_get_contents());
//PageElements::setTheme('newcss');
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');
?>

<h1><?php echo JText::_('DAILY_TIMESHEET'); ?></h1>

<div style="width:50%; margin:auto auto;">
<?php
	$currentDate = $todayDate;
	$fromPopup = "false";
	//include("include/tsx/clockOnOff.inc");
  require("include/tsx/clocking.class.php");
  $clock = new Clocking();           
  //($currentDate,$fromPopup=false,$enableShowHideLink = true,$stopwatch=false
  $clock->createClockOnOff(null,false,true,false); 
?>
</div>

<div id="daily">
<form name="dayForm" action="<?php echo Rewrite::getShortUri(); ?>" method="get">
<!--<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />-->
<!--<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />-->
<input type="hidden" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />

<table>
	<tr>
		<td>
			<?php echo JText::_('CURRENT_DATE').': '?><span><?php echo utf8_encode(strftime(JText::_('DFMT_WKDY_MONTH_DAY_YEAR'), $todayDate)); ?></span>
		</td>
		<td  class="outer_table_heading">
				<?php Common::printDateSelector("daily", $startDate, $yesterdayDate, $tomorrowDate); ?>
		
			</td>

	</tr>
</table>

	<table class="dailyTable">
		<thead>
		<tr class="table_head">
			<th><?php print ucfirst(JText::_('CLIENT')) ?></th>
			<th><?php print ucfirst(JText::_('PROJECT')) ?></th>
			<th><?php print ucfirst(JText::_('TASK')) ?></th>
			<th><?php print ucwords(JText::_('WORK_DESCRIPTION')) ?></th>
			<th class="alignmiddle"><?php print ucfirst(JText::_('START')) ?></th>
			<th class="alignmiddle"><?php print ucfirst(JText::_('END')) ?></th>
			<th class="alignmiddle"><?php print ucfirst(JText::_('TOTAL')) ?></th>
			<th class="alignmiddle"><i><?php print ucfirst(JText::_('ACTIONS')) ?></i></th>
		</tr>
		</thead>

<?php

//Get the data
$startStr = date("Y-m-d H:i:s",$todayDate);
$endStr = date("Y-m-d H:i:s",$tomorrowDate);

$order_by_str = "start_stamp, ".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name, end_stamp";
list($num, $qh) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), 0, 0, $order_by_str);

if ($num == 0) {
  $popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
								"?client_id=".gbl::getClientId()."".
								"&amp;proj_id=".gbl::getProjId()."".
								"&amp;task_id=".gbl::getTaskId()."".
								"&amp;year=".gbl::getYear() . "&amp;month=".gbl::getMonth() . "&amp;day=".gbl::getDay().
								"&amp;destination=".urlencode($_SERVER['REQUEST_URI']).
								"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
	?>
  <tr>
    <td><i><?php echo JText::_('NO_TIME_RECORDED');?></i></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td width="10%">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>  
    <td class="calendar_cell_disabled_right align_right"><a href=" <?php echo $popup_href;?>" class="action_link"> <?php echo ucfirst(JText::_('ADD'));?> </a>&nbsp;</td>
	
	</tr>
<?php
}
else {
	$last_task_id = -1;
	$taskTotal = 0;
	$todaysTotal = 0;

	$count = 0;
	while ($data = dbResult($qh)) {
		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		Common::fixStartEndDuration($data);

		$dateValues = getdate($data["start_stamp"]);
		$ymdStrSd = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$dateValues = getdate($data["end_stamp"]);
		$ymdStrEd = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		
		//get the project title and task name
		$projectTitle = stripslashes($data["projectTitle"]);
		$taskName = stripslashes($data["taskName"]);
		$clientName = stripslashes($data["clientName"]);

		//start printing details of the task
		if (($count % 2) == 1)
			print "<tr class=\"diff\">\n";
		else
			print "<tr>\n";

		print "<td><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('".Config::getRelativeRoot()."/clients/client_info?client_id=$data[client_id]','Client Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$clientName</a></td>\n";
		print "<td><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('".Config::getRelativeRoot()."/projects/proj_info?proj_id=$data[proj_id]','Project Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$projectTitle</a></td>\n";
		print "<td><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('".Config::getRelativeRoot()."/tasks/task_info?task_id=$data[task_id]','Task Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=300,height=150')\">$taskName</a></td>\n";
		print "<td>" . $data['log_message'] . "</td>\n";
		
		if ($data["duration"] > 0) {
			//format printable times
			if ($CfgTimeFormat == "12") {
				$formattedStartTime = date("g:iA",$data["start_stamp"]);
				$formattedEndTime = date("g:iA",$data["end_stamp"]);
			} else {
				$formattedStartTime = date("G:i",$data["start_stamp"]);
				$formattedEndTime = date("G:i",$data["end_stamp"]);
			}

			//if both start and end time are not today
			if ($data["start_stamp"] < $todayDate && $data["end_stamp"] > $tomorrowDate) {
				//all day - no one should work this hard!
				$taskTotal += get_duration($todayDate, $tomorrowDate);  

				echo "<td class=\"alignmiddle\">";
				echo "<font color=\"#909090\"><i>" . $formattedStartTime . ",";
				$dc->make_daily_link($ymdStrSd,gbl::getProjId(),date("d-M",$data["start_stamp"])); 
				echo "</i></font></td>" ;

				echo "<td class=\"alignmiddle\">";
				echo "<font color=\"#909090\"><i>" . $formattedEndTime . ",";
				$dc->make_daily_link($ymdStrEd,gbl::getProjId(),date("d-M",$data["end_stamp"])); 
				echo "</i></font></td>" ;

				echo "<td class=\"alignmiddle\">";
				echo Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " .
					Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} //if end time is not today
			  elseif ($data["end_stamp"] > $tomorrowDate) {
				$taskTotal = Common::get_duration($data["start_stamp"],$tomorrowDate);

				echo "<td class=\"alignmiddle\">";
				echo $formattedStartTime . "</td>" ;

				echo "<td class=\"alignmiddle\">";
				echo "<font color=\"#909090\"><i>" . $formattedEndTime . "," ;
				$dc->make_daily_link($ymdStrEd,gbl::getProjId(),date("d-M",$data["end_stamp"])); 
				echo "</i></font></td>" ;

				echo "<td class=\"alignmiddle\">";
				echo  Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " . Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} //elseif start time is not today
			  elseif ($data["start_stamp"] < $todayDate) {
				$taskTotal = Common::get_duration($todayDate,$data["end_stamp"]);

				echo "<td class=\"alignmiddle\">";
				echo "<font color=\"#909090\"><i>" . $formattedStartTime . "," ;
				$dc->make_daily_link($ymdStrSd,gbl::getProjId(),date("d-M",$data["start_stamp"])); 
				echo "</i></font></td>"; 

				echo "<td class=\"alignmiddle\">";
				echo $formattedEndTime . "</td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " .
					Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} else {
				$taskTotal = $data["duration"];
				echo "<td class=\"align_right\">".$formattedStartTime."</td>\n";
				echo "<td class=\"align_right\">".$formattedEndTime."</td>\n";
				echo "<td class=\"align_right\">".Common::formatMinutes($data["duration"]) . "</td>\n";
			}

			print "<td class=\"calendar_cell_disabled_right actions\">\n";
			if ($data['subStatus'] == "Open") {
				print "	<a href=\"".Config::getRelativeRoot()."/edit?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;trans_num=$data[trans_num]&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()."\" class=\"action_link\">".ucfirst(JText::_('EDIT'))."</a>,&nbsp;\n";
				//print "	<a href=\"".Config::getRelativeRoot()."/delete?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;trans_num=$data[trans_num]\" class=\"action_link\">Delete, </a>\n";
				print "	<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">".ucfirst(JText::_('DELETE')).", </a>\n";
			} else {
				// submitted or approved times cannot be edited
				print  $data['subStatus'] . "&nbsp;\n";
			}
			$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
											"?client_id=".gbl::getClientId()."".
											"&amp;proj_id=".gbl::getProjId()."".
											"&amp;task_id=".gbl::getTaskId()."".
											"$ymdStrSd".
											"&amp;destination=".Rewrite::getShortUri().
											"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
			print "	<a href=\"$popup_href\" class=\"action_link\">".ucfirst(JText::_('ADD'))."</a>&nbsp;\n";
			print "</td>";

			//add to todays total
			$todaysTotal += $taskTotal;
		} 
    else {
			if ($CfgTimeFormat == "12"){ 
				$formattedStartTime = date("g:iA",$data["start_stamp"]);
			}
			else{
				$formattedStartTime = date("G:i",$data["start_stamp"]);
			}
			?>
			<td class="align_right"><?php echo $formattedStartTime;?></td>
			<td class="align_right">&nbsp;</td>
			<td class="align_right">&nbsp;</td>
			
      <td class="actions">
			<?php
      /**
			 * Update by robsearles 26 Jan 2008
			 * Added a "Clock Off" link to make it easier to stop timing a task
			 * Common::getRealTodayDate() is defined in common.inc
			 */
			if ($data["start_stamp"] == Common::getRealTodayDate()) {
				$stop_link = '<a href="'.Config::getRelativeRoot().'/clock_action?client_id='.$data['client_id'].'&amp;proj_id='.
						$data['proj_id'].'&amp;task_id='.$data['task_id'].
						'&amp;clock_off_check=on&amp;clock_off_radio=now" class="action_link\">'.JText::_('CLOCK_OFF').'</a>, ';
				print $stop_link;
			}
			else{
        echo '<small>clock off link currently disabled</small>';
      }
			
      echo "<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">".ucfirst(JText::_('DELETE'))."</a>\n";
			?>
      </td>
    <?php
		}

		print "</tr>";
		$count++;
	}
	print "<tr class=\"totalr\"><td class=\"textproject\" colspan=\"7\">\n";
	print " Daily Total: <span>" . Common::formatMinutes($todaysTotal) . "</span></td>\n";
	print "	<td>&nbsp;</td>\n";
	print "</tr>\n";
	
}
?>

	</table>
</form>
</div><!--close daily div-->
