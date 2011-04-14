<?php
if(!class_exists('Site'))die('Restricted Access');
////get todays values
if(!isset($realToday))
	$realToday = getdate(time());

if(class_exists('Site')){
	if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR))
		if(isset($_REQUEST["contextuid"])) 
			$_SESSION['contextUser'] = $_REQUEST['contextuid'];	
} else {
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

if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	Site::getCommandMenu()->add(new Command("<form action=\"".urlencode($_SERVER['REQUEST_URI'])."\" method=\"get\">",true,false));
	Site::getCommandMenu()->add(new Command(
		"<input type=\"hidden\" name=\"year\" value=\"".gbl::getYear()."\" />" .
		"<input type=\"hidden\" name=\"month\" value=\"".gbl::getMonth()."\" />" .
		"<input type=\"hidden\" name=\"day\" value=\"".gbl::getDay()."\" />"
	,true,false));
}	

$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/stopwatch?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;destination=".Rewrite::getShortUri()."','Stopwatch','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=220')";

/*
 Once we get acls for Users, Submit, Supervisor, and Configuration, we can uncomment this section 
 and delete everything below this section down to the context user stuff

//                 Language string			Access Rights	need relative root?  script			icon file
$menuArray[] = array('STOPWATCH',			'aclStopwatch',		0, $popup_href,					'icon_stopwatch.gif');
$menuArray[] = array('DAILY_TIMESHEET',		'aclDaily',			1, "daily?".gbl::getPost()."",				'icon_daily.gif');
$menuArray[] = array('WEEKLY_TIMESHEET',	'aclWeekly',		1, "weekly?".gbl::getPost()."",				'icon_daily.gif');
$menuArray[] = array('SIMPLE_TIMESHEET',	'aclSimple',		1, "simple?".gbl::getPost()."",				'icon_daily.gif');
$menuArray[] = array('VIEW_MONTH',			'aclMonthly',		1, "monthly?".gbl::getPost()."",				'icon_calendar.gif');
$menuArray[] = array('SUBMIT_TIMES',		'aclSubmit',		1, "submit?".gbl::getPost()."",				'icon_submit.gif');
$menuArray[] = array('ABSENCES',			'aclAbsences',		1, "absences?".gbl::getPost()."",			'icon_calendar.gif');
$menuArray[] = array('CLIENTS',				'aclClients',		1, "clients/client_maint",		'icon_clients.gif');
$menuArray[] = array('PROJECTS',			'aclProjects',		1, "projects/proj_maint",		'icon_projects.gif');
$menuArray[] = array('TASKS',				'aclTasks',			1, "tasks/task_maint",			'icon_tasks.gif');
$menuArray[] = array('PROJECT_RATES',		'aclRates',			1, "projects/project_rates",	'icon_rates.gif');
$menuArray[] = array('REPORTS',				'aclReports',		1, "reports/reports?".gbl::getPost()."",		'icon_reports.gif');
$menuArray[] = array('USERS',				'aclUsers',			1, "users/user_maint",			'icon_users.gif');
$menuArray[] = array('USER_RATES',			'aclRates',			1, "users/rate_maint",			'icon_rates.gif');
$menuArray[] = array('SUPERVISORS',			'aclSupervisor',	1, "supervisor",				'icon_supervisor.gif');
$menuArray[] = array('CONFIGURATION',		'aclConfiguration',	1, "config",					'icon_configuration.gif');
$menuArray[] = array('CHANGE_PASSWD',		'Open',				1, "changepwd",					'icon_key.gif');
$menuArray[] = array('LOGOUT',				'Open',				1, "logout?logout=true",		'icon_logout.gif');

//define the command menu
foreach($menuArray as $menuItem) {

	//Don't add the change password menu item if we're using LDAP
	//if (!Site::getAuthenticationManager()->usingLDAP())
	//if (!Site::getAuthenticationManager()->ldapCfgInfo['useLDAP']==1)
	if(($menuItem[0]=="CHANGE_PASSWD") && (Site::getAuthenticationManager()->ldapCfgInfo['useLDAP']==1))
		continue;

	if (Site::getAuthenticationManager()->hasAccess($menuItem[1])) {
		$menuStr = JText::_($menuItem[0]);

		if($menuItem[2] == 1) //do we need the relative root?
			$script = Config::getRelativeRoot()."/".$menuItem[3];
		else
			$script = $menuItem[3];

		$icon = Config::getRelativeRoot()."/images/".$menuItem[4];

		Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, $script, $icon));
	}
}
*/

