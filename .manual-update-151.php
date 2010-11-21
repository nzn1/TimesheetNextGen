<?php 
define('OLD_VERSION', '1.5.0');
define('INSTALLER_VERSION', '1.5.1');
// set up the global variable that holds any error messages
// don't really like using globals, but this is quick and dirty
// other globals 
$table_inc_file = './table_names.inc';
$db_inc_file = './database_credentials.inc';

if(!file_exists($db_inc_file)) { print "  Error: can't include $db_inc_file"; exit; }
if(!file_exists($table_inc_file)) { print "  Error: can't include $table_inc_file"; exit; }

include_once($db_inc_file);
include_once($table_inc_file);

// check that Timesheet NG isn't already installed
	switch (check_installed_version()) {
		case 1:
			update_the_database();
			break;
		default:
			$db_version = get_database_version($CONFIG_TABLE);
			print "\n  Error: This helps to upgrade a 1.5.0 database to a newer 1.5.1 revision\n";
			print "\n    This installation is version $db_version, not version 1.5.0\n\n";
			exit;
	}

function check_installed_version() {
	global $TIMESHEET_VERSION, $DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS;
	global $CONFIG_TABLE;

	if(version_compare($TIMESHEET_VERSION, OLD_VERSION) == 0) { 
		if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
			exit;
		}
		$db_version = get_database_version($CONFIG_TABLE);
		if(version_compare($db_version, OLD_VERSION) == 0) { 
			return 1; 
		} else { 
			return 0; 
		}
	} else { 
		return 0; 
	}
}

function add_column_if_not_exist($table, $column, $column_attr ){
	$exists = false;
	$columns = mysql_query("show columns from $table");
	while($c = mysql_fetch_assoc($columns)){
		if($c['Field'] == $column){
			$exists = true;
			break;
		}
	}
	if(!$exists){
		print "  adding $column to $table...\n";
		$result = mysql_query("ALTER `$table` ADD `$column` $column_attr");
		if(!$result) {
			print "  Error: Could not add $column to $table\n";
			print get_db_error(mysql_error())."\n";
			return false;
		}
	}
}

