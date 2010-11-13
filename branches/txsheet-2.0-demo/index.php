<?php
/*******************************************************************************
 * Name:                    index.php
 * Recommended Location:    /
 * Last Updated:            April 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 *
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/
if (defined('SESSION_INCLUDED')){
	ErrorHandler::fatalError("index.php was called from index.php<br />".
  "Recursive relationships are not allowed<br />
  <p>Return to <a href=\"".Config::getRelativeRoot()."/\">Home Page</a></p>");
}

new Site();

class Site{

	private static $session;
	private static $database;
	private static $rewrite;
	private static $errorHandler;
	private static $authenticationManager;
	private static $commandMenu;

	/**
	 * 
	 * Constructor for the Site Class.
	 * This is the first function to be called in the website
	 */
	public function __construct(){
		if(ini_get('short_open_tag')==0){
			die('PHP short tags are currently disabled.  This site won\'t work without short tags enabled');
		}
		require('include/debug.class.php');
		require('include/common_functions.php');
		$timeStart = getmicrotime();
		
		require('include/error_handler.php');
		self::$errorHandler = new ErrorHandler();		

		require('include/config.class.php');
		Config::initialise();
		include("database_credentials.inc");
		Config::setDbServer($DATABASE_HOST);
		Config::setDbUser($DATABASE_USER);
		Config::setDbPass($DATABASE_PASS);
		Config::setDbName($DATABASE_DB);		

		include("include/tables.class.php");
		
		require('include/session.class.php');
		require('include/templateparser/templateparser.class.php');
		require("include/rewrite.class.php");
		require("include/database.class.php");

		ob_start();
		self::$session = new Session();
		self::$database = new MySQLDB();
		self::$session->startSession();
			
		$tp = new templateParser();
			
//		if(!self::$session->isadmin() && debug::getHideDebugData()==true){
//			debug::hideDebug();
//		}

		self::$rewrite =new Rewrite();

		//check for site shutdown flag
		if(debug::getSiteDown() == 1 && !self::$session->isAdmin()){
			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Status: 503 Service Temporarily Unavailable");
			header("Retry-After: 3600");
			self::$rewrite->setContent('maintenance');
		}
		

		//the default template that will be loaded
		$tp->getPageElements()->addFile('content',self::$rewrite->getContent());
		$tp->getPageElements()->addFile('menu','menu.php');

		//debugInfoTop is exempt from the module config selection
		$tp->getPageElements()->addFile('debugInfoTop','debugInfoTop.php');
		$tp->getPageElements()->addFile('debugInfoBottom','debugInfoBottom.php');
		$tp->getPageElements()->getTagByName('debugInfoTop')->setOutput(ob_get_contents());
		ob_end_clean();
			
		// parse template file
		$tp->parseTemplate();

		// display generated page
		echo $tp->display();

		$timeEnd = getmicrotime();
		$timeDiff = round($timeEnd - $timeStart, 4);
		if(debug::getPageLoadTime()>=1)echo "<pre>Processing Time: $timeDiff s</pre>";

		$tp->finishFile();
	}

	/**
	 * getDatabase() - returns the database object
	 * @param self::$database - database object
	 */
	public static function getDatabase(){
		return self::$database;
	}

	/**
	 * getSession() - returns the session object
	 * @param self::$session - session object
	 */
	public static function getSession(){
		return self::$session;
	}

	/**
	 * 
	 * returns the static Rewrite Class Object
	 */
	public static function getRewrite(){
		return self::$rewrite;
	}
	
	public static function getAuthenticationManager(){
		return self::$authenticationManager;
	}
	public static function setAuthenticationManager($obj){
		self::$authenticationManager = $obj;
	}

	public static function getCommandMenu(){
		return self::$commandMenu;
	}
	public static function setCommandMenu($obj){
		self::$commandMenu = $obj;
	}
}
?>
