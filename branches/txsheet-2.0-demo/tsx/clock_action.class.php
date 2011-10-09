<?php

class ClockAction{

	private $simpleDebug;
	private $origin;
	private $destination;
	private $fromPopupWindow;
	private $clockOnOff;
	private $clockOnTimeHour;
	private $clockOnTimeMin;
	private $clockOffTimeHour;
	private $clockOffTimeMin;
	private $logMessage;
	private $logMessagePresented;
	private $clockOnCheck;
	private $clockOffCheck;
	private $clockOnRadio;
	private $clockOffRadio;
	private $location;
	private $onStamp;
	private $offStamp;
	
	public function getOnStamp(){
    return $this->onStamp;
  }
  public function setOnStamp($s){
    $this->onStamp = $s;
  }
	public function getOffStamp(){
    return $this->offStamp;
  }
  public function setOffStamp($s){
    $this->offStamp = $s;
  }	
	public function getLocation(){
    return $this->location;
  }
  public function setLocation($s){
    $this->location = $s;
  }
	public function getClockOffRadio(){
		return $this->clockOffRadio;
	}
	public function getClockOnRadio(){
		return $this->clockOnRadio;
	}
	public function getClockOffCheck(){
		return $this->clockOffCheck;
	}
	public function getClockOnCheck(){
		return $this->clockOnCheck;
	}
	public function getLogMessagePresented(){
		return $this->logMessagePresented;
	}

	public function getClockOffTimeMin(){
		return $this->clockOffTimeMin;
	}
	public function setClockOffTimeMin($s){
    $this->clockOffTimeMin = $s;
  }
	public function getClockOffTimeHour(){
		return $this->clockOffTimeHour;
	}
	public function setClockOffTimeHour($s){
    $this->clockOffTimeHour = $s;
  }
	public function getClockOnTimeMin(){
		return $this->clockOnTimeMin;
	}
	public function setClockOnTimeMin($s){
    $this->clockOnTimeMin = $s;
  }
	public function getClockOnTimeHour(){
		return $this->clockOnTimeHour;
	}
	public function setClockOnTimeHour($s){
    $this->clockOnTimeHour = $s;
  }
	public function getClockOnOff(){
		return $this->clockOnOff;
	}
	public function setClockOnOff($s){
    $this->clockOnOff = $s;
  }
	public function getFromPopupWindow(){
		return $this->fromPopupWindow;
	}
	public function getDestination(){
		return $this->destination;
	}
	public function getSimpleDebug(){
		return $this->simpleDebug;
	}
	public function getOrigin(){
		return $this->origin;
	}
	public function __construct(){

		$this->simpleDebug = true;
		//load local vars from request/post/get
		//$month = isset($_POST['month']) ? $_POST['month'] : false;
		//$day = isset($_POST['day']) ? $_POST['day'] : false;
		//$year = isset($_POST['year']) ? $_POST['year'] : false;
		//$client_id = $_POST['client_id'];
		//$proj_id = $_POST['proj_id'];
		//$task_id = $_POST['task_id'];
    
    if(isset($_REQUEST['clientSelect'])){
      gbl::setClientId($_REQUEST['clientSelect']);
    }
    if(isset($_REQUEST['projectSelect'])){
      gbl::setProjId($_REQUEST['projectSelect']);
    }
    if(isset($_REQUEST['taskSelect'])){
      gbl::setTaskId($_REQUEST['taskSelect']);
    }
		$this->origin = isset($_REQUEST['origin']) ? $_REQUEST['origin'] : 'daily';
		$this->destination = isset($_REQUEST['destination']) ? $_REQUEST['destination'] : 'daily';
		$this->fromPopupWindow = isset($_REQUEST['fromPopupWindow']) ? $_REQUEST['fromPopupWindow']: false;
		$this->clockOnOff = isset($_REQUEST['clockonoff']) ? $_REQUEST['clockonoff']: "";
		$this->clockOnTimeHour = isset($_REQUEST['clock_on_time_hour']) ? $_REQUEST['clock_on_time_hour']: 0;
		$this->clockOnTimeMin = isset($_REQUEST['clock_on_time_min']) ? $_REQUEST['clock_on_time_min']: 0;
		$this->clockOffTimeHour = isset($_REQUEST['clock_off_time_hour']) ? $_REQUEST['clock_off_time_hour']: 0;
		$this->clockOffTimeMin = isset($_REQUEST['clock_off_time_min']) ? $_REQUEST['clock_off_time_min']: 0;
		$this->logMessage = isset($_REQUEST['log_message']) ? $_REQUEST['log_message']: "";
		$this->logMessagePresented = isset($_REQUEST['log_message_presented']) ? $_REQUEST['log_message_presented']: false;
		$this->clockOnCheck = isset($_REQUEST['clock_on_check']) ? $_REQUEST['clock_on_check']: "";
		$this->clockOffCheck = isset($_REQUEST['clock_off_check']) ? $_REQUEST['clock_off_check']: "";
		$this->clockOnRadio = isset($_REQUEST['clock_on_radio']) ? $_REQUEST['clock_on_radio']: "";
		$this->clockOffRadio = isset($_REQUEST['clock_off_radio']) ? $_REQUEST['clock_off_radio']: "";
		
		if($this->fromPopupWindow == 'false')$this->fromPopupWindow = false;
	}
	//This is functionally the end of this file...

