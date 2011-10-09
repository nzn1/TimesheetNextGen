<?php

class Report{

function format_time($time,$time_fmt) {
	if($time > 0) {
		if($time_fmt == "decimal")
			return Common::minutes_to_hours($time);
		else 
			return Common::format_minutes($time);
	} else 
		return "-";
}

function jsPopupInfoLink($script, $variable, $info, $title = "Info") {
	print "<a href=\"javascript:void(0)\" onclick=\"window.open('" . $script .
		"?$variable=$info','$title','location=0,directories=no,status=no,scrollbar=yes," .
		"menubar=no,resizable=1,width=500,height=200')\">";
}

function make_daily_link($ymdStr, $projId, $string) {
	echo "<a href=\"".Config::getRelativeRoot()."/daily?" .  $ymdStr .  "&amp;proj_id=".$projId."\">" . 
		$string .  "</a>&nbsp;"; 
}

function printInfo($type, $data) {
	global $time_fmt;
	if($type == "projectTitle") {
		$this->jsPopupInfoLink(Config::getRelativeRoot()."/clients/client_info", "client_id", $data["client_id"], "Client_Info");
		print stripslashes($data["clientName"])."</a> / ";
		$this->jsPopupInfoLink(Config::getRelativeRoot()."/projects/proj_info", "proj_id", $data["proj_id"], "Project_Info");
		print stripslashes($data["projectTitle"])."</a>&nbsp;\n";
	} else if($type == "taskName") {
		$this->jsPopupInfoLink(Config::getRelativeRoot()."/tasks/task_info", "task_id", $data["task_id"], "Task_Info");
		print stripslashes($data["taskName"])."</a>&nbsp;\n";
	} else if($type == "duration") {
		//$this->jsPopupInfoLink(Config::getRelativeRoot()."/trans_info", "trans_num", $data["trans_num"], "Time_Entry_Info");
		print $this->format_time($data["duration"],$time_fmt);
	} else if($type == "start_stamp") {
		$dateValues = getdate($data["start_stamp"]);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];
		$formattedDate = sprintf("%04d-%02d-%02d",$dateValues["year"],$dateValues["mon"],$dateValues["mday"]); 
		$this->make_daily_link($ymdStr,0,$formattedDate); 
	} else if($type == "log") {
		if ($data['log_message']) print nl2br(stripslashes($data['log_message']));
		else print "&nbsp;";
	} else if($type == "status") {
		if ($data['status']) print stripslashes($data['status']);
		else print "&nbsp;";
	} else print "&nbsp;";
}

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







}

?>