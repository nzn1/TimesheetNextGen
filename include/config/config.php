<?php 
/**
* This file will be automatically generated by an install script
*
* ...or at least it will be when i've got round to it
* 
* This file is called immediately after the Config::initialise()
* command in index.php in the root directory.
* 
*/


parent::$webmasterEmail = 'myaddress@tnsg.net';

//parent::$defaultTemplate = 'themes/txsheet/template.php';

//parent::$mainTitle = 'TimesheetNG';

/**
 * Configure the database credentials.
 */  

//parent::$dbServer 		= "localhost";
//parent::$dbUser 		= "username";
//parent::$dbPass 		= "password";
//parent::$dbName 		= "my_tx_db";
//parent::$dbPasswordFunction	= "PASSWORD";

/**
 * for now, get the info from the database_credentials.inc 
 */  
if(file_exists("database_credentials.inc")){
	include("database_credentials.inc");
	parent::$dbServer = $DATABASE_HOST;
	parent::$dbUser = $DATABASE_USER;
	parent::$dbPass = $DATABASE_PASS;
	parent::$dbName = $DATABASE_DB;	
	parent::$dbPasswordFunction = $DATABASE_PASSWORD_FUNCTION;
}
else{
	trigger_error('database_credentials.inc could not be found');
}

//parent::$sessionName = 'a-session-name';

//parent::$overrideWorkOutWhereMySiteIs = false;
//parent::$relativeRoot = '/tx';
//parent::$documentRoot = 'c:/htdocs/tx';
//parent::$absoluteRoot = 'http://localhost/tx';
?>
