<?php
require_once('site.class.php');

class InstallSite extends Site{

	private $page;
	/**
	 * 
	 * Constructor for the Site Class.
	 * This is the first function to be called in the website
	 */
	public function __construct(){
		if(ini_get('short_open_tag')==0){
			die('PHP short tags are currently disabled.  This site won\'t work without short tags enabled');
		}
	
		if(!isset($_GET['page'])){
			$this->page = 'index';		
		}
		else{
			$this->page = $_GET['page'];
		}
		
		require('include/debug.class.php');
		require('include/common_functions.php');
		self::$timeStart = getmicrotime();
				
		require('include/error_handler.php');
		new ErrorHandler();		

		require('include/config.class.php');
		
		Config::initialise();
		Config::setInstaller(true);
				
		//the main database class is required to ensure the constants can be found.
		require("include/database.class.php");
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
		
		self::startTemplating();
		
	}
	
	private function startTemplating(){
		ob_start();
		require('include/templateparser/install.templateparser.class.php');
		$tp = new InstallTemplateParser();
		
    	$tp->getPageElements()->addFile('content','install/'.$this->page);

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
		$timeDiff = round($timeEnd - self::$timeStart, 4);
		if(debug::getPageLoadTime()>=1)echo "<pre>Processing Time: $timeDiff s</pre>";

		$tp->finishFile();
		
	}
}