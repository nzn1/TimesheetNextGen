<?php
if(!class_exists('Site'))die('Restricted Access');
class SubmitClass{

  private $time_fmt;
  
  public function setTimeFmt($t){
    $this->time_fmt = $t;
  }
	public function __construct(){}
	
public function format_time($time) {
	if($time > 0) {
		if($this->time_fmt == "decimal")
			return Common::minutes_to_hours($time);
		else 
			return Common::format_minutes($time);
	} else 
		return "-";
}

public function jsPopupInfoLink($script, $variable, $info, $title = "Info") {
	print "<a href=\"javascript:void(0)\" ONCLICK=window.open(\"" . $script .
		"?$variable=$info\",\"$title\",\"location=0,directories=no,status=no,scrollbar=yes," .
		"menubar=no,resizable=1,width=500,height=200\")>";
}

public function make_daily_link($ymdStr, $proj_id, $string) {
	echo "<a href=\"".Config::getRelativeRoot()."/daily?" .  $ymdStr .  "&amp;proj_id=$proj_id\">" . 
		$string .  "</a>&nbsp;"; 
}

public function printInfo($type, $data) {

//	global $debug;
	
	if($type == "projectTitle") {
		self::jsPopupInfoLink(Config::getRelativeRoot()."/client_info", "client_id", $data["client_id"], "Client_Info");
		print stripslashes($data["clientName"])."</a> / ";
		self::jsPopupInfoLink(Config::getRelativeRoot()."/proj_info", "proj_id", $data["proj_id"], "Project_Info");
		print stripslashes($data["projectTitle"])."</a>&nbsp;\n";
	} else if($type == "taskName") {
		self::jsPopupInfoLink(Config::getRelativeRoot()."/task_info", "task_id", $data["task_id"], "Task_Info");
		print stripslashes($data["taskName"])."</a>&nbsp;\n";
	} else if($type == "duration") {
		//self::jsPopupInfoLink(Config::getRelativeRoot()."/trans_info", "trans_num", $data["trans_num"], "Time_Entry_Info");
		print self::format_time($data["duration"]);
	} else if($type == "start_stamp") {
		$dateValues = getdate($data["start_stamp"]);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$formattedDate = sprintf("%04d-%02d-%02d",$dateValues["year"],$dateValues["mon"],$dateValues["mday"]); 
		self::make_daily_link($ymdStr,0,$formattedDate); 
	} else if($type == "start_time") {
		$dateValues = getdate($data["start_stamp"]);
		//$hmStr = "&hour=".$dateValues["hours"] . "&mins=".$dateValues["minutes"];
		$formattedTime = sprintf("%02d:%02d",$dateValues["hours"],$dateValues["minutes"]); 
//	$debug->write("starttime start_stamp = \"" .  $data["start_stamp"]   ."\" hr =\"" .  $dateValues["hours"]   .
//		"\" min =\"" .  $dateValues["minutes"] . "\" formattedtime =\"" .  $formattedTime . "\"\n");
		print $formattedTime;
				//else print "&nbsp;";
	} else if($type == "stop_time") {
		$dateValues = getdate($data["end_stamp"]);
		//$hmStr = "&hour=".$dateValues["hours"] . "&mins=".$dateValues["minutes"];
		$formattedTime = sprintf("%02d:%02d",$dateValues["hours"],$dateValues["minutes"]); 
		print $formattedTime;
		//else print "&nbsp;";
	} else if($type == "log") {
		if ($data['log_message']) print stripslashes($data['log_message']);
		else print "&nbsp;";
	} else if($type == "status") {
		if ($data['status']) print stripslashes($data['status']);
		else print "&nbsp;";
	} else if($type == "submit") {
		if ($data['status'] == "Open") print "<input type=\"checkbox\" name=\"sub[]\" value=\"" . $data["trans_num"] . "\">";
		else print "&nbsp;";
	} else print "&nbsp;";
}


}