<?
/**
 * *****************************************************************************
 * Name:                    config.class.php
 * Recommended Location:    /include
 * Last Updated:            Oct 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The config class is responsible for setting up all of the variables required
 * to build the site
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/


require('config.factory.class.php');

/**
 *
 * The configuration of the website
 * @author Mark
 *
 */
class Config extends ConfigFactory{

	/**
	 * initialise the config class
	 */
	public static function initialise(){
		
		if(file_exists('include/config/config.php')){
			include('include/config/config.php');
    	}
    	
		parent::initialise();
		
		//finally initialise the table values
		//this is also really temporary code until the install script process is written
		
		tbl::initialise();
		//the database config can't be initialised until the database has been started.
	}

	
	public static function getDbConfig(){
		
		$q = "SELECT * FROM ".tbl::getNewConfigTable();
		
		$data = Database::getInstance()->sql($q,true, Database::TYPE_OBJECT);
		//ppr($data);
		if($data == Database::SQL_EMPTY || $data == Database::SQL_ERROR){
			//no data was found.  This is a potential problem
			//this means that we are either missing the new config table
			//or there is no data!
			//we best run the version check anyway!
			//ppr('table not found');
			self::runVersionCheck();
			return;
		}

		foreach($data as $obj){		
			if($obj->name == 'version'){
				parent::$databaseVersion = $obj->value;								
			}
			
			//more config variables to be stored into the 
			//config class or config.factory.class
			
			
		}
		//run a version check
		self::runVersionCheck();
		
		
	}

}//end config class

?>