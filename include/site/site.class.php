<?php
/*******************************************************************************
 * Name:			index.php
 * Recommended Location:	/
 * Last Updated:		April 2010
 * Author:			Mark Wrightson
 * Contact:			mark@voltnet.co.uk
 *
 * Description:
 *
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

class Site {

	private static $session;
	protected static $timeStart;
	private static $installer;
	private static $authenticationManager;
	private static $commandMenu;
	private static $language;


	/**
	 *
	 * Constructor for the Site Class.
	 * This is the first function to be called in the website
	 */
	public function __construct() {
		if(ini_get('short_open_tag')==0) {
			die('PHP short tags are currently disabled.  This site won\'t work without short tags enabled.<br />
				Please modify your php.ini file to include the line "short_open_tag = On"');
		}

		self::load();
	}

	public function setInstallMode() {
		self::$installer = true;
	}

	public function load() {
		if (!class_exists('JLoader')) {
			require_once 'include/loader.php';
		}

		require('include/debug.class.php');
		require('include/common_functions.php');
		$timeStart = getmicrotime();

		require('include/errorhandler.class.php');
		new ErrorHandler();

		require('include/config/config.class.php');
		Config::initialise();

		require('include/session.class.php');
		require_once('include/auth/auth.class.php');
		require('include/templateparser/templateparser.class.php');
		require("include/rewrite.class.php");
		require("include/database.class.php");

		ob_start();
		self::$session = new Session();
		$database = Database::getInstance();

		try {
			$database->connect();
		}
		catch (Exception $e) {

			//if the database fails to connect then either it isn't installed
			//or the db server has died
			//or a config file has changed

			if(true == Config::isInstalled()) {
				//the site (according to config) has already been installed
				Database::getInstance()->dbError($e);
				exit();

			} else if(self::isInstaller()) {

			} else {
				//the site hasn't been installed yet!
				echo "<div class=\"errorbox\">".$e->getMessage()."</div>";

				echo "<p>The database could not connect, and the site does not appear to be installed.<br />
				Load the site installer:</p>";
				//as the site is not installed, redirect to the install page
				gotoLocation(Config::getRelativeRoot()."/install.php?page=install");
			}

		}

		#if(Database::getInstance()->isConnected())
		Config::getDbConfig();
		self::$session->startSession();

		if(!self::$session->isadmin() && debug::getHideDebugData()==true) {
			debug::hideDebug();
		}


		require_once("include/tsx/common.class.php");
		new Common();

		require("include/tsx/authenticationManager.class.php");
		self::$authenticationManager = new AuthenticationManager();


		require("include/tsx/commandmenu.class.php");
		self::$commandMenu = new CommandMenu();

		require("include/tsx/globals.class.php");
		gbl::initialize();

		Rewrite::__init();
		//check for installed modules
		$module = Rewrite::checkModule();


		// Jclasses for international language support
		jimport('jclasses.factory');
		jimport('jclasses.text');
		jimport('jclasses.path');
		jimport('jclasses.folder');
		jimport('jclasses.file');

		self::$language = JFactory::getLanguage();

		//check for site shutdown flag
		//if(debug::getSiteDown() == 1 && !self::$session->isAdmin()) {
		if(debug::getSiteDown() == 1 && Auth::ACCESS_GRANTED != Auth::requestAuth('maintenance','login')) {
			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Status: 503 Service Temporarily Unavailable");
			header("Retry-After: 3600");
			Rewrite::setContent('maintenance');
			$module = Rewrite::MODULE_NOT_REGISTERED;
		}

		if(Common::get_post_max_size() < 32768) { 
			Rewrite::setContent('max_post_size_too_small');
		}

		$tp = new TemplateParser();
		//apply the theme configuration
		require_once('themes/'.PageElements::getTheme().'/config.php');


		$filename = Config::getDocumentRoot()."/modules/".Rewrite::getModule()."/config.php";

		if($module == Rewrite::MODULE_ACTIVE && file_exists($filename)) {
			/*
			 * if a module is detected then we need to load the module config
			 * to determine what files need to be loaded
			 */
			include($filename);
//			ppr($tp->getPageElements());
		}

		PageElements::addElement(new FunctionTag('response','PageElements::createResponseOutput',FunctionTag::TYPE_STATIC));
		//PageElements::addElement(new FunctionTag('googleanalytics','PageElements::getGoogleAnalyticsCode',FunctionTag::TYPE_STATIC));
		//debugInfoTop is exempt from the module config selection
		PageElements::addFile('debugInfoTop','include/debug/debugInfoTop.php');
		PageElements::addFile('debugInfoBottom','include/debug/debugInfoBottom.php');
		PageElements::addFile('console','include/console/console.php');
		PageElements::getTagByName('debugInfoTop')->setOutput(ob_get_contents());
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
	 * getSession() - returns the session object
	 * @return Session
	 */
	public static function getSession() {
		return self::$session;
	}

	public static function getLanguage(){
		return self::$language;
	}

	public static function isInstaller() {
		return self::$installer;
	}

	public static function getAuthenticationManager() {
		return self::$authenticationManager;
	}
	public static function setAuthenticationManager($obj) {
		self::$authenticationManager = $obj;
	}

	public static function getCommandMenu() {
		return self::$commandMenu;
	}
	public static function setCommandMenu($obj) {
		self::$commandMenu = $obj;
	}
}
?>
