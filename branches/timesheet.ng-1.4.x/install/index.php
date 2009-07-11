<?php 
define('INSTALLER_VERSION', '1.4.1');
// set up the global variable that holds any error messages
// don't really like using globals, but this is quick and dirty
$_ERROR = '';
// other globals 
$table_inc_file = 'table_names.inc';
$db_inc_file = 'database_credentials.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>TimesheetNextGen :: Installation</title>
<style type="text/css">
body { font-family: verdana, helvetia, arial, sans-serif; font-size: 90%; }
code { font-weight: bold; font-size: 1.1em; font-style: normal; }
th { vertical-align: top; text-align: left; }
.error { color: red; }
</style>
</head>
<body>
<h1>Timesheet Next Gen Installation</h1>
<?php
// check that Timesheet NG isn't already installed
if(!isset($_REQUEST['step'])) {
	switch (check_is_installed()) {
	 	case 3:
			display_install_success();
			break;
		case 2:
			display_install_step_3();
			break;
		case 1:
			display_upgrade_step_1();
			break;
		default:
			install();
	}
}
else { install(); }
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
 *   2 if installed but no admin user
 *   3 if installed and up-to-date
 */
function check_is_installed() {
	global $db_inc_file, $table_inc_file;
	if(!file_exists('../'.$db_inc_file)) { return 0; }
	if(!file_exists('../'.$table_inc_file)) { return 0; }

	include_once('../'.$db_inc_file);
	include_once('../'.$table_inc_file);
	if(!isset($TIMESHEET_INSTALLED)) { 
		// pre-V1.3.1 installation?
		if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
			return 0; 
		}
		$ver = get_database_version($CONFIG_TABLE);
		if ($ver==false) { return 0; }
		else { return 1; }
	}
	if($TIMESHEET_INSTALLED == '__INSTALLED__') { return 0; }
	if(version_compare($TIMESHEET_VERSION, INSTALLER_VERSION) == -1) { return 1; }

	if(!check_admin_user()) { return 2; }
	else { return 3; }
}
/** 
 * install()
 * Runs the install functionality
 */
function install() {
	global $table_inc_file, $db_inc_file;
	$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 'one';
	switch ($step) {
		case 'up-one':
			display_upgrade_step_2();
			break;
		case 'up-two':
			upgrade_installation();
			break;
		case 'two':
			create_new_installation();
			break;
		case 'three':
			install_step_final();
			break;
		case 'one':
		default:
			// check that step one is complete (i.e. the include files are writeable)
			if(check_include_files()) {
				display_install_step_2();
			}
			else {
				display_install_step_1();
			}
	}
}

/**
 * check_include_files()
 * Checks to see if Timesheet NG can find and write the include files
 * @returns bool
 */
function check_include_files() {
	global $table_inc_file, $db_inc_file;
	if(
		file_exists('../'.$table_inc_file) && is_writable('../'.$table_inc_file) &&
		file_exists('../'.$db_inc_file) && is_writable('../'.$db_inc_file)
		) {
		return true;
	}
	else { 
		return false;
	}
}

/**
 * display_install_step_1()
 * Output the first step (default) page
 */
function display_install_step_1() {
?>
<p>Thank you for downloading Timesheet Next Gen. 
It'll just take a few more minutes to get it installed and working on your system.</p>
<h3>Things you'll need:</h3>
<ul>
<li>A MySQL database (we recommend version 4.1 or better)</li>
<li>The ability to change permissions of files on your server</li>
<li>The ability to delete directories on your server</li>
</ul>

<h2>Step One: Configuration Files</h2>
<p>Firstly you need to copy the files <code>install/database_credentials.inc.in</code> to <code>database_credentials.inc</code>
and <code>install/table_names.inc.in</code> to <code>table_names.inc</code>
and make sure that they are writeable by the webserver</p>
<p>Once you've done this, please <a href="./">refresh this page and proceed to Step Two</a></p>
<?php 
}

