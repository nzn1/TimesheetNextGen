<?php
/**
 * *****************************************************************************
 * Name:                    debug.class.php
 * Recommended Location:    /include
 * Last Updated:            July 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The Debug class is a set of static variables that specify what debug data
 * should be output to the browser.
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

class Debug{

	private static $consoleData = array();
		
	private static $hideDebugData    = 0;        //hide debug data if not logged in

	private static $sendToConsole	   = 0;
	private static $sendToScreen	   = 1;

	private static $location         = 1;        //display location headers
	private static $_sessionTop      = 0;        //display session data at top of page
	private static $session          = 0;        //display session data
	private static $requestUri       = 0;        //display mini session data
	private static $contentSession   = 0;        //display sesion data within content pane
	private static $_files           = 0;
	private static $_post            = 0;        //display POST data
	private static $_get             = 0;        //display GET data
	private static $_server          = 0;        //display server data
	private static $_cookie          = 0;        //display cookie data
	private static $templateTags     = 0;        //display template tags
	private static $errors			     = 1;		 //show error debug data
	
	private static $formFiles        = 0;        //display form file related debug data	
	private static $formRules        = 0;
	private static $formErrors       = 0;
	private static $formDebug        = 0;
	
	private static $sqlStatement     = 0;        //display SQL strings
	private static $sqlError         = 0;        //display SQL errors
	private static $classSubFunction = 0;        //general debug data for a function within a class
	private static $pageLoadTime     = 0;        //show page processing time
	private static $destroySession   = 0;        //show destroy session menu option
	private static $authFull         = 0;        //display user authorisation data detailed
	private static $authBasic        = 0;        //display user authorisation data basic
	private static $rewrite          = 0;        //display php rewrite information
	private static $pprTrace         = 0;        //display the trace log for all ppr commands
	
	private static $language         = 0;

	/*END DEBUG*/

	private static $siteDown         = 0;       //shut down the site for maintenance


	/**
	 *
	 * hide all the debug data.  This allows
	 * debug to be enabled for an admin account on a live site,
	 * whilst keeping the site active for everyone else
	 */
	public static function hideDebug(){
		self::$sendToConsole 	= 0;
		self::$sendToScreen 	= 0;
		
		self::$location         = 0;        //display location headers
		self::$_sessionTop      = 0;        //display session data at top of page
		self::$session          = 0;        //display session data
		self::$contentSession   = 0;        //display sesion data within content pane
		self::$_post            = 0;        //display POST data
		self::$_get             = 0;        //display GET data
		self::$_server          = 0;        //display server data
		self::$_cookie          = 0;        //display cookie data
		self::$templateTags     = 0;        //display template tags
		self::$sqlStatement     = 0;        //display SQL strings
		self::$sqlError         = 0;        //display SQL errors
		self::$classSubFunction = 0;        //general debug data for a function within a class
		self::$pageLoadTime     = 0;        //show page processing time
		self::$destroySession   = 0;        //show destroy session menu option
		self::$authFull         = 0;        //display user authorisation data detailed
		self::$authBasic        = 0;        //display user authorisation data basic
		self::$rewrite          = 0;        //display php rewrite information
		self::$pprTrace         = 0;        //display the trace log for all ppr commands
		self::$errors			= 0;
	}

	
	public static function getConsoleData(){
		return self::$consoleData;
	}
	
	public static function getHideDebugData(){
		return self::$hideDebugData;
	}
	public static function getSendToConsole(){
		return self::$sendToConsole;
	}
	public static function getSendToScreen(){
		return self::$sendToScreen;
	}
	public static function getLocation(){
		return self::$location;
	}
	public static function getSessionTop(){
		return self::$_sessionTop;
	}
	public static function getSession(){
		return self::$session;
	}
	public static function getRequestUri(){
		return self::$requestUri;
	}
	public static function getContentSession(){
		return self::$contentSession;
	}
	public static function getFiles(){
		return self::$_files;
	}
	public static function getPost(){
		return self::$_post;
	}
	public static function getGet(){
		return self::$_get;
	}
	public static function getServer(){
		return self::$_server;
	}
	public static function getCookie(){
		return self::$_cookie;
	}
	public static function getTemplateTags(){
		return self::$templateTags;
	}
	public static function getFormData(){
		ErrorHandler::fatalError("The method getFormData has been deprecated");
		//return self::$formData;
	}
	public static function getFormClassData(){
		ErrorHandler::fatalError("The method getFormClassData has been deprecated");
		//return self::$formClassData;
	}
	public static function getFormFilesData(){
		ErrorHandler::fatalError("The method getFormFiles has been deprecated");
		//return self::$formFilesData;
	}
    public static function getFormRules(){
        return self::$formRules;
    }
    public static function getFormErrors(){
        return self::$formErrors;
    }
    public static function getFormDebug(){
        return self::$formDebug;
    }
    public static function getFormFiles(){
        return self::$formFiles;
    }
        
	public static function getSqlStatement(){
		return self::$sqlStatement;
	}
	public static function getSqlError(){
		return self::$sqlError;
	}
	public static function getClassSubFunction(){
		return self::$classSubFunction;
	}
	public static function getPageLoadTime(){
		return self::$pageLoadTime;
	}
	public static function getDestroySession(){
		return self::$destroySession;
	}
	public static function getAuthFull(){
		return self::$authFull;
	}
	public static function getAuthBasic(){
		return self::$authBasic;
	}
	public static function getRewrite(){
		return self::$rewrite;
	}
	public static function getPprTrace(){
		return self::$pprTrace;
	}
	public static function getSiteDown(){
		return self::$siteDown;
	}
	public static function getErrors(){
		return self::$errors;
	} 
	public static function getLanguage(){
		return self::$language;
	} 
	
