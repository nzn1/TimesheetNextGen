<?
/**
 * *****************************************************************************
 * Name:                    config.class.php
 * Recommended Location:    /include
 * Last Updated:            Oct 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The config class is responsible for setting up all of the variables required
 * to build the site
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/


require('config.factory.class.php');

/**
 *
 * The configuration of the website
 * @author Mark
 *
 */
class Config extends ConfigFactory {

  	/**
	 * Special Names and Level Constants - the admin
	 * page will only be accessible to the user with
	 * the admin name and also to those users at the
	 * admin user level. Feel free to change the names
	 * and level constants as you see fit, you may
	 * also add additional level specifications.
	 * Levels must be digits between 0-9.
	 */
	
	protected static $headerhtml;
	protected static $bodyhtml;
	protected static $footerhtml;
	protected static $errorhtml;
	protected static $bannerhtml;
	protected static $tablehtml;
	protected static $locale;
	protected static $timezone;
	protected static $timeformat;
	protected static $weekstartday;
	protected static $projectItemsPerPage;
	protected static $taskItemsPerPage;
	protected static $useLDAP;
	protected static $LDAPScheme;
	protected static $LDAPHost;
	protected static $LDAPurl;
	protected static $LDAPPort;
	protected static $LDAPBaseDN;
	protected static $LDAPUsernameAttribute;
	protected static $LDAPSearchScope;
	protected static $LDAPFilter;
	protected static $LDAPProtocolVersion;
	protected static $LDAPBindUsername;
	protected static $LDAPBindPassword;
	protected static $LDAPBindByUser;
	protected static $LDAPReferrals;
	protected static $LDAPFallback;
	protected static $aclStopwatch;
	protected static $aclDaily;
	protected static $aclWeekly;
	protected static $aclMonthly;
	protected static $aclSimple;
	protected static $aclClients;
	protected static $aclProjects;
	protected static $aclTasks;
	protected static $aclReports;
	protected static $aclRates;
	protected static $aclAbsences;
	protected static $aclExpenses;
	protected static $aclECategories;
	protected static $aclTApproval;
	protected static $simpleTimesheetLayout;
	protected static $startPage;
	protected static $timeZone;
	
  protected static $project_items_per_page;
  protected static $task_items_per_page;
  
	
	private static $defaultConfig;	
		
	/**
	 * initialise the config class
	 */
	public static function initialise() {

		if(file_exists('include/config/config.php')) {
			include('include/config/config.php');
    	}

		parent::initialise();

		//finally initialise the table values
		//this is also really temporary code until the install script process is written

		tbl::initialise();
		//the database config can't be initialised until the database has been started.
		
		self::initialConfigValues();
	}


	public static function getDbConfig() {

		$q = "SELECT * FROM ".tbl::getNewConfigTable();

		$data = Database::getInstance()->sql($q,true, Database::TYPE_OBJECT);
		//ppr($data);
		if($data == Database::SQL_EMPTY || $data == Database::SQL_ERROR) {
			//no data was found.  This is a potential problem
			//this means that we are either missing the new config table
			//or there is no data!
			//we best run the version check anyway!
			//ppr('table not found');
			self::runVersionCheck();
			return;
		}

		foreach($data as $obj) {
			if($obj->name == 'version') {
				parent::$databaseVersion = $obj->value;
			}
			else if(property_exists('Config',$obj->name)){
        self::${$obj->name} = $obj->value;
      }
      else{
        trigger_error('An item was found in the Configuration table that cannot be added to the Config class:'.$obj->name." = ".$obj->value,E_USER_WARNING);
      }

			//more config variables to be stored into the
			//config class or config.factory.class


		}
		//run a version check
		self::runVersionCheck();


	}
	
