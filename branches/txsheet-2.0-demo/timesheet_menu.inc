<?php
if(!class_exists('Site'))die('Restricted Access');
////get todays values
if(!isset($realToday))
	$realToday = getdate(time());
//
////load local vars from superglobals
$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: $realToday["year"];
$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: $realToday["mon"];
$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: $realToday["mday"];
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;

if(empty($post)) $post="year=$year&amp;month=$month&amp;day=$day&amp;proj_id=$proj_id&amp;task_id=$task_id&amp;client_id=$client_id";

if(class_exists('Site')){
	if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR))
		if(isset($_REQUEST["contextuid"]))
			$_SESSION['contextUser'] = $_REQUEST['contextuid'];	
}
else{
	if($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR))
		if(isset($_REQUEST["contextuid"]))
			$_SESSION['contextUser'] = $_REQUEST['contextuid'];
}

// View mode (monthly, weekly, all)
if (isset($_REQUEST['mode']))
	$mode = $_REQUEST['mode'];
else
	$mode = "all";
if (!($mode == "all" || $mode == "monthly" || $mode == "weekly"))
	$mode = "all";

if(class_exists('Site')){
	if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		Site::getCommandMenu()->add(new Command("<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"get\">",true,false));
		Site::getCommandMenu()->add(new Command(
			"<input type=\"hidden\" name=\"year\" value=\"$year\" />" .
			"<input type=\"hidden\" name=\"month\" value=\"$month\" />" .
			"<input type=\"hidden\" name=\"day\" value=\"$day\" />"
		,true,false));
	}	
}
	
else {
	if($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		$commandMenu->add(new Command("<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\">",true,false));
		$commandMenu->add(new Command(
			"<input type=\"hidden\" name=\"year\" value=\"$year\" />" .
			"<input type=\"hidden\" name=\"month\" value=\"$month\" />" .
			"<input type=\"hidden\" name=\"day\" value=\"$day\" />"
		,true,false));
	}
}
//define the command menu
if(class_exists('Site')){
	$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/stopwatch?client_id=$client_id&amp;proj_id=$proj_id&amp;task_id=$task_id&amp;destination=".Rewrite::getShortUri()."','Stopwatch','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=220')";
}
else $popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/stopwatch?client_id=$client_id&proj_id=$proj_id&task_id=$task_id&destination=".urlencode($_SERVER['REQUEST_URI'])."\",\"Stopwatch\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=220\") dummy=\"";

