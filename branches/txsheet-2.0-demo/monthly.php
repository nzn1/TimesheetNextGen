<?php
//$Header: /cvsroot/tsheet/timesheet.php/monthly.php,v 1.10 2006/03/15 13:24:28 raghuprasad Exp $

// Authenticate

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclMonthly')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclMonthly'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclMonthly'));
	}
	
	exit;
}

include('monthly.class.php');
$mc = new MonthlyClass();

//define the command menu & we get these variables from $_REQUEST:
//  $month $day $year $client_id $proj_id $task_id
include("timesheet_menu.inc");

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

// Check project assignment.
if ($proj_id != 0) { // id 0 means 'All Projects'
	list($qh, $num) = dbQuery("SELECT * FROM ".tbl::getAssignmentsTable()." WHERE proj_id='$proj_id' AND username='$contextUser'");
	if ($num < 1)
		errorPage("You cannot access this project, because you are not assigned to it.");
} else 
	$task_id = 0;

//get the context date
$todayDate = mktime(0, 0, 0, $month, $day, $year);
$dateValues = getdate($todayDate);

//the day the week should start on: 0=Sunday, 1=Monday
$startDayOfWeek = Common::getWeekStartDay();

//work out the start date by subtracting days to get to beginning of week
$startDate = mktime(0,0,0, $month, 1, $year);
$startStr = date("Y-m-d H:i:s",$startDate);

// Get day of week of 1st of month
$dowForFirstOfMonth = date('w',$startDate);

//get the number of lead in days
$leadInDays = $dowForFirstOfMonth - $startDayOfWeek;
if ($leadInDays < 0)
	$leadInDays += 7;

//get the first printed date
$firstPrintedDate = strtotime(date("d M Y H:i:s",$startDate) . " -$leadInDays days");


$endDate = Common::getMonthlyEndDate($dateValues);
$endStr = date("Y-m-d H:i:s",$endDate);

//get the timeformat
$CfgTimeFormat = Common::getTimeFormat();


$post="proj_id=$proj_id&task_id=$task_id&client_id=$client_id";       //this isn't used anywhere



PageElements::setHead("<title>".Config::getMainTitle()." - Timesheet for ".$contextUser."</title>");

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"clock_popup.php?proj_id=$proj_id&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");

include ("navcal/navcal_monthly.inc");

?>
<form action="<?php echo Rewrite::getShortUri(); ?>" method="get">
<input type="hidden" name="month" value="<?php echo $month; ?>" />
<input type="hidden" name="year" value="<?php echo $year; ?>" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1new.inc"); ?>

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap="nowrap">
							<table width="100%" border="0" cellpadding="1" cellspacing="2">
								<tr>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Client:</td></tr></table></td>
												<td width="100%"><?php 
												if(!class_exists('Site')){
													client_select_list($client_id, $contextUser, false, false, true, false, "submit();");
												} 
												else{
													Common::client_select_list($client_id, $contextUser, false, false, true, false, "submit();");
												}
												?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="<?php echo Config::getRelativeRoot();?>/images/spacer.gif" alt="spacer" width="150" height="1" /></td>
											</tr>
										</table>
									</td>
									<td>
										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td><table width="50"><tr><td>Project:</td></tr></table></td>
												<td width="100%"><?php 
												if(!class_exists('Site')){
													project_select_list($client_id, false, $proj_id, $contextUser, false, true, "submit();"); 
												} 
												else{
													Common::project_select_list($client_id, false, $proj_id, $contextUser, false, true, "submit();"); 
												}
												?></td>
											</tr>
											<tr>
												<td height="1"></td>
												<td height="1"><img src="<?php echo Config::getRelativeRoot();?>/images/spacer.gif" alt="spacer" width="150" height="1" /></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
						<td align="center" nowrap="nowrap" class="outer_table_heading">
							<?php echo date('F Y', $startDate); ?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body">
					<tr class="inner_table_head">
						<?php
						//print the days of the week
						$currentDate = $firstPrintedDate;
						for ($i=0; $i<7; $i++) {
							$currentDayStr = strftime("%A", $currentDate);
							$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 day");
							print "	<td class=\"inner_table_column_heading\" align=\"center\">$currentDayStr</td>\n";
						}
						?>
					</tr>
					<tr>
