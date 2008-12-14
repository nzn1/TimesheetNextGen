<?php 
define('VERSION', '1.3.1');

// set up the global variable that holds any error messages
// don't really like using globals, but this is quick and dirty
$_ERROR = '';
// other globals 
$table_inc_file = '../table_names.inc';
$db_inc_file = '../database_credentials.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Timesheet Next Gen :: Installation</title>
<style type="text/css">
body { font-family: verdana, helvetia, arial, sans-serif; font-size: 90%; }
code { font-weight: bold; font-size: 1.1em; font-style: normal; }
th { vertical-align: top; text-align: left; }
.error { color: red; }
</style>
</head>
<body>
<h1>Welcome to Timesheet Next Gen Installation</h1>
<?php
// check that Timesheet NG isn't already installed
// check_is_installed() returns :
//  0 if not installed, 
//  1 if installed but lower version, 
//  2 if installed and up-to-date
switch (check_is_installed()) {
	case 2:
		install_success();
		break;
	case 1:
		upgrade();
		break;
	default:
		install();
}
?>
</body>
</html>
<?php 
/**
 * check_is_installed()
 * Checks to see if Timesheet NG is already installed
 * @returns int:
 *   0 if not installed, 
 *   1 if installed but lower version, 
 *   2 if installed and up-to-date
 */
function check_is_installed() {
	global $db_inc_file;
	include_once($db_inc_file);
	if($TIMESHEET_INSTALLED == '__INSTALLED__') { return 0; }
	if(version_compare($TIMESHEET_VERSION, VERSION) == -1) { return 1; }
	return 2;
}
/** 
 * install()
 * Runs the install functionality
 */
function install() {
	global $table_inc_file, $db_inc_file;
	$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 'one';
	switch ($step) {
		case 'two':
			step_three();
			break;
		case 'three':
			step_final();
			break;
		default:
			// check that step one is complete (i.e. the include files are writeable)
			if(
				file_exists($table_inc_file) && is_writable($table_inc_file) &&
				file_exists($db_inc_file) && is_writable($db_inc_file)			
				) {
				step_two();
			}
			else {
				step_one();	
			}
	}
}
/**
 * upgrade()
 * No upgrade functionality yet
 * @return unknown_type
 */
function upgrade() {
	echo "<h2>Upgrade</h2><p>Sorry, this functionality hasn't been written yet</p>";
}

/**
 * step_one()
 * Output the first step (default) page
 */
function step_one() {
?>
<p>Thank you for downloading Timesheet Next Gen. 
It'll just take a few more minutes to get it installed and working on your system</p>
<h3>Things you'll need:</h3>
<ul>
<li>A MySQL database (we recommend version 4.1 or better)</li>
<li>The ability to change permissions of files on your server</li>
<li>The ability to delete directories on your server</li>
</ul>

<h2>Step One: Setup File</h2>
<p>Firstly you need to rename the files <code>database_credentials.inc.in</code> to <code>database_credentials.inc</code>
and <code>table_names.inc.in</code> to <code>table_names.inc</code>
and make it writable by the webserver</p>
<p>Once you've done this, please refresh this page and proceed to Step Two</p>
<?php if(file_exists($setup_location)) { ?>
<p><em class="warn">Warning: <code>setup.php</code> exists, but it <strong>is not writable</strong>. Please make 
this file writable and refresh this page</em></p>
<?php } ?>
<p><a href="./">Refresh Page</a></p>
<?php 
}

/**
 * step_two()
 * Display the second ste - the db configuration
 */
