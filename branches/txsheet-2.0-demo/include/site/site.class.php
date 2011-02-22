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

class Site{

	private static $session;
	protected static $database;
	private static $rewrite;
	private static $errorHandler;
	private static $authenticationManager;
	private static $commandMenu;
	
	protected static $timeStart;

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
		
		require('include/session.class.php');
		require_once('include/auth/auth.class.php');
		require('include/templateparser/templateparser.class.php');
		require("include/rewrite.class.php");
		require("include/database.class.php");
		
		ob_start();
		self::$session = new Session();
		self::$database = new MySQLDB();
		
		try{
			self::$database->connect();
		}
		catch (Exception $e){
			
			//if the database fails to connect then either it isn't installed
			//or the db server has died
			//or a config file has changed

			if(true == Config::isInstalled()){
				//the site (according to config has already been installed
				$this->dbError($e);
				exit();
				
			}
			else{
				//the site hasn't been installed yet!
				Config::setInstaller(true);
								
				echo "<div class=\"errorbox\">".$e."</div>";
				//as the site is not installed, redirect to the install page
				gotoLocation(Config::getRelativeRoot()."/install.php?page=install");
			}
			
		}
				
		Config::getDbConfig();
		self::$session->startSession();

		require_once("common.class.php");
		new Common();
		require("class.AuthenticationManager.php");
		self::$authenticationManager = new AuthenticationManager();

		require("class.CommandMenu.php");		
		self::$commandMenu = new CommandMenu();
					
		require("globals.class.php");
		gbl::initialize();

		self::$rewrite =new Rewrite();
		//check for installed modules
		$module = self::$rewrite->checkModule();
	
		//check for site shutdown flag
		if(debug::getSiteDown() == 1 && !self::$session->isAdmin()){
			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Status: 503 Service Temporarily Unavailable");
			header("Retry-After: 3600");
			self::$rewrite->setContent('maintenance');
      		$module = Rewrite::MODULE_NOT_REGISTERED;
		}

		$tp = new templateParser();

		$filename = Config::getDocumentRoot()."/modules/".self::$rewrite->getModule()."/config.php";

		if($module == Rewrite::MODULE_ACTIVE && file_exists($filename)){
			/*if a module is detected then we need to load the module config
			 * to determine what files need to be loaded
			 */
			include($filename);
		}
		else{

    		$tp->getPageElements()->addFile('content',self::$rewrite->getContent());
		    $tp->getPageElements()->addFile('menu','themes/txsheet/menu.php');
  		  	$tp->getPageElements()->addFile('tsx_footer','themes/txsheet/footer.inc');
	    	$tp->getPageElements()->addFile('tsx_banner','themes/txsheet/banner.inc');
		}				

		//debugInfoTop is exempt from the module config selection
		$tp->getPageElements()->addFile('debugInfoTop','debugInfoTop.php');
		$tp->getPageElements()->addFile('debugInfoBottom','debugInfoBottom.php');
    	//$tp->getPageElements()->addFile('console','include/console/console.php');		
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
