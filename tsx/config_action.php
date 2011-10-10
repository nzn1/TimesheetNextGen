<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

//if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;


//if ((!Site::getSession()->isLoggedIn()) || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
//	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=Administrator");
//	exit;
//}

//load local vars from request/post/get
$action = $_REQUEST["action"];
$headerhtml = isset($_REQUEST["headerhtml"]) ? $_REQUEST["headerhtml"]: "";
$bodyhtml = isset($_REQUEST["bodyhtml"]) ? $_REQUEST["bodyhtml"]: "";
$footerhtml = isset($_REQUEST["footerhtml"]) ? $_REQUEST["footerhtml"]: "";
$errorhtml = isset($_REQUEST["errorhtml"]) ? $_REQUEST["errorhtml"]: "";
$bannerhtml = isset($_REQUEST["bannerhtml"]) ? $_REQUEST["bannerhtml"]: "";
$tablehtml = isset($_REQUEST["tablehtml"]) ? $_REQUEST["tablehtml"]: "";
$locale = isset($_REQUEST["locale"]) ? $_REQUEST["locale"]: "";
$timezone = isset($_REQUEST["timezone"]) ? $_REQUEST["timezone"]: "";
$timeformat= isset($_REQUEST["timeformat"]) ? $_REQUEST["timeformat"]: "";
$projectItemsPerPage = isset($_REQUEST["projectItemsPerPage"]) ? $_REQUEST["projectItemsPerPage"]: "";
$taskItemsPerPage = isset($_REQUEST["taskItemsPerPage"]) ? $_REQUEST["taskItemsPerPage"]: "";
$headerReset = isset($_REQUEST["headerReset"]) ? $_REQUEST["headerReset"]: false;
$bodyReset = isset($_REQUEST["bodyReset"]) ? $_REQUEST["bodyReset"]: false;
$footerReset = isset($_REQUEST["footerReset"]) ? $_REQUEST["footerReset"]: false;
$errorReset = isset($_REQUEST["errorReset"]) ? $_REQUEST["errorReset"]: false;
$bannerReset = isset($_REQUEST["bannerReset"]) ? $_REQUEST["bannerReset"]: false;
$tableReset = isset($_REQUEST["tableReset"]) ? $_REQUEST["tableReset"]: false;
$localeReset = isset($_REQUEST["localeReset"]) ? $_REQUEST["localeReset"]: false;
$aclReset = isset($_REQUEST["aclReset"]) ? $_REQUEST["aclReset"]: false;
$timezoneReset = isset($_REQUEST["timezoneReset"]) ? $_REQUEST["timezoneReset"]: false;
$timeformatReset = isset($_REQUEST["timeformatReset"]) ? $_REQUEST["timeformatReset"]: false;
$projectItemsPerPageReset = isset($_REQUEST["projectItemsPerPageReset"]) ? $_REQUEST["projectItemsPerPageReset"]: false;
$taskItemsPerPageReset = isset($_REQUEST["taskItemsPerPageReset"]) ? $_REQUEST["taskItemsPerPageReset"]: false;
$useLDAP = isset($_REQUEST["useLDAP"]) ? $_REQUEST["useLDAP"]: false;
$LDAPFallback = isset( $_REQUEST["LDAPFallback"] ) ? $_REQUEST["LDAPFallback"]: false;
$LDAPReferrals = isset( $_REQUEST["LDAPReferrals"] ) ? $_REQUEST["LDAPReferrals"]: false;
$LDAPScheme = $_REQUEST["LDAPScheme"];
$LDAPHost = $_REQUEST["LDAPHost"];
$LDAPPort = $_REQUEST["LDAPPort"];
$LDAPBaseDN = $_REQUEST["LDAPBaseDN"];
$LDAPUsernameAttribute = $_REQUEST["LDAPUsernameAttribute"];
$LDAPSearchScope = $_REQUEST["LDAPSearchScope"];
$LDAPFilter = $_REQUEST["LDAPFilter"];
$LDAPProtocolVersion = $_REQUEST["LDAPProtocolVersion"];
$LDAPBindByUser = isset($_REQUEST["LDAPBindByUser"]) ? $_REQUEST["LDAPBindByUser"]: false;
$LDAPBindUsername = $_REQUEST["LDAPBindUsername"];
$LDAPBindPassword = $_REQUEST["LDAPBindPassword"];
$weekstartday = isset($_REQUEST["weekstartday"]) ? $_REQUEST["weekstartday"]: 0;
$weekStartDayReset = isset($_REQUEST["weekStartDayReset"]) ? $_REQUEST["weekStartDayReset"]: false;
$aclStopwatch = $_REQUEST["aclStopwatch"];
$aclDaily = $_REQUEST["aclDaily"];
$aclWeekly = $_REQUEST["aclWeekly"];
$aclMonthly = $_REQUEST["aclMonthly"];
$aclSimple = $_REQUEST["aclSimple"];
$aclClients = $_REQUEST["aclClients"];
$aclProjects = $_REQUEST["aclProjects"];
$aclTasks = $_REQUEST["aclTasks"];
$aclReports = $_REQUEST["aclReports"];
$aclRates = $_REQUEST["aclRates"];
$aclAbsences = $_REQUEST["aclAbsences"];
$aclExpenses = $_REQUEST["aclExpenses"];
$aclECategories = $_REQUEST["aclECategories"];
$aclTApproval = $_REQUEST["aclTApproval"];
$simpleTimesheetLayout = isset($_REQUEST["simpleTimesheetLayout"]) ? $_REQUEST["simpleTimesheetLayout"]: false;
$startPage = $_REQUEST["startPage"];