function step_two() {
	global $_ERROR;
?>
<h2>Step Two: Database Configuration</h2>
<form action="<?php echo $SCRIPT_NAME; ?>" method="post">
<p>Please enter you database credentials below</p>
<?php if($_ERROR) {?>
<h3 class="error">There was an error</h3>
<p class="error"><?php echo $_ERROR; ?></p>
<?php } ?>
<table border="0">
<tr>
<th>Host</th><td><input type="text" name="db_host" value="<?php if(!$_REQUEST['db_host']) { echo "localhost"; } else { echo $_REQUEST['db_host']; }?>" /></td>
</tr>
<tr>
<th rowspan="2">Database Name</th><td><input type="text" name="db_name" value="<?php if(!$_REQUEST['db_name']) { echo "timesheet"; } else { echo $_REQUEST['db_name']; }?>" /></td>
</tr>
<?php 
/*
<tr><td>
<input type="radio" name="db_name_exist" value="yes" checked="checked"/> This database exists, no need to create<br />
<input type="radio" name="db_name_exist" value="no"/> This database does not exist, create now
</td></tr>
*/
?>
<tr><td>Please make sure that this database exist and you have sufficient permissions to create tables</td></tr>
<tr>
<th>Table Prefix</th><td><input type="text" name="db_prefix" value="<?php if(!$_REQUEST['db_prefix']) { echo "timesheet_"; } else { echo $_REQUEST['db_prefix']; }?>" /></td>
</tr>
<tr>
<th>Username</th><td><input type="text" name="db_user" value="<?php echo $_REQUEST['db_user']; ?>" /></td>
</tr>
<tr>
<th>Password</th><td><input type="password" name="db_pass" value="" /></td>
</tr>
<tr>
<th rowspan="2">Password Function</th>
<td><select name="db_pass_func">
<option value="SHA1">SHA1</option>
<option value="PASSWORD">PASSWORD</option>
<option value="OLD_PASSWORD">OLD PASSWORD</option>
</select></td>
</tr>
<tr><td>This is the function the database uses to encrypt the passwords. If your MySQL version is 4.1 or above
you should use SHA1. PASSWORD should be used on MySQL version 4.0 or below, and OLD PASSWORD for MySQL
version 4.1 or above where SHA1 is not available.<br /><em>If in doubt, use SHA1.</em></td></tr>
<tr><td colspan="2">
<input type="button" value="Test Configuration" />
<input type="submit" value="Proceed to Step Three" />
</td></tr>
</table>
<input type="hidden" name="step" value="two" />
</form>
<?php 
}

/**
 * step_three()
 * 1. Create database if needed
 * 2. Create tables
 * 3. Display confirmation (delete install directory) page
 */
function step_three() {
	global $_ERROR;
	
	// get the passed data
	$db_host = (isset($_REQUEST['db_host']) && $_REQUEST['db_host']) ? $_REQUEST['db_host'] : false;
	$db_name = (isset($_REQUEST['db_name']) && $_REQUEST['db_name']) ? $_REQUEST['db_name'] : false;
	$db_name_exist = (isset($_REQUEST['db_name_exist']) && $_REQUEST['db_name_exist']) ? $_REQUEST['db_name_exist'] : 'yes';
	$db_prefix = (isset($_REQUEST['db_prefix']) && $_REQUEST['db_prefix']) ? $_REQUEST['db_prefix'] : false;
	$db_user = (isset($_REQUEST['db_user']) && $_REQUEST['db_user']) ? $_REQUEST['db_user'] : false;
	$db_pass = (isset($_REQUEST['db_pass']) && $_REQUEST['db_pass']) ? $_REQUEST['db_pass'] : false;
	$db_pass_func = (isset($_REQUEST['db_pass_func']) && $_REQUEST['db_pass_func']) ? $_REQUEST['db_pass_func'] : 'SHA1';
	
	// check that we have all we need
	$_ERROR = '';
	if(!$db_host) { $_ERROR .= 'You have not specified a database host<br />'; }
	if(!$db_name) { $_ERROR .= 'You have not specified a database name<br />'; }
	if(!$db_user) { $_ERROR .= 'You have not specified a database username<br />'; }
	if($_ERROR != '') {
		return step_two();
	}
	
	// check to see if we need to create database
	/*
	if($db_name_exist == 'no') {
		$_ERROR = '';
		if(!create_database($db_host, $db_name, $db_user, $db_pass)) { 
			return step_two(); 
		}	
	}
	*/
	
	// connect to the database
	if(!database_connect($db_host, $db_name, $db_user, $db_pass)) { 
		return step_two(); 
	}	
	
	// now create the tables
	if(!create_tables($db_host, $db_name, $db_user, $db_pass, $db_prefix)) { 
		return step_two(); 
	}		
	
	// finally write the include files
	if(!write_includes($db_host, $db_name, $db_user, $db_pass, $db_prefix, $db_pass_func)) {
		return fatal_error();
	}
	return step_three_display();
}
/**
 * step_three_display()
 * outputs the actual "Step Three" page
 */