/**
 * display_install_step_2()
 * Display the second step - the db configuration
 */
function display_install_step_2() {
	global $_ERROR;
?>
<h2>Step Two: MySQL Database Configuration</h2>
<?php if($_ERROR) {?>
<h3 class="error">There was an error</h3>
<p class="error"><?php echo $_ERROR; ?></p>
<?php } ?>
<form method="post">
<p>Please enter your database credentials:</p>
<table border="0">
<tr>
<th>Host</th><td><input type="text" name="db_host" value="<?php if(!isset($_REQUEST['db_host'])) { echo "localhost"; } else { echo $_REQUEST['db_host']; }?>" /></td>
</tr>
<tr>
<th>Database Name</th><td><input type="text" name="db_name" value="<?php if(!isset($_REQUEST['db_name'])) { echo "timesheet"; } else { echo $_REQUEST['db_name']; }?>" /></td>
<td>Please make sure that this database exists and you have sufficient permissions to create tables</td>
</tr>
<?php 
/*
<tr><td>
<input type="radio" name="db_name_exist" value="yes" checked="checked"/> This database exists, no need to create<br />
<input type="radio" name="db_name_exist" value="no"/> This database does not exist, create now
</td></tr>
*/
?>
<tr>
<th>Username</th><td><input type="text" name="db_user" value="<?php if(isset($_REQUEST['db_user'])) {echo $_REQUEST['db_user'];} ?>" /></td>
</tr>
<tr>
<th>Password</th><td><input type="password" name="db_pass" value="" /></td>
</tr>
<tr>
<th>Password Function</th>
<td><select name="db_pass_func">
<option value="SHA1">SHA1</option>
<option value="PASSWORD">PASSWORD</option>
<option value="OLD_PASSWORD">OLD PASSWORD</option>
</select></td>
<td>This is the function the database uses to encrypt the passwords. If your MySQL version is 4.1 or above
you should use SHA1. PASSWORD should be used on MySQL version 4.0 or below, and OLD PASSWORD for MySQL
version 4.1 or above where SHA1 is not available.<br /><em>If in doubt, use SHA1.</em></td></tr>
<tr>
<th>Table Prefix</th><td><input type="text" name="db_prefix" value="<?php if(!isset($_REQUEST['db_prefix'])) { echo "timesheet_"; } else { echo $_REQUEST['db_prefix']; }?>" /></td>
<td>This prefix is used for all table names</td>
</tr>
<tr><td colspan="3">
<?php /* <input type="button" value="Test Configuration" onclick="alert('Sorry, this doesn\'t work yet');"/> */ ?>
<input type="submit" value="Proceed to Step Three" />
</td></tr>
</table>
<input type="hidden" name="step" value="two" />
</form>
<?php 
}

/**
 * create_new_installation()
 * 1. Create database if needed
 * 2. Create tables
 * 3. Display confirmation (delete install directory) page
 */
function create_new_installation() {
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
		return display_install_step_2();
	}

	// check to see if we need to create database
	/*
	if($db_name_exist == 'no') {
		$_ERROR = '';
		if(!create_database($db_host, $db_name, $db_user, $db_pass)) { 
			return display_install_step_2(); 
		}
	}
	*/

	// connect to the database
	if(!database_connect($db_host, $db_name, $db_user, $db_pass)) { 
		return display_install_step_2(); 
	}

	// now create the tables
	if(!create_tables($db_host, $db_name, $db_user, $db_pass, $db_prefix)) { 
		return display_install_step_2(); 
	}

	// finally write the include files
	if(!create_include_files($db_host, $db_name, $db_user, $db_pass, $db_prefix, $db_pass_func)) {
		return display_fatal_error();
	}
	return display_install_step_3();
}
/**
 * display_install_step_3()
 * outputs the actual "Step Three" page
 */
