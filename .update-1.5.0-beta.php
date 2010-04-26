<?php 
define('INSTALLER_VERSION', '1.5.0');
// set up the global variable that holds any error messages
// don't really like using globals, but this is quick and dirty
// other globals 
$table_inc_file = './table_names.inc';
$db_inc_file = './database_credentials.inc';

if(!file_exists($db_inc_file)) { print "can't include $db_inc_file"; exit; }
if(!file_exists($table_inc_file)) { print "can't include $table_inc_file"; exit; }

include_once($db_inc_file);
include_once($table_inc_file);

// check that Timesheet NG isn't already installed
	switch (check_installed_version()) {
		case 1:
			update_the_database();
			break;
		default:
			print "This helps to upgrade a BETA 1.5.0 database to a newer 1.5.0 revision\n";
			print "This installation is not an installed 1.5.0 system\n";
			exit;
	}

function check_installed_version() {
	global $TIMESHEET_VERSION, $DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS;
	global $CONFIG_TABLE;

	if(version_compare($TIMESHEET_VERSION, INSTALLER_VERSION) == 0) { 
		if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
			print "couldn't connect to the database\n";
			exit;
		}
		$db_version = get_database_version($CONFIG_TABLE);
		if(version_compare($db_version, INSTALLER_VERSION) == 0) { 
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
		print "adding $column to $table...\n";
		$result = mysql_query("ALTER `$table` ADD `$column` $column_attr");
		if(!$result) {
			print "Could not add $column to $table\n";
			print get_db_error(mysql_error())."\n";
			return false;
		}
	}
}

function update_the_database() {
	/*
	ALTER table __TABLE_PREFIX__user  ADD 'session' varchar(32) after uid;

	ALTER table __TABLE_PREFIX__user CHANGE COLUMN status status enum('INACTIVE','ACTIVE') NOT NULL DEFAULT 'ACTIVE';
	UPDATE __TABLE_PREFIX__user SET status='ACTIVE';
	*/
	global $TIMESHEET_VERSION, $DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS;
	global $USER_TABLE;


	if(!database_connect($DATABASE_HOST, $DATABASE_DB, $DATABASE_USER, $DATABASE_PASS)) { 
		print "couldn't connect to the database\n";
		exit;
	}

	add_column_if_not_exist($USER_TABLE, 'session', 'varchar(32) after uid');

	$exists = false;
	$columns = mysql_query("show columns from ".$USER_TABLE);
	while($c = mysql_fetch_assoc($columns)){
		if($c['Field'] == 'status'){
			if(strpos($c['Type'],'ACTIVE') === FALSE){
				print "Changing status field in user table...\n";
				$sql = "ALTER table $USER_TABLE CHANGE COLUMN status status enum('INACTIVE','ACTIVE') NOT NULL DEFAULT 'ACTIVE'";
				$result = mysql_query($sql);
				if(!$result) {
					print "Could not Alter user table\n";
					print get_db_error(mysql_error())."\n";
					return false;
				}

				print "Setting status='ACTIVE' in user table...\n";
				$sql = "UPDATE $USER_TABLE SET status='ACTIVE'";
				$result = mysql_query($sql);
				if(!$result) {
					print "Could not Update user table\n";
					print get_db_error(mysql_error())."\n";
					return false;
				}
			}
		}
	}
	print "Database updates complete\n";
}


/**
 * get_db_error()
 * get DB error info 
 */
function get_db_error($error,$query='') {
	$estring='';
	if($query) $estring= "Query said: $query\n";
	return  $estring .  "Database said: $error";
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
		print "Could not get version from config table\n";
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
	global $_ERROR;
	$link = @mysql_connect($db_host, $db_user, $db_pass);
	if (!$link) {
		$_ERROR .= "Could not connect to the database\n";
		$_ERROR .= get_db_error(mysql_error())."\n";
		return false;
	}
	if(!mysql_select_db($db_name)) {
		$_ERROR .= "Could not select the database\n";
		$_ERROR .= get_db_error(mysql_error())."\n";
		return false;
	}
	return true;
}

// vim:ai:ts=4:sw=4