function step_three_display() {
	global $_ERROR;
?>
<h2>Step Three: Create Admin User</h2>
<form action="<?php echo $SCRIPT_NAME; ?>" method="post">
<p class="success">Database and setup files have been successfully created</p>

<p>We now need to create an admin user for your installation of Timesheet Next Gen</p>
<?php if($_ERROR) {?>
<h3 class="error">There was an error</h3>
<p class="error"><?php echo $_ERROR; ?></p>
<?php } ?>
<table border="0">
<tr>
<th>Admin User Name</th><td><input type="text" name="username" value="<?php echo $_REQUEST['username'];?>" /></td>
</tr>
<tr>
<th>Admin Password</th><td><input type="password" name="password" /></td>
</tr>
<tr>
<th>Admin Password (again)</th><td><input type="password" name="password2" /></td>
</tr>
<tr><td colspan="2">
<input type="submit" value="Install Now" />
</td></tr>
</table>
<input type="hidden" name="step" value="three" />
</form>
<?php 
}
/**
 * step_final()
 * Creates the admin user for the installation, displays the "completed" page
 * @return unknown_type
 */
function step_final() {
	global $_ERROR;
	// get the passed data
	$username = (isset($_REQUEST['username']) && $_REQUEST['username']) ? $_REQUEST['username'] : false;
	$password = (isset($_REQUEST['password']) && $_REQUEST['password']) ? $_REQUEST['password'] : false;
	$password2 = (isset($_REQUEST['password2']) && $_REQUEST['password2']) ? $_REQUEST['password2'] : false;
	
	// check that we have all we need
	$_ERROR = '';
	if(!$username) { $_ERROR .= 'You have not specified an admin username<br />'; }
	if(!$password) { $_ERROR .= 'You have not specified an admin password<br />'; }
	if($password != $password2) { $_ERROR .= 'The passwords do not match<br />'; }
	if($_ERROR != '') {
		return step_three_display();
	}
	
	// add the admin user to the database
	if(!admin_user_create($username, $password)) {
		return fatal_error();	
	}
	return install_success();
}
/**
 * install_success()
 * Displays the "Success" page when install has completed
 */
function install_success() {
?>
<h2>Installation Complete</h2>
<p>Installation was successul</p>
<h3>Final Bits</h3>
<p>There are just a few things to do before you can start using Timesheet Next Gen</p>
<ol>
<li>Make <code>database_credentials.inc</code> <strong>read only</strong></li>
<li>Make <code>table_names.inc</code> <strong>read only</strong></li>
<li>Delete the <code>install</code> directory and all its contents</li>
</ol>
<p>Once you have done those, then you can <a href="../">contine to Timesheet Next Gen</a></p>
<?php 	
}
/**
 * fatal_error()
 * Displays page when there was a non-recoverable error
 */
function fatal_error() {
	global $_ERROR;
?>
<h2>Installation Error</h2>
<p class="error">There has been an error that couldn't be recovered from</p>
<h3 class="error">Error Message</h3>
<p class="error"><?php echo $_ERROR;?></p>
<?php 
}
/**
 * create_database()
 * Creates the database
 * @param $db_host
 * @param $db_name
 * @param $db_user
 * @param $db_pass
 * @return bool true on successful creation
 */
function create_database($db_host, $db_name, $db_user, $db_pass) {
	// not done yet
	return true;
}
/**
 * create_tables()
 * Creates the database tables and populates
 * @param $db_host
 * @param $db_name
 * @param $db_user
 * @param $db_pass
 * @param $db_prefix
 * @return bool true on successful creation
 */
function create_tables($db_host, $db_name, $db_user, $db_pass, $db_prefix) {
	// for the time being we are just installing a clean MySQL database
	return install_create_tables($db_prefix);
}
/**
 * write_includes()
 * Writes the configuration to the include files 
 * @param $db_host
 * @param $db_name
 * @param $db_user
 * @param $db_pass
 * @param $db_prefix
 * @param $db_pass_func
 * @return bool true if successfully written
 */
