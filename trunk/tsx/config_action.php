<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Administrator'))return;


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
$aclTsubmission = $_REQUEST["aclTsubmission"];
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

	Site::Config()->set("headerhtml", $headerhtml);
	Site::Config()->set("bodyhtml", $bodyhtml);
	Site::Config()->set("footerhtml", $footerhtml);
	Site::Config()->set("errorhtml", $errorhtml);
	Site::Config()->set("bannerhtml", $bannerhtml);
	Site::Config()->set("tablehtml", $tablehtml);
	Site::Config()->set("locale", $locale);
	Site::Config()->set("timezone", $timezone);
	Site::Config()->set("timeformat", $timeformat);
	Site::Config()->set("weekstartday", $weekstartday);
	Site::Config()->set("project_items_per_page", $projectItemsPerPage);
	Site::Config()->set("task_items_per_page", $taskItemsPerPage);
	Site::Config()->set("useLDAP", $useLDAP);
	Site::Config()->set("LDAPScheme", $LDAPScheme);
	Site::Config()->set("LDAPHost", $LDAPHost);
	Site::Config()->set("LDAPPort", $LDAPPort);
	Site::Config()->set("LDAPBaseDN", $LDAPBaseDN);
	Site::Config()->set("LDAPUsernameAttribute", $LDAPUsernameAttribute);
	Site::Config()->set("LDAPSearchScope", $LDAPSearchScope);
	Site::Config()->set("LDAPFilter", $LDAPFilter);
	Site::Config()->set("LDAPProtocolVersion", $LDAPProtocolVersion);
	Site::Config()->set("LDAPBindUsername", $LDAPBindUsername);
	Site::Config()->set("LDAPBindPassword", $LDAPBindPassword);
	Site::Config()->set("LDAPBindByUser", $LDAPBindByUser);
	Site::Config()->set("LDAPReferrals", $LDAPReferrals);
	Site::Config()->set("LDAPFallback", $LDAPFallback);
	Site::Config()->set("aclStopwatch", $aclStopwatch);
	Site::Config()->set("aclDaily", $aclDaily);
	Site::Config()->set("aclWeekly", $aclWeekly);
	Site::Config()->set("aclMonthly", $aclMonthly);
	Site::Config()->set("aclSimple", $aclSimple);
	Site::Config()->set("aclClients", $aclClients);
	Site::Config()->set("aclProjects", $aclProjects);
	Site::Config()->set("aclTasks", $aclTasks);
	Site::Config()->set("aclReports", $aclReports);
	Site::Config()->set("aclRates", $aclRates);
	Site::Config()->set("aclAbsences", $aclAbsences);
	Site::Config()->set("aclExpenses", $aclExpenses);
	Site::Config()->set("aclECategories", $aclECategories);
	Site::Config()->set("aclTsubmission, $aclTsubmission);
	Site::Config()->set("aclTApproval, $aclTApproval);
	Site::Config()->set("SimpleTimesheetLayout", $simpleTimesheetLayout);
	Site::Config()->set("startPage", $startPage);

	//LogFile::write("config_action edit: ", $query. "\n");
	//list($qh,$num) = dbquery($query);

	if ($headerReset == true)
		Site::Config()->set("headerhtml", '<meta name="description" content="Timesheet Next Gen">\r\n<link href="css/timesheet.css" rel="stylesheet" type="text/css">\r\n<link rel="shortcut icon" href="images/favicon.ico">');
	if ($bodyReset == true)
		Site::Config()->set("bodyhtml", 'link=\"#004E8A\" vlink=\"#171A42\"');
	if ($footerReset == true)
		Site::Config()->set("footerhtml", '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\nTimesheetNextGen\r\n<br /><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n</td></tr></table>');
	if ($errorReset == true)
		Site::Config()->set("errorhtml", '<table border=0 cellpadding=5 width=\"100%\">\r\n<tr>\r\n  <td><font size=\"+2\" color=\"red\">%errormsg%</font></td>\r\n</tr></table>\r\n<p>Please go <a href=\"javascript:history.back()\">Back</a> and try again.</p>');
	if ($bannerReset == true)
		Site::Config()->set("bannerhtml", '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" style=\"background-image: url(\'images/timesheet_background_pattern.gif\');\"><img src=\"images/timesheet_banner.gif\" alt=\"Timesheet Banner\" /></td>\r\n</tr><tr>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><tr>\r\n<td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" alt=\"\" width=\"1\" height=\"1\" /></td>\r\n</tr></table>');
	if ($tableReset == true)
		Site::Config()->set("tablehtml", '');
	if ($localeReset == true)
		Site::Config()->set("locale", 'C');
	if ($timezoneReset == true)
		Site::Config()->set("timezone", 'Europe/Zurich');
	if ($timeformatReset == true)
		Site::Config()->set("timeformat", '12');
	if ($weekStartDayReset == true)
		Site::Config()->set("weekstartday", '1');
	if ($projectItemsPerPageReset == true)
		Site::Config()->set("project_items_per_page", '10');
	if ($taskItemsPerPageReset == true)
		Site::Config()->set("task_items_per_page", '10');
	if ($aclReset == true)
	{
		Site::Config()->set("aclStopwatch", 'Basic');
		Site::Config()->set("aclDaily", 'Basic');
		Site::Config()->set("aclWeekly", 'Basic');
		Site::Config()->set("aclMonthly", 'Basic');
		Site::Config()->set("aclSimple", 'Basic');
		Site::Config()->set("aclClients", 'Basic');
		Site::Config()->set("aclProjects", 'Basic');
		Site::Config()->set("aclTasks", 'Basic');
		Site::Config()->set("aclReports", 'Basic');
		Site::Config()->set("aclRates", 'Basic');
		Site::Config()->set("aclAbsences", 'Basic');
		Site::Config()->set("aclExpenses", 'Basic');
		Site::Config()->set("aclECategories", 'Basic');
		Site::Config()->set("aclTsubmission", 'Basic');
		Site::Config()->set("aclTApproval", 'Basic');
	}
}

//return to the config.php page
gotoLocation(Config::getRelativeRoot()."/config");

?>
