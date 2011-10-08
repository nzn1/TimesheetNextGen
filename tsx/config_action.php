<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

//if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclMonthly'))return;


//if ((!Site::getSession()->isLoggedIn()) || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
//	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=Administrator");
//	exit;
//}

include('include/config/config.class.php');
$ccl = new Config();

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
$simpleTimesheetLayout = $_REQUEST["simpleTimesheetLayout"];
$startPage = $_REQUEST["startPage"];

//LogFile::write("startPage is $startPage\n");
// reset values in the new configuration table 
	function resetConfigurationValue($fieldName) {
		include("table_names.inc");

		//get the default value
		$fieldName;

		//set it
		dbQuery("UPDATE ".tbl::getConfigurationTable()." SET value = '$fieldName=' WHERE name = '$fieldName';");
	}
	
	function resetConfigValue($fieldName) {
		include("table_names.inc");

		//get the default value
		list($qh, $num) = dbQuery("SELECT $fieldName FROM ".tbl::getConfigTable()." WHERE config_set_id='0';");
		$resultset = dbResult($qh);

		//set it
		dbQuery("UPDATE ".tbl::getConfigTable()." SET $fieldName='" . $resultset[$fieldName] . "' WHERE config_set_id='1';");
	}

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
	$query = "UPDATE ".tbl::getConfigTable()." SET ".
		"headerhtml='$headerhtml',".
		"bodyhtml='$bodyhtml',".
		"footerhtml='$footerhtml',".
		"errorhtml='$errorhtml',".
		"bannerhtml='$bannerhtml',".
		"tablehtml='$tablehtml',".
		"locale='$locale',".
		"timezone='$timezone',".
		"timeformat='$timeformat', ".
		"weekstartday='$weekstartday', " .
		"project_items_per_page='$projectItemsPerPage', " .
		"task_items_per_page='$taskItemsPerPage', " .
		"useLDAP='$useLDAP', " .
		"LDAPScheme='$LDAPScheme', " .
		"LDAPHost='$LDAPHost', " .
		"LDAPPort='$LDAPPort', " .
		"LDAPBaseDN='$LDAPBaseDN', " .
		"LDAPUsernameAttribute='$LDAPUsernameAttribute', " .
		"LDAPSearchScope='$LDAPSearchScope', " .
		"LDAPFilter='$LDAPFilter', " .
		"LDAPProtocolVersion='$LDAPProtocolVersion', " .
		"LDAPBindUsername='$LDAPBindUsername', ".
		"LDAPBindPassword='$LDAPBindPassword', ".
		"LDAPBindByUser='$LDAPBindByUser', " .
		"LDAPReferrals='$LDAPReferrals', " .
		"LDAPFallback='$LDAPFallback', " .
		"aclStopwatch='$aclStopwatch', " .
		"aclDaily='$aclDaily', " .
		"aclWeekly='$aclWeekly', " .
		"aclMonthly='$aclMonthly', " .
		"aclSimple='$aclSimple', " .
		"aclClients='$aclClients', " .
		"aclProjects='$aclProjects', " .
		"aclTasks='$aclTasks', " .
		"aclReports='$aclReports', " .
		"aclRates='$aclRates', " .
		"aclAbsences='$aclAbsences', " .
		"aclExpenses = '$aclExpenses', ".
		"aclECategories = '$aclECategories', ".
		"aclTApproval = '$aclTApproval', ".
		"simpleTimesheetLayout= '$simpleTimesheetLayout', " .
		"startPage='$startPage' " .
		"WHERE config_set_id='1';";
	
	// now change values in new configuration table

		$ccl->setHeaderHtml($headerhtml);
		$ccl->setBodyHtml($bodyhtml);
		$ccl->setFooterHtml($footerhtml);
		$ccl->setErrorHtml($errorhtml);
		$ccl->setBannerHtml($bannerhtml);
		$ccl->setTableHtml($tablehtml);
		$ccl->setLocale($locale);
		$ccl->setTimeZone($timezone);
		$ccl->setTimeFormat($timeformat);
		$ccl->setWeekStartDay($weekstartday);
		$ccl->setProjectItemsPerPage($projectItemsPerPage);
		$ccl->setTaskItemsPerPage($taskItemsPerPage);
		$ccl->setUseLDAP($useLDAP);
		$ccl->setLDAPScheme($LDAPScheme);
		$ccl->setLDAPHost($LDAPHost);
		$ccl->setLDAPPort($LDAPPort);
		$ccl->setLDAPBaseDN($LDAPBaseDN);
		$ccl->setLDAPUsernameAttribute($LDAPUsernameAttribute);
		$ccl->setLDAPSearchScope($LDAPSearchScope);
		$ccl->setLDAPFilter($LDAPFilter);
		$ccl->setLDAPProtocolVersion($LDAPProtocolVersion);
		$ccl->setLDAPBindUsername($LDAPBindUsername);
		$ccl->setLDAPBindPassword($LDAPBindPassword);
		$ccl->setLDAPBindByUser($LDAPBindByUser);
		$ccl->setLDAPReferrals($LDAPReferrals);
		$ccl->setLDAPFallback($LDAPFallback);
		$ccl->setAclStopwatch($aclStopwatch);
		$ccl->setAclDaily($aclDaily);
		$ccl->setAclWeekly($aclWeekly);
		$ccl->setAclMonthly($aclMonthly);
		$ccl->setAclSimple($aclSimple);
		$ccl->setAclClients($aclClients);
		$ccl->setAclProjects($aclProjects);
		$ccl->setAclTasks($aclTasks);
		$ccl->setAclReports($aclReports);
		$ccl->setAclRates($aclRates);
		$ccl->setAclAbsences($aclAbsences);
		$ccl->setAclExpenses($aclExpenses);
		$ccl->setAclECategories($aclECategories);
		$ccl->setAclTApproval($aclTApproval);
		$ccl->setSimpleTimesheetLayout($simpleTimesheetLayout);
		$ccl->setStartPage($startPage);
		
	//LogFile::write("$query\n");
	list($qh,$num) = dbquery($query);

	if ($headerReset == true)
		$ccl->resetConfigValue("headerhtml");
	if ($bodyReset == true)
		$ccl->$ccl->resetConfigValue("bodyhtml");
	if ($footerReset == true)
		$ccl->resetConfigValue("footerhtml");
	if ($errorReset == true)
		$ccl->resetConfigValue("errorhtml");
	if ($bannerReset == true)
		$ccl->resetConfigValue("bannerhtml");
	if ($tableReset == true)
		$ccl->resetConfigValue("tablehtml");
	if ($localeReset == true)
		$ccl->resetConfigValue("locale");
	if ($timezoneReset == true)
		$ccl->resetConfigValue("timezone");
	if ($timeformatReset == true)
		$ccl->resetConfigValue("timeformat");
	if ($weekStartDayReset == true)
		$ccl->resetConfigValue("weekstartday");
	if ($projectItemsPerPageReset == true)
		$ccl->resetConfigValue("project_items_per_page");
	if ($taskItemsPerPageReset == true)
		$ccl->resetConfigValue("task_items_per_page");
	if ($aclReset == true)
	{
		$ccl->resetConfigValue("aclStopwatch");
		$ccl->resetConfigValue("aclDaily");
		$ccl->resetConfigValue("aclWeekly");
		$ccl->resetConfigValue("aclMonthly");
		$ccl->resetConfigValue("aclSimple");
		$ccl->resetConfigValue("aclClients");
		$ccl->resetConfigValue("aclProjects");
		$ccl->resetConfigValue("aclTasks");
		$ccl->resetConfigValue("aclReports");
		$ccl->resetConfigValue("aclRates");
		$ccl->resetConfigValue("aclAbsences");
		$ccl->resetConfigValue("aclExpenses");
		$ccl->resetConfigValue("aclECategories");
		$ccl->resetConfigValue("aclTApproval");
	}
}

//return to the config.php page
gotoLocation(Config::getRelativeRoot()."/config");

?>
