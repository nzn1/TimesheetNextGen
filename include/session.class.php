<?php
/**
 * *****************************************************************************
 * Name:                    session.php
 * Recommended Location:    /include
 * Last Updated:            July 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The Session class is meant to simplify the task of keeping
 * track of logged in users and also guests.
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

class Session
{

	private $time;                     //Time user was last active (page loaded)
	private $loggedIn;                //True if user is logged in, false otherwise

	/* Class constructor */
	public function Session(){
		if (!defined("SESSION_INCLUDED")) {
			define("SESSION_INCLUDED", 1);
		}
	}
	
	public function getTime(){
		return $this->time;
	}

	/**
	 * init
	 *
	 * init was created because subclass call Site::session->...
	 * and the $session object only becomes valid when the constructor
	 * has finished and returned.  As a consequence, all items in the Session
	 * constructor have moved into a new init function.
	 *
	 * This function performs all the actions necessary to
	 * initialize this session object. Tries to determine if the
	 * the user has logged in already, and sets the variables
	 * accordingly. Also takes advantage of this page load to
	 * update the active visitors tables.
	 */
	function startSession(){

//		session_name(Config::getSessionName());

		//session_save_path(Config::getDocumentRoot()."/tmp");
		session_start();   //Tell PHP to start the session

		//date_default_timezone_set(Config::getTimeZone());

		/**if get_magic_quotes_gpc is set,
		 * this cause back slashes to be add to all POST, GET and maybe cookies
		 * this causes problems with our implementation.
		 * This command removes the slashes to mimic
		 * get_magic_quotes_gpc off
		 */
		if (get_magic_quotes_gpc()) {
			$_POST = stripslashes_deep($_POST);
			$_GET = stripslashes_deep($_GET);
			$_COOKIE = stripslashes_deep($_COOKIE);
		}

		$this->time = time();
	}
	 
	/**
	 * fatalError() - when something serious goes wrong.  This function is called.
	 * It displays a boring page stating the error that occured.
	 *
	 * @param $msg - the message to display
	 * @param $title - the title of the page
	 * @param $heading - the level 1 heading of the error page
	 */
	
	public function fatalError($msg,$title='Fatal Error',$heading='Error'){
		ErrorHandler::fatalError($msg,$title,$heading);
		exit;
	}

};  //end of session class
?>