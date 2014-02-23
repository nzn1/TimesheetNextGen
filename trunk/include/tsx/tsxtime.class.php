<?php

class TsxTime extends stdClass{

  public $username;      
  public $start_time_str;
  public $end_time_str;
  public $start_stamp;
  public $end_stamp;
  public $duration;
  
  public $start_time;
  public $end_time;
  
  public $trans_num;
  public $proj_id;
  public $task_id;
  public $log_message;
  public $subStatus;
  
  public $first_name;
  public $last_name;
  public $projectTitle;
  public $taskName;
  public $clientName;
  public $client_id;
  
  public function __construct(stdClass $timeObj=null){
    
    if($timeObj == null)return 0;
    
    $this->username = $timeObj->username;
    $this->start_time_str = $timeObj->start_time_str;
    $this->end_time_str = $timeObj->end_time_str;
    $this->start_stamp = $timeObj->start_stamp;
    $this->end_stamp = $timeObj->end_stamp;
    $this->duration = $timeObj->duration;
    $this->trans_num = $timeObj->trans_num;
    $this->proj_id = $timeObj->proj_id;
    $this->task_id = $timeObj->task_id;
    $this->log_message = $timeObj->log_message;
    $this->subStatus = $timeObj->subStatus;
    $this->first_name = $timeObj->first_name;
    $this->last_name = $timeObj->last_name;
    $this->projectTitle = $timeObj->projectTitle;
    $this->taskName = $timeObj->taskName;
    $this->clientName = $timeObj->clientName;
    $this->client_id = $timeObj->client_id;
    
    $this->fixStartEndDuration();
  } 
  
  
  
  private function fixStartEndDuration() {
	
		//Due to a bug in mysql with converting to unix timestamp from the string,
		//we are going to use php's strtotime to make the timestamp from the string.
		//the problem has something to do with timezones.
		$this->start_time = strtotime($this->start_time_str);

		//If we've got a duration, use that to determine/override the end_stamp
		//If not, figure out the duration
		if(isset($this->duration) && ($this->duration > 0) ) {
			$new_end_stamp = Common::get_end_date_time($this->start_stamp, $this->duration);			
			
			if($this->end_stamp != $new_end_stamp) {
				$old_end_stamp = $this->end_stamp;
				$this->end_stamp = $new_end_stamp;
				//even if stamps are different, it may result in same string being stored in db
				//see: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php
				//and read the comments by Miles Nordin and Joakim Nygard
				if(strftime("%Y-%m-%d %H:%M:%S", $old_end_stamp) != strftime("%Y-%m-%d %H:%M:%S", $new_end_stamp))
					$this->fix_entry_endstamp();
			}
		} 
    else {
			if($this->end_time_str != '0000-00-00 00:00:00') {
				$this->end_stamp = strtotime($this->end_time_str);
				$this->duration = Common::get_duration($this->start_stamp, $this->end_stamp, 1);
				$this->fix_entry_duration();
			} else {
				//have start time, but no end time or duration, return 0 (false)
				return 0;
			}
		}
		return 1;
	} 
	
	
	private function fix_entry_duration() {
		//print "  fix_entry_duration<br />\n";

		$duration = $this->duration;
		$trans_num = $this->trans_num;
		tryDbQuery("UPDATE ".tbl::getTimesTable()." set duration=$duration WHERE trans_num=$trans_num");
	}

	private function fix_entry_endstamp() {
		//print "  fix_entry_endstamp ". $entry["trans_num"].") ".strftime("%Y-%m-%d %H:%M:%S", $entry["start_stamp"])." - ".strftime("%Y-%m-%d %H:%M:%S", $entry["end_stamp"])."<br />\n";

		$etsStr = strftime("%Y-%m-%d %H:%M:%S", $this->end_stamp);
		$trans_num = $this->trans_num;
		tryDbQuery("UPDATE ".tbl::getTimesTable()." set end_time=\"$etsStr\" WHERE trans_num=$trans_num");
	}
	
	public function setBooleans($curStamp){
	
		$tomorrowStamp = strtotime(date("d M Y H:i:s",$curStamp) . " +1 day");
        			//set some booleans
		$this->startsToday = (($this->start_stamp >= $curStamp ) && ( $this->start_stamp < $tomorrowStamp ));
		$this->endsToday =   (($this->end_stamp > $curStamp ) && ($this->end_stamp <= $tomorrowStamp));
		$this->startsBeforeToday = ($this->start_stamp < $curStamp);
		$this->endsAfterToday = ($this->end_stamp > $tomorrowStamp);
  }


  public function getFormattedStartTime(){
					if (Site::Config()->get('timeformat') == "12") {
						return date("g:iA",$this->start_stamp);
					} else {
						return date("G:i",$this->start_stamp);
					}
  }

  public function getFormattedEndTime(){
					if (Site::Config()->get('timeformat') == "12") {
						return date("g:iA",$this->end_stamp);
					} else {
						return date("G:i",$this->end_stamp);
					}
  }  
  
}





?>