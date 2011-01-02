<?php
// $Header: /cvsroot/tsheet/timesheet.php/daily.php,v 1.7 2005/05/10 11:42:53 vexil Exp $

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclDaily')) {
	if(!class_exists('Site')){
		Header("Location: login?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclDaily'));	
	}
	else{
		Header("Location: login?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclDaily'));
	}
	
	exit;
}

include('daily.class.php');
$dc = new DailyClass();

// Connect to database.
//$dbh = dbConnect();

//define the command menu & we get these variables from $_REQUEST:
//  gbl::getMonth() gbl::getDay() gbl::getYear() gbl::getClientId() gbl::getProjId() gbl::getTaskId()
//include("timesheet_menu.inc");

$contextUser = strtolower($_SESSION['contextUser']);
gbl::setContextUser($contextUser);

if (empty($contextUser))
	errorPage("Could not determine the context user");

//check that project id is valid
if (gbl::getProjId() == 0)
	gbl::setTaskId(0);

$month = gbl::getMonth();
$day = gbl::getDay(); 
$year = gbl::getYear();
$startDayOfWeek = Common::getWeekStartDay();  //needed by NavCalendar
$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());

$tomorrowDate = strtotime(date("d M Y H:i:s",$todayDate) . " +1 days");

//get the timeformat
$CfgTimeFormat = Common::getTimeFormat();

$post="proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;client_id=".gbl::getClientId()."";   //THIS LINE ISN'T USED!!

PageElements::setHead("<title>".Config::getMainTitle()." - Timesheet for ".$contextUser."</title>");
ob_start();

include("client_proj_task_javascript.php");
?>
<script type="text/javascript">

	function delete_entry(transNum) {
		if (confirm('Are you sure you want to delete this time entry?'))
			location.href = 'delete.php?month=<?php echo gbl::getMonth(); ?>&amp;year=<?php echo gbl::getYear(); ?>&amp;day=<?php echo gbl::getDay(); ?>&amp;client_id=<?php echo gbl::getClientId(); ?>&amp;proj_id=<?php echo gbl::getProjId(); ?>&amp;task_id=<?php echo gbl::getTaskId(); ?>&amp;trans_num=' + transNum;
	}

</script>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/datetimepicker_css.js"></script>
<form name="dayForm" action="<?php echo Rewrite::getShortUri(); ?>" method="get">
<!--<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />-->
<!--<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />-->
<input type="hidden" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />
<?php PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');

	$currentDate = $todayDate;
	$fromPopup = "false";
	include("clockOnOff.inc"); 
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
			Daily Timesheet
		</td>
		<td align="left" nowrap class="outer_table_heading">
			<?php echo strftime("%A %B %d, %Y", $todayDate); ?>
		</td>
		<td align="right" nowrap>
			<input id="date1" name="date1" type="text" size="25" onclick="javascript:NewCssCal('date1', 'ddmmmyyyy')" 
				value="<?php echo date('d-M-Y', $todayDate);  ?>" />
		</td>
		<td align="center" nowrap="nowrap" class="outer_table_heading">
			<input id="sub" type="submit" name="Change Date" value="Change Date"></input>
		</td>
	</tr>
</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
			<tr class="inner_table_head">
			<td class="inner_table_column_heading" align="center">Client</td>
			<td class="inner_table_column_heading" align="center">Project</td>
			<td class="inner_table_column_heading" align="center">Task</td>
			<td class="inner_table_column_heading" align="center">Work Description</td>
			<td class="inner_table_column_heading" align="center" width="10%">Start</td>
			<td class="inner_table_column_heading" align="center" width="10%">End</td>
			<td class="inner_table_column_heading" align="center" width="10%">Total</td>
			<td class="inner_table_column_heading" align="center" width="15%"><i>Actions</i></td>
		</tr>
<?php

//Get the data
$startStr = date("Y-m-d H:i:s",$todayDate);
$endStr = date("Y-m-d H:i:s",$tomorrowDate);

$order_by_str = "start_stamp, ".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name, end_stamp";
list($num, $qh) = Common::get_time_records($startStr, $endStr, $contextUser, 0, 0, $order_by_str);

