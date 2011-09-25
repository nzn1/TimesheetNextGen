<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;

//load local vars from request/post/get
$action = $_REQUEST["action"];
$timezone = isset($_REQUEST["timezone"]) ? $_REQUEST["timezone"]: 0;
$uid = gbl::getContextUser();

if (!isset($action))
	gotoLocation($HTTP_REFERER);
elseif ($action == "settimezone") {

		// set the default timezone
		//$winter = date_create('2010-12-21', timezone_open('America/New_York'));
		//echo $winter->getOffset() . "\n";
		//date_default_timezone_set($timezone); 
		
		// now get each times record for user, and adjust start and stop times according to the timezone

		list($qh, $num) = dbQuery("SELECT timezone, trans_num, start_time, end_time FROM ".tbl::getUTCTimesTable().
			" WHERE uid = '$uid'");
		
		$group = 0;
		for ($i=0; $i<$num; $i++) {
			
			$data = dbResult($qh);
			if (empty($data['timezone'])) { // if timezone has not been updated in record, then process, else skip record
				// calculate new start and stop dates based on provided timezone
				$newStartDate = gmstrftime("%F %T",  strtotime($data['start_time']));
				// check to see if this record represents an open time record
				if ($data['end_time'] == "0000-00-00 00:00:00") $newStopDate = $data['end_time'];
				else $newStopDate = gmstrftime("%F %T", strtotime($data['end_time']));
				LogFile::write("\nstart_time ". $data["start_time"]. " end_time ". $data["end_time"]. " newstart ". strtotime($data['start_time']). " ".
				$newStartDate.  " newstop ". strtotime($data['end_time']). " ". $newStopDate );
				dbQuery("UPDATE ".tbl::getUTCTimesTable()." SET start_time = '$newStartDate', end_time = '$newStopDate', " .
				" timezone = '$timezone' ".
				"  WHERE ".tbl::getUTCTimesTable().".trans_num = '" .$data['trans_num']. "'");
				$group++;
			}
			LogFile::write("\n timezone ". $data['timezone'] ." next in loop ". $i);
		}
	}

	// redirect to the task management page (we're done)
	gotoLocation(Config::getRelativeRoot()."/timezoneupdate");

?>