if (!isset($action)) {
	gotoLocation($HTTP_REFERER);
}
elseif ($action == "edit") {
	$headerhtml = mysql_real_escape_string(trim($headerhtml));
	$bodyhtml = mysql_real_escape_string(trim($bodyhtml));
	$footerhtml = mysql_real_escape_string(trim($footerhtml));
	$errorhtml = mysql_real_escape_string(trim($errorhtml));
	$bannerhtml = mysql_real_escape_string(trim($bannerhtml));
	$tablehtml = mysql_real_escape_string(trim($tablehtml));
	$locale = mysql_real_escape_string(trim($locale));
	$timezone = mysql_real_escape_string(trim($timezone));
	$projectItemsPerPage = mysql_real_escape_string(trim($projectItemsPerPage));
	$taskItemsPerPage = mysql_real_escape_string(trim($taskItemsPerPage));

	// now change values in new configuration table

	Config::setHeaderHtml($headerhtml);
	Config::setBodyHtml($bodyhtml);
	Config::setFooterHtml($footerhtml);
	Config::setErrorHtml($errorhtml);
	Config::setBannerHtml($bannerhtml);
	Config::setTableHtml($tablehtml);
	Config::setLocale($locale);
	Config::setTimeZone($timezone);
	Config::setTimeFormat($timeformat);
	Config::setWeekStartDay($weekstartday);
	Config::setProjectItemsPerPage($projectItemsPerPage);
	Config::setTaskItemsPerPage($taskItemsPerPage);
	Config::setUseLDAP($useLDAP);
	Config::setLDAPScheme($LDAPScheme);
	Config::setLDAPHost($LDAPHost);
	Config::setLDAPPort($LDAPPort);
	Config::setLDAPBaseDN($LDAPBaseDN);
	Config::setLDAPUsernameAttribute($LDAPUsernameAttribute);
	Config::setLDAPSearchScope($LDAPSearchScope);
	Config::setLDAPFilter($LDAPFilter);
	Config::setLDAPProtocolVersion($LDAPProtocolVersion);
	Config::setLDAPBindUsername($LDAPBindUsername);
	Config::setLDAPBindPassword($LDAPBindPassword);
	Config::setLDAPBindByUser($LDAPBindByUser);
	Config::setLDAPReferrals($LDAPReferrals);
	Config::setLDAPFallback($LDAPFallback);
	Config::setAclStopwatch($aclStopwatch);
	Config::setAclDaily($aclDaily);
	Config::setAclWeekly($aclWeekly);
	Config::setAclMonthly($aclMonthly);
	Config::setAclSimple($aclSimple);
	Config::setAclClients($aclClients);
	Config::setAclProjects($aclProjects);
	Config::setAclTasks($aclTasks);
	Config::setAclReports($aclReports);
	Config::setAclRates($aclRates);
	Config::setAclAbsences($aclAbsences);
	Config::setAclExpenses($aclExpenses);
	Config::setAclECategories($aclECategories);
	Config::setAclTApproval($aclTApproval);
	Config::setSimpleTimesheetLayout($simpleTimesheetLayout);
	Config::setStartPage($startPage);
		
	//LogFile::write("config_action edit: ", $query. "\n");
	//list($qh,$num) = dbquery($query);

	if ($headerReset == true)
		Config::resetConfigValue("headerhtml");
	if ($bodyReset == true)
		Config::resetConfigValue("bodyhtml");
	if ($footerReset == true)
		Config::resetConfigValue("footerhtml");
	if ($errorReset == true)
		Config::resetConfigValue("errorhtml");
	if ($bannerReset == true)
		Config::resetConfigValue("bannerhtml");
	if ($tableReset == true)
		Config::resetConfigValue("tablehtml");
	if ($localeReset == true)
		Config::resetConfigValue("locale");
	if ($timezoneReset == true)
		Config::resetConfigValue("timezone");
	if ($timeformatReset == true)
		Config::resetConfigValue("timeformat");
	if ($weekStartDayReset == true)
		Config::resetConfigValue("weekstartday");
	if ($projectItemsPerPageReset == true)
		Config::resetConfigValue("project_items_per_page");
	if ($taskItemsPerPageReset == true)
		Config::resetConfigValue("task_items_per_page");
	if ($aclReset == true)
	{
		Config::resetConfigValue("aclStopwatch");
		Config::resetConfigValue("aclDaily");
		Config::resetConfigValue("aclWeekly");
		Config::resetConfigValue("aclMonthly");
		Config::resetConfigValue("aclSimple");
		Config::resetConfigValue("aclClients");
		Config::resetConfigValue("aclProjects");
		Config::resetConfigValue("aclTasks");
		Config::resetConfigValue("aclReports");
		Config::resetConfigValue("aclRates");
		Config::resetConfigValue("aclAbsences");
		Config::resetConfigValue("aclExpenses");
		Config::resetConfigValue("aclECategories");
		Config::resetConfigValue("aclTApproval");
	}
}

//return to the config.php page
gotoLocation(Config::getRelativeRoot()."/config");

?>