<?php

	//define the variable dayCol
	$dayCol = 0;

	// Print last months' days spots.
	for ($i=0; $i<$leadInDays; $i++) {
	//while (($dayCol < $dowForFirstOfMonth) && ($dowForFirstOfMonth != 0)) {
		print "<td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
		$dayCol++;
	}

	// Get the Monthly data.
	if(!class_exists('Site')){
		list($num, $qh) = get_time_records($startStr, $endStr, $contextUser, $proj_id, $client_id);
		list($qhol, $holnum) = get_absences($month, $year, $contextUser);	
	}
	else{
		list($num, $qh) = Common::get_time_records($startStr, $endStr, $contextUser, $proj_id, $client_id);
		list($qhol, $holnum) = Common::get_absences($month, $year, $contextUser);
	}

	$ihol = 0; $holtitle = "";
	if ($holnum>$ihol)
		$holdata = dbResult($qhol, $ihol);

	$a=0; $b=0; $curDay = 1; $monthlyTotal = 0; $weeklyTotal = 0; 

	while (checkdate($month, $curDay, $year)) {
		$curStamp = mktime(0,0,0, $month, $curDay, $year);
		$tomorrowStamp = strtotime(date("d M Y H:i:s",$curStamp) . " +1 day");

		// New Week.
		if ((($dayCol % 7) == 0) && ($dowForFirstOfMonth != 0)) {
			$mc->print_totals($weeklyTotal, "weekly", $year, $month, $curDay);
			$weeklyTotal = 0;
			print "</tr>\n<tr>\n";
		} else
			$dowForFirstOfMonth = 1;

		//define subtable
		if (($dayCol % 7) == 6)
			print "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_holiday_right\">\n";
		else if (($dayCol % 7 ) == 5)
			print "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"calendar_cell_holiday_middle\">\n";
		else {
			$cellstyle = 'calendar_cell_middle';
			if ($holnum>$ihol) {
				if ($holdata['day_of_month']==$curDay) {
					$cellstyle = 'calendar_cell_holiday_middle';
					if ($holdata['user']=='') 
					{
						$holtitle = urldecode($holdata['subject']);
						if (($holdata['AM_PM']=='AM')||($holdata['AM_PM']=='PM'))
							$holtitle .= " (".$holdata['AM_PM'].")";
					} else
						$holtitle = $holdata['user'].": ".urldecode($holdata['type'])." ".$holdata['AM_PM'];
					$ihol++;
					if ($holnum>$ihol)
					{
						$holdata = dbResult($qhol, $ihol);
						if ($holdata['day_of_month']==$curDay) 
						{
							if ($holdata['user']=='')
							{
								$holtitle .= " ".urldecode($holdata['subject']);
								if (($holdata['AM_PM']=='AM')||($holdata['AM_PM']=='PM'))
									$holtitle .= " (".$holdata['AM_PM'].")";
							} else {
								if ($holtitle==$holdata['user'].": ".urldecode($holdata['type'])." AM")
									$holtitle = $holdata['user'].": ".urldecode($holdata['type']);
								else
									$holtitle .= " ".$holdata['user'].": ".urldecode($holdata['type'])." ".$holdata['AM_PM'];
							}
							$ihol++;
							if ($holnum>$ihol)
								$holdata = dbResult($qhol, $ihol);
						}
					}

				}
			}
			print "<td width=\"14%\" height=\"25%\" valign=\"top\" class=\"".$cellstyle."\">\n";
		}
		
		print "	<table width=\"100%\">\n";

		// Print out date.
		/*print "<tr><td valign=\"top\"><tt><a href=\"daily.php?month=$month&amp;year=$year&amp;".
			"day=$curDay&amp;client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id\">$curDay</a></tt></td></tr>";*/

		$ymdStr = "&year=".$year . "&month=".$month . "&day=" . $curDay;
		$popup_href = "javascript:void(0)\" onclick=window.open(\"clock_popup.php".
											"?client_id=$client_id".
											"&amp;proj_id=$proj_id".
											"&amp;task_id=$task_id".
											"$ymdStr".
											"&amp;destination=$_SERVER[PHP_SELF]".
											"\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310\") dummy=\"";

		print "<tr><td valign=\"top\"><table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">";
		print "<tr><td valign=\"top\"><a href=\"daily.php?$ymdStr".
			"&amp;client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id\">$curDay <span class=\"task_time_small\">$holtitle</span></a></td>";
		print "<td valign=\"top\" align=\"right\"><a href=\"$popup_href\" class=\"action_link\">".
				 "<img src=\"".Config::getRelativeRoot()."/images/add.gif\" alt=\"+\" width=\"11\" height=\"11\" border=\"0\" />".
				"</a></td>";
		print "</tr>";
		print "</table></td></tr>";


		$data_seen = 0;
		$holtitle = ""; // reset

		//Ok, the logic is going to get a little thick here.  Previous version of code looped
		//through the entire set of the month's data entries for every day of the month.  That
		//works out to be O=N^2, ie. way inefficient.

		//Here we need to keep track of how far back we need to keep checking the time entries
		//and how far forward we need to check them, in variables $a and $b respectively.  
		//As tasks finish, $a can be incremented, as we check additional entries, $b is
		//incremented.  If tasks are nested, ie. one starts and stops in the middle of another
		//task, we have to keep $a from being incremented until the end of the outer most nested
		//task is finished. This complicated logic changes the code to be O=2N at worst, and can
		//be very close to O=N at best. In either case, this is much more efficient than O=N^2.

		//(acutally, if every task is nested inside another thoughout the entire month, I think
		//it's O=N log N, but that's still better than N^2, and that's not exactly a valid real
		//world time card, in fact nesting tasks is probably a highly questionable practice.)

		//set data to the earliest set of data we need to check
		$i=$a;
		$data = dbResult($qh,$i);

		$todaysTotal = 0;

		if($i<$num) {
			//There are several potential problems with the date/time data comming from the database
			//because this application hasn't taken care to cast the time data into a consistent TZ.
			//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
			//So, we handle it as best we can for now...

		if(!class_exists('Site')){
			fixStartEndDuration($data);
		}
		else{
			Common::fixStartEndDuration($data);
		}
			

			//set some booleans
			$startsToday = (($data["start_stamp"] >= $curStamp ) && ( $data["start_stamp"] < $tomorrowStamp ));
			$endsToday =   (($data["end_stamp"] > $curStamp ) && ($data["end_stamp"] <= $tomorrowStamp));
			$startsBeforeToday = ($data["start_stamp"] < $curStamp);
			$endsAfterToday = ($data["end_stamp"] > $tomorrowStamp);

			$todaysData=array();

			$can_change_a = 1;

			// If the day has data, gather the info...
			while($i <= $b ) {
				if(($startsBeforeToday && $endsAfterToday) ||
				  ($startsBeforeToday && $endsToday) ||
				  ($startsToday && $endsToday) ||
				  ($startsToday && $endsAfterToday) ) {

					// This day has data in it.  Therefore we want to print out a summary at the bottom of each day.
					$data_seen = 1;

					//format printable times
					if ($CfgTimeFormat == "12") {
						$formattedStartTime = date("g:iA",$data["start_stamp"]);
						$formattedEndTime = date("g:iA",$data["end_stamp"]);
					} else {
						$formattedStartTime = date("G:i",$data["start_stamp"]);
						$formattedEndTime = date("G:i",$data["end_stamp"]);
					}

					if ($startsBeforeToday && $endsAfterToday) {
						$todaysData[$data["clientName"]][$data["projectTitle"]][$data["taskName"]][]= "...-...";
						if(!class_exists('Site')){
							$todaysTotal += get_duration($curStamp, $tomorrowStamp);
						}
						else{
							$todaysTotal += Common::get_duration($curStamp, $tomorrowStamp);
						}
					} else if ($startsBeforeToday && $endsToday) {
						$todaysData[$data["clientName"]][$data["projectTitle"]][$data["taskName"]][]= "...-" . $formattedEndTime;
						if(!class_exists('Site')){
							$todaysTotal += get_duration($curStamp, $data["end_stamp"]);
						}
						else{
							$todaysTotal += Common::get_duration($curStamp, $data["end_stamp"]);
						}
						
					} else if ($startsToday && $endsToday) {
						$todaysData[$data["clientName"]][$data["projectTitle"]][$data["taskName"]][]= $formattedStartTime . "-" . $formattedEndTime;
						$todaysTotal += $data["duration"];
					} else if ($startsToday && $endsAfterToday) {
						$todaysData[$data["clientName"]][$data["projectTitle"]][$data["taskName"]][]= $formattedStartTime . "-...";
						if(!class_exists('Site')){
							$todaysTotal += get_duration($data["start_stamp"],$tomorrowStamp);
						}
						else{
							$todaysTotal += Common::get_duration($data["start_stamp"],$tomorrowStamp);
						}
						
					} else {
						print "Error: time booleans are in a confused state<br />\n";
					}


					if($can_change_a && $endsAfterToday) {
						$a=$i;
						$can_change_a = 0;
					}

					if($can_change_a  && $endsToday) $a=$i+1;

					if($b<=$i) $b=$i+1;

				}
				$i++;

				if($i<$num) {
					$data = dbResult($qh,$i);
					if(!class_exists('Site')){
						fixStartEndDuration($data);
					}
					else{
						Common::fixStartEndDuration($data);
					}
					

					$startsToday = (($data["start_stamp"] >= $curStamp ) && ( $data["start_stamp"] < $tomorrowStamp ));
					$endsToday =   (($data["end_stamp"] > $curStamp ) && ($data["end_stamp"] <= $tomorrowStamp));
					$startsBeforeToday = ($data["start_stamp"] < $curStamp);
					$endsAfterToday = ($data["end_stamp"] > $tomorrowStamp);
				} else {
					$startsToday=false;
					$endsToday=false;
					$startsBeforeToday=false;
					$endsAftertoday=true;
				}
			}
		}

		$weeklyTotal += $todaysTotal;
		$monthlyTotal += $todaysTotal;

		if ($data_seen == 1) {
			//Print the entire day's worth of info we've gathered
			foreach($todaysData as $clientName => $clientArray) {
				print "<tr><td valign=\"top\" class=\"client_name_small\">$clientName</td></tr>";
				foreach($clientArray as $projectName => $projectArray) {
					print "<tr><td valign=\"top\" class=\"project_name_small\">&nbsp;$projectName</td></tr>";
					foreach($projectArray as $taskName => $taskArray) {
						print "<tr><td valign=\"top\" class=\"task_name_small\">&nbsp;&nbsp;$taskName</td></tr>";
						foreach($taskArray as $taskStr) {
							print "<tr><td valign=\"top\" class=\"task_time_small\">&nbsp;&nbsp;&nbsp;$taskStr</td></tr>";
						}
					}
				}
			}

			if(!class_exists('Site')){
				print "<tr><td valign=\"top\" class=\"task_time_total_small\">" . formatMinutes($todaysTotal) ."</td></tr>";
			}
			else{
				print "<tr><td valign=\"top\" class=\"task_time_total_small\">" . Common::formatMinutes($todaysTotal) ."</td></tr>";
			}
		

		} else {
			print "<tr><td>&nbsp;</td></tr>";
		}

		//end subtable
		print "		</table>\n";
		print " </td>\n";

		$curDay++;
		$dayCol++;
	}
	// Print the rest of the calendar.
	while (($dayCol % 7) != 0) {
		if (($dayCol % 7) == 6)
			print " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_right\">&nbsp;</td>\n ";
		else
			print " <td width=\"14%\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
		$dayCol++;
	}
	$mc->print_totals($weeklyTotal, "weekly", $year, $month, $curDay);
	$weeklyTotal = 0;
	print "</tr>\n<tr>\n";
	$mc->print_totals($monthlyTotal, "monthly", $year, $month, $curDay);

?>
					</tr>
				</table>
			</td>
		</tr>
	</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>

