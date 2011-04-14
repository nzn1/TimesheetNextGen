<?php
die('NOT CONVERTED TO OO YET');
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
require("class.AuthenticationManager.php");
require("class.CommandMenu.php");

if (!$authenticationManager->isLoggedIn() || !$authenticationManager->hasClearance(CLEARANCE_ADMINISTRATOR)) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=Administrator");
	exit;
}



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
$simpleTimesheetLayout = $_REQUEST["simpleTimesheetLayout"];
$startPage = $_REQUEST["startPage"];

//LogFile::write("startPage is $startPage\n");

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
	$headerhtml = addslashes(unhtmlentities(trim($headerhtml)));
	$bodyhtml = addslashes(unhtmlentities(trim($bodyhtml)));
	$footerhtml = addslashes(unhtmlentities(trim($footerhtml)));
	$errorhtml = addslashes(unhtmlentities(trim($errorhtml)));
	$bannerhtml = addslashes(unhtmlentities(trim($bannerhtml)));
	$tablehtml = addslashes(unhtmlentities(trim($tablehtml)));
	$locale = addslashes(unhtmlentities(trim($locale)));
	$timezone = addslashes(unhtmlentities(trim($timezone)));
	$projectItemsPerPage = addslashes(unhtmlentities(trim($projectItemsPerPage)));
	$taskItemsPerPage = addslashes(unhtmlentities(trim($taskItemsPerPage)));
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
		"aclWeekly='$aclStopwatch', " .
		"aclMonthly='$aclMonthly', " .
		"aclSimple='$aclSimple', " .
		"aclClients='$aclClients', " .
		"aclProjects='$aclProjects', " .
		"aclTasks='$aclTasks', " .
		"aclReports='$aclReports', " .
		"aclRates='$aclRates', " .
		"aclAbsences='$aclAbsences', " .
		"simpleTimesheetLayout= '$simpleTimesheetLayout', " .
		"startPage='$startPage' " .
		"WHERE config_set_id='1';";
	//LogFile::write("$query\n");
	list($qh,$num) = dbquery($query);

	if ($headerReset == true)
		resetConfigValue("headerhtml");
	if ($bodyReset == true)
		resetConfigValue("bodyhtml");
	if ($footerReset == true)
		resetConfigValue("footerhtml");
	if ($errorReset == true)
		resetConfigValue("errorhtml");
	if ($bannerReset == true)
		resetConfigValue("bannerhtml");
	if ($tableReset == true)
		resetConfigValue("tablehtml");
	if ($localeReset == true)
		resetConfigValue("locale");
	if ($timezoneReset == true)
		resetConfigValue("timezone");
	if ($timeformatReset == true)
		resetConfigValue("timeformat");
	if ($weekStartDayReset == true)
		resetConfigValue("weekstartday");
	if ($projectItemsPerPageReset == true)
		resetConfigValue("project_items_per_page");
	if ($taskItemsPerPageReset == true)
		resetConfigValue("task_items_per_page");
	if ($aclReset == true)
	{
		resetConfigValue("aclStopwatch");
		resetConfigValue("aclDaily");
		resetConfigValue("aclWeekly");
		resetConfigValue("aclMonthly");
		resetConfigValue("aclSimple");
		resetConfigValue("aclClients");
		resetConfigValue("aclProjects");
		resetConfigValue("aclTasks");
		resetConfigValue("aclReports");
		resetConfigValue("aclRates");
		resetConfigValue("aclAbsences");
	}
}

//return to the config.php page
gotoLocation(Config::getRelativeRoot()."/config");

?>
