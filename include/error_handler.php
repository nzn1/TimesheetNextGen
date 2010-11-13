<?php

class ErrorHandler
{
	
	
    private $debug;
    
    private static $originalErrorHandler;
    
    private static $returnException = false;
    
    public function __construct()
    {
        self::setErrorHandler();
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this,'shutdown'));
        self::configureErrorReporting();
    }
    
    public static function setErrorHandler(){
    	self::$originalErrorHandler = set_error_handler(array('ErrorHandler', 'handleError'));
    	print_r(self::$originalErrorHandler);
    }
    
    public static function restoreErrorHandler(){
    	//set_error_handler(self::$originalErrorHandler);
    	restore_error_handler();    	
    }
    
	/**
	 * 
	 * Configure which errors should be reported
	 */
	public function configureErrorReporting(){
		/** DEBUG CONSTANTS
		 define('E_ERROR',1);
		 define('E_WARNING',2);
		 define('E_PARSE',4);
		 define('E_NOTICE',8);
		 define('E_CORE_ERROR',16);
		 define('E_CORE_WARNING',32);
		 define('E_COMPILE_ERROR',64);
		 define('E_COMPILE_WARNING',128);
		 define('E_USER_ERROR',256);
		 define('E_USER_WARNING',512);
		 define('E_USER_NOTICE',1024);
		 define('E_STRICT',2048);
		 define('E_RECOVERABLE_ERROR',4096);
		 define('E_DEPRECATED',8192);
		 define('E_USER_DEPRECATED',16384);
		 define('E_ALL',30719);
		 */
			
		error_reporting(-1);
		//error_reporting(E_ALL);
	}



	public function exceptionHandler(Exception $e){
		ErrorHandler::fatalError(ppr($e,'exception',true),null,"Uncaught Exception Error");
    }
	    

	public static function handleError2($errorNo, $errorString, $errorFile, $errorLine){
		// if error has been supressed with an @
	    if (error_reporting() == 0) {
	        return;
	    }
	    
		$exception=new CustomException($errorString, $errorNo);
	    $exception->setLine($errorLine);
	    $exception->setFile($errorFile);

	    echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
	    
	    throw $exception;
		
	}
	/**
	 * 
	 * Enter description here ...
	 * @todo Implement: Store the date/time of the error
     * @todo Implement: Use more than one method of storing error logs (database, file, etc.)
     * @todo Implement: Email severe warnings/errors to yourself so you may deal with any critical problems as soon as possible
     * @todo Implement: Use different error handling functions for different error levels if necessary
     * @todo Implement: Create a new file log daily so that your log file doesn't balloon
	 * 
	 * @param unknown_type $errorNo
	 * @param unknown_type $errorString
	 * @param unknown_type $errorFile
	 * @param unknown_type $errorLine
	 */
    public static function handleError($errorNo, $errorString, $errorFile, $errorLine)
    {	

	    // if error has been supressed with an @
	    if (error_reporting() == 0) {
	        return;
	    }


	    if(self::$returnException == true){
	    	$exception=new CustomException($errorString, $errorNo);
		    $exception->setLine($errorLine);
		    $exception->setFile($errorFile);
		
		    //echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
		    
		    throw $exception;
		    return;
	    }
    	/**
    	 * Known error on x64 laptop-r620 setup:
    	 * 
    	 * E_WARNING [Type: 2] [Line: 36] [File: C:\htdocs\uybb\include\database.class.php]
		 * mysql_connect() [function.mysql-connect]: Headers and client library minor version mismatch. Headers:60000 Library:50151
		 *
		 * This isn't acutally a problem, but does need looking at eventually.
		 * This if statement below just handles it and ignores it.
    	 */
    	if($errorNo == E_WARNING && $errorLine == 66 
        	&& $errorFile == 'C:\htdocs\uybb\include\database.class.php')
        	return;
    	
    	switch ($errorNo)
        {
        	case E_ERROR:
        		echo "oh shite";
        		exit;
        	
        	case E_USER_ERROR:
            	if(debug::getErrors()){
            		echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
	                    exit;
            	}
            	else{
            		echo 'Sadly an error has occured!';
	                    exit;
            	}	
            
            case E_USER_WARNING:
	            echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
	            break;
            case E_USER_NOTICE:
	            echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
	            break;
            default:
            	echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
            	break;
        }
    }
    

    private static function getErrorName($errorNo){
        $errorType = array (
               E_ERROR            => 'E_ERROR',
               E_WARNING        => 'E_WARNING',
               E_PARSE          => 'E_PARSE',
               E_NOTICE         => 'E_NOTICE',
               E_CORE_ERROR     => 'E_CORE ERROR',
               E_CORE_WARNING   => 'E_CORE WARNING',
               E_COMPILE_ERROR  => 'E_COMPILE ERROR',
               E_COMPILE_WARNING => 'E_COMPILE WARNING',
               E_USER_ERROR     => 'E_USER ERROR',
               E_USER_WARNING   => 'E_USER WARNING',
               E_USER_NOTICE    => 'E_USER NOTICE',
               E_STRICT         => 'E_STRICT NOTICE',
               E_RECOVERABLE_ERROR  => 'E_RECOVERABLE ERROR'
               );

    // create error message
    if (array_key_exists($errorNo, $errorType)) {
        $err = $errorType[$errorNo];
    } else {
        $err = 'CAUGHT EXCEPTION';
    }
    return $err;
    	
    	
    }
    /**
     * 
     * This is the function that is called when the script finishes execution
     * 
     * Its primary purpose is to catch and log fatal errors
     */
    public function shutdown() {
		 if (($error = error_get_last())) {
		   
		 switch ($error['type'])
        {
            case E_ERROR:
				$msg = "We just caught a fatal error in our website.  It has been logged and we will get it fixed as soon as possible.<br />"
		   		."The operation you just attempted may or may not have succeeded.<br />"
		   		."We apologise for any inconvenience.<br /><br />"
		   		."<a href=\"".Config::getRelativeRoot()."\">Return to our Home Page</a>";
		   
		   		$msg .= "<h2>Implement Error Logging</h2>";
		   		$msg .= ppr($error,'Error',true);
		   		$msg .= ppr(debug_print_backtrace(),'Error',true);
		   		//there is not point on outputting a debug trace as it reveals nothing 
		   		//from the shutdown() function.
		   				   
		   		ErrorHandler::fatalError($msg,null,"Oh Dear");
		   		exit;  

            default:
				echo "<pre><b>Shutdown Error Caught:</b>";
            	echo "<pre><b>".self::getErrorName($error['type'])."</b> [Type: ".$error['type']."] [Line: ".$error['line']."] [File: ".$error['file']."]<br/>".$error['message']."<br /></pre>";
            	break;
        }
           			 	
//		 	ob_clean();
		   # raport the event, send email etc.
		   //header("Location: http://localhost/error-capture");
		  # from /error-capture, you can use another redirect, to e.g. home page
		   

		  }
	}
	

	/**
	 * fatalError() - when something serious goes wrong.  This function is called.
	 * It displays a boring page stating the error that occured.
	 *
	 * @param $msg - the message to display
	 * @param $title - the title of the page
	 * @param $heading - the level 1 heading of the error page
	 */
	public static function fatalError($msg,$title='Fatal Error',$heading='Error'){
		ob_end_clean();
		if($msg==''){
			$msg = 'An error has occured.';
		}
		if($title == '' || $title == null){
			$title = 'Fatal Error';
		}
		if($heading == '' || $heading == null){
			$heading = 'Error';
		}

		echo"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n"
		."\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
		."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">";
		echo"<head>
			<title>".$title."</title>
			</head>";
		echo"<body bgcolor=\"black\" text=\"white\" link=\"white\" vlink=\"white\" alink=\"white\">
			<h1>".$heading."</h1>";
		echo"<p>";
		echo $msg;
		echo"<br />
			</p>
			</body>
			</html>";
		exit;
	}	
	
	
	public static function setReturnException(){
		self::$returnException = true;		
	}
	
	public static function clearReturnException(){
		self::$returnException = false;
	}

} 



class CustomException extends Exception {
    public function setLine($line) { 
        $this->line=$line;
    }
    
    public function setFile($file) {
        $this->file=$file;
    }
} 
?>