function write_includes($db_host, $db_name, $db_user, $db_pass, $db_prefix, $db_pass_func) {
	global $_ERROR, $table_inc_file, $db_inc_file;
	// make sure the values are safe
	$db_host = mysql_real_escape_string($db_host);
	$db_name = mysql_real_escape_string($db_name);
	$db_user = mysql_real_escape_string($db_user);
	$db_pass = mysql_real_escape_string($db_pass);
	$db_prefix = mysql_real_escape_string($db_prefix);
	$db_pass_func = mysql_real_escape_string($db_pass_func);
	
	// first write the database credentials: read in current file
	$contents = file_get_contents($db_inc_file);
	// edit the file	
	$contents = str_replace("__INSTALLED__", 1, $contents);
	$contents = str_replace("__VERSION__", VERSION, $contents);
	$contents = str_replace("__DBHOST__", $db_host, $contents);
	$contents = str_replace("__DBUSER__", $db_user, $contents);
	$contents = str_replace("__DBPASS__", $db_pass, $contents);
	$contents = str_replace("__DBNAME__", $db_name, $contents);
	$contents = str_replace("__DBPASSWORDFUNCTION__", $db_pass_func, $contents);	
	// re-write it
	if (!$handle = fopen($db_inc_file, 'w')) {
		$_ERROR .= 'Could not open <code>'.$db_inc_file.'</code> file for writing';
		return false;
    }
    if (fwrite($handle, $contents) === FALSE) {
		$_ERROR .= 'Could not write to <code>'.$db_inc_file.'</code>';
		return false;
    }
    fclose($handle);
	
	// now the table names
	$contents = file_get_contents($table_inc_file);
	$contents = str_replace("__TABLE_PREFIX__", $db_prefix, $contents);
	if (!$handle = fopen($table_inc_file, 'w')) {
		$_ERROR .= 'Could not open <code>'.$table_inc_file.'</code> file for writing';
		return false;
    }
    if (fwrite($handle, $contents) === FALSE) {
		$_ERROR .= 'Could not write to <code>'.$table_inc_file.'</code>';
		return false;
    }
    fclose($handle);
	return true;
}
/**
 * admin_user_create()
 * @param $username
 * @param $password
 * @return bool true if admin user was created
 */
function admin_user_create($username, $password) {
	global $_ERROR, $db_inc_file, $table_inc_file, $mysql_db_inc;
	
	include($db_inc_file);
	include($table_inc_file);
	
	// clean up input
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	
	// connect to the database
	if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
		return fatal_error(); 
	}	
	
	$sql = 'INSERT INTO '.$USER_TABLE.' (username,level,password,first_name,last_name) 
		VALUES ("'.$username.'",10,'.$DATABASE_PASSWORD_FUNCTION.'("'.$password.'"),"Timesheet","Admin")';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create the admin user <br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	$sql = 'INSERT INTO '.$ASSIGNMENTS_TABLE.' VALUES(1,"'.$username.'", 1)';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not add user to default assignment<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	$sql = 'INSERT INTO '.$TASK_ASSIGNMENTS_TABLE.' VALUES(1,"'.$username.'", 1)';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not add user to default assignment<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}

function database_connect($db_host, $db_name, $db_user, $db_pass) {
	global $_ERROR;
	$link = @mysql_connect($db_host, $db_user, $db_pass);
	if (!$link) {
		$_ERROR .= 'Could not connect to the database.<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';
		return false;
	}
	if(!mysql_select_db($db_name)) {
		$_ERROR .= 'Could not select the database.<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';
		return false;
	}
	return true;
}


