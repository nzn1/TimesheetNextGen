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

/**
 *
 * The configuration of the website
 * @author Mark
 *
 */
class Config{
	/**
	 *
	 * determine whether config has been initialised as it is
	 * a static class and is therefore never instantiated
	 * @var unknown_type
	 */
	private static $initialised = false;

	/**
	 *
	 * This is the default page template
	 */
	private static $defaultTemplate = 'themes/txsheet/template.php';

	/**
	 * The default main title of a webpage to be placed in
	 * the <title> </title> tags
	 */
	private static $mainTitle = 'TimeSheetNg';//Set Default Main <title>
	/**
	* The default doctype for web pages.
	*/
	private static $docType;
		
	/**
	 ******************
	 * 
	 * Error Page Configuration
	 * 
	 * 
	 ******************/
	
	/**
	 * Reference to the 404 Error Page
	 */
	private static $error404 = 'error/404.php';

	/**
	 ******************
	 * 
	 * Email Configuration
	 * 
	 * 
	 ******************/	
	
	/**
	 * the email address of the webmaster
	 * this address should be obfuscated
	 */
	private static $webmasterEmail = 'a@b.com';	

	/**
	 ******************
	 * 
	 * Database Configuration
	 * 
	 * These are currently setup in index.php
	 * 
	 * 
	 ******************/
	/**
	 * The Database server name
	 */
	private static $dbServer;
	/**
	 * The Database username
	 */
	private static $dbUser;
	/**
	 * The Database user password
	 */
	private static $dbPass;
	/**
	 * The Database table name
	 */
	private static $dbName;

	/**
	 * the absoluteRoot is the root url of the website
	 * i.e. http://www.voltnet.co.uk/minisite/;
	 */
	private static $absoluteRoot;
	/**
	 * the root path of the website. i.e. /minisite
	 * for most sites this will be ''
	 */
	private static $relativeRoot;
	/**
	 * the document root is the filesystem path to the website directory
	 * i.e. /home/mark4703/public_html/minisite
	 */
	private static $documentRoot;

	/**
	 * initialise the config class
	 */
	public static function initialise(){


		if(self::$initialised) return;
		self::$initialised = true;
		
		self::workOutWhereMySiteIs();
		
		self::testDirectoryVariables();
	
		self::$docType = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n"
		."\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
		."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">";
			
	}
	

	/**
	 * 
	 * an override flag for when workOutWhereMySiteIs() throws an error and has
	 * to be configured manually
	 * @var unknown_type
	 */
	private static $overrideWorkOutWhereMySiteIs = false;
	
