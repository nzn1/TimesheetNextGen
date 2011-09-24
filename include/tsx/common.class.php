<?php 

class Common{
	
	private static $motd;
	
  public static function getMotd(){
    return self::$motd;
  }  
  public static function setMotd($i){
    self::$motd = $i;
  }
	public function __construct(){
	if (!defined("COMMON_INC")) define("COMMON_INC", 1);


//	include("database_credentials.inc");
	include_once("mysql.db.inc");
	require("timezone.inc");

	self::$motd = 1;
	//Useful constants
	define("A_DAY", 24 * 60 * 60);  //seconds per day
	define("WORK_DAY", 8); //hours per day
	define("SECONDS_PER_HOUR", 60 * 60);
	
	// For an hour break every 8 hours this would be: (1/8)
	gbl::setBreakRatio(0);     

	}
	
	public static function get_time_records($startStr, $endStr, $uid='', $projId=0, $clientId=0, 
							$order_by = "start_time, proj_id, task_id") {

	//build the database query
		$query = "SELECT start_time AS start_time_str, ".
					"end_time AS end_time_str, ".
					"unix_timestamp(start_time) AS start_stamp, ".
					"unix_timestamp(end_time) AS end_stamp, ".
					"duration, ".		//duration is stored in minutes 
					"".tbl::getUTCTimesTable().".status AS subStatus, " . 
					"trans_num, ".
					"".tbl::getUTCTimesTable().".uid, " .
					"".tbl::getUserTable().".first_name, " .
					"".tbl::getUserTable().".last_name, " .
					"".tbl::getProjectTable().".title AS projectTitle, " .
					"".tbl::getTaskTable().".name AS taskName, " .
					"".tbl::getUTCTimesTable().".proj_id, " .
					"".tbl::getUTCTimesTable().".task_id, " .
					"".tbl::getUTCTimesTable().".timezone, " .
					"".tbl::getUTCTimesTable().".log_message, " .
					"".tbl::getClientTable().".organisation AS clientName, " .
					"".tbl::getProjectTable().".client_id " .
				"FROM ".tbl::getUTCTimesTable().", ".tbl::getUserTable().", ".tbl::getTaskTable().", ".tbl::getProjectTable().", ".tbl::getClientTable()." " .
				"WHERE ";

		if ($uid != '') //otherwise we want all users
			$query .= "".tbl::getUTCTimesTable().".uid='$uid' AND ";
		if ($projId > 0) //otherwise we want all projects
			$query .= "".tbl::getUTCTimesTable().".proj_id=$projId AND ";
		if ($clientId > 0) //otherwise we want all clients
			$query .= "".tbl::getProjectTable().".client_id=$clientId AND ";

		$query .=	"".tbl::getUTCTimesTable().".uid    = ".tbl::getUserTable().".username AND ".
					"".tbl::getTaskTable().".task_id = ".tbl::getUTCTimesTable().".task_id AND ".
					"".tbl::getTaskTable().".proj_id = ".tbl::getProjectTable().".proj_id AND ".
					"".tbl::getProjectTable().".client_id = ".tbl::getClientTable().".client_id AND ".
					"((start_time    >= '$startStr' AND " . 
					"  start_time    <  '$endStr') " .
					" OR (end_time   >  '$startStr' AND " .
					"     end_time   <= '$endStr') " .
					" OR (start_time <  '$startStr' AND " .
					"     end_time   >  '$endStr')) " .
				"ORDER BY $order_by";

    //ppr($query);
    
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
			"".tbl::getTaskTable().".name, ".tbl::getUTCTimesTable().".proj_id, ".tbl::getUTCTimesTable().".task_id ".
			"FROM ".tbl::getUTCTimesTable().", ".tbl::getTaskTable()." WHERE uid='$id' AND ";

		$query .= "".tbl::getTaskTable().".task_id = ".tbl::getUTCTimesTable().".task_id AND ".
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

	/**
	* Parse a size with a "binary prefix" multiplier 
	* Function borrowed from the Drupal Project
	*/
	public static function parse_size($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.

		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		} else {
			return round($size);
		}
	}

