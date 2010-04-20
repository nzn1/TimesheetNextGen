<?php

/**
 * Load in the Timesheet Utility Class. 
 */
require_once(dirname(__FILE__).DS.'models'.DS.'Timesheet_Util'.EXT);

/**
 * Load in the database class
 */

require_once(dirname(__FILE__).DS.'models'.DS.'Database'.EXT);

/**
 * Load in the Timesheet Base Class
 */
require_once(dirname(__FILE__).DS.'models'.DS.'Timesheet_Base'.EXT);

/**
 * Load in the initial setup file
 */
$setup_path = APPLICATION.'setup'.EXT;
if(!file_exists($setup_path)) { die('No setup.php file detected'); }
require_once($setup_path);

/**
 * translate the URL we are currently at into module/controller method
 */
$URL = new Core_URL($_SERVER['REQUEST_URI']);
$URL->translate_url();

// run the controller
$controller_name = $URL->controller;
$method_name = $URL->method;
$Controller = new $controller_name();
$Controller->$method_name();
exit;

/**
 * __autoload()
 * Function for auto loading classes. Checks to see if class exists before
 * loading it in
 *
 * Note:  Exceptions thrown in __autoload function cannot be caught in the
 * catch block and results in a fatal error:
 * http://uk2.php.net/autoload
 *
 * This is obviously a bit of a problem, so my solution was to load in a
 * generic class whos only job it is is to throw an exception?? Not sure if
 * this is the best way?
 *
 * @param string $class_name
 */
function __autoload($class_name = false) {
    global $_PATHS;

    // check we have had a class name passed
    if(!$class_name) {
    	// should throw error, but can't
    	require_once(APPLICATION.'core'.DS.'ExceptionAutoload'.EXT);
    }
    // split into constituent parts
    $parts = split('_', $class_name);
    if($parts[0] == 'Controller') {
    	// convert ModuleName to module-name
    	$module = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $parts[1]));
    	if(isset($parts[2])) {
    		$class = $parts[2];
    	}    	
    	else {
    		$class = $parts[1];
    	}
    	$class_path = MODULES.$module.DS.'controllers'.DS.$class.EXT; 	
    }
    // if not a controller, must be a model
    else {
    	// convert ModuleName to module-name
    	$module = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $parts[0]));
    	$dir = '';
    	if(isset($parts[2])) {
    		$class = $parts[2];
    		$dir = DS.$parts[1];
    	}
    	elseif(isset($parts[1])) {
    		$class = $parts[1];
    	}    	
    	else {
    		$class = $parts[0];
    	}
    	$class_path = MODULES.$module.DS.'models'.$dir.DS.$class.EXT; 
    }
    // check file exists before loading it in
    if(file_exists($class_path)) {
    	require_once($class_path);
    }
    else {
    	global $_NON_EXIST_CLASS_NAME;
    	$_NON_EXIST_CLASS_NAME = $class_name;
    	require_once($_PATHS['classes'].DS.'ExceptionAutoload'.EXT);
    }
}