	public function getLogMessage() {
		
		if ($this->logMessagePresented == false) {
			$targetWindowLocation = Config::getRelativeRoot()."/log_message".
  					"?origin=".$this->origin."&destination=".$this->destination.
  					"&clock_on_time_hour=".$this->clockOnTimeHour.
  					"&clock_off_time_hour=".$this->clockOffTimeHour.
  					"&clock_on_time_min=".$this->clockOnTimeMin.
  					"&clock_off_time_min=".$this->clockOffTimeMin.
  					"&year=".gbl::getYear().
  					"&month=".gbl::getMonth().
  					"&day=".gbl::getDay().
  					"&client_id=".gbl::getClientId().
  					"&proj_id=".gbl::getProjId().
  					"&task_id=".gbl::getTaskId().
  					"&clockonoff=".$this->clockOnOff;

			if ($this->fromPopupWindow == "1" || $this->fromPopupWindow == "true") {
				//close this popup window and load the log message page in the main window.
				Common::loadMainPageAndCloseWindow($targetWindowLocation);
			}
			else {
				gotoLocation($targetWindowLocation);
				exit;
			}
		}
	}

	public function clockon() {
		
		if (empty($this->location)){
		  Common::errorPage("failed sanity check, location empty");
		}

		//check that we are not already clocked on
		$querystring = "SELECT timest.start_time, tt.name FROM ".
  			"".tbl::getTimesTable()." timest, ".tbl::getTaskTable()." tt WHERE ".
  			"username='".gbl::getContextUser()."' AND ".
  			"end_time='0000-00-00 00:00:00' AND ".
		//"start_time>='".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()."' AND ".
		//"start_time<='".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()." 23:59:59' AND ".
  			"timest.task_id=".gbl::getTaskId()." AND ".
  			"timest.proj_id=".gbl::getProjId()." AND ".
  			"tt.task_id=".gbl::getTaskId()." AND ".
  			"tt.proj_id=".gbl::getProjId();

        ppr($querystring);
		list($qh,$num) = dbQuery($querystring);
		$resultset = dbResult($qh);

		if ($num > 0)
		Common::errorPage("You have already clocked on for task '".$resultset['name']."' at ".$resultset['start_time'].".  Please clock off first.", $this->fromPopupWindow);

		$onStr = strftime("%Y-%m-%d %H:%M:%S", $this->onStamp);

		//now insert the record for this clock on
		$querystring = "INSERT INTO ".tbl::getTimesTable()." (username, start_time, proj_id,task_id) ".
  			"VALUES ('".gbl::getContextUser()."','$onStr', ".gbl::getProjId().", ".gbl::getTaskId().")";
		list($qh,$num) = dbQuery($querystring);

		//now output an ok page, the redirect back
		?>
		<html>
      <head>
		    <script language="javascript">
		      function alertAndLoad(){
            alert('Clocked on successfully');
            <?php
		        if ($this->fromPopupWindow){		  
		          echo "window.opener.location.reload();\n";
		        }
		        ?>
		        window.location="<?php echo $this->location;?>";
		      }
		    </script>
      </head>
		
      <?php
        if(Debug::getLocation()){
        ?>
          <body><a href="javascript:alertAndLoad();" />javascript:alertAndLoad()</a></body>
        <?php
        ppr($this);
        }
        else{
        ?>
        <body onload="javascript:alertAndLoad();"></body>
        <?php
        
        }
      ?>
      
	 </html>
	 <?php
		exit;
	}