function display_install_step_3() {
	global $_ERROR;
?>
<h2>Step Three: Create Admin User</h2>
<form method="post">
<p class="success">Database and setup files have been successfully created</p>

<p>We now need to create an admin user for your installation of Timesheet Next Gen</p>
<?php if($_ERROR) {?>
<h3 class="error">There was an error</h3>
<p class="error"><?php echo $_ERROR; ?></p>
<?php } ?>
<table border="0">
<tr>
<th>Admin User Name</th><td><input type="text" name="username" value="<?php if (isset($_REQUEST['username'])) echo $_REQUEST['username'];?>" /></td>
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
 * install_step_final()
 * Creates the admin user for the installation, displays the "completed" page
 * @return unknown_type
 */
function install_step_final() {
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
		return display_install_step_3();
	}

	// add the admin user to the database
	if(!create_admin_user($username, $password)) {
		return display_fatal_error();
	}
	return display_install_success();
}
/**
 * display_install_success()
 * Displays the "Success" page when install has completed
 */
function display_install_success() {
?>
<h2>Installation Complete</h2>
<p>Installation was successful</p>
<h3>Final Bits</h3>
<p>There are just a few things to do before you can start using Timesheet Next Gen</p>
<ol>
<li>Make <code>database_credentials.inc</code> <strong>read only</strong></li>
<li>Make <code>table_names.inc</code> <strong>read only</strong></li>
<li>Delete the <code>install</code> directory and all its contents</li>
</ol>
<p>Once you have done those, then you can <a href="../">continue to Timesheet Next Gen</a></p>
<?php 
}
/**
 * display_fatal_error()
 * Displays page when there was a non-recoverable error
 */
function display_fatal_error() {
	global $_ERROR;
?>
<h2>Installation Error</h2>
<p class="error">There has been an error that couldn't be recovered from</p>
<h3 class="error">Error Message</h3>
<p class="error"><?php echo $_ERROR;?></p>
<?php 
}



/**
 * display_upgrade_step_1()
 * Initial page for the upgrade script
 */
function display_upgrade_step_1() {
	global $table_inc_file, $db_inc_file;
	?>
<p>Thank you for downloading Timesheet Next Gen. 
It'll just take a few more minutes to get it installed and working on your system.</p>
<h2>Upgrade</h2>
<p>This script will help you upgrade from version 1.2.0 to the current version <?php echo INSTALLER_VERSION; ?>.</p>
<p>If you are trying to upgrade from a version before 1.2.0, please 
<a href="http://wiki.timesheetng.org/user-docs/update">see our wiki</a> for more details.</p>
<h2>Step One: Configuration Files</h2>
<p>Please confirm that the configuration files <code>database_credentials.inc</code> and 
<code>table_names.inc</code> exist and are both writeable by the webserver.</p>
	<?php 
	if(
		file_exists('../'.$table_inc_file) && is_writable('../'.$table_inc_file) &&
		file_exists('../'.$db_inc_file) && is_writable('../'.$db_inc_file)
		) {
		echo '<p><a href="?step=up-one">Proceed to step 2</a></p>';
	}
	else {
		echo '<p style="color: red">Files do not exist or are not writeable. Please fix and refresh this page</p>';
	}
}
/**
 * display_upgrade_step_2()
 * Step two of the upgrade script
 */