	private static function workOutWhereMySiteIs(){
		
		/**
		 * If this function throws an error, then set self::$overrideWorkOutWhereMySiteIs
		 * to true and specify the parameters in here manually
		 */
		if(self::$overrideWorkOutWhereMySiteIs){
			/**
			 * relativeRoot should always have a leading slash and never a trailing slash.
			 * e.g. self::$relativeRoot = '/TimesheetNG';
			 */
			self::$relativeRoot = '/TimesheetNG';
			
			/**
			 * document Root is the full system path to the root directory of the site.
			 * It should not have a trailing slash.
			 * e.g. self::$documentRoot = 'C:/htdocs/TimesheetNG';
			 */
			self::$documentRoot = 'C:/htdocs/TimesheetNG';
			
			/**
			 * This isn't really utilised yet, but you may aswell define it
			 * This should configure itself.
			 */
			self::$absoluteRoot = "http://".$_SERVER['SERVER_NAME'].self::getRelativeRoot();
			
			return;
			
		}

		//set document to current working directory
		//replace backslashes with forward slashes
		//trim trailing forward slash
		self::$documentRoot = rtrim(str_replace('\\', '/', getcwd()),'/');		
		//ppr(self::$documentRoot,'self::documentRoot');

		//create a temporary version of _server['document_root']
		//replace backslashes with forward slashes
		//trim trailing forward slash
		$serverDocumentRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),'/');
		//ppr($serverDocumentRoot,'$_SERVER[\'DOCUMENT_ROOT\']');

		//we now want to subtract serverDocumentRoot from self::documentRoot
		//to find self::relativeRoot

		//find the position in the string which it starts
		$pos = strpos(self::$documentRoot,$serverDocumentRoot);
		if(is_int($pos)){
			//replace the string and save it to relativeRoot
			self::$relativeRoot = substr_replace(self::$documentRoot, '', $pos,strlen($serverDocumentRoot));
//			ppr(self::$relativeRoot,'relativeRoot');
			$errorCode = 0;			
		}
		else{
			$errorCode = 1;	
		}

		//ok so the first attempt at resolving the relativeRoot failed
		//this is probably because $_SERVER['document_root'] and getcwd()
		//don't correlate.  A symlink would cause this to happen.
		
		//so lets try another way to calculate the directory:
		
		if(isset($_SERVER['SCRIPT_FILENAME']) && $errorCode !=0){
	
			//get the script_filename from the server variables
			//replace backslashes with forward slashes
			//trim trailing forward slash
			$scriptFileName = rtrim(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']),'/');
			//ppr($scriptFileName,'scriptFileName');
			
			//the only page this is called from is index.php
			//so strip the index.php and the trailing slash
			$scriptFileName = rtrim(str_replace('index.php', '', $scriptFileName),'/');
			//ppr($scriptFileName,'index.php stripped');

			//create a temporary version of _server['document_root']
			//replace backslashes with forward slashes
			//trim trailing forward slash
			$serverDocumentRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),'/');
			//ppr($serverDocumentRoot,'$_SERVER[\'DOCUMENT_ROOT\']');
	
			//we now want to subtract $serverDocumentRoot from $scriptFileName
			//to find self::relativeRoot
	
			//find the position in the string which it starts
			$pos = strpos($scriptFileName,$serverDocumentRoot);
			if(is_int($pos)){
				//replace the string and save it to relativeRoot
				self::$relativeRoot = substr_replace($scriptFileName, '', $pos,strlen($serverDocumentRoot));
//				ppr(self::$relativeRoot,'relativeRoot');
				$errorCode = 0;
			
			}
			else{
				$errorCode = 2;	
			}
			
		}

		
		if($errorCode == 0){
				/**
				 * @todo check what happens when the server is on a different port
				 */
				self::$absoluteRoot = "http://".$_SERVER['SERVER_NAME'].self::getRelativeRoot();
		}
		
		else if($errorCode == 1 || $errorCode == 2){
				/**
				 * @todo cause a massive error here as the auto sensing has failed.
				 */
				$msg = "<p>The Config Class was unable to determine the directory path"
				." in which your site resides.  Therefore it cannot load correctly."
				."<br />This is because the variables:<br />"
				."SERVER['DOCUMENT_ROOT'] and self::documentRoot don't correlate.<br />"
				."self::documentRoot is calculated using the getcwd() function (get current working directory)"
				." and if these two variables don't match up then the function Config::workOutWhereMySiteIs()"
				."cannot determine the correct value for self::relativeRoot</p>"
				."<p>A plausible reason for this is that you are using a Linux system with a logical link from something like:<br />"
				."/var/www/html/mysite<br />"
				."to:<br />"
				."/home/username/workspace/project_trunk/branches/branch_name</p>"
				."<p>If SERVER['DOCUMENT_ROOT'] and self::documentRoot differ significantly then this may be the case</p>"
				."<br />";
				$msg .= "<h3>Debug Information</h3>";
				$msg .= ppr($serverDocumentRoot,'$_SERVER[\'DOCUMENT_ROOT\']',true);
				$msg.="<pre>This is the absolute path to the root directory of your site.  In apache this is specified in the http.conf file</pre>";
				$msg .="<hr />";
				$msg .= ppr(self::$documentRoot,'self::documentRoot',true);
				$msg.="<pre>self::documentRoot should be the server document root + any subdirectory that your site resides in.<br />"
				." i.e. localhost/folder1/mysite  should give a documentRoot of root_public_html/folder1/mysite</pre>";
				$msg .="<hr />";
				$msg .= ppr(self::$relativeRoot,'self::RelativeRoot',true);
				$msg.="<pre>self::relativeRoot should be the any subdirectory that your site resides relative to the public_html folder path.<br />"
				." i.e. localhost/folder1/mysite  should give a relativeRoot of /folder1/mysite</pre>";
				$msg .="<hr />";
				$msg .="<h3>How can I fix this error?</h3>";
				$msg .="<p>You can specify the parameters yourself<br />"
					."Just set self::overrideWorkOutWhereMySiteIs to true and then setup the values in the first few lines of workOutWhereMySiteIs()</p>";
				//ErrorHandler::fatalError($msg,'Config Failed','Site Configuration Problem',false);
		
		}
		
		
	}
	
	/**
	 * 
	 * Perform a file exists test to check whether the variable
	 * self::$documentRoot is configured correctly.
	 * We will try to find the file include/config.class.php
	 * (i.e. this class)
	 */
	private static function testDirectoryVariables(){
		$path = self::$documentRoot."/include/config.class.php";
		if(file_exists($path)){
			//echo "document root is correct";
		}
		else{
			$msg = "<p>Your value for Config::\$documentRoot is not correct.<br />"
			."We just ran a test to check whether we could find the class:<br />"
			."include/config.class.php and unfortunately it failed.</p>"
			."The file we requested was:<br />"
			."<pre>".$path."</pre>"
			."<p>This means that you need to check your parameters in the config class.<br />"
			."Specifically check the self::\$relativeRoot and self::\$documentRoot variables.</p>";
			
			if(self::$overrideWorkOutWhereMySiteIs){
				$msg .="<p>The parameters have been manually configured as the variable:<br />"
				."self::\$overrideWorkOutWhereMySiteIs has been set.<br />"
				."Please review the variables and try again."							
				."</p>";
			}
			else{
				$msg .="<p>The parameters could not be determined automatically.<br />"
				."Set the variable self::\$overrideWorkOutWhereMySiteIs to true and"
				." setup the variables manually in the function workOutWhereMySiteIs()."							
				."</p>";		
			}
			
			$msg .= "<h3>Debug Information</h3>";
				$msg .= ppr($serverDocumentRoot,'$_SERVER[\'DOCUMENT_ROOT\']',true);
				$msg.="<pre>This is the absolute path to the root directory of your site.  In apache this is specified in the http.conf file</pre>";
				$msg .="<hr />";
				$msg .= ppr(self::$documentRoot,'self::documentRoot',true);
				$msg.="<pre>self::documentRoot should be the server document root + any subdirectory that your site resides in.<br />"
				." i.e. localhost/folder1/mysite  should give a documentRoot of root_public_html/folder1/mysite</pre>";
				$msg .="<hr />";
				$msg .= ppr(self::$relativeRoot,'self::RelativeRoot',true);
				$msg.="<pre>self::relativeRoot should be the any subdirectory that your site resides relative to the public_html folder path.<br />"
				." i.e. localhost/folder1/mysite  should give a relativeRoot of /folder1/mysite</pre>";
				$msg .="<hr />";
				$msg .="<h3>How can I fix this error?</h3>";
				$msg .="<p>You can specify the parameters yourself<br />"
					."Just set self::overrideWorkOutWhereMySiteIs to true and then setup the values in the first few lines of workOutWhereMySiteIs()</p>";
			ErrorHandler::fatalError($msg,'Config Failed','Site Configuration Problem',false);
		}
	}

	/**
	 *
	 */
	public static function getAbsoluteRoot(){
		return self::$absoluteRoot;
	}
	/**
	 *
	 */
	public static function getRelativeRoot(){
		return self::$relativeRoot;
	}
	/**
	 *
	 */
	public static function getDocumentRoot(){
		return self::$documentRoot;
	}

	/**
	 *
	 */
	public static function getDefaultTemplate(){
		return self::$defaultTemplate;
	}
	/**
	 *
	 */
	public static function getWebmasterEmail(){
		return encodeEmail(self::$webmasterEmail);
	}

	/**
	 *
	 */
	public static function getMainTitle(){
		return self::$mainTitle;
	}
	/**
	 *
	 */
	public static function getDocType(){
		return self::$docType;
	}
	/**
	 *
	 */
	public static function getError404(){
		return self::$error404;
	}

	/**
	 *
	 */
	public static function getDbServer(){
		return self::$dbServer;
	}
	public static function setDbServer($i){
		self::$dbServer = $i;
		
	}	
	/**
	 *
	 */
	public static function getDbUser(){
		return self::$dbUser;
	}
	public static function setDbUser($i){
		self::$dbUser = $i;
		
	}
	/**
	 *
	 */
	public static function getDbPass(){
		return self::$dbPass;
	}
	public static function setDbPass($i){
		self::$dbPass = $i;
		
	}
	/**
	 *
	 */
	public static function getDbName(){
		return self::$dbName;
	}
	public static function setDbName($i){
		self::$dbName = $i;
		
	}


}//end config class

?>