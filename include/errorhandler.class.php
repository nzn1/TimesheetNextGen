<?php

class ErrorHandler{	   
    /**
     * 
     * Flag to determine whether errors should be converted
     * to exceptions and thrown
     * @var unknown_type
     */
    private static $returnException = false;
    
    /**
     * 
     * Initialise the Error handler class.
     */
    public function __construct()
    {
        self::setErrorHandler();
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this,'shutdown'));
        self::configureErrorReporting();
    }
    
    /**
     * 
     * Set the ErrorHandler to the static function ErrorHandler::handleError()
     */
    public static function setErrorHandler(){
    	set_error_handler(array('ErrorHandler', 'handleError'));
    }
    
    /**
     * Restores the previous error handler function (the PHP default handler)
     */
    public static function restoreErrorHandler(){    	
    	restore_error_handler();    	
    }
    
	/**
	 * 
	 * Configure which errors should be reported.
	 * This currently is set to display every error it finds
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


	/**
	 * When an exception occurs that is not caught, this function will be called by PHP
	 * to handle it.
	 * @param Exception $e
	 */
	public function exceptionHandler(Exception $e){
		ErrorHandler::fatalError(ppr($e,'exception',true),null,"Uncaught Exception Error");
    }
	    
	/**
	 * 
	 * This function is responsible for handling any error that occurs in the system.
	 * 
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

		/**
		 * If returnException is set, the convert the error message into an exception
		 */
	    if(self::$returnException == true){
	    	$exception=new CustomException($errorString, $errorNo);
		    $exception->setLine($errorLine);
		    $exception->setFile($errorFile);		
		    //echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";		    
		    throw $exception;
		    return;
	    }

    	/*@todo Integrate the error messages with the debug class*/ 
    	switch ($errorNo)
        {
        	case E_ERROR:
        		//i don't think this can be ever called.
        		//an E_ERROR results in the PHP engine failing and calling the shutdown method
        		echo "oh shite";
        		exit;
        	
        	case E_USER_ERROR:
            	if(debug::getErrors()){
            		ob_start();
            		//echo '<hr /><h2>'.self::getErrorName($errorNo).'</h2>';
            		//echo "<pre><b>".self::getErrorName($errorNo)."</b> [Type: $errorNo] [Line: $errorLine] [File: $errorFile]<br />$errorString<br /></pre>";
					echo '<p><strong>Error Message: '.$errorString.'</strong></p';            	            
					
            		self::showSource($errorFile,$errorLine, 5, 5);
            		self::callStackDump();
					$msg = ob_get_contents();
					ob_end_clean();

					self::fatalError($msg,'E_USER_ERROR','E_USER_ERROR',false,false);
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
    
	/**
	 * 
	 * Converts an integer error code into the equivalent error string
	 *  
	 * @param int $errorNo
	 */
    private static function getErrorName($errorNo){
        $errorType = array (
               E_ERROR          	=> 'E_ERROR',
               E_WARNING        	=> 'E_WARNING',
               E_PARSE          	=> 'E_PARSE',
               E_NOTICE        		=> 'E_NOTICE',
               E_CORE_ERROR     	=> 'E_CORE ERROR',
               E_CORE_WARNING   	=> 'E_CORE WARNING',
               E_COMPILE_ERROR  	=> 'E_COMPILE ERROR',
               E_COMPILE_WARNING	=> 'E_COMPILE WARNING',
               E_USER_ERROR     	=> 'E_USER ERROR',
               E_USER_WARNING   	=> 'E_USER WARNING',
               E_USER_NOTICE    	=> 'E_USER NOTICE',
               E_STRICT         	=> 'E_STRICT NOTICE',
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
     * Its primary purpose is to catch and log fatal errors
     * 
     * Potential Features:
     * raport the event, send email etc.
	 * Redirect to error page header("Location: http://localhost/error-capture");
	 * from /error-capture, you can use another redirect, to e.g. home page	
     */
    public function shutdown() {
    	
    	if(!function_exists('error_get_last')){
    		//The function error_get_last only exists in PHP >= 5.2
    		return;
    	}
		 if (null == ($error = error_get_last())) {
		 	//no error so exit gracefully
		 	return;
		 }

		//If possible, log the error to a database
		$data = new stdClass();
	    $data->error_type = $error['type'];
	    $data->file = $error['file'];
	    $data->line = $error['line'];
	    $data->message = $error['message'];
	    $data->trace = debug_backtrace();
	    $data->var_dump = '';
	    $logErrorResult = self::logErrorToDb($data);
            	
		 //an error occurred so exit with an error message
		 //not sure which error messages can be caught here.
		 switch ($error['type']){
            case E_ERROR:            	
            default:
            	ob_start();
            	?>
            	<p>We just caught a fatal error in our website.<br />
           		The operation you just attempted may or may not have succeeded.<br />
            	We apologise for any inconvenience.<br /><br />
            	<a href="<?php echo Config::getRelativeRoot();?> ">Return to our Home Page</a></p>
            		   		           	
            	<?php                         	
            	if($logErrorResult['status'] == 1){
            		echo "<p>The error was successfully logged</p>";	
            	}
            	else{            		
            		echo "<p>Something serious has gone wrong and we were not able to log it.<br />
            		Please contact the webmaster and inform him of this error: <a href=\"mailto:".Config::getWebmasterEmail()."\">Webmaster Email</a></p>";
            	}
            	
            	if(Debug::getErrors()){
					//echo '<p><strong>Error Message: '.$error['message'].'</strong></p>';
					echo '<p><strong>Error Message:</strong></p>'; 
	            	echo "<pre><b>".self::getErrorName($error['type'])."</b> [Type: ".$error['type']."] [Line: ".$error['line']."] [File: ".$error['file']."]<br/>".$error['message']."<br /></pre>";
	            	//there is not point on outputting a debug trace as it reveals nothing from the shutdown() function.				
					self::showSource($error['file'],$error['line'], 5, 5);
            	}
            	else{
            		echo '<p>Admin: To view more details here, set the errors flag in the debug class</p>';
            	}
				$msg = ob_get_contents();
				ob_end_clean();            		   				   
            	ErrorHandler::fatalError($msg,'Shutdown Error',"Shutdown Error",false,false);
            	exit;
        }
           			 	
	  
	}
	

	/**
	 * fatalError() - when something serious goes wrong.  This function is called.
	 * It displays a boring page stating the error that occured.
	 *
	 * @param $msg - the message to display
	 * @param $title - the title of the page
	 * @param $heading - the level 1 heading of the error page
	 * @param $wrapMsgInPTags - (Default=true) Specify whether the $msg should be wrapped in html paragraph tags.
	 * @param $showDebugInfo - (Default=true) If true, a stack trace and a source coude trace will be 
	 * displayed in the error message for debug purposes.
	 */
	public static function fatalError($msg,$title='Fatal Error',$heading='Error',$wrapMsgInPTags=true,$showDebugInfo=true){
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
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<title><?php echo $title;?></title>
			<style type="text/css">
				p{color:#000000;padding-top:3px;padding-bottom:3px;margin:0;line-height:120%;}
				a,a#visited{text-decoration:none;border:none;}
				.code{padding:5px; margin:5px;background:#f0f0f0;color:black;}
			</style>
		</head>
		<body>
			<h1><?php echo $heading; ?></h1>
			<?php 
		if($wrapMsgInPTags==true)echo"<p>";
		echo $msg;
		if($wrapMsgInPTags==true)echo"<br /></p>";
		
		if($showDebugInfo == true && Debug::getErrors()){
			self::callStackDump();
			$trace = debug_backtrace(true);
			self::showSource($trace[0]['file'],$trace[0]['line'], 5, 5);
		}
		?>
		
		</body>
		</html>
		<?php 
		exit;
	}	
	
	
	/**
	 * Generate a call stack trace
	 */
	public static function callStackDump(){
		$trace = debug_backtrace(true);
		?>
	<p><strong>Call Stack:</strong></p>
	<div class="code">
		<script type="text/javascript">
			function callStackShowHide(id){
				obj = document.getElementById(id);
				var stlSection = obj.style; 
				var isCollapsed = obj.style.display.length; 
				if (isCollapsed) stlSection.display = ''; 
				else stlSection.display = 'none';				
			}		
		</script>
		<?php 
		foreach($trace as $key=>$t){			
			echo "<span>#".$key;
			if(isset($t['file']))echo " ".$t['file'];
			if(isset($t['line']))echo " (Line ".$t['line'].")";
			
			echo": ";
			
			if(isset($t['class']))echo $t['class'].$t['type'];
			echo $t['function']."()</span>";

			echo "<span style=\"float:right;\"><a onclick=\"callStackShowHide('callStack".$key."');\" href=\"#\">Show/Hide</a></span>";					
			echo "<div id=\"callStack".$key."\" style=\"display:none;\">".ppr($trace[$key],'',true)."</div><br />";
		}

		?>
		</div>	
		<?php 		
	}
	
	/**
	* Show source part of the file.
	* Thanks to:
	* http://www.phpkode.com/source/s/error-handler-classes/error-handler-classes/showSource.php
	* 
	* @param string $file Filename
	* @param int $line Line to read
	* @param int $prev How many lines before main line to read
	* @param int $next How many lines after main line to read
	* @return string
	* @package ErrorHandler
	*/
	private static function showSource($file, $line, $prev = 10, $next = 10) {
	    
	    if (!(file_exists($file) && is_file($file))) {
	        echo'not exists';
	    	return trigger_error("showSource() failed, file does not exist `$file`", E_USER_ERROR);
	        return false;
	    }
	    
	    //read code
	    $data = highlight_file($file,true);
	    
	    //separate lines
	    $data  = explode('<br />', $data);
	    $count = count($data) - 1;
	    
	    //count which lines to display
	    $start = $line - $prev;
	    if ($start < 1) {
	        $start = 1;
	    }
	    $end = $line + $next;
	    if ($end > $count) {
	        $end = $count + 1;
	    }
	    
	    //color for numbering lines
	    $highlight_default = ini_get('highlight.default');	   
	    ?>
	    
	    <div class="code">
	    	<p style="display:block; color:black; font-size:0.8em;"><strong>Source File: </strong> <?php echo $file;?> &nbsp; <strong>Line: </strong><?php echo $line;?></p>
	    	<table cellspacing="0" cellpadding="0">
		    	<tr>
		    		<td style="vertical-align: top;">
			    		
			    		<code style="background-color: #FFFFCC; color: #666666;">	    
			    		<?php
						//create all of the line numbers			    		
			    		for ($x = $start; $x <= $end; $x++) {
					        if($line == $x)echo '<font style="background-color: red; color: white;">';
					        echo str_repeat('&nbsp;', (strlen($end) - strlen($x)) + 1).$x;
					        echo '&nbsp;';
					        if($line == $x)echo '</font>';
					        echo '<br />';
					    }
					    ?>
			    		</code>
			    	</td>
			    	<td style="vertical-align: top;">
			    		<code>
						    <?php 
						    //output the source code lines
						    
						    while ($start <= $end) {
						        
						    	echo '&nbsp;' . $data[$start - 1] . '<br />';
						        ++$start;
						    }
				    		?>
			    		</code>
			    	</td>
		    	</tr>
	    	</table><br />
	    </div>	    
	    <?php 
	}

	/**
	 * 
	 * Attempt to save the error to the error logging database
	 * @param stdClass $data
	 */
	private static function logErrorToDb(stdClass $data){
		if(!Database::getInstance()->isConnected())return;
		/*not yet implemented properly*/
		return;
		
		$data->trace = serialize($data->trace);
		
		$data = recursive_mysql_real_escape_string($data);
		
		$data->username = Site::getSession()->getUserInfo()->getUsername();
		$data->ip = $_SERVER['REMOTE_ADDR'];
		$data->timestamp = date('y-m-d H:i:s',time());
		//ppr($data);
		
		$q = "INSERT INTO ".tbl::getErrorLog()." (
		`id` ,
		`error_type` ,
		`timestamp` ,
		`file` ,
		`line` ,
		`message` ,
		`trace` ,
		`var_dump` ,
		`username` ,
		`ip`
		)
		VALUES (
		NULL , '".$data->error_type."','".$data->timestamp."', '".$data->file."', 
		'".$data->line."', '".$data->message."', '".$data->trace."', '".$data->var_dump."' ,
		'".$data->username."', '".$data->ip."');";
		
		if(debug::getSqlStatement()==1)ppr($q,'SQL');
		$retval['status'] = Database::getInstance()->query($q);
		

		if($retval['status'] == false && debug::getSqlError()==1){
			Debug::ppr(mysql_error(),'sqlError');
		}
		return $retval;


	}

	/**
	 * 
	 * This tells the error handler to convert any errors
	 * into exceptions and throw them
	 */
	public static function setReturnException(){
		self::$returnException = true;		
	}
	
	/**
	 * This clears the returnException flag such that
	 * the error handler handles errors in the normal way
	 * rather than throwing Exceptions
	 * 
	 */
	public static function clearReturnException(){
		self::$returnException = false;
	}

} 


/**
 * 
 * A Custom Exception Class that provides setter methods
 * for the line and file variables of the Exception class
 * @author Mark
 *
 */
class CustomException extends Exception {
    public function setLine($line) { 
        $this->line=$line;
    }
    
    public function setFile($file) {
        $this->file=$file;
    }
} 
?>