function display_upgrade_step_2() {
	global $_ERROR, $table_inc_file, $db_inc_file;
	include('../'.$db_inc_file);

	if(isset($_REQUEST['db_host'])) { $DATABASE_HOST = $_REQUEST['db_host']; }
	if(isset($_REQUEST['db_name'])) { $DATABASE_DB = $_REQUEST['db_name']; }
	if(isset($_REQUEST['db_user'])) { $DATABASE_USER = $_REQUEST['db_user']; }
	if(isset($_REQUEST['db_pass'])) { $DATABASE_PASS = $_REQUEST['db_pass']; }
	if(isset($_REQUEST['db_pass_func'])) { $DATABASE_PASSWORD_FUNCTION = $_REQUEST['db_pass_func']; }

	// try to work out the prefix
	if(isset($_REQUEST['db_prefix'])) {
		$prefix = $_REQUEST['db_prefix'];
	}
	else {
		include('../'.$table_inc_file);
		$pos = strpos(strtolower($CONFIG_TABLE), 'config');
		$prefix = substr($CONFIG_TABLE, 0, $pos);
	}
	?>
<h2>Upgrade Step Two: Database Configuration</h2>
<form method="post">
<p>Please confirm your database credentials below are correct</p>
<?php if($_ERROR) {?>
<h3 class="error">There was an error</h3>
<p class="error"><?php echo $_ERROR; ?></p>
<?php } ?>
<table border="0">
<tr>
<th>Host</th><td><input type="text" name="db_host" value="<?php echo $DATABASE_HOST; ?>" /></td>
</tr>
<tr>
<th>Database Name</th><td><input type="text" name="db_name" value="<?php echo $DATABASE_DB; ?>" /></td>
<td>Please make sure that this database exists and you have sufficient permissions to create and alter tables</td></tr>
<tr>
<th>Username</th><td><input type="text" name="db_user" value="<?php echo $DATABASE_USER; ?>"/></td>
</tr>
<tr>
<th>Password</th><td><input type="password" name="db_pass" value="" /></td>
</tr>
<tr>
<th>Password Function</th>
<td><select name="db_pass_func">
<option value="SHA1" <?php if ($DATABASE_PASSWORD_FUNCTION == 'SHA1') { echo 'selected="selected"'; } ?>>SHA1</option>
<option value="PASSWORD" <?php if ($DATABASE_PASSWORD_FUNCTION == 'PASSWORD') { echo 'selected="selected"'; } ?>>PASSWORD</option>
<option value="OLD_PASSWORD" <?php if ($DATABASE_PASSWORD_FUNCTION == 'OLD_PASSWORD') { echo 'selected="selected"'; } ?>>OLD PASSWORD</option>
</select></td>
<td>This is the function the database uses to encrypt the passwords. If your MySQL version is 4.1 or above
you should use SHA1. PASSWORD should be used on MySQL version 4.0 or below, and OLD PASSWORD for MySQL
version 4.1 or above where SHA1 is not available.<br /><em>If in doubt, use SHA1.</em></td></tr>
<tr>
<th>Table Prefix</th><td><input type="text" name="db_prefix" value="<?php echo $prefix; ?>" /></td>
<td>This prefix is used for all table names</td>
</tr>
<tr><td colspan="2">
<?php /*<input type="button" value="Test Configuration" onclick="alert('Sorry, this doesn\'t work yet');"/> */ ?>
<input type="submit" value="Proceed to Step Three" />
</td></tr>
</table>
<input type="hidden" name="step" value="up-two" />
</form>
	<?php 
}
/**
 * upgrade_installation()
 * This does the main bulk of the upgrading:
 *  1. Writes the config files
 *  2. upgrades the database
 *  3. reports success or fail
 */
