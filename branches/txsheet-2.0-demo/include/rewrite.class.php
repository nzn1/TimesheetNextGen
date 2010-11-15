<?php
/**
 * *****************************************************************************
 * Name:                    rewrite.php
 * Recommended Location:    /include/
 * Last Updated:            June 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * URL rewrite.  Quick Fix for Compila Mod rewrite bug
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/


class Rewrite {

	//the url with all GET variables stripped off
	private static $shortUri = null;
	//the requested uri of the site.
	private static $uri = null;
	//the page that will be displayed in the content pane
	private static $content = null;
	//the name of the module being used
	private static $module = null;


	public function __construct(){
		$this->checkRewriteModule();
		$this->phpModRewrite();         //rewrite url here instead of mod rewrite
		$this->rewriteRequestUri();


	}
	/**
	 * phpModRewrite() - rewrites the received url into the form used by
	 * this web application
	 *
	 */
	private function phpModRewrite(){
		//get the URI to work with
		$uri = $_SERVER['REQUEST_URI'];
		if(debug::getRewrite()==1)echo "<pre>REQUEST_URI: ".$uri."</pre>";

		//Remove script path:
		$uri = substr($uri, strlen(Config::getRelativeRoot())+1);
		if(debug::getRewrite()==1)echo "<pre>Removed script path: ".$uri."</pre>";



		if(preg_match("|^index.php|",$uri)){
			//default to home when no page requested
			if(!isset($_GET["c"])||$_GET["c"]=="")$_GET["c"]='content/index';
			return;
		}
		//clear out GET.  We will re-generate this
		$_GET = null;
		 
		//Explode path to directories and remove empty items:
		$uri_explode = explode('?', $uri,2);
		//check for a slash on the end and remove it.
		//this prevents a .co.uk// url from thinking it is a module
		if($uri_explode['0'] != '' && $uri_explode['0'][strlen($uri_explode['0'])-1]=="/") {
			$uri_explode['0'] = substr_replace($uri_explode['0'],"",-1);
		}
		if(debug::getRewrite()==1)ppr($uri_explode,'uri_explode');
		//uri_explode[0] is the page ref
		//uri_explode[1 to n] is the arguments


		//get all the get variables and repopulate the $_GET
		isset($uri_explode['1'])?parse_str($uri_explode['1'],$i):$i=array();
		$_GET = $i;
		//populate the c variable
		$_GET['c'] =  $uri_explode[0];
		if(debug::getRewrite()==1)ppr($_GET,'GET updated');
		//used for making self generating links relative to current page without ?id=1... tags
		$_SERVER['PHP_SELF'] = self::$shortUri = Config::getRelativeRoot()."/".$uri_explode[0];

		//check for a URL that uses the id tag.
		$id_explode = explode('/id/', $uri_explode[0]);
		if(debug::getRewrite()==1)ppr($id_explode,'check for /id/');
		if(isset($id_explode[1])){
			$_GET['c'] = $id_explode[0];
			$_GET['id'] = $id_explode[1];
		}

//		//check for a URL that uses the section tag
//		$section_explode = explode('/section/', $uri_explode[0]);
//		if(debug::getRewrite()==1)ppr($section_explode,'check for /section/');
//		if(isset($section_explode[1])){
//			$_GET['c'] = $section_explode[0];
//			$_GET['section'] = $section_explode[1];
//		}

		//check for a URL that uses the user tag
//		$user_explode = explode('/user/', $uri_explode[0]);
//		if(debug::getRewrite()==1)ppr($user_explode,'check for /user/');
//		if(isset($user_explode[1])){
//			$_GET['c'] = $user_explode[0];
//			$_GET['user'] = $user_explode[1];
//		}

		//default to home when no page requested
		if(!isset($_GET["c"])||$_GET["c"]=="")$_GET["c"]='content/index';

		//strip the last slash off it is present.  (fixes a mod_rewrite issue)
		if(file_exists($_GET['c'])){
			if($_GET["c"][strlen($_GET["c"])-1] != "/") $_GET["c"] = $_GET["c"]."/";
		}
		else{
			if($_GET["c"][strlen($_GET["c"])-1]=="/") $_GET["c"] = substr_replace($_GET["c"],"",-1);
		}
		self::$content = $_GET['c'];
		unset($_GET['c']);
		return;
	}

	/**
	 * Function Name:   rewriteRequestUri
	 *
	 * Description:
	 * This function modifies the $_SERVER['REQUEST_URI'] element to remove any
	 * unwanted tags i.e. Response tag
	 * This ensures that self generating urls don't do things like:
	 *  -  http://domain/tasks/view/id/8?&response=127?&response=127?&response=127
	 */
	private function rewriteRequestUri(){
		if(debug::getRewrite()==1)echo"<pre>rewrite_request_uri()</pre>";

		if(!isset($get))$get = array();
		$uri = $_SERVER['REQUEST_URI'];
		//Explode path to directories and variables:
		$uri_explode = explode('?', $uri,2);
		if(debug::getRewrite()==1)ppr($uri_explode,'split uri at ?');

		//uri_explode[0] is the page ref
		//uri_explode[1 to n] is the arguments
		//ensure uri has a / on the end.  this is required for consistency.
		//this will ensure that the regex's for the menu's work correctly
		if($uri_explode['0'][strlen($uri_explode['0'])-1]!="/") $uri_explode['0'] = $uri_explode['0']."/";
		 
		isset($uri_explode['1'])?parse_str($uri_explode['1'],$get):$i=array();

		if(debug::getRewrite()==1)ppr($get);
		if(isset($get['response']))unset($get['response']);        //remove the response element


		//reconstruct the get part of the uri
		$var = null;
		$first = true;
		foreach($get as $key=>$value){
			if($first)$var .= $key."=".$value;
			else $var .= "&".$key."=".$value;
			$first = false;
		}
		$get = $var;

		//reconstruct the uri
		if($get == '') $_SERVER['REQUEST_URI'] = $uri_explode['0'];
		else $_SERVER['REQUEST_URI'] = $uri_explode['0']."?".$get;
		self::$uri = $_SERVER['REQUEST_URI'];
		if(debug::getRewrite()==1)echo "<pre>".$_SERVER['REQUEST_URI']."</pre>";
	}
	/**
	 * checkDir() -
	 *
	 */
	public function checkDir(){
		//NOTE THIS IS RELATIVE TO THE CURRENT DIRECTORY i.e. /INDEX.PHP
		if(debug::getRewrite()==1)ppr(self::$content,'check dir');
		$ext = substr(strrchr(self::$content, '.'), 1);
		if(strlen($ext)==3){
			return;     //found file extension so cannot be directory.  so dont append /index.php
		}
		if(file_exists(self::$content)){
			self::$content = self::$content."/index.php";
		}
		return;
	}

	
	
	/*-1 - SQL Error
		 *   0 - Module not Registered
		 *   1 - Not a module
		 *   2 - Module InActive / Module Disabled
		 *   3 - Module Confirmed & Active
		 */
		  
	const SQL_ERROR = -1;
	const MODULE_NOT_REGISTERED = 0;
	const NOT_A_MODULE = 1;
	const MODULE_DISABLED = 2;
	const MODULE_ACTIVE = 3;
		 
	/**
	 *
	 * @return -1 - SQL_ERROR
	 *          0 - MODULE_NOT_REGISTERED
	 *          1 - NOT_A_MODULE
	 *          2 - MODULE_DISABLED
	 *          3 - MODULE_ACTIVE
	 */
	function checkModule(){
		if(debug::getRewrite()==1)echo"<pre>check_module()</pre>";
		$module_dir = explode('/', self::$content,2);
		if(debug::getRewrite()==1)ppr($module_dir,'check module');
		if ($module_dir[0]=="modules"){
			$i=1;
			$temp = $module_dir[1];
			$module_dir = explode('/', self::$content,3);
			if(debug::getRewrite()==1)ppr($module_dir,'Module Requested');
			self::$content = $temp;

			//a module has been requested directly through the modules uri.
			//i.e. http://www.voltnet.co.uk/modules/admin/index
		}
		else $i=0;

		$q = "SELECT * FROM `_modules` WHERE `dirname` LIKE '".$module_dir[$i]."' LIMIT 0 , 1";
		if(debug::getSqlStatement() || debug::getRewrite())echo "<pre>".$q."</pre>";
		$result = Site::getDatabase()->query($q);


		if($result == false){
			//Error occurred, output sql error
			if(debug::getSqlError() || debug::getRewrite())echo "<br /><pre>".mysql_error()."</pre>";
			return self::SQL_ERROR;
		}
		else $num_rows = mysql_num_rows($result);

		if(!$result || ($num_rows <= 0)){
			if(file_exists( Config::getDocumentRoot() . "/modules/".$module_dir[$i])){
				if(debug::getRewrite()==1)echo "error: module not registered";
				self::$module = $module_dir[$i];
				self::$content = "modules/not_registered";
				return self::MODULE_NOT_REGISTERED;            //module not registered
			}
			else {
				if(debug::getRewrite()==1) echo 'this is not a module';
				return self::NOT_A_MODULE;
			}

		}
		//module is registered

		$module = mysql_fetch_array($result, MYSQL_ASSOC);

		//check to see if a module is active or not
		if($module['isactive']==0){
			if(debug::getRewrite()==1)echo "error: module isn't active";
			self::$module = $module_dir[$i];
			self::$content = "modules/not_active";
			return self::MODULE_DISABLED;
		}
		self::$content = "modules/".self::$content;   //direct to modules directory
		self::$module = $module_dir[0];
		return self::MODULE_ACTIVE;
	}

	/**
	 *  checkRewriteModule() - determine whether the mod_rewrite
	 *  module has been loaded into the apache web server
	 *
	 */
	function checkRewriteModule(){
		if(!function_exists('apache_get_modules')){
			//if here then apache_get_modules cannot be used
			//we can therefore only assume that mod_rewrite is enabled
			return true;
		}
		if (in_array("mod_rewrite", apache_get_modules())) {
			//echo "mod_rewrite loaded";
			return true;
		}
		else {
			//echo "mod_rewrite not loaded";
			$msg = 'This website requires the Apache module mod rewrite to operate correctly';
			ErrorHandler::fatalError($msg,'Mod Rewrite Error','Mod Rewrite Error');
			exit();
		}
	}

	public static function getUri(){
		return self::$uri;
	}
	public static function getShortUri(){
		return self::$shortUri;
	}
	public static function getContent(){
		return self::$content;
	}
	public static function setContent($s){
		self::$content = $s;
	}
	public static function getModule(){
		return self::$module;
	}
}