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
	
	private static $defaultConfig = array();	
		
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
	 * "foo" => "bar"
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
	 *
	 */
	public static function getTimeZone() {
		return self::$timeZone;
	}
	
	public static function 	configbodyhtml($bodyhtml) {
		self::$bodyhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET bodyhtml = '$value'";
				
}
	public static function configfooterhtml($footerhtml) {
				self::$footerhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET footerhtml = '$value'";
		
}
	public static function 	configerrorhtml($errorhtml) {
				self::$errorhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET errorhtml = '$value'";
		
}
	public static function 	configbannerhtml($bannerhtml) {
		self::$bannerhtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET bannerhtml = '$value'";
		
}
	public static function 	configtablehtml($tablehtml) {
		self::$tablehtml = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET tablehtml = '$value'";
		
}
	public static function 	configlocale($locale) {
		self::$locale = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET locale = '$value'";
		
}
	public static function 	configtimezone($value) {
		self::$timezone = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET timezone = '$value'";
		
}
	public static function 	configtimeformat($value) {
		self::$timeformat = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET timeformat = '$value'";
		
}
	public static function 	configweekstartday($value) {
		self::$weekstartday = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET weekstartday = '$value'";
		
}
	public static function 	configproject_items_per_page($value) {
		self::$project_items_per_page = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET project_items_per_page = '$value'";
		
}
	public static function 	configtask_items_per_page($value) {
		self::$task_items_per_page = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET task_items_per_page = '$value'";
		
}
	public static function 	configuseLDAP($value) {
		self::$useLDAP = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET useLDAP = '$value'";
		
}
	public static function 	configLDAPScheme($value) {
		self::$LDAPScheme = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPScheme = '$value'";
		
}
/*	public static function 	configLDAPHost($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPPort($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPBaseDN($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPUsernameAttribute($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPSearchScope($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPFilter($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPProtocolVersion($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPBindUsername($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
	public static function 	configLDAPBindPassword($value) {
		self::$ = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET  = '$value'";
		
}
*/
	public static function 	configLDAPBindByUser($value) {
		self::$LDAPBindByUser = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPBindByUser = '$value'";
		
}
	public static function 	configLDAPReferrals($value) {
		self::$LDAPReferrals = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPReferrals = '$value'";
		
}
	public static function 	configLDAPFallback($value) {
		self::$LDAPFallback = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET LDAPFallback = '$value'";
		
}
	public static function 	configaclStopwatch($value) {
		self::$aclStopwatch = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclStopwatch = '$value'";
		
}
	public static function 	configaclDaily($value) {
		self::$aclDaily = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclDaily = '$value'";
		
}
	public static function 	configaclWeekly($value) {
		self::$aclWeekly = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclWeekly = '$value'";
		
}
	public static function 	configaclMonthly($value) {
		self::$aclMonthly = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclMonthly = '$value'";
		
}
	public static function 	configaclSimple($value) {
		self::$aclSimple = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclSimple = '$value'";
		
}
	public static function 	configaclClients($value) {
		self::$aclClients = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclClients = '$value'";
		
}
public static function 	configaclProjects($value) {
		self::$aclProjects = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclProjects = '$value'";
	
}
	public static function 	configaclTasks($value) {
		self::$aclTasks = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclTasks = '$value'";
		
}
	public static function 	configaclReports($value) {
		self::$aclReports = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclReports = '$value'";
		
}
	public static function 	configaclRates($value) {
		self::$aclRates = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclRates = '$value'";
		
}
	public static function 	configaclAbsences($value) {
		self::$aclAbsences = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclAbsences = '$value'";
		
}
	public static function 	configaclExpenses($value) {
		self::$aclExpenses = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclExpenses = '$value'";
		
}
	public static function 	configaclECategories($value) {
		self::$aclECategories = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclECategories = '$value'";
		
}
	public static function 	configaclTApproval($value) {
		self::$aclTApproval = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET aclTApproval = '$value'";
		
}
	public static function 	configsimpleTimesheetLayout($value) {
		self::$simpleTimesheetLayout = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET simpleTimesheetLayout = '$value'";
		
}
	public static function 	configstartPage($value) {
		self::$startPage = $value;
		$query = "UPDATE ".tbl::getConfigTable()." SET startPage = '$value'";
		
}

	public static function 	changeConfig($name, $value) {
		self::$currentConfig['$name'] = $value;
		$query = "UPDATE ".tbl::getConfigurationTable()." SET '$name' = '$value'";
		
}

}//end config class

?>