function upgrade_installation() {
	global $_ERROR;

	// get the passed data
	$db_host = (isset($_REQUEST['db_host']) && $_REQUEST['db_host']) ? $_REQUEST['db_host'] : false;
	$db_name = (isset($_REQUEST['db_name']) && $_REQUEST['db_name']) ? $_REQUEST['db_name'] : false;
	//$db_name_exist = (isset($_REQUEST['db_name_exist']) && $_REQUEST['db_name_exist']) ? $_REQUEST['db_name_exist'] : 'yes';
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
		return display_upgrade_step_2();
	}

	// connect to the database
	if(!database_connect($db_host, $db_name, $db_user, $db_pass)) { 
		return display_upgrade_step_2(); 
	}

	// now create the tables
	if(!upgrade_tables($db_prefix, $db_pass_func)) { 
		return display_upgrade_step_2(); 
	}

	// finally write the include files
	if(!create_include_files($db_host, $db_name, $db_user, $db_pass, $db_prefix, $db_pass_func)) {
		return display_fatal_error();
	}
	return display_install_success();
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
	global $_ERROR, $db_pass_func;
	$db_prefix = mysql_real_escape_string($db_prefix);

	$contents = file_get_contents('timesheet.sql.in');
	// finalise the file
	$key_words = array("__TIMESHEET_VERSION__", "__TABLE_PREFIX__", "__DBPASSWORDFUNCTION__");
	$key_values = array(INSTALLER_VERSION, $db_prefix, $db_pass_func);
	$contents = str_replace($key_words, $key_values, $contents);

	$queries = preg_split("/;+(?=([^'|^\\']*['|\\'][^'|^\\']*['|\\'])*[^'|^\\']*[^'|^\\']$)/", $contents);
	foreach ($queries as $sql) {
		if (strlen(trim($sql)) > 0) {
			if(!mysql_query($sql)) {
				$_ERROR .= 'Could not create <strong>Tables</strong><br />';
				$_ERROR .= 'Your query said: '.htmlentities($sql).'<br />';
				$_ERROR .= 'Database said: '.mysql_error().'<br />';
				return false;
			}
		}
	}
	return true;
}
/**
 * create_admin_user()
 * @param $username
 * @param $password
 * @return bool true if admin user was created
 */
function create_admin_user($username, $password) {
	global $_ERROR, $db_inc_file, $table_inc_file;

	include('../'.$db_inc_file);
	include('../'.$table_inc_file);

	// connect to the database
	if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
		return display_fatal_error(); 
	}
	// clean up input
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);

	$sql = 'INSERT INTO '.$USER_TABLE.' (username,level,password,first_name,last_name) 
		VALUES ("'.$username.'",11,'.$DATABASE_PASSWORD_FUNCTION.'("'.$password.'"),"Timesheet","Admin")';
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
/**
 * check_admin_user()
 * Checks to see if an admin user has been created. 
 * @return bool true if created
 */
function check_admin_user() {
	global $_ERROR, $db_inc_file, $table_inc_file;

	include('../'.$db_inc_file);
	include('../'.$table_inc_file);

	if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
		return display_fatal_error(); 
	}

	$sql = 'SELECT COUNT(*) FROM '.$USER_TABLE.' WHERE level>10';
	$result = mysql_query($sql);
	if(!$result) {
		$_ERROR .= 'Could not check admin user<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';
		return false;
	}
	$row = mysql_fetch_row($result);
	return $row[0];
}
/**
 * get_database_version()
 * Check the status in the DB 
 * @return version number
 */
