<?php

/**
 * Define shorthand for the directory separator
 */
define('DS', DIRECTORY_SEPARATOR);
/**
 * Path to the main directories: 
 *  - application
 *    - modules
 *  - assets
 *  - themes
 */
$application_path = 'application';
$assets_path = 'assets';
$themes_path = 'themes';


/**
 * Timesheet Next Gen required at least PHP 5.2
 */
if (version_compare(PHP_VERSION, '5.2', '<')) {
	die ('Timesheet Next Gen requires at least PHP version 5.2, you are currently running PHP '.PHP_VERSION);
}

/**
 * We want Timesheet Next Gen to display errors if necessary
 */  
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);


/**
 * The extension for PHP files. By default it is ".php" but this can be
 * changed to ".php5" ".phtml" etc.
 */
define('EXT', '.php');


/* --== No need to edit below this point ==-- */

/**
 * Based on the paths entered above, discover and define their
 * full path values
 * 
 * Note: this was heavily inspired by the Kohana PHP framework
 */
$pathinfo = pathinfo(__FILE__);
// Define the front controller name and docroot
define('DOCROOT', $pathinfo['dirname'].DS);
$application_path = file_exists($application_path) ? $application_path : DOCROOT.$application_path;
$assets_path = file_exists($assets_path) ? $assets_path : DOCROOT.$assets_path;
$themes_path = file_exists($themes_path) ? $themes_path : DOCROOT.$themes_path;
define('APPLICATION', str_replace('\\', '/', realpath($application_path)).'/');
define('ASSETS', str_replace('\\', '/', realpath($assets_path)).'/');
define('THEMES', str_replace('\\', '/', realpath($themes_path)).'/');
define('MODULES', APPLICATION.'modules'.DS);
unset($application_path, $assets_path, $themes_path);

// if the install file exists, user has not installed Timesheet Next Gen
if (file_exists(DOCROOT.'install'.EXT)) {
	include DOCROOT.'install'.EXT;
}
else {
	require(MODULES.'core'.DS.'init'.EXT);
}