	public function clockoff() {

		$offStr = strftime("%Y-%m-%d %H:%M:%S", $this->offStamp);

		//check that we are actually clocked on
		$querystring = "SELECT start_time, start_time < '$offStr' AS valid FROM ".tbl::getTimesTable()." WHERE ".
  			"username='".gbl::getContextUser()."' AND ".
  			"end_time='0000-00-00 00:00:00' AND ".
		//"start_time >= '".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()."' AND ".
		//"start_time <= '".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()." 23:59:59' AND ".
  			"proj_id=".gbl::getProjId()." AND ".
  			"task_id=".gbl::getTaskId();

		list($qh,$num) = dbQuery($querystring);
		$data = dbResult($qh);

		if ($num == 0)
		Common::errorPage("You are not currently clocked on. You must clock on before you can clock off. If you have just clocked on please wait at least one minute before clocking off", $this->fromPopupWindow);
		//also check that the clockoff time is after the clockon time
		else if ($data["valid"] == 0)
		Common::errorPage("You must clock off <i>after</i> you clock on.", $this->fromPopupWindow);

		$this->onStamp = strtotime($data["start_time"]);
		$duration = ($this->offStamp - $this->onStamp)/60;

		//do we need to present the user with a log message screen?
		if ($this->logMessagePresented == false)
		$this->getLogMessage();

		//now insert the record for this clock off
		$this->logMessage = addslashes($this->logMessage);
		$querystring = "UPDATE ".tbl::getTimesTable()." SET log_message='".$this->logMessage."', end_time='$offStr', duration='$duration' WHERE ".
  			"username='".gbl::getContextUser()."' AND ".
  			"proj_id=".gbl::getProjId()." AND ".
  			"end_time=0 AND ".
		//"start_time >= '".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()."' AND ".
		//"start_time < '".gbl::getYear()."-".gbl::getMonth()."-".gbl::getDay()." 23:59:59' AND ".
  			"task_id=".gbl::getTaskId();
		list($qh,$num) = dbQuery($querystring);
		gotoLocation($this->location);
	}

	public function clockonandoff() {
		
		if($this->simpleDebug) {
			LogFile::write("onStamp = ".$this->onStamp);
			LogFile::write("offStamp = ".$this->offStamp);
		}

		//make sure we're not clocking on after clocking off
		if ($this->offStamp < $this->onStamp){
		  Common::errorPage("You cannot have your clock on time (".$this->clockOnTimeHour.":".$this->clockOnTimeMin.") ".
  			"later than your clock off time (".$this->clockOffTimeHour.":".$this->clockOffTimeMin.")", $this->fromPopupWindow);
  	}
		else if ($this->onStamp == $this->offStamp){
		  //errorPage("You cannot clock on and off with the same time. (".$this->clockOnTimeHour.":".$this->clockOnTimeMin." = ".$this->clockOffTimeHour.":".$this->clockOffTimeMin.")", $this->fromPopupWindow);
		  Common::errorPage("You cannot clock on and off with the same time. (".$this->onStamp." == ".$this->offStamp.")", $this->fromPopupWindow);
    }
		if ($this->logMessagePresented == false){
		  $this->getLogMessage();
		}

		$duration=($this->offStamp - $this->onStamp)/60; //get duration in minutes
		$onStr = strftime("%Y-%m-%d %H:%M:%S", $this->onStamp);
		$offStr = strftime("%Y-%m-%d %H:%M:%S", $this->offStamp);
		 
		$this->logMessage = addslashes($this->logMessage);
		LogFile::write("\nclock_action. start time: ". $onStr. " stop time: ". $offStr. "\n");
		$q = "INSERT INTO ".tbl::getTimesTable()." (username, start_time, end_time, duration, proj_id, task_id, log_message) ".
  			"VALUES ('".gbl::getContextUser()."','$onStr', '$offStr', '$duration', " .
  			"".gbl::getProjId().", ".gbl::getTaskId().", '".$this->logMessage."')";
		
    //list($qh,$num) = dbQuery($queryString);
    if(debug::getSqlStatement()==1)ppr($q,'SQL');
		$retval['status'] = Database::getInstance()->query($q);
		$retval['id'] = mysql_insert_id(Database::getInstance()->getConnection());

		if($retval['status'] == false && debug::getSqlError()==1){
			Debug::ppr(mysql_error(),'sqlError');
		}

		gotoLocation($this->location);
		exit;
	}

}
?>