function get_database_version($cfg_table) {
	global $_ERROR;

	$sql = 'SELECT version FROM '.$cfg_table.' WHERE config_set_id=\'1\'';
	$result = mysql_query($sql);
	if(!$result) {
		$_ERROR .= 'Could not check version<br />';
		$_ERROR .= 'Database said: '.mysql_error().'<br />';
		return false;
	}
	$row = mysql_fetch_row($result);

	// hack for V1.3.1
	if ($row[0]=='__timesheet_VERSION__')
		$row[0] = '1.3.1';

	return $row[0];
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

/**
 * create_include_files()
 * Writes the configuration to the include files 
 * @param $db_host
 * @param $db_name
 * @param $db_user
 * @param $db_pass
 * @param $db_prefix
 * @param $db_pass_func
 * @return bool true if successfully written
 */
function create_include_files($db_host, $db_name, $db_user, $db_pass, $db_prefix, $db_pass_func) {
	global $_ERROR, $table_inc_file, $db_inc_file;
	// make sure the values are safe
	$db_host = mysql_real_escape_string($db_host);
	$db_name = mysql_real_escape_string($db_name);
	$db_user = mysql_real_escape_string($db_user);
	$db_pass = mysql_real_escape_string($db_pass);
	$db_prefix = mysql_real_escape_string($db_prefix);
	$db_pass_func = mysql_real_escape_string($db_pass_func);

	// first write the database credentials: read in current file
	$contents = file_get_contents($db_inc_file.'.in');
	// edit the file
	$contents = str_replace("__INSTALLED__", 1, $contents);
	$contents = str_replace("__VERSION__", INSTALLER_VERSION, $contents);
	$contents = str_replace("__DBHOST__", $db_host, $contents);
	$contents = str_replace("__DBUSER__", $db_user, $contents);
	$contents = str_replace("__DBPASS__", $db_pass, $contents);
	$contents = str_replace("__DBNAME__", $db_name, $contents);
	$contents = str_replace("__DBPASSWORDFUNCTION__", $db_pass_func, $contents);
	// re-write it
	if (!$handle = fopen('../'.$db_inc_file, 'w')) {
		$_ERROR .= 'Could not open <code>'.$db_inc_file.'</code> file for writing';
		return false;
    }
    if (fwrite($handle, $contents) === FALSE) {
		$_ERROR .= 'Could not write to <code>'.$db_inc_file.'</code>';
		return false;
    }
    fclose($handle);

	// now the table names
	$contents = file_get_contents($table_inc_file.'.in');
	$contents = str_replace("__TABLE_PREFIX__", $db_prefix, $contents);
	if (!$handle = fopen('../'.$table_inc_file, 'w')) {
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

/* Database Upgrade */
function upgrade_tables($db_prefix, $db_pass_func) {
	global $_ERROR;
	$result = true;
	$db_version = get_database_version($db_prefix.'config');

	switch ($db_version) {
	case '1.2.0' :
		$result &= run_sql_script($db_prefix, $db_pass_func, 'timesheet_upgrade_to_1.2.1.sql.in');
	case '1.2.1' :
		$result &= run_sql_script($db_prefix, $db_pass_func, 'timesheet_upgrade_to_1.3.1.sql.in');
	case '1.3.1' :
		$result &= run_sql_script($db_prefix, $db_pass_func, 'timesheet_upgrade_to_1.4.1.sql.in');
	}
	//Upgrade the DB version
	$sql = 'UPDATE '.$db_prefix.'config set version=\''.INSTALLER_VERSION.'\';';
	if(!mysql_query($sql)) {
		$_ERROR .= '<strong>Could not update DB version</strong><br />';
		$_ERROR .= 'Your query said:   '.htmlentities($sql).'<br />';
		$_ERROR .= 'Our database said: '.mysql_error().'<br />';
		return false;
	}
	return $result;
}
function run_sql_script($db_prefix, $db_pass_func, $sqlfile) {
	global $_ERROR;
	$db_prefix = mysql_real_escape_string($db_prefix);

	$contents = file_get_contents($sqlfile);
	// finalise the script
	$key_words = array("__TIMESHEET_VERSION__", "__TABLE_PREFIX__", "__DBPASSWORDFUNCTION__");
	$key_values = array(INSTALLER_VERSION, $db_prefix, $db_pass_func);
	$contents = str_replace($key_words, $key_values, $contents);

	$queries = preg_split("/;+(?=([^'|^\\']*['|\\'][^'|^\\']*['|\\'])*[^'|^\\']*[^'|^\\']$)/", $contents);
	foreach ($queries as $sql) {
		if (strlen(trim($sql)) > 0) {
			if(!mysql_query($sql)) {
				$_ERROR .= '<strong>Could not complete script</strong><br />';
				$_ERROR .= 'Your query said:   '.htmlentities($sql).'<br />';
				$_ERROR .= 'Our database said: '.mysql_error().'<br />';
				return false;
			}
		}
	}
	return true;
}
