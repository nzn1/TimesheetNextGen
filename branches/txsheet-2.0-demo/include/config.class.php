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


require('include/config.factory.class.php');

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
	}


}//end config class

?>