if ($num == 0) {
	print "	<tr>\n";
	print "		<td class=\"calendar_cell_middle\"><i>No hours recorded.</i></td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\" width=\"10%\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\" width=\"10%\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_middle\" width=\"10%\">&nbsp;</td>\n";
	print "		<td class=\"calendar_cell_disabled_right\" width=\"15%\">&nbsp;</td>\n";
	print "	</tr>\n";
	//print "</table>\n";
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

		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('client_info?client_id=$data[client_id]','Client Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$clientName</a></td>\n";
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('proj_info?proj_id=$data[proj_id]','Project Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=500,height=200')\">$projectTitle</a></td>\n";
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=\"javascript:window.open('task_info?task_id=$data[task_id]','Task Info','location=0,directories=no,status=no,scrollbar=yes,menubar=no,resizable=1,width=300,height=150')\">$taskName</a></td>\n";
		print "<td class=\"calendar_cell_middle\">" . $data['log_message'] . "</td>\n";
		
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

				$dc->open_cell_middle_td(); //<td....>
				echo "<font color=\"#909090\"><i>" . $formattedStartTime . ",";
				$dc->make_daily_link($ymdStrSd,gbl::getProjId(),date("d-M",$data["start_stamp"])); 
				echo "</i></font></td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo "<font color=\"#909090\"><i>" . $formattedEndTime . ",";
				$dc->make_daily_link($ymdStrEd,gbl::getProjId(),date("d-M",$data["end_stamp"])); 
				echo "</i></font></td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " .
					Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} //if end time is not today
			  elseif ($data["end_stamp"] > $tomorrowDate) {
				$taskTotal = Common::get_duration($data["start_stamp"],$tomorrowDate);

				$dc->open_cell_middle_td(); //<td....>
				echo $formattedStartTime . "</td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo "<font color=\"#909090\"><i>" . $formattedEndTime . "," ;
				$dc->make_daily_link($ymdStrEd,gbl::getProjId(),date("d-M",$data["end_stamp"])); 
				echo "</i></font></td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo  Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " . Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} //elseif start time is not today
			  elseif ($data["start_stamp"] < $todayDate) {
				$taskTotal = Common::get_duration($todayDate,$data["end_stamp"]);

				$dc->open_cell_middle_td(); //<td....>
				echo "<font color=\"#909090\"><i>" . $formattedStartTime . "," ;
				$dc->make_daily_link($ymdStrSd,gbl::getProjId(),date("d-M",$data["start_stamp"])); 
				echo "</i></font></td>"; 

				$dc->open_cell_middle_td(); //<td....>
				echo $formattedEndTime . "</td>" ;

				$dc->open_cell_middle_td(); //<td....>
				echo Common::formatMinutes($taskTotal). "<font color=\"#909090\"><i> of " .
					Common::formatMinutes($data["duration"]) . "</i></font></td>\n";
			} else {
				$taskTotal = $data["duration"];
				$dc->open_cell_middle_td(); //<td....>
				print "$formattedStartTime</td>\n";
				$dc->open_cell_middle_td(); //<td....>
				print "$formattedEndTime</td>\n";
				$dc->open_cell_middle_td(); //<td....>
				print Common::formatMinutes($data["duration"]) . "</td>\n";
			}

			print "<td class=\"calendar_cell_disabled_right\" align=\"right\" nowrap>\n";
			if ($data['subStatus'] == "Open") {
				print "	<a href=\"edit.php?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;trans_num=$data[trans_num]&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()."\" class=\"action_link\">Edit</a>,&nbsp;\n";
				//print "	<a href=\"delete.php?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;trans_num=$data[trans_num]\" class=\"action_link\">Delete</a>\n";
				print "	<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">Delete</a>\n";
			} else {
				// submitted or approved times cannot be edited
				print  $data['subStatus'] . "&nbsp;\n";
			}
			print "</td>";

			//add to todays total
			$todaysTotal += $taskTotal;
		} else {
			if ($CfgTimeFormat == "12") 
				$formattedStartTime = date("g:iA",$data["start_stamp"]);
			else
				$formattedStartTime = date("G:i",$data["start_stamp"]);
			
			$dc->open_cell_middle_td(); //<td....>
			print "$formattedStartTime</td>\n";
			$dc->open_cell_middle_td(); //<td....>
			print "&nbsp;</td>\n";
			$dc->open_cell_middle_td(); //<td....>
			print "&nbsp;</td>\n";
			print "<td class=\"calendar_cell_disabled_right\" align=\"right\" nowrap>\n";
			/**
			 * Update by robsearles 26 Jan 2008
			 * Added a "Clock Off" link to make it easier to stop timing a task
			 * Common::getRealTodayDate() is defined in common.inc
			 */
			if ($data["start_stamp"] == Common::getRealTodayDate()) {
				$stop_link = '<a href="clock_action.php?client_id='.$data['client_id'].'&amp;proj_id='.
						$data['proj_id'].'&amp;task_id='.$data['task_id'].
						'&amp;clock_off_check=on&amp;clock_off_radio=now" class="action_link\">Clock Off</a>, ';
				print $stop_link;
			}
			print "	<a href=\"javascript:delete_entry($data[trans_num]);\" class=\"action_link\">Delete</a>\n";
			print "</td>";
		}

		print "</tr>";
		$count++;
	}
	print "<tr>\n";
	print "	<td class=\"calendar_totals_line_weekly_right\" colspan=\"7\" align=\"right\">";
	print " Daily Total: <span class=\"calendar_total_value_weekly\" nowrap>" . Common::formatMinutes($todaysTotal) . "</span></td>\n";
	print "	<td class=\"calendar_cell_disabled_right\" align=\"right\" nowrap>&nbsp;</td>\n";
	print "</tr>\n";
	print "</table>";
}
?>

			</td>
		</tr>
	</table>
</form>
		</td>
	</tr>
</table>

<!-- ?php include("clockOnOff.inc"); ?-->