if(class_exists('Site')){
	if (Site::getAuthenticationManager()->hasAccess('aclStopwatch'))	
		Site::getCommandMenu()->add(new IconTextCommand("Stopwatch", true, $popup_href, Config::getRelativeRoot()."/images/icon_stopwatch.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclDaily'))
		Site::getCommandMenu()->add(new IconTextCommand("Daily Timesheet", true, Config::getRelativeRoot()."/daily?$post", Config::getRelativeRoot()."/images/icon_daily.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclWeekly'))
		Site::getCommandMenu()->add(new IconTextCommand("Weekly Timesheet", true, Config::getRelativeRoot()."/weekly?$post", Config::getRelativeRoot()."/images/icon_daily.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclSimple'))
		Site::getCommandMenu()->add(new IconTextCommand("Simple Timesheet", true, Config::getRelativeRoot()."/simple?$post", Config::getRelativeRoot()."/images/icon_daily.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclSimple'))
		Site::getCommandMenu()->add(new IconTextCommand("Submit Times", true, Config::getRelativeRoot()."/submit?$post", Config::getRelativeRoot()."/images/icon_daily.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclMonthly'))
		Site::getCommandMenu()->add(new IconTextCommand("View Month", true, Config::getRelativeRoot()."/monthly?$post", Config::getRelativeRoot()."/images/icon_calendar.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclAbsences'))
		Site::getCommandMenu()->add(new IconTextCommand("Absences", true, Config::getRelativeRoot()."/absences?$post", Config::getRelativeRoot()."/images/icon_calendar.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclClients'))
		Site::getCommandMenu()->add(new IconTextCommand("Clients", true, Config::getRelativeRoot()."/client_maint", Config::getRelativeRoot()."/images/icon_clients.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclProjects'))
		Site::getCommandMenu()->add(new IconTextCommand("Projects", true, Config::getRelativeRoot()."/proj_maint", Config::getRelativeRoot()."/images/icon_projects.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclRates'))
		Site::getCommandMenu()->add(new IconTextCommand("Project Rates", true, Config::getRelativeRoot()."/project_user_rates", Config::getRelativeRoot()."/images/icon_rates.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclTasks'))
		Site::getCommandMenu()->add(new IconTextCommand("Tasks", true, Config::getRelativeRoot()."/task_maint", Config::getRelativeRoot()."/images/icon_tasks.gif"));
	if (Site::getAuthenticationManager()->hasAccess('aclReports'))
		Site::getCommandMenu()->add(new IconTextCommand("Reports", true, Config::getRelativeRoot()."/reports/reports?$post", Config::getRelativeRoot()."/images/icon_reports.gif"));
	if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		Site::getCommandMenu()->add(new IconTextCommand("Users", true, Config::getRelativeRoot()."/user_maint", Config::getRelativeRoot()."/images/icon_users.gif"));
		Site::getCommandMenu()->add(new IconTextCommand("Supervisor", true, Config::getRelativeRoot()."/supervisor", Config::getRelativeRoot()."/images/icon_users.gif"));
		if (Site::getAuthenticationManager()->hasAccess('aclRates'))
			Site::getCommandMenu()->add(new IconTextCommand("User Rates", true, Config::getRelativeRoot()."/rate_maint", Config::getRelativeRoot()."/images/icon_rates.gif"));
		Site::getCommandMenu()->add(new IconTextCommand("Configuration", true, Config::getRelativeRoot()."/config", Config::getRelativeRoot()."/images/icon_configuration.gif"));
	}
	if (!Site::getAuthenticationManager()->usingLDAP())
		Site::getCommandMenu()->add(new IconTextCommand("Change Password", true, Config::getRelativeRoot()."/changepwd", Config::getRelativeRoot()."/images/icon_key.gif"));
	Site::getCommandMenu()->add(new IconTextCommand("Logout", true, Config::getRelativeRoot()."/logout?logout=true", Config::getRelativeRoot()."/images/icon_logout.gif"));
	if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		if (isset($_SESSION['contextUser'])) 
			$uid=$_SESSION['contextUser'];
		else
			$uid='unknown';
		//Site::getCommandMenu()->add(new Command("&nbsp; &nbsp; &nbsp; <nobr>context user:&nbsp".user_select_droplist_string('contextuid',$uid)."</nobr></form>",true,false));
		//<NOBR> is not a valid tag.  Removed for now
		Site::getCommandMenu()->add(new Command("&nbsp; &nbsp; &nbsp; <div class=\"nobr\">context user:&nbsp".Common::user_select_droplist_string('contextuid',$uid)."</div></form>",true,false));
	}
	
	//disable yourself
	Site::getCommandMenu()->disableSelf();

}

else{
	if ($authenticationManager->hasAccess('aclStopwatch'))
		$commandMenu->add(new IconTextCommand("Stopwatch", true, $popup_href, "images/icon_stopwatch.gif"));
	if ($authenticationManager->hasAccess('aclDaily'))
		$commandMenu->add(new IconTextCommand("Daily Timesheet", true, "daily?$post", "images/icon_daily.gif"));
	if ($authenticationManager->hasAccess('aclWeekly'))
		$commandMenu->add(new IconTextCommand("Weekly Timesheet", true, "weekly?$post", "images/icon_daily.gif"));
	if ($authenticationManager->hasAccess('aclSimple'))
		$commandMenu->add(new IconTextCommand("Simple Timesheet", true, "simple?$post", "images/icon_daily.gif"));
	if ($authenticationManager->hasAccess('aclMonthly'))
		$commandMenu->add(new IconTextCommand("View Month", true, "monthly?$post", "images/icon_calendar.gif"));
	if ($authenticationManager->hasAccess('aclAbsences'))
		$commandMenu->add(new IconTextCommand("Absences", true, "absences?$post", "images/icon_calendar.gif"));
	if ($authenticationManager->hasAccess('aclClients'))
		$commandMenu->add(new IconTextCommand("Clients", true, "client_maint", "images/icon_clients.gif"));
	if ($authenticationManager->hasAccess('aclProjects'))
		$commandMenu->add(new IconTextCommand("Projects", true, "proj_maint", "images/icon_projects.gif"));
	if ($authenticationManager->hasAccess('aclRates'))
		$commandMenu->add(new IconTextCommand("Project Rates", true, "project_user_rates", "images/icon_rates.gif"));
	if ($authenticationManager->hasAccess('aclTasks'))
		$commandMenu->add(new IconTextCommand("Tasks", true, "task_maint", "images/icon_tasks.gif"));
	if ($authenticationManager->hasAccess('aclReports'))
		$commandMenu->add(new IconTextCommand("Reports", true, "reports?$post", "images/icon_reports.gif"));
	if ($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		$commandMenu->add(new IconTextCommand("Users", true, "user_maint", "images/icon_users.gif"));
		if ($authenticationManager->hasAccess('aclRates'))
			$commandMenu->add(new IconTextCommand("User Rates", true, "rate_maint", "images/icon_rates.gif"));
		$commandMenu->add(new IconTextCommand("Configuration", true, "config", "images/icon_configuration.gif"));
	}
	if (!$authenticationManager->usingLDAP())
		$commandMenu->add(new IconTextCommand("Change Password", true, "changepwd", "images/icon_key.gif"));
	$commandMenu->add(new IconTextCommand("Logout", true, "logout?logout=true", "images/icon_logout.gif"));
	if($authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
		if (isset($_SESSION['contextUser'])) 
			$uid=$_SESSION['contextUser'];
		else
			$uid='unknown';
		//$commandMenu->add(new Command("&nbsp; &nbsp; &nbsp; <nobr>context user:&nbsp".user_select_droplist_string('contextuid',$uid)."</nobr></form>",true,false));
		//<NOBR> is not a valid tag.  Removed for now
		if(class_exists('Site')) $commandMenu->add(new Command("&nbsp; &nbsp; &nbsp; context user:&nbsp".Common::user_select_droplist_string('contextuid',$uid)."</form>",true,false));
		else $commandMenu->add(new Command("&nbsp; &nbsp; &nbsp; context user:&nbsp".user_select_droplist_string('contextuid',$uid)."</form>",true,false));
	}
	
	//disable yourself
	$commandMenu->disableSelf();	
}
// vim:ai:ts=4:sw=4:filetype=php
?>