	/**
	* Get the "post" max size limit in bytes
	*/
	public static function get_post_max_size() {
		static $max_post_size = -1;

		if ($max_post_size < 0) {
			// Start with post_max_size.
			$max_post_size = self::parse_size(ini_get('post_max_size'));
		}
		return $max_post_size;
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

		return "${hours}".JText::_('HR')." ${minutes}".JText::_('MN');
	}

	public static function formatMinutes($minutes) {
		$hours = (int)($minutes/60);
		$minutes -= $hours * 60;

		return "${hours}".JText::_('HR')." ${minutes}".JText::_('MN');
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

		$last_day = self::get_last_day($month, $year);
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

		$user_start_date = self::get_start_date($user);
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

		$user_start_date = self::get_start_date($user);
		if (strcmp($user_start_date,sprintf("%04d-%02d-%02d",$year,$month,$day))>0)
			return 0;
		$current_allowance = self::get_allowance($day, $month, $year, $user, $type);
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
			$count = $current_allowance - self::count_absences($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user, $type);
		} else {
			//glidetime takes some work
			//First count the attendance
			$worked_sec = self::count_worked_secs($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user);
			//Then calculate the expected working time
			$working_time = self::count_working_time($user_start_day, $user_start_month, $user_start_year, $day, $month, $year, $user);
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
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, '', 'Public');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, '', 'Other');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Sick');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Holiday');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Training');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Military');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Other');
		$working_time -= self::count_absences($start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $user, 'Compensation');

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
		$day = self::get_last_day($month, $year);
		return self::count_absences(1, $month, $year, $day, $month, $year, $user, $type);
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
		tryDbQuery("UPDATE ".tbl::getUTCTimesTable()." set duration=$duration WHERE trans_num=$trans_num");
	}

	public static function fix_entry_endstamp($entry) {
		//print "  fix_entry_endstamp ". $entry["trans_num"].") ".strftime("%Y-%m-%d %H:%M:%S", $entry["start_stamp"])." - ".strftime("%Y-%m-%d %H:%M:%S", $entry["end_stamp"])."<br />\n";

		$etsStr = strftime("%Y-%m-%d %H:%M:%S", $entry["end_stamp"]);
		$trans_num = $entry["trans_num"];
		tryDbQuery("UPDATE ".tbl::getUTCTimesTable()." set end_time=\"$etsStr\" WHERE trans_num=$trans_num");
	}

	public static function get_user_empid($username) {

		// Retreives user's payroll Employee ID
		$sql = "SELECT EmpId FROM ".tbl::getUserTable()." WHERE username='$username'";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['EmpId'];
	}

	public static function get_project_name($projId) {

		// Retreives project title (name)
		$sql = "SELECT title FROM ".tbl::getProjectTable()." WHERE proj_id=$projId";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['title'];
	}

	public static function get_project_info($projId) {

		// Retreives title, client_id, description, deadline and link from database for a given proj_id
		$result = array();
		if ($projId > 0) {
			$sql = "SELECT * FROM ".tbl::getProjectTable()." WHERE proj_id=$projId";
			list($my_qh, $num) = dbQuery($sql);
			$result = dbResult($my_qh);
		}
		return $result;
	}

	public static function get_trans_info($trans_num) {

		$result = array();
		if ($trans_num > 0) {
			$query = "SELECT ".tbl::getProjectTable().".client_id, ".tbl::getUTCTimesTable().".proj_id, task_id, log_message, ".
							"end_time as end_time_str, ".
							"start_time as start_time_str, ".
							"unix_timestamp(start_time) as start_stamp, ".
							"unix_timestamp(end_time) as end_stamp, ".
							"duration, " .
							"trans_num " .
						"FROM ".tbl::getUTCTimesTable().", ".tbl::getProjectTable()." " .
						"WHERE trans_num='$trans_num' AND ".
							"".tbl::getUTCTimesTable().".proj_id = ".tbl::getProjectTable().".proj_id";

			list($my_qh, $num) = dbQuery($query);
			$result = dbResult($my_qh);
		}
		return $result;
	}

	public static function get_acl_level($page) {
	
	  if($page == 'Administrator')return 'Administrator';
	  if($page == 'Manager')return 'Manager';
	  if($page == 'Basic')return 'Basic';
		list($qhq, $numq) = dbQuery("SELECT aclStopwatch,aclDaily,aclWeekly,aclMonthly,aclSimple,aclClients,aclProjects,aclTasks,aclReports,aclRates,aclAbsences,aclExpenses,aclECategories,aclTApproval FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
		$configData = dbResult($qhq);
		if(array_key_exists($page,$configData)){
		  return $configData[$page];
		}
		else {
			echo "<pre>The ACL Level Requested (".$page.") could not be found</pre>";
			ppr(getShortDebugTrace());
      		return null;
    	}
	}
	
	/**
 	* Routine to produce a selection list of employees
 	* @params String $name - the name of the html selection list
 	* @params String Array $emparray - a list of employee names
 	* @params String $default - if set enables the existing employee to be pre-selected in the list
 	*/
	public static function emp_button($name, $emparray, $default='') {
		echo "<select name=\"$name\" style=\"width:100%\">\n";

		foreach($emparray as $emp) {
			echo "<option value=\"$emp\"";
				if ($default == $emp) print " selected ";
			echo "> $emp </option> \n";
		}

		echo "</select>";
	}

	/**
 	* Routine to produce a selection list of employees who are supervisors, designated as managers in the user table
 	* @params String $name - the name of the html selection list
 	* @params String Array $svruid - a list of employee's uids who are noted as managers
 	* @params String Array $svrname - a list of employee's names who are noted as managers
 	* * @params String $default - if set enables the existing supervisor to be pre-selected in the list
 	*/
	public static function svr_button($name, $svruid, $svrname, $default='') {
		echo "<select name=\"$name\" style=\"width:100%\">\n";
		print "<option value=\"none\"";
			if ($default == "none" | $default == "") print " selected ";
			print ">None</option>\n";
			for ($i=0; $i<count($svruid); $i++)  {
				print "<option value=\"$svruid[$i]\"";
				if ($default == $svruid[$i]) print " selected ";
					print ">$svrname[$i]</option>\n";
			}
		echo "</select>";
	}
	
	public static function day_button($name, $timeStamp=0, $limit=1) {
		$realToday = gbl::getTodayDate();
		if (!$timeStamp)
			$timeStamp = $realToday[0];

		$dti=getdate($timeStamp);
		if($limit)
			$last_day = self::get_last_day($dti["mon"], $dti["year"]);
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
		$realToday = gbl::getTodayDate();
		if(!$month)
			$month = $realToday["mon"]; //date("m");

		$i = 1;
		echo "<select name=\"$name\">\n";
		//I'm failing to understand why we would offer a "none" value for dates. -SLM
		//print "<option value=0>None</a>";
		while ($i <= 12) {
			switch($i) {
			case $month:
				echo "<option value=\"$i\" selected=\"selected\" >" . utf8_encode(strftime("%b",mktime(0,0,0,$i,1,1999))) . "\n";
			break;
			default:
				echo "<option value=\"$i\">". utf8_encode(strftime("%b",mktime(0,0,0,$i,1,1999))) . "\n";
			}
			$i++;
		}
		echo "</select>";
	}

	public static function year_button ($name, $year=0) {
		$realToday = gbl::getTodayDate();
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
				print "<option value=\"0\">".JText::_('SELECT_USER')."</option>\n";

			if($show_disabled) {
				print "<optgroup label=\"".JText::_('ACTIVE_USERS')."\">";
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

	public static function multi_user_select_list($name, $selected_array=array(), $size='11') {

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
		print "<select name=\"$name\" multiple=\"multiple\" size=\"$size\">\n";
		if($show_disabled) {
			print "<optgroup label=\"".JText::_('ACTIVE_USERS')."\">";
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
				$dbquery = "SELECT ".tbl::getClientTable().".client_id, ".tbl::getClientTable().".organisation, ".
						"".tbl::getProjectTable().".client_id, ".tbl::getProjectTable().".proj_id, ".
						"".tbl::getAssignmentsTable().".proj_id, ".tbl::getAssignmentsTable().".username ".
						"FROM ".tbl::getClientTable().", ".tbl::getProjectTable().", ".tbl::getAssignmentsTable()." ".
						"WHERE ".tbl::getClientTable().".client_id=".tbl::getProjectTable().".client_id ".
						"AND ".tbl::getProjectTable().".proj_id=".tbl::getAssignmentsTable().".proj_id ";
				if ($contextUser > 0) 
					$dbquery .=	"AND ".tbl::getAssignmentsTable().".username='$contextUser' ";
					$dbquery .=	"GROUP BY ".tbl::getClientTable().".client_id ". 
								"ORDER BY organisation";
			list($qh,$num) = dbQuery($dbquery);
		}
		else {
				list($qh,$num) = dbQuery(
						"SELECT client_id, organisation ".
						"FROM ".tbl::getClientTable()." ORDER BY organisation");
		}

		print "<select name=\"client_id\" onchange=\"$onChange\" style=\"width:100%;\"";
		if ($isMultiple)
			print "multiple size=\"4\"";
		print ">\n";

		//should we show the 'Select Client' option
		if ($showSelectClient)
			print "<option value=\"0\">".JText::_('SELECT_CLIENT')."</option>\n";
		else if ($showAllClients)
			print "<option value=\"0\">".JText::_('ALL_CLIENTS')."</option>\n";

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
			$query = "SELECT uid, username, last_name, first_name, status FROM ".tbl::getUserTable(). 
				" WHERE (SELECT uid FROM " .tbl::getUserTable(). " s WHERE s.username = '$username') = supervisor " .
				" ORDER BY status DESC, last_name, first_name";
		} else {
			$query = "SELECT uid, username, last_name, first_name FROM ".tbl::getuserTable()."  " .
				" WHERE status='ACTIVE' AND (SELECT uid FROM " .tbl::getUserTable(). "  s WHERE s.username = '$username') = supervisor " .
				" ORDER BY last_name, first_name";
		}

		$drop_list_string = "<select name=\"uid\" onchange=\"submit()\" ";
		if (!empty($width))
			$drop_list_string.= "style=\"width: $width;\" ";
		if ($disabled == 'true')
			$drop_list_string .= " disabled";
		$drop_list_string .= ">\n";

		list($qh, $num) = dbQuery($query);
		if ($num > 0) {

			if($show_disabled) {
				$drop_list_string .= "<optgroup label=\"".JText::_('ACTIVE_USERS')."\">";
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
			print "<select name=\"dummy\" disabled=\"disabled\" >\n";
			print "  <option>".JText::_('SELECT_CLIENT')."</option>\n";
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
				print "<select name=\"dummy\" disabled=\"disabled\" >\n";
				print "  <option>There are no projects assigned to you</option>\n";
				print "</select>\n";
				return;
			}
			print "<select name=\"dummy\" disabled=\"disabled\" style=\"width: 100%;\">\n";
			print "  <option>".JText::_('NO_PROJECTS_FOR_CLIENT')."</option>\n";
			print "</select>\n";
			return;
		}

		print "<select name=\"proj_id\" onchange=\"$onChange\" style=\"width:100%;\"";
		if ($disabled == 'true')
			print " disabled=\"disabled\"";
		print ">\n";

		//should we show the 'Select Project' option
		if ($showSelectProject)
			print "<option value=\"0\">".JText::_('SELECT_PROJECT')."</option>\n";

		if ($showAllProjects) {
			print "<option value=\"0\"";
			if ($currentProjectId == 0)
				print " selected=\"selected\"";
			print ">".JText::_('ALL_PROJECTS')."</option>\n";
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
			print "  <option>".JText::_('SELECT_PROJECT')."</option>\n";
			print "</select>\n";
			return;
		}

		if ($contextUser == '')
			$query = "SELECT task_id, name, status FROM ".tbl::getTaskTable()." WHERE proj_id=$currentProjectId";
		else {
// 	$query = "SELECT DISTINCT tat.task_id, tt.name FROM ".tbl::getTaskAssignmentsTable()." tat, ".tbl::getTaskTable()." tt , ".tbl::getAssignmentsTable()." at WHERE ".
// 	  "at.proj_id=$projId AND tat.task_id = tt.task_id and ".
// 	  "tat.task_id > 1 AND tt.status='Started' ORDER BY tat.task_id";
			$query = "SELECT DISTINCT tat.task_id, tt.name " .
				"FROM ".tbl::getTaskAssignmentsTable()." tat, ".tbl::getTaskTable()." tt WHERE ".
				"tt.proj_id=$currentProjectId AND ".
				"tat.task_id = ".tbl::getTaskTable().".task_id AND ".
				"tat.username = '$contextUser' AND ".
				"tt.status='Started' " .
				"ORDER BY tat.name,tat.task_id";
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
				$drop_list_string .= "<optgroup label=\"".JText::_('ACTIVE_USERS')."\">";
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
	<select name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php if ($disabled=='true') echo 'disabled="disabled"'?>>
	<option value="Admin" <?php if ($selected== 'Admin') echo "selected=\"selected\"";?>>Admin</option>
	<option value="Mgr" <?php if ($selected== 'Mgr') echo "selected=\"selected\"";?>>Mgr</option>
	<option value="Basic" <?php if ($selected== 'Basic') echo "selected=\"selected\"";?>>Basic</option>
	<option value="None" <?php if ($selected== 'None') echo "selected=\"selected\"";?>>None</option>
	</select>
<?php
	}

	public static function absence_select_droplist($selected='', $disabled='false', $id) {
?>
	<select name="<?php echo $id; ?>" onchange="OnChange()" style="width: 100%;" id="<?php echo $id; ?>" <?php if ($disabled=='true') echo 'disabled="disabled"'?>>
	<option value="0" <?php if ($selected == '') echo "selected=\"selected\"";?>>&nbsp;</option>
	<option value="Holiday" <?php if ($selected== 'Holiday') echo "selected=\"selected\"";?>><?php echo JText::_('HOLIDAY'); ?></option>
	<option value="Sick" <?php if ($selected== 'Sick') echo "selected=\"selected\"";?>><?php echo JText::_('SICK'); ?></option>
	<option value="Military" <?php if ($selected== 'Military') echo "selected=\"selected\"";?>><?php echo JText::_('MILITARY'); ?></option>
	<option value="Training" <?php if ($selected== 'Training') echo "selected=\"selected\"";?>><?php echo JText::_('TRAINING'); ?></option>
	<option value="Compensation" <?php if ($selected== 'Compensation') echo "selected=\"selected\"";?>><?php echo JText::_('COMPENSATION'); ?></option>
	<option value="Other" <?php if ($selected== 'Other') echo "selected=\"selected\"";?>><?php echo JText::_('OTHER'); ?></option>
	<option value="Public" <?php if ($selected== 'Public') echo "selected=\"selected\"";?>><?php echo JText::_('PUBLIC_DAY'); ?></option>
	</select>
<?php
	}

	public static function client_select_droplist($clientId=1, $disabled=false, $info=true) {

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
				if ($return["client_id"] == $clientId)
					print " selected=\"selected\"";
				print ">$current_organisation</option>\n";
			}
			print "</select>";
			if($info) {
				print "</td><td width=\"0\">";
				print "<input type=\"button\" name=\"info\" value=\"Info\"";
				print "onclick=\"window.open('".Config::getRelativeRoot()."/client_info?client_id=$clientId',";
				print "'Client_Info',";
				print "'location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=200') />";
			}
			//print "</td></tr></table>";
		}
		else
			print "</select>";
			//print "</td></tr></table>";
	}

	public static function project_select_droplist($projId=1, $disabled='false') {

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
				if ($return["proj_id"] == $projId)
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
		global $check_in_time_hour, $check_out_time_hour,$check_in_time_min, $check_out_time_min, $destination;
	?>
<HTML>
<body bgcolor="#FFFFFF" >
<form action="<?php echo Config::getRelativeRoot(); ?>/clock_action" method="post">
<table border="1" align="center">
	<?php {
	if ($destination)
		print "<input type=\"hidden\" name=destination value=$destination />\n";
		print "<input type=\"hidden\" name=check_in_time_hour value=\"$check_in_time_hour\" />\n";
		print "<input type=\"hidden\" name=check_out_time_hour value=\"$check_out_time_hour\" />\n";
		print "<input type=\"hidden\" name=check_in_time_min value=\"$check_in_time_min\" />\n";
		print "<input type=\"hidden\" name=check_out_time_min value=\"$check_out_time_min\" />\n";
		print "<input type=\"hidden\" name=year value=\"".gbl::getYear()."\" />\n";
		print "<input type=\"hidden\" name=month value=\"".gbl::getMonth()."\" />\n";
		print "<input type=\"hidden\" name=day value=\"".gbl::getDay()."\" />\n";
		print "<input type=\"hidden\" name=proj_id value=\"".gbl::getProjId()."\" />\n";
		print "<input type=\"hidden\" name=task_id value=\"".gbl::getTaskId()."\" />\n";
		switch($action) {
		case "inout":
			print "<input type=\"hidden\" name=\"check_in_out_x\" value=\"1\" />\n";
			break;
		case "at":
			print "<input type=\"hidden\" name=\"check_out_at_x\" value=\"1\" />\n";
			break;
		case "now":
			print "<input type=\"hidden\" name=\"check_out_now_x\" value=\"1\" />\n";
		}
		print "<input type=\"hidden\" name=\"log_message_presented\" value=\"1\" />\n";
}?>

	<tr><td><?php echo JText::_('ENTER_LOG_MESSAGE'); ?></td></tr>
	<tr><td><TEXTAREA name=log_message COLS=60 ROWS=4></TEXTAREA></td></tr>
	<tr><td><input type="submit" value="Done" /></td></tr>
</table>
</FORM>
</body>
</HTML>
<?php
	}

	public static function proj_status_list($name, $status='',$size='') {
?>
	<select name="<?php echo $name; if ($size != '') echo "size=".$size; ?>">
	<option value="Pending" <?php if ($status == 'Pending') echo "selected=\"selected\"";?>><?php echo JText::_('PENDING');?> </option>
	<option value="Started" <?php if ($status == 'Started') echo "selected=\"selected\"";?>><?php echo JText::_('STARTED');?></option>
	<option value="Suspended" <?php if ($status == 'Suspended') echo "selected=\"selected\"";?>><?php echo JText::_('SUSPENDED');?></option>
	<option value="Complete" <?php if ($status == 'Complete') echo "selected=\"selected\"";?>><?php echo JText::_('COMPLETE');?></option>
	</select>
<?php
	}	
	
	public static function proj_status_list_filter($name, $status='', $onChange='submit();') {
?>
	<select name="<?php echo $name ?>" onchange="<?php echo $onChange?>" >
	<option value="All" <?php if ($status == 'All') echo "selected=\"selected\"";?>><?php echo JText::_('ALL');?></option>
	<option value="Pending" <?php if ($status == 'Pending') echo "selected=\"selected\"";?>><?php echo JText::_('PENDING');?></option>
	<option value="Started" <?php if ($status == 'Started') echo "selected=\"selected\"";?>><?php echo JText::_('STARTED');?></option>
	<option value="Suspended" <?php if ($status == 'Suspended') echo "selected=\"selected\"";?>><?php echo JText::_('SUSPENDED');?></option>
	<option value="Complete" <?php if ($status == 'Complete') echo "selected=\"selected\"";?>><?php echo JText::_('COMPLETE');?></option>
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
		$targetWindowLocation = Config::getRelativeRoot()."/error?errormsg=$message";

		if (!$from_popup)
			gotoLocation($targetWindowLocation);
		else
			Common::loadMainPageAndCloseWindow($targetWindowLocation);
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
					<?php echo JText::_('JAVASCRIPT_REQUIRED');?>
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

	public static function getClientNameFromProject($projId) {

		list($qh, $num) = dbQuery("SELECT client_id FROM ".tbl::getProjectTable()." WHERE proj_id = $projId");
		if ($num == 0)
			return 0;

		//get the first result
		$data = dbResult($qh);
		$clientId = $data["client_id"];

		list($qh, $num) = dbQuery("SELECT organisation FROM ".tbl::getClientTable()." WHERE client_id = $clientId");
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
	
	public static function getLayout() {
		list($qhq, $numq) = dbQuery("SELECT simpleTimesheetLayout FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
			$configData = dbResult($qhq);
			return $configData["simpleTimesheetLayout"];
	}
	
	public static function getVersion() {
    trigger_error('the method getVersion() is deprecated.  Replace with Config::getVersion()<br />'.ppr(debug_backtrace(),'trace',true));
    return Config::getVersion();
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
			$info["start_time"]=$data['start_time'];
			$info["start_time_str"]=$data['start_time_str'];
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
			$info["timezone"]=$data["timezone"];

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
				print "Error: time booleans are in a confused state<br />\n";
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
	
	 if($data instanceof stdClass){
    ErrorHandler::fatalError('fixStartEndDuration cannot be used with objects!');
   }
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
		} 
    else {
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
		
		header("Location: $startPage");
		exit();
	}
	
	public static function printDateSelector($mode, $startDate, $previous_date, $next_date) {
		// calculate previous and next dates
		$last_year = date("Y", $previous_date);
		$last_month = date("m", $previous_date);
		$last_day = date("d", $previous_date);
		$next_year = date("Y", $next_date);
		$next_month = date("m", $next_date);
		$next_day = date("d", $next_date);
		echo JText::_('Date').": "; 
  	echo "<span style=\"color:#00066F;\">";
    echo "<a href=\"" . Rewrite::getShortUri(). "?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;year=".$last_year."&amp;month=".$last_month."&amp;day=".$last_day."&amp;mode=".$mode. "\" >";
    echo "<img src=\"{relativeRoot}/images/cal_reverse.gif\" alt=\"prev\" /></a>";
    if ($mode == "monthly"){
    	echo utf8_encode(strftime(JText::_('DFMT_MONTH_YEAR'), $startDate));
    }
    else if ($mode = "weekly"){
    	echo utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'), $startDate));
    }
    else{ 
    	echo utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'), $startDate));
    }
                 
  	echo "<a href=\"" .Rewrite::getShortUri()."?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;year=".$next_year."&amp;month=".$next_month."&amp;day=".$next_day."&amp;mode=".$mode." \">";
  	echo "<img src=\"{relativeRoot}/images/cal_forward.gif\" alt=\"next\" /></a>";
  	if ($mode == "monthly"){
      echo "<img style=\"cursor: pointer;\" onclick=\"javascript:NewCssCal('date1', 'ddmmyyyy', 'dropdown', 'false', '24', 'false', 'MONTH')\" alt=\"\" src=\"{relativeRoot}/images/cal.gif\" />";
    }
  	else{ 
  	 echo "<img style=\"cursor: pointer;\" onclick=\"javascript:NewCssCal('date1', 'ddmmyyyy', 'arrow')\" alt=\"\" src=\"{relativeRoot}/images/cal.gif\" />";
  	}
  	echo "</span>";
  	echo "<input id=\"date1\" name=\"date1\" type=\"hidden\" value=\"" . date('d-m-Y', $startDate) ."\"/>";
	}
}
// vim:ai:ts=4:sw=4:filetype=php
?>
