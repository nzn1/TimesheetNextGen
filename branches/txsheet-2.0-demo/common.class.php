<?php 
//$Header: /cvsroot/tsheet/timesheet.php/common.inc,v 1.24 2005/09/12 23:58:09 vexil Exp $

class Common{
	
	private static $motd;
	private static $realTodayDate;
	
  public static function getMotd(){
    return self::$motd;
  }  
  public static function setMotd($i){
    self::$motd = $i;
  }
  public static function getRealTodayDate(){
    return self::$realTodayDate;
  }
	public function __construct(){
	if (!defined("COMMON_INC")) define("COMMON_INC", 1);


	include("database_credentials.inc");
	include_once("mysql.db.inc");
	require("timezone.inc");

	//get realToday's values
	$realToday = getdate(time());
	$realTodayDate = mktime(0, 0, 0, $realToday['mon'], $realToday['mday'], $realToday['year']);

	self::$motd = 1;
	//Useful constants
	define("A_DAY", 24 * 60 * 60);  //seconds per day
	define("WORK_DAY", 8); //hours per day
	define("SECONDS_PER_HOUR", 60 * 60);
	$BREAK_RATIO = (0);     // For an hour break every 8 hours this would be: (1/8)

	}
	
	public static function get_time_records($startStr, $endStr, $uid='', $proj_id=0, $client_id=0, 
							$order_by = "start_time, proj_id, task_id") {

	//build the database query
		$query = "SELECT start_time AS start_time_str, ".
					"end_time AS end_time_str, ".
					"unix_timestamp(start_time) AS start_stamp, ".
					"unix_timestamp(end_time) AS end_stamp, ".
					"duration, ".		//duration is stored in minutes 
					"".tbl::getTimesTable().".status AS subStatus, " . 
					"trans_num, ".
					"".tbl::getTimesTable().".uid, " .
					"".tbl::getUserTable().".first_name, " .
					"".tbl::getUserTable().".last_name, " .
					"".tbl::getProjectTable().".title AS projectTitle, " .
					"".tbl::getTaskTable().".name AS taskName, " .
					"".tbl::getTimesTable().".proj_id, " .
					"".tbl::getTimesTable().".task_id, " .
					"".tbl::getTimesTable().".log_message, " .
					"".tbl::getClientTable().".organisation AS clientName, " .
					"".tbl::getProjectTable().".client_id " .
				"FROM ".tbl::getTimesTable().", ".tbl::getUserTable().", ".tbl::getTaskTable().", ".tbl::getProjectTable().", ".tbl::getClientTable()." " .
				"WHERE ";

		if ($uid != '') //otherwise we want all users
			$query .= "".tbl::getTimesTable().".uid='$uid' AND ";
		if ($proj_id > 0) //otherwise we want all projects
			$query .= "".tbl::getTimesTable().".proj_id=$proj_id AND ";
		if ($client_id > 0) //otherwise we want all clients
			$query .= "".tbl::getProjectTable().".client_id=$client_id AND ";

		$query .=	"".tbl::getTimesTable().".uid    = ".tbl::getUserTable().".username AND ".
					"".tbl::getTaskTable().".task_id = ".tbl::getTimesTable().".task_id AND ".
					"".tbl::getTaskTable().".proj_id = ".tbl::getProjectTable().".proj_id AND ".
					"".tbl::getProjectTable().".client_id = ".tbl::getClientTable().".client_id AND ".
					"((start_time    >= '$startStr' AND " . 
					"  start_time    <  '$endStr') " .
					" OR (end_time   >  '$startStr' AND " .
					"     end_time   <= '$endStr') " .
					" OR (start_time <  '$startStr' AND " .
					"     end_time   >  '$endStr')) " .
				"ORDER BY $order_by";

		list($my_qh, $num) = dbQuery($query);
		return array($num, $my_qh);
	}

	public static function get_users_for_supervisor($username) {
		if ($username) {
			//build the database query and select by $uid if present
			$query = "SELECT uid, last_name, first_name, status FROM ".tbl::getUserTable(). 
				" WHERE (select uid FROM " .tbl::getUserTable(). " s WHERE s.username = '$username') = supervisor " .
				" ORDER BY status DESC, last_name, first_name";
			//print $query;
		}
		else {
			//build the database query and get all users
			$query = "SELECT uid, last_name, first_name, status FROM ".tbl::getUserTable(). 
				" ORDER BY status DESC, last_name, first_name";
			
		}

		list($my_qh, $num) = dbQuery($query);
		return array($num, $my_qh);
	}
	