/**
 *
 * ppr - print out an object into a neat array list
 * surrounded by pre tags
 * @param $var - the object to be printed
 * @param $group - a reference name for the object to be printed
 */
public static function ppr($var,$group='None',$traceData=null){
	$showTrace = false;
	
	if($traceData==null){		
		$trace = debug_backtrace();
		//ppr($trace);		
		if (isset($trace[2])){
			$t = @array(
		        'file'=>$trace[1]['file'],
		        'line'=>$trace[1]['line'],
		        'function'=>$trace[2]['function'], 
				'class'=>$trace[2]['class']      
				);
		}
	}
	else $t = $traceData;
	$item = new ConsoleItem(print_r($var,true), $group, $t);	
	self::$consoleData[] = $item;
	if(debug::getPprTrace()==1)$showTrace=true;

	if(Debug::getSendToScreen()){
		$i = "<pre>$group: ";
		$i .= print_r($var,true);
		if($showTrace==true)$i .= "ppr Trace: ".getShortDebugTrace();
		$i .= "</pre>";	
		echo $i;
	}
}

public static function log($var,$group){
	$showTrace = false;
	$trace = debug_backtrace();
	//ppr($trace);		
	if (isset($trace[1]) && isset($trace[0])){
		$t = @array(
	        'file'=>$trace[0]['file'],
	        'line'=>$trace[0]['line'],
	        'function'=>$trace[0]['function'], 
			'class'=>$trace[1]['class']      
			);
	}
	$item = new ConsoleItem(print_r($var,true), $group, $t);	
	self::$consoleData[] = $item;	
	
	if(Debug::getSendToScreen()){
		$i = "<pre>$group: ";
		$i .= print_r($var,true);
		if($showTrace==true)$i .= "ppr Trace: ".getShortDebugTrace();
		$i .= "</pre>";	
		echo $i;
	}
}


}

class ConsoleItem{
	private $data;
	private $group;
	private $trace;
	
	public function __construct($data,$group,array $trace){
		$this->data = $data;
		$this->group = $group;
		$this->trace = arrayToObject($trace);
	}
	public function getData(){
		return $this->data;
	}
	public function getGroup(){
		return $this->group;
	}
	public function getTrace(){
		return $this->trace;
	}
}