	/**
	 * 
	 * Default values for configuration
	 * @var unknown_type
	 */ 
	protected static function initialConfigValues() {
	self::$defaultConfig = new stdClass();
	self::$defaultConfig->headerhtml = "<meta name=\"description\" content=\"Timesheet Next Gen\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">\r\n<link rel=\"shortcut icon\" href=\"images/favicon.ico\">";
	self::$defaultConfig->bodyhtml =  "link=\"#004E8A\" vlink=\"#171A42\"";
	self::$defaultConfig->footerhtml = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\nTimesheetNextGen\r\n<br /><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n</td></tr></table>";
	self::$defaultConfig->errorhtml =  "<table border=\"0\" cellpadding=\"5\" width=\"100%\">\r\n<tr>\r\n  <td><font size=\"+2\" color=\"red\">%errormsg%</font></td>\r\n</tr></table>\r\n<p>Please go <a href=\"javascript:history.back()\">Back</a> and try again.</p>";
	self::$defaultConfig->bannerhtml =  "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" style=\"background-image: url(\'images/timesheet_background_pattern.gif\');\"><img src=\"images/timesheet_banner.gif\" alt=\"Timesheet Banner\" /></td>\r\n</tr><tr>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><tr>\r\n<td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" alt=\"\" width=\"1\" height=\"1\" /></td>\r\n</tr></table>";
	self::$defaultConfig->tablehtml =  "";
	self::$defaultConfig->locale =  "";
	self::$defaultConfig->timezone =  "Europe/Zurich";
	self::$defaultConfig->timeformat =  "12";
	self::$defaultConfig->weekstartday =  "0";
	self::$defaultConfig->projectItemsPerPage =  "10";
	self::$defaultConfig->taskItemsPerPage =  "10";
	self::$defaultConfig->useLDAP =  "0";
	self::$defaultConfig->LDAPScheme =  "ldap";
	self::$defaultConfig->LDAPHost =  "10.0.0.1";
	self::$defaultConfig->LDAPPort =  "389";
	self::$defaultConfig->LDAPBaseDN =  "dc=timesheet,dc=com";
	self::$defaultConfig->LDAPUsernameAttribute =  "CN";
	self::$defaultConfig->LDAPSearchScope =  "base";
	self::$defaultConfig->LDAPFilter =  "";
	self::$defaultConfig->LDAPProtocolVersion =  "3";
	self::$defaultConfig->LDAPBindUsername =  "";
	self::$defaultConfig->LDAPBindPassword =  "";
	self::$defaultConfig->LDAPBindByUser =  "0";
	self::$defaultConfig->LDAPReferrals =  "0";
	self::$defaultConfig->LDAPFallback =  "0";
	self::$defaultConfig->aclStopwatch =  "Basic";
	self::$defaultConfig->aclDaily =  "Basic";
	self::$defaultConfig->aclWeekly =  "Basic";
	self::$defaultConfig->aclMonthly =  "Basic";
	self::$defaultConfig->aclSimple =  "Basic";
	self::$defaultConfig->aclClients =  "Basic";
	self::$defaultConfig->aclProjects =  "Basic";
	self::$defaultConfig->aclTasks =  "Basic";
	self::$defaultConfig->aclReports =  "Basic";
	self::$defaultConfig->aclRates =  "Basic";
	self::$defaultConfig->aclAbsences =  "Basic";
	self::$defaultConfig->aclExpenses =  "Basic";
	self::$defaultConfig->aclECategories =  "Basic";
	self::$defaultConfig->aclTApproval =  "Basic";
	self::$defaultConfig->simpleTimesheetLayout =  "small work description field";
	self::$defaultConfig->startPage =  "monthly";
	self::$defaultConfig->timeZone =  'Europe/London';
	}
	/**
	* Configuration Routine getTimeZone
	*/
	public static function getTimeZone() {
		return self::$timeZone;
	}
	/**
	* Configuration Routine getHeaderHtml
	*/
	public static function getHeaderHtml() {
		return self::$headerhtml;
	}
	/**
	* Configuration Routine getBodyHtml
	*/
	public static function getBodyHtml() {
		return self::$bodyhtml;
	}
	/**
	* Configuration Routine getFooterHtml
	*/
	public static function getFooterHhtml() {
		return self::$footerhtml;
	}
	/**
	* Configuration Routine getErrorHtml
	*/
	public static function getErrorHtml() {
		return self::$errorhtml;
	}
	/**
	* Configuration Routine getBbannerHtml
	*/
	public static function getBannerHtml() {
		return self::$bannerhtml;
	}
	/**
	* Configuration Routine getTtableHhtml
	*/
	public static function getTtableHtml() {
		return self::$tablehtml;
	}
	/**
	* Configuration Routine getLocale
	*/
	public static function getLocale() {
		return self::$locale;
	}
	/**
	* Configuration Routine getTimeFormat
	*/
	public static function getTimeFormat() {
		return self::$timeformat;
	}
	/**
	* Configuration Routine getWeekStartDay
	*/
	public static function getWeekStartDay() {
		return self::$weekstartday;
	}
	/**
	* Configuration Routine getProjectItemsPerPage
	*/
	public static function getProjectItemsPerPage() {
		return self::$projectItemsPerPage;
	}
	/**
	* Configuration Routine getTaskItemsPerPage
	*/
	public static function getTaskItemsPerPage() {
		return self::$taskItemsPerPage;
	}
	/**
	* Configuration Routine getUseLDAP
	*/
	public static function getUseLDAP() {
		return self::$useLDAP;
	}
	/**
	* Configuration Routine getLDAPScheme
	*/
	public static function getLDAPScheme() {
		return self::$LDAPScheme;
	}
	/**
	* Configuration Routine getLDAPHost
	*/
	public static function getLDAPHost() {
		return self::$LDAPHost;
	}
	/**
	* Configuration Routine getLDAPPort
	*/
	public static function getLDAPPort() {
		return self::$LDAPPort;
	}
	/**
	* Configuration Routine getLDAPBaseDN
	*/
	public static function getLDAPBaseDN() {
		return self::$LDAPBaseDN;
	}
	/**
	* Configuration Routine getLDAPUsernameAttribute
	*/
	public static function getLDAPUsernameAttribute() {
		return self::$LDAPUsernameAttribute;
	}
	/**
	* Configuration Routine getLDAPSearchScope
	*/
	public static function getLDAPSearchScope() {
		return self::$LDAPSearchScope;
	}
	/**
	* Configuration Routine
	*/
	public static function getLDAPFilter() {
		return self::$LDAPFilter;
	}
	/**
	* Configuration Routine getLDAPProtocolVersion
	*/
	public static function getLDAPProtocolVersion() {
		return self::$LDAPProtocolVersion;
	}
	/**
	* Configuration Routine getLDAPBindUsername
	*/
	public static function getLDAPBindUsername() {
		return self::$LDAPBindUsername;
	}
	/**
	* Configuration Routine getLDAPBindPassword
	*/
	public static function getLDAPBindPassword() {
		return self::$LDAPBindPassword;
	}
	/**
	* Configuration Routine getLDAPBindByUser
	*/
	public static function getLDAPBindByUser() {
		return self::$LDAPBindByUser;
	}
	/**
	* Configuration Routine getLDAPReferrals
	*/
	public static function getLDAPReferrals() {
		return self::$LDAPReferrals;
	}
	/**
	* Configuration Routine getLDAPFallback
	*/
	public static function getLDAPFallback() {
		return self::$LDAPFallback;
	}
	/**
	* Configuration Routine getAclStopwatch
	*/
	public static function getAclStopwatch() {
		return self::$aclStopwatch;
	}
	/**
	* Configuration Routine getAclDaily
	*/
	public static function getAclDaily() {
		return self::$aclDaily;
	}
	/**
	* Configuration Routine getAclWeekly
	*/
	public static function getAclWeekly() {
		return self::$aclWeekly;
	}
	/**
	* Configuration Routine getAclMonthly
	*/
	public static function getAclMonthly() {
		return self::$aclMonthly;
	}
	/**
	* Configuration Routine getAclSimple
	*/
	public static function getAclSimple() {
		return self::$aclSimple;
	}
	/**
	* Configuration Routine getAclClients
	*/
	public static function getAclClients() {
		return self::$aclClients;
	}
	/**
	* Configuration Routine getAclProjects
	*/
	public static function getAclProjects() {
		return self::$aclProjects;
	}
	/**
	* Configuration Routine getAclTasks
	*/
	public static function getAclTasks() {
		return self::$aclTasks;
	}
	/**
	* Configuration Routine getAclReports
	*/
	public static function getAclReports() {
		return self::$aclReports;
	}
	/**
	* Configuration Routine getAclRates
	*/
	public static function getAclRates() {
		return self::$aclRates;
	}
	/**
	* Configuration Routine getAclAbsences
	*/
	public static function getAclAbsences() {
		return self::$aclAbsences;
	}
	/**
	* Configuration Routine getAclExpenses
	*/
	public static function getAclExpenses() {
		return self::$aclExpenses;
	}
	/**
	* Configuration Routine getAclECategories
	*/
	public static function getAclECategories() {
		return self::$aclECategories;
	}
	/**
	* Configuration Routine getAclTApproval
	*/
	public static function getAclTApproval() {
		return self::$aclTApproval;
	}
	/**
	* Configuration Routine getSimpleTimesheetLayout
	*/
	public static function getSimpleTimesheetLayout() {
		return self::$simpleTimesheetLayout;
	}
	/**
	* Configuration Routine getStartPage
	*/
	public static function getStartPage() {
		return self::$startPage;
	}
	/**
	* Configuration Routine set configuration item in Configuration table set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setHeaderHtml($value) {
		self::$headerhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET headerhtml = '$value'";
				
	}
	/**
	* Configuration Routine set configuration item in Configuration table set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setBodyHtml($value) {
		self::$bodyhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET bodyhtml = '$value'";
				
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setFooterHtml($value) {
		self::$footerhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET footerhtml = '$value'";
		
		}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setErrorHtml($value) {
				self::$errorhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET errorhtml = '$value'";
		
		}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setBannerHtml($value) {
		self::$bannerhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET bannerhtml = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setTableHtml($value) {
		self::$tablehtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET tablehtml = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLocale($value) {
		self::$locale = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET locale = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setTimeZone($value) {
		self::$timezone = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET timezone = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setTimeFormat($value) {
		self::$timeformat = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET timeformat = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setWeekStartDay($value) {
		self::$weekstartday = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET weekstartday = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setProjectItemsPerPage($value) {
		self::$project_items_per_page = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET project_items_per_page = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setTaskIemsPerPage($value) {
		self::$task_items_per_page = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET task_items_per_page = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setUseLDAP($value) {
		self::$useLDAP = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET useLDAP = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPScheme($value) {
		self::$LDAPScheme = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPScheme = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPHost($value) {
		self::$LDAPHost = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPHost = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPPort($value) {
		self::$LDAPPort = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPPort = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPBaseDN($value) {
		self::$LDAPBaseDN = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPBaseDN = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPUsernameAttribute($value) {
		self::$LDAPUsernameAttribute = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPUsernameAttribute = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPSearchScope($value) {
		self::$LDAPSearchScope = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPSearchScope = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPFilter($value) {
		self::$LDAPFilter = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPFilter = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPProtocolVersion($value) {
		self::$LDAPProtocolVersion = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPProtocolVersion = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPBindUsername($value) {
		self::$LDAPBindUsername = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPBindUsername = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPBindPassword($value) {
		self::$LDAPBindPassword = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPBindPassword = '$value'";
		
	}

	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPBindByUser($value) {
		self::$LDAPBindByUser = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPBindByUser = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPReferrals($value) {
		self::$LDAPReferrals = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPReferrals = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setLDAPFallback($value) {
		self::$LDAPFallback = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPFallback = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclStopwatch($value) {
		self::$aclStopwatch = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclStopwatch = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclDaily($value) {
		self::$aclDaily = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclDaily = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclWeekly($value) {
		self::$aclWeekly = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclWeekly = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclMonthly($value) {
		self::$aclMonthly = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclMonthly = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclSimple($value) {
		self::$aclSimple = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclSimple = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclClients($value) {
		self::$aclClients = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclClients = '$value'";
		
	}
/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclProjects($value) {
		self::$aclProjects = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclProjects = '$value'";
	
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclTasks($value) {
		self::$aclTasks = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclTasks = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclReports($value) {
		self::$aclReports = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclReports = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclRates($value) {
		self::$aclRates = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclRates = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclAbsences($value) {
		self::$aclAbsences = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclAbsences = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclExpenses($value) {
		self::$aclExpenses = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclExpenses = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclECategories($value) {
		self::$aclECategories = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclECategories = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setAclTApproval($value) {
		self::$aclTApproval = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclTApproval = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setSimpleTimesheetLayout($value) {
		self::$simpleTimesheetLayout = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET simpleTimesheetLayout = '$value'";
		
	}
	/**
	* Configuration Routine set configuration item in Configuration table
	* @params String $value - the value of the parameter to be changed
	*/
	public static function setStartPage($value) {
		self::$startPage = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET startPage = '$value'";
		
	}

	/**
	*	* Configuration Routine set configuration item in Configuration table changeConfig
	* @params String $value - the value of the parameter to be changed
	*/
	public static function changeConfig($name, $value) {
		self::$currentConfig['$name'] = $value;
		$query = "UPDATE ".tbl::getConfigurationTable()." SET '$name' = '$value'";
		
	}
	
	/**
	*	* Configuration Routine reset configuration item to the default
	* @params String $value - the value of the parameter to be reset to default
	*/
	public static function resetConfigValue($name) {
		if(property_exists('Config',$name)){
		  self::${$name} = self::$defaultConfig->{$name};
		}
		$query = "UPDATE ".tbl::getConfigurationTable()." SET '$name' = '" . $defaultConfig['name']."'";
		
	}

	
}//end config class

?>