function update_the_database() {
/*
DELETE __table_PREFIX__config WHERE config_set_id=0;
INSERT INTO __table_PREFIX__config (config_set_id, version, headerhtml, bodyhtml, footerhtml, errorhtml, bannerhtml, tablehtml, locale, timezone, timeformat, weekstartday, useLDAP, LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, LDAPBindUsername, LDAPBindPassword, LDAPBindByUser, aclStopwatch, aclDaily, aclWeekly, aclMonthly, aclSimple, aclClients, aclProjects, aclTasks, aclReports, aclRates, aclAbsences) 
VALUES (0, '1.5.1', '<meta name=\"description\" content=\"Timesheet Next Gen\">\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">\n<link rel=\"shortcut icon\" href=\"images/favicon.ico\">', 'link=\"#004E8A\" vlink=\"#171A42\"', '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\nTimesheetNextGen\n<br /><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\n</td></tr></table>', '<table border=\"0\" cellpadding=5 width=\"100%\">\n<tr>\n  <td><font size=\"+2\" color=\"red\">%errormsg%</font></td>\n</tr></table>\n<p>Please go <a href=\"javascript:history.back()\">Back</a> and try again.</p>', '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\n<td colspan=\"2\" background=\"images/timesheet_background_pattern.gif\"><img src=\"images/timesheet_banner.gif\" alt="Timesheet Banner"/></td>\n</tr><tr>\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\n</tr><tr>\n<td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" alt="" width=\"1\" height=\"1\" /></td>\n</tr></table>', '', '', 'Europe/Zurich', '12', 1, 0, 'ldap', 'watson', 389, 'dc=watson', 'cn', 'base', '', '3', '', '', '0', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic');
*/
	global $TIMESHEET_VERSION, $DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS;
	global $CONFIG_TABLE;


	if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
		print "Error: couldn't connect to the database\n";
		exit;
	}


	print "  Remove old default configuration set...\n";
	$sql = "DELETE FROM $CONFIG_TABLE WHERE config_set_id=0";
	$result = mysql_query($sql);
	if(!$result) {
		print "  Error: Could not remove config_set 0\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}

	print "  Inserting new default configuration set...\n";
	$sql = "INSERT INTO $CONFIG_TABLE (config_set_id, version, headerhtml, bodyhtml, footerhtml, errorhtml, bannerhtml, tablehtml, locale, timezone, timeformat, weekstartday, useLDAP, LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, LDAPBindUsername, LDAPBindPassword, LDAPBindByUser, aclStopwatch, aclDaily, aclWeekly, aclMonthly, aclSimple, aclClients, aclProjects, aclTasks, aclReports, aclRates, aclAbsences) 
VALUES (0, '1.5.1', '<meta name=\"description\" content=\"Timesheet Next Gen\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">\r\n<link rel=\"shortcut icon\" href=\"images/favicon.ico\">', 'link=\"#004E8A\" vlink=\"#171A42\"', '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\nTimesheetNextGen\r\n<br /><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n</td></tr></table>', '<table border=\"0\" cellpadding=5 width=\"100%\">\r\n<tr>\r\n  <td><font size=\"+2\" color=\"red\">%errormsg%</font></td>\r\n</tr></table>\r\n<p>Please go <a href=\"javascript:history.back()\">Back</a> and try again.</p>', '<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" background=\"images/timesheet_background_pattern.gif\"><img src=\"images/timesheet_banner.gif\" alt=\"Timesheet Banner\" /></td>\r\n</tr><tr>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><tr>\r\n<td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" alt=\"\" width=\"1\" height=\"1\" /></td>\r\n</tr></table>', '', '', 'Europe/Zurich', '12', 1, 0, 'ldap', 'watson', 389, 'dc=watson', 'cn', 'base', '', '3', '', '', '0', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic', 'Basic')";
	$result = mysql_query($sql);
	if(!$result) {
		print "Error: Could not add new config_set 0\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}

	update_db_version("1.5.1");

	print "Database updates complete\n";
}

function update_db_version($version) {
	global $CONFIG_TABLE;
	$sql = "UPDATE `$CONFIG_TABLE` set version='$version';";
	if(!mysql_query($sql)) {
		print "  Error: Could not update DB version in $CONFIG_TABLE\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}
}

function run_sql_script($sqlfile) {
	global $DATABASE_PASSWORD_FUNCTION;
	global $CONFIG_TABLE;
	$db_prefix = preg_replace("/config$/","",$CONFIG_TABLE);
	$db_prefix = mysql_real_escape_string($db_prefix);

	$contents = file_get_contents($sqlfile);
	// finalise the script
	$key_words = array("__TIMESHEET_VERSION__", "__table_PREFIX__", "__DBPASSWORDFUNCTION__");
	$key_values = array(INSTALLER_VERSION, $db_prefix, $DATABASE_PASSWORD_FUNCTION);
	$contents = str_replace($key_words, $key_values, $contents);

	$queries = preg_split("/;+(?=([^'|^\\']*['|\\'][^'|^\\']*['|\\'])*[^'|^\\']*[^'|^\\']$)/", $contents);
	foreach ($queries as $sql) {
		if (strlen(trim($sql)) > 0) {
			if(!mysql_query($sql)) {
				print "  Error: Could not complete script\n";
				print get_db_error(mysql_error())."\n";
				return false;
			}
		}
	}
	return true;
}

/**
 * get_db_error()
 * get DB error info 
 */
function get_db_error($error,$query='') {
	$estring='';
	if($query) $estring= "    Query said: $query\n";
	return  $estring .  "    Database said: $error";
}

/**
 * get_database_version()
 * Check the status in the DB 
 * @return version number
 */
function get_database_version($cfg_table) {
	$sql = 'SELECT version FROM '.$cfg_table.' WHERE config_set_id=\'1\'';
	$result = mysql_query($sql);
	if(!$result) {
		print "  Error: Could not get version from config table\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}
	$row = mysql_fetch_row($result);

	// hack for V1.3.1
	if ($row[0]=='__timesheet_VERSION__')
		$row[0] = '1.3.1';

	return $row[0];
}

function database_connect($db_host, $db_name, $db_user, $db_pass) {
	$link = @mysql_connect($db_host, $db_user, $db_pass);
	if (!$link) {
		print "  Error: Could not connect to the database\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}
	if(!mysql_select_db($db_name)) {
		print "  Error: Could not select the database\n";
		print get_db_error(mysql_error())."\n";
		return false;
	}
	return true;
}

// vim:ai:ts=4:sw=4