/** Database Installation **/
function install_create_tables($db_prefix) {
	global $_ERROR;
	$db_prefix = mysql_real_escape_string($db_prefix);
	// run through each table
	if(!install_create_table_billrate($db_prefix)) { return false; }
	if(!install_create_table_assignments($db_prefix)) { return false; }
	if(!install_create_table_client($db_prefix)) { return false; }
	if(!install_create_table_config($db_prefix)) { return false; }
	if(!install_create_table_project($db_prefix)) { return false; }
	if(!install_create_table_task($db_prefix)) { return false; }
	if(!install_create_table_task_assignments($db_prefix)) { return false; }
	if(!install_create_table_times($db_prefix)) { return false; }
	if(!install_create_table_user($db_prefix)) { return false; }
	if(!install_create_table_absences($db_prefix)) { return false; }
	if(!install_create_table_allowances($db_prefix)) { return false; }
	return true;
}
function install_create_table_billrate($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sbillrate (rate_id int(8) NOT NULL auto_increment, bill_rate decimal(8,2) DEFAULT "0.00" NOT NULL,PRIMARY KEY (rate_id))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>billrate</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %sbillrate VALUES ( 1, 0.00)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>billrate</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_assignments($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sassignments (proj_id int(11) DEFAULT "0" NOT NULL, username char(32) DEFAULT "" NOT NULL,rate_id int(11) NOT NULL,PRIMARY KEY (proj_id,username))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>assignments</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %sassignments VALUES (1, "guest", 1)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>billrate</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_client($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sclient (client_id int(8) NOT NULL auto_increment, organisation varchar(64), description varchar(255), address1 varchar(127), city varchar(60), 
		state varchar(80), country char(2), postal_code varchar(13), contact_first_name varchar(127), contact_last_name varchar(127), username varchar(32), 
		contact_email varchar(127), phone_number varchar(20), fax_number varchar(20), gsm_number varchar(20), http_url varchar(127), address2 varchar(127), PRIMARY KEY (client_id))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>client</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %sclient VALUES (1,"No Client", "This is required, do not edit or delete this client record", "", "", "", "", "", "", "", "", "", "", "", "", "", "")',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>client</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_config($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sconfig (
		config_set_id int(1) NOT NULL default "0",
		version varchar(32) NOT NULL default "%s",
		headerhtml mediumtext NOT NULL,
		bodyhtml mediumtext NOT NULL,
		footerhtml mediumtext NOT NULL,
		errorhtml mediumtext NOT NULL,
		bannerhtml mediumtext NOT NULL,
		tablehtml mediumtext NOT NULL,
		locale varchar(127) default NULL,
		timezone varchar(127) default NULL,
		timeformat enum("12","24") NOT NULL default "12",
		weekstartday TINYINT NOT NULL default 0,
		useLDAP tinyint(4) NOT NULL default "0",
		LDAPScheme varchar(32) default NULL,
		LDAPHost varchar(255) default NULL,
		LDAPPort int(11) default NULL,
		LDAPBaseDN varchar(255) default NULL,
		LDAPUsernameAttribute varchar(255) default NULL,
		LDAPSearchScope enum("base","sub","one") NOT NULL default "base",
		LDAPFilter varchar(255) default NULL,
		LDAPProtocolVersion varchar(255) default "3",
		LDAPBindUsername varchar(255) default "",
		LDAPBindPassword varchar(255) default "",
		LDAPBindByUser tinyint(4) NOT NULL default "0",
		LDAPReferrals bit(1) default 0,
		LDAPFallback bit(1) default 0,
		aclStopwatch enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclDaily enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclWeekly enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclCalendar enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclSimple enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclClients enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclProjects enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclTasks enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclReports enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		aclRates enum("Admin","Mgr","Basic","None") NOT NULL default "Basic",
		simpleTimesheetLayout enum("small work description field","big work description field","no work description field") NOT NULL DEFAULT "small work description field",
		startPage enum("stopwatch", "daily", "weekly", "calendar", "simple") NOT NULL DEFAULT "calendar",
		PRIMARY KEY  (config_set_id)
		)',
		$db_prefix, VERSION);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>config</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = 'INSERT INTO '.$db_prefix.'config (config_set_id, version, headerhtml, bodyhtml, footerhtml, errorhtml, bannerhtml, tablehtml, locale, timezone, timeformat, weekstartday, useLDAP, LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, LDAPBindUsername, LDAPBindPassword, LDAPBindByUser, aclStopwatch, aclDaily, aclWeekly, aclCalendar, aclSimple, aclClients, aclProjects, aclTasks, aclReports, aclRates)
		VALUES (0,"'.VERSION.'",
		"<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">","link=\"#004E8A\" vlink=\"#171A42\"","<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheetNextGen \r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>",
		"<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>","<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" background=\"images/'.$db_prefix.'background_pattern.gif\"><img src=\"images/'.$db_prefix.'banner.gif\"></td></tr><tr>\r\n\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" width=\"1\" height=\"1\"></td></tr>\r\n</table>","","en_AU","Australia/Melbourne","12",1,0,"ldap","watson",389,"dc=watson","cn","base", "", "3", "", "", "0", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic")';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>config values</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = 'INSERT INTO '.$db_prefix.'config (config_set_id, version, headerhtml, bodyhtml, footerhtml, errorhtml, bannerhtml, tablehtml, locale, timezone, timeformat, weekstartday, useLDAP, LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, LDAPBindUsername, LDAPBindPassword, LDAPBindByUser, aclStopwatch, aclDaily, aclWeekly, aclCalendar, aclSimple, aclClients, aclProjects, aclTasks, aclReports, aclRates) 
		VALUES (1,"'.VERSION.'","<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">","link=\"#004E8A\" vlink=\"#171A42\"","<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n<tr><td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheetNextGen \r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>","<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>","<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"2\" background=\"images/'.$db_prefix.'background_pattern.gif\"><img src=\"images/'.$db_prefix.'banner.gif\"></td></tr><tr>\r\n\r\n<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>\r\n<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">You are logged in as %username%</td>\r\n</tr><td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\"><img src=\"images/spacer.gif\" width=\"1\" height=\"1\"></td></tr>\r\n</table>","","en_AU","Australia/Melbourne","12",1,0,"ldap","watson",389,"dc=watson","cn","base","","3","","", "0", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic")';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>config values</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	$sql = 'INSERT INTO '.$db_prefix.'config (config_set_id, version, headerhtml, bodyhtml, footerhtml, errorhtml, bannerhtml, tablehtml, locale, timezone, timeformat, weekstartday, useLDAP, LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, LDAPBindUsername, LDAPBindPassword, LDAPBindByUser, aclStopwatch, aclDaily, aclWeekly, aclCalendar, aclSimple, aclClients, aclProjects, aclTasks, aclReports, aclRates) 
		VALUES (2,"'.VERSION.'","<META name=\"description\" content=\"Timesheet.php Employee/Contractor Timesheets\">\r\n<link href=\"css/questra/timesheet.css\" rel=\"stylesheet\" type=\"text/css\">","link=\"#004E8A\" vlink=\"#171A42\"","</td><td width=\"2\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"2\" height=\"1\"></td></tr>\r\n<tr><td colspan=\"3\" style=\"background-color: #9494B7; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">\r\n\r\nTimesheet.php website: <A href=\"http://www.advancen.com/timesheet/\"><span \r\n\r\nclass=\"bottom_bar_text\">http://www.advancen.com/timesheet/</span></A>\r\n<br><span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b></span>\r\n\r\n</td></tr></table>","<TABLE border=0 cellpadding=5 width=\"100%\">\r\n<TR><TD><FONT size=\"+2\" color=\"red\">%errormsg%</FONT></TD></TR></TABLE>\r\n<P>Please go <A href=\"javascript:history.back()\">Back</A> and try again.</P>","<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n  <td style=\"padding-right: 15; padding-bottom: 8;\"><img src=\"images/questra/logo.gif\"></td>\r\n  <td width=\"100%\" valign=\"bottom\">\r\n    <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\r\n      <tr><td colspan=\"3\" class=\"text_faint\" style=\"padding-bottom: 5;\" align=\"right\">You are logged in as %username%.</td></tr>\r\n      <tr>\r\n        <td background=\"images/questra/bar_left.gif\" valign=\"top\"><img src=\"images/questra/spacer.gif\" height=\"1\" width=\"8\"></td>\r\n        <td background=\"images/questra/bar_background.gif\" width=\"100%\" style=\"padding: 5;\">%commandmenu%</td>\r\n        <td background=\"images/questra/bar_right.gif\" valign=\"top\"><img src=\"images/questra/spacer.gif\" height=\"1\" width=\"8\"></td>\r\n      </tr>\r\n    </table>\r\n  </td>\r\n</tr></table>\r\n\r\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr>\r\n<td colspan=\"3\" height=\"8\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"1\" height=\"8\"></td></tr><tr>\r\n<td width=\"2\" style=\"background-color: #9494B7;\"><img src=\"images/questra/spacer.gif\" width=\"2\" height=\"1\"></td>\r\n<td width=\"100%\" bgcolor=\"#F2F2F8\">","","en_AU","Australia/Melbourne","12",1,0,"ldap","watson",389,"dc=watson","cn","base","","3","","","0", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic", "Basic")';
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>config values</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}		
	return true;
}	
function install_create_table_project($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sproject (
  		proj_id int(11) NOT NULL auto_increment,
  		title varchar(200) DEFAULT "" NOT NULL,
  		client_id int(11) DEFAULT "0" NOT NULL,
  		description varchar(255),
  		start_date date DEFAULT "1970-01-01" NOT NULL,
  		deadline date DEFAULT "0000-00-00" NOT NULL,
  		http_link varchar(127),
  		proj_status enum("Pending","Started","Suspended","Complete") DEFAULT "Pending" NOT NULL,
  		proj_leader varchar(32) DEFAULT "" NOT NULL,
  		PRIMARY KEY (proj_id)
		)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>project</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %sproject VALUES ( 1, "Default Project", 1, "","","","","Started","")',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>project</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_task($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %stask (
		task_id int(11) NOT NULL auto_increment,
		proj_id int(11) DEFAULT "0" NOT NULL,
		name varchar(127) DEFAULT "" NOT NULL,
		description text,
  		assigned datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
  		started datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
  		suspended datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
  		completed datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
  		status enum("Pending","Assigned","Started","Suspended","Complete") DEFAULT "Pending" NOT NULL,
 		PRIMARY KEY (task_id))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>task</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %stask VALUES (1,1,"Default Task","","","","","","Started")',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>task</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_task_assignments($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %stask_assignments (
		  task_id int(8) DEFAULT "0" NOT NULL,
		  username varchar(32) DEFAULT "" NOT NULL,
		  proj_id int(11) DEFAULT "0" NOT NULL,
		  PRIMARY KEY (task_id,username)
		)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>task_assignments</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	$sql = sprintf(
		'INSERT INTO %stask_assignments VALUES ( 1, "guest", 1)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could add the default <strong>task assignment</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}	
	return true;
}
function install_create_table_times($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %stimes (
		  uid varchar(32) DEFAULT "" NOT NULL,
		  start_time datetime DEFAULT "1970-01-01 00:00:00" NOT NULL,
		  end_time datetime DEFAULT "0000-00-00 00:00:00" NOT NULL,
		  trans_num int(11) NOT NULL auto_increment,
		  proj_id int(11) DEFAULT "1" NOT NULL,
		  task_id int(11) DEFAULT "1" NOT NULL,
		  log_message TEXT,
		  KEY uid (uid,trans_num),
		  UNIQUE trans_num (trans_num)
		)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>times</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	return true;
}
function install_create_table_user($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %suser (
		  username varchar(32) DEFAULT "" NOT NULL,
		  level int(11) DEFAULT "0" NOT NULL,
		  password varchar(41) DEFAULT "" NOT NULL,
		  first_name varchar(64) DEFAULT "" NOT NULL,
		  last_name varchar(64) DEFAULT "" NOT NULL,
		  email_address varchar(63) DEFAULT "" NOT NULL,
		  time_stamp timestamp(14),
		  status enum("IN","OUT") DEFAULT "OUT" NOT NULL,
		  uid int(11) NOT NULL auto_increment,
		  PRIMARY KEY (username),
		 KEY uid (uid)
		)',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>user</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	return true;
}
function install_create_table_absences($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sabsences (entry_id int(6) NOT NULL auto_increment,date datetime NOT NULL default "0000-00-00 00:00:00",AM_PM enum("day","AM","PM") NOT NULL default "day",subject varchar(127) NOT NULL default "",
		type enum("Holiday","Sick","Military","Training","Compensation","Other","Public") NOT NULL default "Holiday", user varchar(32) NOT NULL default "0",PRIMARY KEY  (entry_id))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>absences</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	return true;
}
function install_create_table_allowances($db_prefix) {
	global $_ERROR;
	$sql = sprintf(
		'CREATE TABLE %sallowances (entry_id INT NOT NULL AUTO_INCREMENT, username varchar(32) NOT NULL default "0", date DATE NOT NULL, holiday INT NOT NULL,glidetime TIME NOT NULL, PRIMARY KEY (entry_id))',
		$db_prefix);
	if(!mysql_query($sql)) {
		$_ERROR .= 'Could not create <strong>allowances</strong><br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';		
		return false;
	}
	return true;
}