	public static function count_worked_secs($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $id) {

		list($qhq, $numq) = dbQuery("SELECT timeformat FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);

		$query = "SELECT date_format(start_time,'%d') AS day_of_month, trans_num, ";

		if ($configData["timeformat"] == "12")
			$query .= "date_format(end_time, '%l:%i%p') AS endd, date_format(start_time, '%l:%i%p') AS start, ";
		else
			$query .= "date_format(end_time, '%k:%i') AS endd, date_format(start_time, '%k:%i') as start, ";

		$query .= 
			"unix_timestamp(end_time) - unix_timestamp(start_time) AS diff_sec, ".
			"unix_timestamp(start_time) AS start_time, ".
			"unix_timestamp(end_time) As end_time, ".
			"end_time As end_time_str, ".
			"start_time AS start_time_str, ".
			"".tbl::getTaskTable().".name, ".tbl::getTimesTable().".proj_id, ".tbl::getTimesTable().".task_id ".
			"FROM ".tbl::getTimesTable().", ".tbl::getTaskTable()." WHERE uid='$id' AND ";

		$query .= "".tbl::getTaskTable().".task_id = ".tbl::getTimesTable().".task_id AND ".
			"((start_time >= '$start_year-$start_month-$start_day 00:00:00' AND start_time <= '$end_year-$end_month-$end_day 23:59:59') ".
			" OR (end_time >= '$start_year-$start_month-$start_day 00:00:00' AND end_time <= '$end_year-$end_month-$end_day 23:59:59') ".
			" OR (start_time < '$start_year-$start_month-$start_day 00:00:00' AND end_time > '$end_year-$end_month-$end_day 23:59:59')) ".
			" ORDER BY day_of_month, proj_id, task_id, start_time";

		list($my_qh, $num) = dbQuery($query);

		$worked_sec = 0;
		for ($currentEntry=0;$currentEntry<$num;$currentEntry++) {
			$data = DbResult($my_qh, $currentEntry);
			//Due to a bug in mysql with converting to unix timestamp from the string,
			//we are going to use php's strtotime to make the timestamp from the string.
			//the problem has something to do with timezones.
			$data["start_time"] = strtotime($data["start_time_str"]);
			$data["end_time"] = strtotime($data["end_time_str"]);

			if ($data["start_time"] < mktime(0,0,0,$start_month,$start_day,$start_year))
				$worked_sec += $data["end_time"] - mktime(0,0,0,$start_month,$start_day,$start_year);
			if ($data["end_time"] > mktime(23,59,59,$end_month,$end_day,$end_year))
				$worked_sec += mktime(23,59,59,$end_month,$end_day,$end_year) - $data["start_time"];
			else
				$worked_sec += $data["end_time"] - $data["start_time"];
		}

		return $worked_sec;
	}

	public static function format_hours_minutes($seconds) {
		$temp = $seconds;
		if ($seconds < 0) {
			$temp = 0 - $seconds;
			$sign = '-';
		}
		else {
			$sign = '';
		}
		$hour = (int) ($temp / (60*60));

		if ($hour < 10)
			$hour = '0'. $hour;

		$temp -= (60*60)*$hour;
		$minutes = (int) ($temp / 60);

		if ($minutes < 10)
			$minutes = '0'. $minutes;

		$temp -= (60*$minutes);
		$sec = $temp;

		if ($sec > 30)
			$minutes += 1;

		return "$sign$hour:$minutes";
	}

	public static function format_seconds($seconds) {
		$temp = $seconds;
		$hour = (int) ($temp / (60*60));

		if ($hour < 10)
			$hour = '0' . $hour;

		$temp -= (60*60)*$hour;
		$minutes = (int) ($temp / 60);

		if ($minutes < 10)
			$minutes = '0' . $minutes;

		$temp -= (60*$minutes);
		$sec = $temp;

		if ($sec < 10)
			$sec = '0' . $sec;
					
		return "$hour:$minutes:$sec";
	}

	public static function formatSeconds($seconds) {
		$hours = (int)($seconds/3600);
		$seconds -= $hours * 3600;
		$minutes = (int)($seconds/60);
		$seconds -= $minutes * 60;

		return "${hours}h ${minutes}m";
	}

	public static function formatMinutes($minutes) {
		$hours = (int)($minutes/60);
		$minutes -= $hours * 60;

		return "${hours}h ${minutes}m";
	}

	public static function format_minutes($minutes) {
		$temp = $minutes;
		$hour = (int) ($temp / (60));

		if ($hour < 10)
			$hour = '0' . $hour;

		$minutes = $temp - $hour*60;

		if ($minutes < 10)
			$minutes = '0' . $minutes;

		return "$hour:$minutes";
	}

	public static function minutes_to_hours($minutes) {
		$temp = $minutes/60;
		$hours = number_format(round($temp,2),2);

		return "$hours";
	}

	public static function get_holidays($month, $year, $day=0) {

		$last_day = get_last_day($month, $year);
		$query = "SELECT date_format(date,'%d') AS day_of_month, ".
			"unix_timestamp(date) AS date, ".
			"date AS date_str, ".
			"user, type, ".
			"AM_PM, subject FROM ".tbl::getAbsenceTable()." WHERE ";
			$query .= "(type='Public') AND ";

		if ($day==0) //want the whole month's entries
			$query .= "(date >= '$year-$month-1 00:00:00' AND date <= '$year-$month-$last_day 23:59:59') ";
		else
			$query .= "(date >= '$year-$month-$day 00:00:00' AND date <= '$year-$month-$day 23:59:59') ";

		$query .= " ORDER BY day_of_month, AM_PM";

		list($my_qh, $num) = dbQuery($query);
		return array($my_qh, $num);
	}

	public static function get_absences($month, $year, $user='', $day=0) {

		$last_day = self::get_last_day($month, $year);
		
		$query = "SELECT date_format(date,'%d') AS day_of_month, ".
			"unix_timestamp(date) AS date, ".
			"date AS date_str, ".
			"user, type, ".
			"AM_PM, subject FROM ".tbl::getAbsenceTable()." WHERE ";
		if ($user=='') //want only the public holidays
			$query .= "(type='Public') AND ";
		else
			$query .= "((user='$user') OR (type='Public') OR ((type='Other') AND (user=''))) AND ";

		if ($day==0) //want the whole month's entries
			$query .= "(date >= '$year-$month-1 00:00:00' AND date <= '$year-$month-$last_day 23:59:59') ";
		else
			$query .= "(date >= '$year-$month-$day 00:00:00' AND date <= '$year-$month-$day 23:59:59') ";
		$query .= " ORDER BY day_of_month, AM_PM";

		list($my_qh, $num) = dbQuery($query);
		return array($my_qh, $num);
	}

	public static function get_start_date($user) {

		$query = "SELECT min(date) as start_date FROM ".tbl::getAllowanceTable()." WHERE username='$user'";
		list($my_qu, $num) = dbQuery($query); //num should only be 1
		$userdata = dbResult($my_qu, 1);
		return $userdata['start_date'];
	}

	public static function get_allowance($day, $month, $year, $user, $type='Holiday') {

		$user_start_date = get_start_date($user);
		if (strcmp($user_start_date,sprintf("%04d-%02d-%02d",$year,$month,$day))>0)
			return 0;

		if ($type=='Holiday')
			$query = "SELECT sum(Holiday) ";
		else
			$query = "SELECT sum(glidetime) ";
		$query .= "AS balance FROM ".tbl::getAllowanceTable()." WHERE (username='$user') ";
		$query .= "AND (date >= '$user_start_date 00:00:00' AND date <= '$year-$month-$day 23:59:59')";
		list($my_qu, $num) = dbQuery($query);
		$data = dbResult($my_qu, 1);
		return $data['balance'];
	}

	public static function get_balance($day, $month, $year, $user, $type='Holiday') {

		$user_start_date = get_start_date($user);
		if (strcmp($user_start_date,sprintf("%04d-%02d-%02d",$year,$month,$day))>0)
			return 0;
		$current_allowance = get_allowance($day, $month, $year, $user, $type);
		if($user_start_date) {
			$user_start_date_parts = explode('-',$user_start_date);
			$user_start_day = $user_start_date_parts[2];
			$user_start_month = $user_start_date_parts[1];
			$user_start_year = $user_start_date_parts[0];
		}
		else {
			$user_start_date_parts = false;
			$user_start_day = false;
			$user_start_month = false;
			$user_start_year = false;
		}
		
		if ($type!='glidetime') {
			//Holidays are easy
			$count = $current_allowance - count_absences($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user, $type);
		} else {
			//glidetime takes some work
			//First count the attendance
			$worked_sec = count_worked_secs($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user);
			//Then calculate the expected working time
			$working_time = count_working_time($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user);
			// handle times and seconds
			$count = ($current_allowance*60*60 + $worked_sec - $working_time*60*60)/(60*60);
		}
		return $count;
	}

	public static function count_working_time($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user) {
		$working_time = 0;
		$the_day = mktime(0,0,0,$start_month,$start_day,$start_year);
		$last_day = mktime(23,59,59,$end_month,$end_day,$end_year);
		while ($the_day<$last_day) {
			if ((date('w', $the_day) != 6)&&(date('w', $the_day) != 0)) {
				$working_time += WORK_DAY;
			}
			$the_day += A_DAY;
		}
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, '', 'Public');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, '', 'Other');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Sick');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Holiday');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Training');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Military');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Other');
		$working_time -= count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Compensation');

		return $working_time;
	}

	public static function count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, $type='Holiday') {

		$query = "SELECT date,AM_PM,type,user FROM ".tbl::getAbsenceTable()." WHERE ".
				"(type='$type') AND (user='$user') AND ".
				"(date >= '$start_year-$start_month-$start_day 00:00:00' AND date <= '$end_year-$end_month-$end_day 23:59:59') ".
				" ORDER BY date, AM_PM";
		list($my_qh, $num) = dbQuery($query);
		$count = 0;
		for($i=0;$i<$num;$i++) {
			$entry = dbResult($my_qh, $i);
			if ($entry['AM_PM']=='day')
				$count = $count + WORK_DAY; //hours per day
			else
				$count = $count + WORK_DAY/2;
		}
		return $count;
	}

	public static function count_absences_in_month($month, $year, $user, $type='Holiday') {
		$day = get_last_day($month, $year);
		return count_absences(1, $month, $year, $day, $month, $year, $user, $type);
	}

	public static function get_last_day($month, $year) {
		$days = date('t', strtotime("$year-$month-1"));
		return $days;
	}

	public static function get_dst_adjustment($start_stamp) {
		static $adjustment_array = array();

		//calculate the adjustment made by subtracting the total number of seconds during the DST adjusted day
		//from the number of seconds in a normal day.  I believe this will work for every possible DST scenario.
		$stdt = getdate($start_stamp);
		$stamp_a = mktime(0,0,0,$stdt["mon"],$stdt["mday"],$stdt["year"]);

		if(isset($adjustment_array[$stamp_a])) {
			return $adjustment_array[$stamp_a];
		}

		$stamp_b = strtotime(date("d M Y H:i:s",$stamp_a) . " +1 days");
		$dst_adjustment = 24*60*60 - ($stamp_b - $stamp_a);

		$adjustment_array[$start_stamp]=$dst_adjustment;
		return $dst_adjustment;
	}

	//Expects start and end to be in "Unix Timestamp" format, returns answer in minutes
	public static function get_duration($start, $end, $wdst=0) {
		$diff_in_seconds = $end - $start;
		//print "  get_duration: $end - $start = $diff_in_seconds<br />\n";

		//If we're called from simple.php, and we want to check for DST adjustments, and if the end time and 
		//start time are not both in the same Daylight Savings Time state, and the start time is midnight, 
		//we then assume the time entry was made via simple.php, and a DST adjustment needs to be made.

		//This is needed because the old simple_action.php stored time data in the database in a way that 
		//DST states were not noticed nor accounted for, but, when getting the times out of the database
		//and converting them into unix timestamps, the duration is automatically adjusted to take DST into 
		//account.  Therefore we need to remove that adjustment.

		//print "  get_duration: $wdst  $end_dst  $start_dst<br />\n";
		if( $wdst==1 ) {
			$start_dst = date('I', $start);
			$end_dst = date('I', $end);

			if( $end_dst != $start_dst ) {
				$stdt = getdate($start);

				//does the time start at midnight?  if so, we assume the time entry was made with simple.php
				//and we "undo" the adjustment that was made automatically
				if($stdt["hours"] == 0 && $stdt["minutes"] == 0 && $stdt["seconds"] == 0) {
					$dst_adjustment = get_dst_adjustment($start);
					$diff_in_seconds += $dst_adjustment;
					//print "  get_duration: adjustment $dst_adjustment<br />\n";
				}
			}
		}
		//print "  get_duration: returning ".($diff_in_seconds/60)."<br />\n";
		return ($diff_in_seconds/60);
	}

	//Expects start to be in "Unix Timestamp" format, duration in minutes
	//Returns answer in "Unix Timestamp" format
	public static function get_end_date_time($start, $duration) {
		$end_date_time = strtotime(date("d M Y H:i:s T",$start) . " + $duration minutes");
		return ($end_date_time);
	}

	public static function fix_entry_duration($entry) {
		//print "  fix_entry_duration<br />\n";

		$duration = $entry["duration"];
		$trans_num = $entry["trans_num"];
		tryDbQuery("UPDATE ".tbl::getTimesTable()." set duration=$duration WHERE trans_num=$trans_num");
	}

	public static function fix_entry_endstamp($entry) {
		//print "  fix_entry_endstamp ". $entry["trans_num"].") ".strftime("%Y-%m-%d %H:%M:%S", $entry["start_stamp"])." - ".strftime("%Y-%m-%d %H:%M:%S", $entry["end_stamp"])."<br />\n";

		$etsStr = strftime("%Y-%m-%d %H:%M:%S", $entry["end_stamp"]);
		$trans_num = $entry["trans_num"];
		tryDbQuery("UPDATE ".tbl::getTimesTable()." set end_time=\"$etsStr\" WHERE trans_num=$trans_num");
	}

	public static function get_user_empid($username) {

		// Retreives user's payroll Employee ID
		$sql = "SELECT EmpId FROM ".tbl::getUserTable()." WHERE username='$username'";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['EmpId'];
	}

	public static function get_project_name($proj_id) {

		// Retreives project title (name)
		$sql = "SELECT title FROM ".tbl::getProjectTable()." WHERE proj_id=$proj_id";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['title'];
	}

	public static function get_project_info($proj_id) {

		// Retreives title, client_id, description, deadline and link from database for a given proj_id
		$result = array();
		if ($proj_id > 0) {
			$sql = "SELECT * FROM ".tbl::getProjectTable()." WHERE proj_id=$proj_id";
			list($my_qh, $num) = dbQuery($sql);
			$result = dbResult($my_qh);
		}
		return $result;
	}

	public static function get_trans_info($trans_num) {

		$result = array();
		if ($trans_num > 0) {
			$query = "SELECT ".tbl::getProjectTable().".client_id, ".tbl::getTimesTable().".proj_id, task_id, log_message, ".
							"end_time as end_time_str, ".
							"start_time as start_time_str, ".
							"unix_timestamp(start_time) as start_stamp, ".
							"unix_timestamp(end_time) as end_stamp, ".
							"duration, " .
							"trans_num " .
						"FROM ".tbl::getTimesTable().", ".tbl::getProjectTable()." " .
						"WHERE trans_num='$trans_num' AND ".
							"".tbl::getTimesTable().".proj_id = ".tbl::getProjectTable().".proj_id";

			list($my_qh, $num) = dbQuery($query);
			$result = dbResult($my_qh);
		}
		return $result;
	}

	public static function get_acl_level($page) {
		list($qhq, $numq) = dbQuery("SELECT aclStopwatch,aclDaily,aclWeekly,aclMonthly,aclSimple,aclClients,aclProjects,aclTasks,aclReports,aclRates,aclAbsences FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);
		return $configData[$page];
	}

	public static function day_button($name, $timeStamp=0, $limit=1) {
		global $realToday, $month, $year;
		if (!$timeStamp)
			$timeStamp = $realToday[0];

		$dti=getdate($timeStamp);
		if($limit)
			$last_day = get_last_day($dti["mon"], $dti["year"]);
		else
			$last_day = 31;

		$i = 0;
		echo "<select name=\"$name\">\n";
		//I'm failing to understand why we would offer a "none" value for dates. -SLM
		//print "<option value=0>None</a>";

		for($i=1; $i <= $last_day; $i++) {
			switch($i) {
			case $dti["mday"]:
				echo "<option value=\"$i\" selected=\"selected\" >$i\n";
				break;
			default:
				echo "<option value=\"$i\">$i\n";
			}
		}
		echo "</select>";
	}

	public static function month_button ($name, $month=0) {
		global $realToday;
		if(!$month)
			$month = $realToday["mon"]; //date("m");

		$i = 1;
		echo "<select name=\"$name\">\n";
		//I'm failing to understand why we would offer a "none" value for dates. -SLM
		//print "<option value=0>None</a>";
		while ($i <= 12) {
			switch($i) {
			case $month:
				echo "<option value=\"$i\" selected=\"selected\" >" . date("M",mktime(0,0,0,$i,1,1999)) . "\n";
			break;
			default:
				echo "<option value=\"$i\">". date("M",mktime(0,0,0,$i,1,1999)) . "\n";
			}
			$i++;
		}
		echo "</select>";
	}

	public static function year_button ($name, $year=0) {
		global $realToday;
		if(!$year)
			$year = $realToday["year"]; //date("Y");

		//whatever year we're working with, we want to offer a range before and in after...
		$i = $year-5;
		echo "<select name=\"$name\">\n";
		//I'm failing to understand why we would offer a "none" value for dates. -SLM
		//print "<option value=0>None</a>";
		while ($i <= $year+5) {
			switch($i) {
			case $year:
				echo "<option value=\"$i\" selected=\"selected\" >$i\n";
				break;
			default:
				echo "<option value=\"$i\">$i\n";
			}
			$i++;
		}
		echo "</select>";
	}

	public static function single_user_select_list($varname, $username='', $extra='', $showSelect='false') {

		$authenticationManager = Site::getAuthenticationManager();
	
		$show_disabled=0;
		if($authenticationManager->hasClearance(CLEARANCE_MANAGER)) {
			//show disabled users at bottom of list
			$show_disabled=1;
			$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getUserTable()." ORDER BY status DESC, last_name, first_name";
		} else {
			$query = "SELECT uid, username, last_name, first_name FROM ".tbl::getUserTable()." where status='ACTIVE' ORDER BY last_name, first_name";
		}

		print "<select name=\"$varname\"";
		if (!empty($extra)) {
			print " $extra "; 
		}
		print ">\n";

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {
			if ($showSelect)
				print "<option value=\"0\">Select User</option>\n";

			if($show_disabled) {
				print "<optgroup label=\"Active Users\">";
				$found_disabled=0;
			}

			while ($return = dbResult($qh)) {
				if($show_disabled && !$found_disabled && $return['status']=='INACTIVE') {
					$found_disabled=1;
					print "</optgroup><optgroup style=\"color:red\"; label=\"Inactive Users\">";
				}

				$current_username = stripslashes($return["username"]);
				$current_name = stripslashes($return["last_name"] . " " . $return["first_name"] );
				print "<option value=\"$current_username\"";
				if ($current_username == $username)
					print " selected=\"selected\"";
				if ($current_name == " ")
					print ">$current_username</option>\n";
				else
					print ">$current_name</option>\n";
			}

			if($show_disabled) {
				print "</optgroup>";
			}
		}
		print "</select>";
	}


	public static function get_users_name($uid) {

		$query = "SELECT last_name, first_name, status FROM ".tbl::getUserTable()." where username='$uid'";
		list($qh, $num) = dbQuery($query);
		$data = dbResult($qh);
		return array($data['first_name'], $data['last_name'], $data['status']);
	}

	public static function multi_user_select_list($name, $selected_array=array()) {

		$authenticationManager = Site::getAuthenticationManager();
	
		$show_disabled=0;
		if($authenticationManager->hasClearance(CLEARANCE_MANAGER)) {
			//show disabled users at bottom of list
			$show_disabled=1;
			$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getUserTable()." ORDER BY status DESC, last_name, first_name";
		} else {
			$query = "SELECT uid, username, last_name, first_name FROM ".tbl::getUserTable()." where status='ACTIVE' ORDER BY last_name, first_name";
		}
		list($qh, $num) = dbQuery($query);
		print "<select name=\"$name\" multiple size=\"11\">\n";
		if($show_disabled) {
			print "<optgroup label=\"Active Users\">";
			$found_disabled=0;
		}

		while ($data = dbResult($qh)) {
				if($show_disabled && !$found_disabled && $data['status']=='INACTIVE') {
					$found_disabled=1;
					print "</optgroup><optgroup style=\"color:red\"; label=\"Inactive Users\">";
				}

				$current_username = stripslashes($data["username"]);
				$current_name = stripslashes($data["last_name"] . " " . $data["first_name"] );
				if ($current_name == " ") $current_name = $current_username;
				print "<option value=\"$current_username\"";
				if (in_array($current_username, $selected_array))
					print " selected=\"selected\" ";
				print ">$current_name</option>\n";
		}
		print "</select>";
	}

	public static function get_client_name($clientId) {

		if($clientId == 0) return "All Clients";
		$query = "SELECT organisation FROM ".tbl::getClientTable()." where client_id='$clientId'";
		list($qh, $num) = dbQuery($query);
		$data = dbResult($qh);
		return $data['organisation'];
	}

	public static function client_select_list($currentClientId, $contextUser, $isMultiple, $showSelectClient, $showAllClients, $showNoClient, $onChange="", $restrictedList=true) {

		if ($restrictedList) {
				list($qh,$num) = dbQuery(
						"SELECT ".tbl::getClientTable().".client_id, ".tbl::getClientTable().".organisation, ".
						"".tbl::getProjectTable().".client_id, ".tbl::getProjectTable().".proj_id, ".
						"".tbl::getAssignmentsTable().".proj_id, ".tbl::getAssignmentsTable().".username ".
						"FROM ".tbl::getClientTable().", ".tbl::getProjectTable().", ".tbl::getAssignmentsTable()." ".
						"WHERE ".tbl::getClientTable().".client_id > 1 ".
						"AND ".tbl::getClientTable().".client_id=".tbl::getProjectTable().".client_id ".
						"AND ".tbl::getProjectTable().".proj_id=".tbl::getAssignmentsTable().".proj_id ".
						"AND ".tbl::getAssignmentsTable().".username='$contextUser' ".
						"GROUP BY ".tbl::getClientTable().".client_id ".
						"ORDER BY organisation");
		}
		else {
				list($qh,$num) = dbQuery(
						"SELECT client_id, organisation ".
						"FROM ".tbl::getClientTable()." WHERE client_id > 1 " .
						"ORDER BY organisation");
		}

		print "<select name=\"client_id\" onchange=\"$onChange\" style=\"width:100%;\"";
		if ($isMultiple)
			print "multiple size=\"4\"";
		print ">\n";

		//should we show the 'Select Client' option
		if ($showSelectClient)
			print "<option value=\"0\">Select Client</option>\n";
		else if ($showAllClients)
			print "<option value=\"0\">All Clients</option>\n";

		//should we show the 'No Client' option
		if ($showNoClient) {
			print "<option value=\"1\"";
			if ($currentClientId == 1)
				print " selected=\"selected\"";
			print ">No Client</option>\n";
		}

		while ($result = dbResult($qh)) {
			print "<option value=\"$result[client_id]\"";
			if ($currentClientId == $result["client_id"])
				echo " selected=\"selected\"";
			print ">";
			/*$printComma = false;
			$printSpace = false;
			if (!empty($result["contact_first_name"])) {
				echo $result["contact_first_name"];
				$printSpace = true;
				$printComma = true;
			}
			if (!empty($result["contact_last_name"])) {
				if ($printSpace)
					print " ";
				echo $result["contact_last_name"];
				$printComma = true;
			}
			if ($printComma)
				print ", ";*/
			print "$result[organisation]</option>\n";
		}
		print "</select>";
	}
	
	public static function supervised_user_select_droplist($username='', $disabled='false', $width='') {
		// Creates a drop-down list of users supervised by this uid 
	
		$authenticationManager = Site::getAuthenticationManager();
	
		$show_disabled=0;
		if($authenticationManager->hasClearance(CLEARANCE_MANAGER)) {
			// show disabled users at bottom of list
			$show_disabled=1;
			$query = "SELECT uid, last_name, first_name, status FROM ".tbl::getUserTable(). 
				" WHERE (SELECT uid FROM " .tbl::getUserTable(). " s WHERE s.username = '$username') = supervisor " .
				" ORDER BY status DESC, last_name, first_name";
		} else {
			$query = "SELECT uid, username, last_name, first_name FROM $USER_TABLE  " .
				" WHERE status='ACTIVE' AND (SELECT uid FROM " .tbl::getUserTable(). "  s WHERE s.username = '$username') = supervisor " .
				" ORDER BY last_name, first_name";
		}

		$drop_list_string = "<select name=\"uid\" onChange=\"submit()\" ";
		if (!empty($width))
			$drop_list_string.= "style=\"width: $width;\" ";
		if ($disabled == 'true')
			$drop_list_string .= " disabled";
		$drop_list_string .= ">\n";

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {

			if($show_disabled) {
				$drop_list_string .= "<optgroup label=\"Active Users\">";
				$found_disabled=0;
			}

			while ($return = dbResult($qh)) {
				if($show_disabled && !$found_disabled && $return['status']=='INACTIVE') {
					$found_disabled=1;
					$drop_list_string .= "</optgroup><optgroup style=\"color:red\"; label=\"Inactive Users\">";
				}

				$current_username = stripslashes($return["username"]);
				$current_name = stripslashes($return["last_name"] . " " . $return["first_name"] );
				$drop_list_string .= "<option value=\"$current_username\"";
				if ($current_username == $username)
					$drop_list_string .= " selected";
				if ($current_name == " ")
					$drop_list_string .= ">$current_username</option>\n";
				else
					$drop_list_string .= ">$current_name</option>\n";
			}

			if($show_disabled) {
				$drop_list_string .= "</optgroup>";
			}
		}
		$drop_list_string .= "</select>";
		print $drop_list_string;
	}

	public static function project_select_list($currentClientId, $needsClient, $currentProjectId, $contextUser, $showSelectProject, $showAllProjects, $onChange="", $disabled=false) {

		if ($currentClientId == 0 && $needsClient) {
			print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
			print "  <option>Please select a client</option>\n";
			print "</select>\n";
			return;
		}

		if (empty($contextUser)) {
			$query = "SELECT proj_id, title FROM ".tbl::getProjectTable()." ";
			if ($currentClientId != 0)
				$query .= "WHERE ".tbl::getProjectTable().".client_id = $currentClientId ";
			$query .= "ORDER BY title";
		}
		else {
			$query = "SELECT DISTINCT ".tbl::getAssignmentsTable().".proj_id, ".tbl::getProjectTable().".title FROM " .
							"".tbl::getAssignmentsTable().", ".tbl::getProjectTable()." WHERE ";
			if ($currentClientId != 0)
				$query .= "".tbl::getProjectTable().".client_id = $currentClientId AND ";
			$query .= "".tbl::getAssignmentsTable().".proj_id = ".tbl::getProjectTable().".proj_id AND " .
							"".tbl::getAssignmentsTable().".username='$contextUser' AND " .
							"".tbl::getAssignmentsTable().".proj_id > 0 AND " .
							"".tbl::getProjectTable().".proj_status='Started' " .
							"ORDER BY ".tbl::getProjectTable().".title,".tbl::getAssignmentsTable().".proj_id";
		}

		list($qh, $num) = dbQuery($query);
		if ($num == 0) {
			if (!empty($contextUser)) {
				print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
				print "  <option>There are no projects assigned to you</option>\n";
				print "</select>\n";
				return;
			}
			print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
			print "  <option>There are no projects for this client</option>\n";
			print "</select>\n";
			return;
		}

		print "<select name=\"proj_id\" onchange=\"$onChange\" style=\"width:100%;\"";
		if ($disabled == 'true')
			print " disabled=\"disabled\"";
		print ">\n";

		//should we show the 'Select Project' option
		if ($showSelectProject)
			print "<option value=\"0\">Select Project</option>\n";

		if ($showAllProjects) {
			print "<option value=\"0\"";
			if ($currentProjectId == 0)
				print " selected=\"selected\"";
			print ">All Projects</option>\n";
		}

		if ($num > 0) {
			while ($return = dbResult($qh)) {
				$title = stripslashes($return["title"]);
				print "<option value=\"$return[proj_id]\"";
				if ($currentProjectId == $return["proj_id"])
					print " selected=\"selected\"";
				print ">$title</option>\n";
			}
		}
		print "</select>";
	}

	public static function task_select_list ($currentProjectId, $currentTaskId, $contextUser="", $onChange="") {


		if ($currentProjectId == 0) {
			print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
			print "  <option>Please select a project</option>\n";
			print "</select>\n";
			return;
		}

		if ($contextUser == '')
			$query = "SELECT task_id, name, status FROM ".tbl::getTaskTable()." WHERE proj_id=$currentProjectId";
		else {
// 	$query = "SELECT DISTINCT $TASK_ASSIGNMENTS_TABLE.task_id, ".tbl::getTaskTable().".name FROM $TASK_ASSIGNMENTS_TABLE, ".tbl::getTaskTable().", ".tbl::getAssignmentsTable()." WHERE ".
// 	  "".tbl::getAssignmentsTable().".proj_id=$proj_id AND $TASK_ASSIGNMENTS_TABLE.task_id = ".tbl::getTaskTable().".task_id and ".
// 	  "$TASK_ASSIGNMENTS_TABLE.task_id > 1 AND ".tbl::getTaskTable().".status='Started' ORDER BY $TASK_ASSIGNMENTS_TABLE.task_id";
			$query = "SELECT DISTINCT $TASK_ASSIGNMENTS_TABLE.task_id, ".tbl::getTaskTable().".name " .
				"FROM $TASK_ASSIGNMENTS_TABLE, ".tbl::getTaskTable()." WHERE ".
				"".tbl::getTaskTable().".proj_id=$currentProjectId AND ".
				"$TASK_ASSIGNMENTS_TABLE.task_id = ".tbl::getTaskTable().".task_id AND ".
				"$TASK_ASSIGNMENTS_TABLE.username = '$contextUser' AND ".
				"".tbl::getTaskTable().".status='Started' " .
				"ORDER BY $TASK_ASSIGNMENTS_TABLE.name,$TASK_ASSIGNMENTS_TABLE.task_id";
		}

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {
			print "<select name=\"task_id\" onchange=\"$onChange\" style=\"width:100%;\">\n";
			while ($return = dbResult($qh)) {
				print "<option value=\"$return[task_id]\"";
				if ($currentTaskId == $return["task_id"])
					print " selected=\"selected\"";
				print ">$return[name]</option>\n";
			}
			print "</select>";
		}
		else {
			print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
			print "  <option>There are no tasks assigned</option>\n";
			print "</select>\n";
		}
	}


	/*
	 * Function to build an HTML uni-select widget.
	 * Parameters:
	 *     $name: Name of the input select
	 *     $values: An array of strings. Each string is of the form
	 *         value_part:display_part
	 *     $selected: If supplied, it contains the value_part which should
	 *         be selected by default. Its default value is NULl.
	 * Returns: A string containing the text to build the required uni-select
	 *     widget.
	 */
	public static function build_uni_select($name, $values, $selected=NULL) {
		if (empty($name)) {
			echo "build_uni_select: first parameter must be non-empty string";
			return "";
		}
		if (!is_array($values)) {
			echo "build_uni_select: second parameter is not an array";
			return "";
		}
		$ret = "<select name=\"" . $name . "\" id=\"" . $name . "\">\n";
		$len = count($values);
		$idx = 0;
		while ($idx < $len) {
			$val = explode(':', $values[$idx], 2);
			if (!(empty($selected)) && ($val[0] == $selected)) {
				$ret .= "<option value=\"$val[0]\" selected=\"selected\" >$val[1]</option>\n";
			} else {
				$ret .= "<option value=\"$val[0]\">$val[1]</option>\n";
			}
			$idx++;
		}
		$ret .= "</select>\n";
		return $ret;
	}

	public static function user_select_droplist_string($varname, $username='', $width='', $disabled='false') {

		$authenticationManager = Site::getAuthenticationManager();

	
		$show_disabled=0;
		if($authenticationManager->hasClearance(CLEARANCE_MANAGER)) {
			//show disabled users at bottom of list
			$show_disabled=1;
			$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getUserTable()." ORDER BY status DESC, last_name, first_name";
		} else {
			$query = "SELECT uid, username, last_name, first_name FROM ".tbl::getUserTable()." where status='ACTIVE' ORDER BY last_name, first_name";
		}

		$drop_list_string = "<select name=\"$varname\" onchange=\"submit()\" ";
		if (!empty($width))
			$drop_list_string.= "style=\"width: $width;\" ";
		if ($disabled == 'true')
			$drop_list_string .= " disabled=\"disabled\"";
		$drop_list_string .= ">\n";

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {

			if($show_disabled) {
				$drop_list_string .= "<optgroup label=\"Active Users\">";
				$found_disabled=0;
			}

			while ($return = dbResult($qh)) {
				if($show_disabled && !$found_disabled && $return['status']=='INACTIVE') {
					$found_disabled=1;
					$drop_list_string .= "</optgroup><optgroup style=\"color:red\"; label=\"Inactive Users\">";
				}

				$current_username = stripslashes($return["username"]);
				$current_name = stripslashes($return["last_name"] . " " . $return["first_name"] );
				$drop_list_string .= "<option value=\"$current_username\"";
				if ($current_username == $username)
					$drop_list_string .= " selected=\"selected\"";
				if ($current_name == " ")
					$drop_list_string .= ">$current_username</option>\n";
				else
					$drop_list_string .= ">$current_name</option>\n";
			}

			if($show_disabled) {
				$drop_list_string .= "</optgroup>";
			}
		}
		$drop_list_string .= "</select>";
		return $drop_list_string;
	}

	public static function user_select_droplist($username='', $disabled='false', $width='') {
		$drop_list_string = self::user_select_droplist_string('uid', $username, $width, $disabled);
		print $drop_list_string;
	}

	public static function acl_select_droplist($id, $selected='', $disabled='false') {
?>
	<select name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php if ($disabled=='true') echo 'readonly'?>>
	<option value="Admin" <?php if ($selected== 'Admin') echo "selected=\"selected\"";?>>Admin</option>
	<option value="Mgr" <?php if ($selected== 'Mgr') echo "selected=\"selected\"";?>>Mgr</option>
	<option value="Basic" <?php if ($selected== 'Basic') echo "selected=\"selected\"";?>>Basic</option>
	<option value="None" <?php if ($selected== 'None') echo "selected=\"selected\"";?>>None</option>
	</select>
<?php
	}

	public static function absence_select_droplist($selected='', $disabled='false', $id) {
?>
	<select name="<?php echo $id; ?>" onchange="OnChange()" id="<?php echo $id; ?>" <?php if ($disabled=='true') echo 'readonly'?>>
	<option value="" <?php if ($selected == '') echo "selected=\"selected\"";?>></option>
	<option value="Holiday" <?php if ($selected== 'Holiday') echo "selected=\"selected\"";?>>Holiday</option>
	<option value="Sick" <?php if ($selected== 'Sick') echo "selected=\"selected\"";?>>Sick</option>
	<option value="Military" <?php if ($selected== 'Military') echo "selected=\"selected\"";?>>Mil/Civ</option>
	<option value="Training" <?php if ($selected== 'Training') echo "selected=\"selected\"";?>>Training</option>
	<option value="Compensation" <?php if ($selected== 'Compensation') echo "selected=\"selected\"";?>>Compensation</option>
	<option value="Other" <?php if ($selected== 'Other') echo "selected=\"selected\"";?>>Other</option>
	<option value="Public" <?php if ($selected== 'Public') echo "selected=\"selected\"";?>>Public</option>
	</select>
<?php
	}

	public static function client_select_droplist($client_id=1, $disabled=false, $info=true) {

		$query = "SELECT client_id, organisation FROM ".tbl::getClientTable()." ORDER BY organisation";

		//print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"100%\">";
		print "<select name=\"client_id\" onchange=\"submit()\" style=\"width: 100%;\"";
		if ($disabled)
			print " disabled=\"disabled\"";
		print ">\n";

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {
			while ($return = dbResult($qh)) {
				if($num > 1 && $return["client_id"] == 1) continue; //ignore default client
				$current_organisation = stripslashes($return["organisation"]);
				print "<option value=\"$return[client_id]\"";
				if ($return["client_id"] == $client_id)
					print " selected=\"selected\"";
				print ">$current_organisation</option>\n";
			}
			print "</select>";
			if($info) {
				print "</td><td width=\"0\">";
				print "<input type=\"button\" name=\"info\" value=\"Info\"";
				print "onclick=window.open(\"client_info.php?client_id=$client_id\",";
				print "\"Client_Info\",";
				print "\"location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=200\") />";
			}
			//print "</td></tr></table>";
		}
		else
			print "</select>";
			//print "</td></tr></table>";
	}

	public static function project_select_droplist($proj_id=1, $disabled='false') {

			$query = "SELECT " .
							"proj_id, " .
							"title, " .
							"organisation " .
							"FROM ".tbl::getProjectTable().", ".tbl::getClientTable()." ".
							"WHERE ".tbl::getProjectTable().".client_id = ".tbl::getClientTable().".client_id ".
							"ORDER BY ".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title";

		print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td width=\"100%\">";
		print "<select name=\"proj_id\" onchange=\"submit()\" style=\"width: 100%;\"";
		if ($disabled == 'true')
			print " disabled=\"disabled\"";
		print ">\n";

		list($qh, $num) = dbQuery($query);
		$current_organisation = NULL;
		if ($num > 0) {
			while ($return = dbResult($qh)) {
				if ($current_organisation != stripslashes($return["organisation"])) {
						if ($current_organisation != NULL)
							print "</optgroup>";
						$current_organisation = stripslashes($return["organisation"]);
						print "<optgroup label=\"".$current_organisation."\">\n";
				}
				$current_title = $return['title'];
				print "<option value=\"$return[proj_id]\"";
				if ($return["proj_id"] == $proj_id)
					print " selected=\"selected\"";
				print ">$current_title</option>\n";
			}
			print "</optgroup>\n";
			print "</select></td><td width=\"0\">";
			print "</td></tr></table>";
		}
		else
			print "</select></td></tr></table>";
	}


	public static function present_log_message($action) {
		global $check_in_time_hour, $check_out_time_hour,$check_in_time_min, $check_out_time_min, $year,
		$month, $day, $proj_id, $task_id, $destination;
	?>
<HTML>
<body BGCOLOR="#FFFFFF" >
<FORM ACTION="clock_action.php" METHOD=POST>
<table BORDER=1 align=CENTER>
	<?php {
	if ($destination)
		print "<input type=HIDDEN name=destination value=$destination />\n";
		print "<input type=HIDDEN name=check_in_time_hour value=\"$check_in_time_hour\" />\n";
		print "<input type=HIDDEN name=check_out_time_hour value=\"$check_out_time_hour\" />\n";
		print "<input type=HIDDEN name=check_in_time_min value=\"$check_in_time_min\" />\n";
		print "<input type=HIDDEN name=check_out_time_min value=\"$check_out_time_min\" />\n";
		print "<input type=HIDDEN name=year value=\"$year\" />\n";
		print "<input type=HIDDEN name=month value=\"$month\" />\n";
		print "<input type=HIDDEN name=day value=\"$day\" />\n";
		print "<input type=HIDDEN name=proj_id value=\"$proj_id\" />\n";
		print "<input type=HIDDEN name=task_id value=\"$task_id\" />\n";
		switch($action) {
		case "inout":
			print "<input type=HIDDEN name=check_in_out_x value=1 />\n";
			break;
		case "at":
			print "<input type=HIDDEN name=check_out_at_x value=1 />\n";
			break;
		case "now":
			print "<input type=HIDDEN name=check_out_now_x value=1 />\n";
		}
		print "<input type=HIDDEN name=log_message_presented value=1 />\n";
}?>

	<tr><td>Please Enter Log message: (max 255 characters)</td></tr>
	<tr><td><TEXTAREA name=log_message COLS=60 ROWS=4></TEXTAREA></td></tr>
	<tr><td><input type="submit" value="Done" /></td></tr>
</table>
</FORM>
</body>
</HTML>
<?php
	}

	public static function proj_status_list($name, $status='') {
?>
	<select name=<?php echo $name ?>>
	<option value="Pending" <?php if ($status == 'Pending') echo "selected=\"selected\"";?>>Pending</option>
	<option value="Started" <?php if ($status == 'Started') echo "selected=\"selected\"";?>>Started</option>
	<option value="Suspended" <?php if ($status == 'Suspended') echo "selected=\"selected\"";?>>Suspended</option>
	<option value="Complete" <?php if ($status == 'Complete') echo "selected=\"selected\"";?>>Complete</option>
	</select>
<?php
	}	
	
	public static function proj_status_list_filter($name, $status='', $onChange='submit();') {
?>
	<select name=<?php echo $name ?> onchange="<?php echo $onChange?>" >
	<option value="All" <?php if ($status == 'All') echo "selected=\"selected\"";?>>All</option>
	<option value="Pending" <?php if ($status == 'Pending') echo "selected=\"selected\"";?>>Pending</option>
	<option value="Started" <?php if ($status == 'Started') echo "selected=\"selected\"";?>>Started</option>
	<option value="Suspended" <?php if ($status == 'Suspended') echo "selected=\"selected\"";?>>Suspended</option>
	<option value="Complete" <?php if ($status == 'Complete') echo "selected=\"selected\"";?>>Complete</option>
	</select>
<?php
	}


	public static function parse_and_echo($text) {
		if (isset($_SESSION['loggedInUser'])) 
			$uid=$_SESSION['loggedInUser'];
		else
			$uid='unknown';


		$text = str_replace("%commandmenu%", Site::getCommandMenu()->toString(), $text);


		global $errormsg;

		//replace errormsg string
		$text = str_replace("%errormsg%", $errormsg, $text);

		//replace username
		$text = str_replace("%username%", $uid, $text);

		//replace time
		$text = str_replace("%time%", date("g:ia"), $text);

		//replace date
		$text = str_replace("%date%", strftime("%A %B %d, %Y"), $text);

		//replace timezone
		if(function_exists("date_default_timezone_get"))
			$text = str_replace("%timezone%", date_default_timezone_get(), $text);
		else
			$text = str_replace("%timezone%", getenv("TZ"), $text);

		//output the result
		echo $text;
	}


	//reverses the effects of htmlentities (see PHP manual)
	public static function unhtmlentities($str) {
		$trans = get_html_translation_table(HTML_ENTITIES);
		$trans = array_flip($trans);
		return strtr($str, $trans);
	}


	public static function errorPage($message, $from_popup = false) {
		$targetWindowLocation = "error.php?errormsg=$message";

		if (!$from_popup)
			Header("Location: $targetWindowLocation");
		else
			loadMainPageAndCloseWindow($targetWindowLocation);
		exit;
	}

	public static function loadMainPageAndCloseWindow($targetWindowLocation) {
		//now close this window, and open the target page in the main window
		//(passing it all the parms it needs)

		?>
			<html>
				<head>
					<script type="text/javascript">
						function loadAndClose() {
							if (window.opener.closed) {
								//create a new window
								window.open("<?php echo $targetWindowLocation; ?>", "newMainWindow");
							}
							else {
								//get the main window's location and store it as the destination
								var targetWindowLocation = "<?php echo $targetWindowLocation; ?>";
								var mainWindowLocation = window.opener.location.href;
								var questionPos = mainWindowLocation.indexOf('?');
								if (questionPos != -1)
									mainWindowLocation = mainWindowLocation.substring(0,questionPos);
								var destinationPos = targetWindowLocation.indexOf('destination=');
								if (destinationPos == -1)
									//just append it
									targetWindowLocation += '&desination=' + mainWindowLocation;
								else {
									var nextAmpPos = targetWindowLocation.indexOf('&', destinationPos);
									if (nextAmpPos == -1) {
										targetWindowLocation =
											targetWindowLocation.substring(destinationPos, targetWindowLocation.length);
									}
									else {
										var myRegex = new RegExp("destination=(.+?)&", "g")
										targetWindowLocation = targetWindowLocation.replace(myRegex, 'destination=' + mainWindowLocation + '&');
									}
								}
								window.opener.location=targetWindowLocation;
							}

							//close the popup window
							window.close();
						}
					</script>
				</head>
				<body onLoad="javascript:loadAndClose();">
					You do not have javascript enabled. Javascript is required for TimesheetNextGen
				</body>
			</html>
		<?php
		exit;
	}

	public static function isValidProjectForClient($projectId, $clientId) {

		list($qh, $num) = dbQuery("SELECT proj_id FROM ".tbl::getProjectTable()." " .
						"WHERE client_id='$clientId' AND proj_id='$projectId'");

		return ($num > 0);
	}

	public static function getValidProjectForClient($clientId) {

		list($qh, $num) = dbQuery("SELECT proj_id FROM ".tbl::getProjectTable()." " .
						"WHERE client_id='$clientId'");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		return $data["proj_id"];
	}

	public static function getFirstClient() {

		list($qh, $num) = dbQuery("SELECT client_id FROM ".tbl::getClientTable()."");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		return $data["client_id"];
	}

	public static function getClientNameFromProject($proj_id) {

		list($qh, $num) = dbQuery("SELECT client_id FROM ".tbl::getProjectTable()." WHERE proj_id = $proj_id");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		$client_id = $data["client_id"];

		list($qh, $num) = dbQuery("SELECT organisation FROM ".tbl::getClientTable()." WHERE client_id = $client_id");
		if ($num == 0)
			return 0;
		$data = dbResult($qh);
		return $data["organisation"];
	}

	public static function getFirstProject() {

		list($qh, $num) = dbQuery("SELECT proj_id FROM ".tbl::getProjectTable()."");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		return $data["proj_id"];
	}

	public static function getWeekStartDay() {

		list($qhq, $numq) = dbQuery("SELECT weekstartday FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);
		return $configData["weekstartday"];
	}
	
	public static function getProjectItemsPerPage() {

		list($qhq, $numq) = dbQuery("SELECT project_items_per_page FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);
		return $configData["project_items_per_page"];
	}
	
	public static function getTaskItemsPerPage() {

		list($qhq, $numq) = dbQuery("SELECT task_items_per_page FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);
		return $configData["task_items_per_page"];
	}

	public static function getFirstUser() {

		list($qh, $num) = dbQuery("SELECT username FROM ".tbl::getUserTable()." ");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		return $data["username"];
	}

	public static function getTimeFormat() {

			list($qhq, $numq) = dbQuery("SELECT timeformat FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
			$configData = dbResult($qhq);
			return $configData["timeformat"];
	}

	public static function getVersion() {

		list($qh, $num) = dbQuery("SELECT version FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		return $data["version"];
	}

	public static function getWeeklyStartEndDates($time) {
		$wsd = self::getWeekStartDay();
		$daysToMinus = date('w',$time) - $wsd;
		if ($daysToMinus < 0)
		    $daysToMinus += 7;

		//make sure we're calculating from midnight
		$dti = getdate($time);
		$day = $dti["mday"];
		$month = $dti["mon"]; 
		$year = $dti["year"];
		$time = mktime(0,0,0, $month, $day, $year);

		//work out the start date by subtracting days to get to beginning of week
		$startDate = strtotime(date("d M Y H:i:s",$time) . " -$daysToMinus days");
		$endDate = strtotime("+1 week", $startDate);

		return array($startDate,$endDate);
	}

	public static function getMonthlyEndDate($dti) {
		$next_month = $dti["mon"] + 1;
		$next_year = $dti["year"];
		if($next_month == 13) {
			$next_month = 1;
			$next_year++;
		}

		return mktime(0,0,0,$next_month,1,$next_year);
	}

	public static function __put_data_in_array(&$newarray, $index, $data, $curStamp, $endStamp, $duration, $check_log) {

		//if we already have time in the indexed box, and the log messages match
		//add the duration to the existing duration
		$found_matching=0;
		if(array_key_exists($index,$newarray)) {
			if($check_log) {
				foreach($newarray[$index] as &$ary) {
					if($ary["log_message"] == $data["log_message"]) {
						$ary["duration"]+=$duration;
						$found_matching = 1;
					}
				}
			} else {
				$newarray[$index][0]["duration"]+=$duration;
				$found_matching = 1;
			}
		}

		if(!$found_matching) {
			$info=array();
			$info["start_stamp"]=$curStamp;
			$info["end_stamp"]=$endStamp;
			$info["duration"]=$duration;
			$info["clientName"]=$data["clientName"];
			$info["projectTitle"]=$data["projectTitle"];
			$info["taskName"]=$data["taskName"];
			$info["log_message"]=$data["log_message"];
			$info["status"]=$data["subStatus"];
			$info["client_id"]=$data["client_id"];
			$info["proj_id"]=$data["proj_id"];
			$info["task_id"]=$data["task_id"];
			$info["trans_num"]=$data["trans_num"];
			$info["uid"]=$data["uid"];
			$info["first_name"]=$data["first_name"];
			$info["last_name"]=$data["last_name"];

			$newarray[$index][]=$info;
		}
	}

	public static function split_data_into_discrete_days($data,$orderby,&$darray,$check_log=0) {
		//The job of this function is to split those entries that span date boundaries 
		//into multiple entries that stop & re-start on date boundaries, and to put the
		//new entries into the array $darray
		$dval = getdate($data["start_stamp"]);
		$curStamp = mktime(0,0,0,$dval["mon"],$dval["mday"],$dval["year"]);
		while($curStamp < $data["end_stamp"]){
			$tomorrowStamp = strtotime(date("d M Y H:i:s",$curStamp) . " +1 day");

			$startsToday = (($data["start_stamp"] >= $curStamp ) && ( $data["start_stamp"] < $tomorrowStamp ));
			$endsToday =   (($data["end_stamp"] > $curStamp ) && ($data["end_stamp"] <= $tomorrowStamp));
			$startsBeforeToday = ($data["start_stamp"] < $curStamp);
			$endsAfterToday = ($data["end_stamp"] > $tomorrowStamp);

			if ($startsBeforeToday && $endsAfterToday) {
				$duration = self::get_duration($curStamp, $tomorrowStamp);
				$endStamp=$tomorrowStamp;
			} else if ($startsBeforeToday && $endsToday) {
				$duration = self::get_duration($curStamp, $data["end_stamp"]);
				$endStamp = $data["end_stamp"];
			} else if ($startsToday && $endsToday) {
				$duration = $data["duration"];
				$curStamp = $data["start_stamp"];
				$endStamp = $data["end_stamp"];
			} else if ($startsToday && $endsAfterToday) {
				$duration = self::get_duration($data["start_stamp"],$tomorrowStamp);
				$endStamp = $tomorrowStamp;
				$curStamp = $data["start_stamp"];
			} else {
				print "Error: time booleans are in a confused state<br>\n";
				continue;
			}

			// don't reset the start time of all entries
			//$data["start_stamp"]=$curStamp;
			$dndx=make_index($data,$orderby);
			self::__put_data_in_array($darray,$dndx,$data,$curStamp,$endStamp,$duration,$check_log);

			$curStamp = $tomorrowStamp;
		}
	}
	
	public static function fixStartEndDuration(&$data) {
		//Due to a bug in mysql with converting to unix timestamp from the string,
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$data["start_time"] = strtotime($data["start_time_str"]);

		//If we've got a duration, use that to determine/override the end_stamp
		//If not, figure out the duration
		if(isset($data["duration"]) && ($data["duration"] > 0) ) {
			$new_end_stamp = self::get_end_date_time($data["start_stamp"], $data["duration"]);			

			
			
			if($data["end_stamp"] != $new_end_stamp) {
				$old_end_stamp = $data["end_stamp"];
				$data["end_stamp"] = $new_end_stamp;
				//even if stamps are different, it may result in same string being stored in db
				//see: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php
				//and read the comments by Miles Nordin and Joakim Nygard
				if(strftime("%Y-%m-%d %H:%M:%S", $old_end_stamp) != strftime("%Y-%m-%d %H:%M:%S", $new_end_stamp))
					self::fix_entry_endstamp($data);
			}
		} else {
			if($data["end_time_str"] != '0000-00-00 00:00:00') {
				$data["end_stamp"] = strtotime($data["end_time_str"]);
				$data["duration"]=self::get_duration($data["start_stamp"], $data["end_stamp"], 1);
				self::fix_entry_duration($data);
			} else {
				//have start time, but no end time or duration, return 0 (false)
				return 0;
			}
		}
		return 1;
	}

	public static function gotoStartPage() {
		list($result, $count) = dbQuery("SELECT startPage FROM ".tbl::getConfigTable()." WHERE config_set_id = '1';");
		list($startPage) = dbResult($result);
		
		header("Location: $startPage.php");
		exit();
	}
}
// vim:ai:ts=4:sw=4:filetype=php
?>