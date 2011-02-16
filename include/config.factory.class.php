<?
/**
 * *****************************************************************************
 * Name:                    config.factory.class.php
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

require('include/tables.class.php');

/**
 *
 * The configuration of the website
 * @author Mark
 *
 */
class ConfigFactory{

	/**
	 * This is the current version of the code.
	 * 
	 */
	private static $version = '1.5.3'; 
	
	/**
	 * 
	 * This is the version stored in the database config.
	 * $version and $databaseVersion can be compared to ensure that
	 * the code and the database are uptodate.
	 * @var unknown_type
	 */
	protected static $databaseVersion;
	
	
	/**
	 * This variable is set in config.php to 
	 * say that the site has already been installed
	 * successfully.
	 */
	protected static $isInstalled = false;
	
	/**
	 *
	 * determine whether config has been initialised as it is
	 * a static class and is therefore never instantiated
	 * @var unknown_type
	 */
	protected static $initialised = false;

	/**
	 *
	 * This is the default page template
	 */
	protected static $defaultTemplate = 'themes/txsheet/template.php';

	/**
	 * The default main title of a webpage to be placed in
	 * the <title> </title> tags
	 */
	protected static $mainTitle = 'TimesheetNG 2.0';
		
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
	protected static $error404 = 'error/404.php';

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
	protected static $webmasterEmail = 'webmaster@mysite.com';	

	/**
	 ******************
	 * 
	 * Database Configuration
	 * 
	 * 
	 ******************/
	/**
	 * The Database server name
	 */
	protected static $dbServer = "localhost";
	/**
	 * The Database username
	 */
	protected static $dbUser = "root";
	/**
	 * The Database user password
	 */
	protected static $dbPass = "root";
	/**
	 * The Database table name
	 */
	protected static $dbName = "core";

	
	
	/**
	 * the name of the session
	 */
	protected static $sessionName = 'timesheetng';
	
	/**
	 * Special Names and Level Constants - the admin
	 * page will only be accessible to the user with
	 * the admin name and also to those users at the
	 * admin user level. Feel free to change the names
	 * and level constants as you see fit, you may
	 * also add additional level specifications.
	 * Levels must be digits between 0-9.
	 */

	protected static $timeZone = 'Europe/London';
	
	/**
	 * the absoluteRoot is the root url of the website
	 * i.e. http://www.voltnet.co.uk/minisite/;
	 */
	protected static $absoluteRoot;
	/**
	 * the root path of the website. i.e. /minisite
	 * for most sites this will be ''
	 */
	protected static $relativeRoot;
	/**
	 * the document root is the filesystem path to the website directory
	 * i.e. /home/mark4703/public_html/minisite
	 */
	protected static $documentRoot;

	/**
	 * initialise the config class
	 */
	protected static function initialise(){
		if(self::$initialised) return;
		self::$initialised = true;

		self::workOutWhereMySiteIs();		
		self::testDirectoryVariables();			
	}
	
	/**
	 * 
	 * an override flag for when workOutWhereMySiteIs() throws an error and has
	 * to be configured manually
	 * @var unknown_type
	 */
	protected static $overrideWorkOutWhereMySiteIs = false;

	
	protected static function workOutWhereMySiteIs(){

		/**
		 * If this function throws an error, then set self::$overrideWorkOutWhereMySiteIs
		 * to true and specify the parameters in here manually
		 */
		if(self::$overrideWorkOutWhereMySiteIs){

			
			if(self::$relativeRoot == '' || is_null(self::$relativeRoot)){
				ErrorHandler::fatalError('Config::overrideWorkOutWhereMySiteIs is set to true and relative Root is blank');	
			}
			if(self::$documentRoot == '' || is_null(self::$documentRoot)){
				ErrorHandler::fatalError('Config::overrideWorkOutWhereMySiteIs is set to true and document Root is blank');	
			}
			if(self::$absoluteRoot == '' || is_null(self::$absoluteRoot)){
				ErrorHandler::fatalError('Config::overrideWorkOutWhereMySiteIs is set to true and document Root is blank');	
			}
						
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
				self::$absoluteRoot = "http://".$_SERVER['HTTP_HOST'].self::getRelativeRoot();
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
	protected static function testDirectoryVariables(){
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
	
	private static $installer = false;
	public static function setInstaller($var){
		self::$installer = $var;	
	}
	public static function getInstaller(){
		return self::$installer;
	}
	public static function runVersionCheck(){
		if(self::$installer == true){
			return;
		}
		
		if(self::$databaseVersion != self::$version){
			gotoLocation(Config::getRelativeRoot()."/install.php?page=upgrade");
		}
	}

	public static function getVersion(){
		return self::$version; 
	}
	
	public static function getDatabaseVersion(){
		return self::$databaseVersion;
	}
	
	public static function isInstalled(){
		return self::$isInstalled;
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
	public static function getSessionName(){
		return self::$sessionName;
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
	public static function getWebmasterEmail($encoded=true){
		if($encoded==true){
			return encodeEmail(self::$webmasterEmail);
		}
		else{
			return self::$webmasterEmail;
		}
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
	public static function getError404(){
		return self::$error404;
	}

	/**
	 *
	 */
	public static function getDbServer(){
		return self::$dbServer;
	}
	/**
	 *
	 */
	public static function getDbUser(){
		return self::$dbUser;
	}
	/**
	 *
	 */
	public static function getDbPass(){
		return self::$dbPass;
	}
	/**
	 *
	 */
	public static function getDbName(){
		return self::$dbName;
	}
	/**
	 *
	 */
	public static function getTimeZone(){
		return self::$timeZone;
	}


}//end config class

?>