$menuStr = JText::_('STOPWATCH');
if (Site::getAuthenticationManager()->hasAccess('aclStopwatch'))	
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, $popup_href, Config::getRelativeRoot()."/images/icon_stopwatch.gif"));

$menuStr = JText::_('DAILY_TIMESHEET');
if (Site::getAuthenticationManager()->hasAccess('aclDaily'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/daily?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_daily.gif"));

$menuStr = JText::_('WEEKLY_TIMESHEET');
if (Site::getAuthenticationManager()->hasAccess('aclWeekly'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/weekly?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_daily.gif"));

$menuStr = JText::_('SIMPLE_TIMESHEET');
if (Site::getAuthenticationManager()->hasAccess('aclSimple'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/simple?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_daily.gif"));

$menuStr = JText::_('VIEW_MONTH');
if (Site::getAuthenticationManager()->hasAccess('aclMonthly'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/monthly?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_calendar.gif"));

$menuStr = JText::_('SUBMIT_TIMES');
if (Site::getAuthenticationManager()->hasAccess('aclSimple'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/submit?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_submit.gif"));

$menuStr = JText::_('ABSENCES');
if (Site::getAuthenticationManager()->hasAccess('aclAbsences'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/absences?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_calendar.gif"));

$menuStr = JText::_('CLIENTS');
if (Site::getAuthenticationManager()->hasAccess('aclClients'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/clients/client_maint", Config::getRelativeRoot()."/images/icon_clients.gif"));

$menuStr = JText::_('PROJECTS');
if (Site::getAuthenticationManager()->hasAccess('aclProjects'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/projects/proj_maint", Config::getRelativeRoot()."/images/icon_projects.gif"));

$menuStr = JText::_('PROJECT_RATES');
if (Site::getAuthenticationManager()->hasAccess('aclRates'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/projects/project_user_rates", Config::getRelativeRoot()."/images/icon_rates.gif"));

$menuStr = JText::_('TASKS');
if (Site::getAuthenticationManager()->hasAccess('aclTasks'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/tasks/task_maint", Config::getRelativeRoot()."/images/icon_tasks.gif"));

$menuStr = JText::_('REPORTS');
if (Site::getAuthenticationManager()->hasAccess('aclReports'))
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/reports/reports?".gbl::getPost()."", Config::getRelativeRoot()."/images/icon_reports.gif"));

if (Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	$menuStr = JText::_('USERS');
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/users/user_maint", Config::getRelativeRoot()."/images/icon_users.gif"));

	$menuStr = JText::_('SUPERVISORS');
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/supervisor", Config::getRelativeRoot()."/images/icon_supervisor.gif"));

	$menuStr = JText::_('USER_RATES');
	if (Site::getAuthenticationManager()->hasAccess('aclRates'))
		Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/users/rate_maint", Config::getRelativeRoot()."/images/icon_rates.gif"));

	$menuStr = JText::_('CONFIGURATION');
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/config", Config::getRelativeRoot()."/images/icon_configuration.gif"));
}

$menuStr = JText::_('CHANGE_PASSWD');
//if (!Site::getAuthenticationManager()->usingLDAP())
if (!Site::getAuthenticationManager()->ldapCfgInfo['useLDAP']==1)
	Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/changepwd", Config::getRelativeRoot()."/images/icon_key.gif"));

$menuStr = JText::_('LOGOUT');
Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/logout?logout=true", Config::getRelativeRoot()."/images/icon_logout.gif"));

$menuStr = JText::_('LOGIN');
Site::getCommandMenu()->add(new IconTextCommand($menuStr, true, Config::getRelativeRoot()."/login", Config::getRelativeRoot()."/images/icon_logout.gif"));
// !!!! Delete to here once we have all the needed ACLs defined !!!!

if(Site::getAuthenticationManager()->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	if (isset($_SESSION['contextUser'])) 
		$uid=$_SESSION['contextUser'];
	else
		$uid='unknown';
	//Site::getCommandMenu()->add(new Command("&nbsp; &nbsp; &nbsp; <nobr>context user:&nbsp".user_select_droplist_string('contextuid',$uid)."</nobr></form>",true,false));
	//<NOBR> is not a valid tag.  Removed for now
	Site::getCommandMenu()->add(new Command("&nbsp; &nbsp; &nbsp; <div class=\"nobr\">".JText::_('CONTEXT_USER').":&nbsp".Common::user_select_droplist_string('contextuid',$uid)."</div></form>",true,false));
}

//disable yourself
Site::getCommandMenu()->disableSelf();

// vim:ai:ts=4:sw=4:filetype=php
?>
