<?php
/**
 * *****************************************************************************
 * Name:			rewrite.php
 * Recommended Location:	/include/
 * Last Updated:		June 2010
 * Author:			Mark Wrightson
 * Contact:			mark@voltnet.co.uk
 *
 * Description:
 * URL rewrite.  Quick Fix for Compila Mod rewrite bug
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/


class Rewrite {

	/**
	 * the url with all GET variables stripped off
	 */
	private static $shortUri = null;
	/**
	 * the requested uri of the site.
	 * @var string
	 */
	private static $uri = null;
	/**
	 * the page that will be displayed in the content pane
	 * @var string
	 */
	private static $content = null;
	/**
	 *
	 * the name of the module being used
	 * @var string
	 */
	private static $module = null;


	public static function __init(){
		if(debug::getRewrite())
			echo"<hr /><pre>Rewrite::__construct()</pre>";
		if(debug::getRewrite())
			echo"<p><strong>--START OF REWRITE DATA--</strong></p><hr />";
		self::checkRewriteModule();
		self::phpModRewrite();         //rewrite url here instead of mod rewrite
		self::rewriteRequestUri();
		if(debug::getRewrite())
			echo"<p><strong>--END OF REWRITE DATA--</strong></p><hr />";


	}

	/**
	 * phpModRewrite() - rewrites the received url into the form used by
	 * this web application
	 *
	 */
	private static function phpModRewrite(){
		if(debug::getRewrite())
			echo"<hr /><pre>phpModRewrite()</pre>";
		//get the URI to work with
		$uri = $_SERVER['REQUEST_URI'];
		if(debug::getRewrite())
			echo"<pre>Get the uri to work with</pre>";
		if(debug::getRewrite()==1)
			echo "<pre>REQUEST_URI: ".$uri."</pre>";

		//Remove script path:
		$uri = substr($uri, strlen(Config::getRelativeRoot())+1);
		if(debug::getRewrite()==1)
			echo "<pre>Removed Config::relativeRoot from uri. uri='".$uri."'</pre>";

		//clear out GET.  We will re-generate this
		$_GET = null;

		//Explode path to directories and remove empty items:
		$uri_explode = explode('?', $uri,2);
		//check for a slash on the end and remove it.
		//this prevents a .co.uk// url from thinking it is a module
		if($uri_explode['0'] != '' && $uri_explode['0'][strlen($uri_explode['0'])-1]=="/") {
			$uri_explode['0'] = substr_replace($uri_explode['0'],"",-1);
		}
		if(debug::getRewrite()){
			echo"<pre>Explode the path to directories and remove empty items.<br />"
			."uri_explose[0] is the page ref.<br />"
			."uri_explode[1 to n] is the arguments:</pre>";
		}
		//uri_explode[0] is the page ref
		//uri_explode[1 to n] is the arguments
		if(debug::getRewrite()==1)
			ppr($uri_explode,'uri_explode');

		//get all the get variables and repopulate the $_GET
		isset($uri_explode['1'])?parse_str($uri_explode['1'],$i):$i=array();
		$_GET = $i;
		//populate the c variable
		$content = $uri_explode[0];
		if(debug::getRewrite())
			echo"<pre>Parse the string to extract all of the get variables</pre>";
		if(debug::getRewrite()==1)
			ppr($_GET,'GET updated');
		//used for making self generating links relative to current page without ?id=1... tags
		$_SERVER['PHP_SELF'] = self::$shortUri = Config::getRelativeRoot()."/".$uri_explode[0];

		//check for a URL that uses the id tag.
		$id_explode = explode('/id/', $uri_explode[0]);
		if(debug::getRewrite())echo"<pre>Check for a special seo url that containss '/id/ i.e.page/id/3
		and add the argument to the \$_GET[] array</pre>";

		if(debug::getRewrite()==1)
			ppr($id_explode,'check for /id/');
		if(isset($id_explode[1])){
			$content = $id_explode[0];
			$_GET['id'] = $id_explode[1];
		}

//		//check for a URL that uses the section tag
//		$section_explode = explode('/section/', $uri_explode[0]);
//		if(debug::getRewrite()==1)
//			ppr($section_explode,'check for /section/');
//		if(isset($section_explode[1])){
//			$_GET['c'] = $section_explode[0];
//			$_GET['section'] = $section_explode[1];
//		}

		//check for a URL that uses the user tag
//		$user_explode = explode('/user/', $uri_explode[0]);
//		if(debug::getRewrite()==1)
//			ppr($user_explode,'check for /user/');
//		if(isset($user_explode[1])){
//			$_GET['c'] = $user_explode[0];
//			$_GET['user'] = $user_explode[1];
//		}

 		/**
		 * if $content is blank then set the page as the homepage
		 */
		if(!isset($content)||$content==""){
			$content='content/index';
		}
	 	/**
		 * to prevent recursion (index.php accessing index.php)
		 * detect for this and redirect to default homepage.
		 */
		if($content=='index'||$content=='index.php'){
			//$content='content/index';
			Header( "HTTP/1.1 301 Moved Permanently" );
			gotoLocation(Config::getRelativeRoot());
			exit;
		}
		if($content=='install.php'){
				if(!isset($_GET['page'])){
					$content = 'install/index';
				} else{
					$content = 'install/'.$_GET['page'];
				}
		}
		//strip the last slash off it is present.  (fixes a mod_rewrite issue)
		if(file_exists($content)){
			if($content[strlen($content)-1] != "/")
				$content = $content."/";
		} else{
			if($content[strlen($content)-1]=="/")
				$content = substr_replace($content,"",-1);
		}
		self::$content = $content;
		if(debug::getRewrite())
			echo"<p><strong>The page that will be retrieved is: '".$content."'</strong></p>";
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
	private static function rewriteRequestUri(){
		if(debug::getRewrite())
			echo"<hr /><pre>rewriteRequestUri()</pre>";

		if(!isset($get))
			$get = array();
		$uri = $_SERVER['REQUEST_URI'];
		//Explode path to directories and variables:
		$uri_explode = explode('?', $uri,2);
		if(debug::getRewrite()){
			echo"<pre>Explode the path to directories and remove empty items.<br />"
			."uri_explose[0] is the page ref.<br />"
			."uri_explode[1 to n] is the arguments:</pre>";
		}
		if(debug::getRewrite()==1)
			ppr($uri_explode,'uri_explode');

		//uri_explode[0] is the page ref
		//uri_explode[1 to n] is the arguments
		//ensure uri has a / on the end.  this is required for consistency.
		//this will ensure that the regex's for the menu's work correctly
		if($uri_explode['0'][strlen($uri_explode['0'])-1]!="/")
			$uri_explode['0'] = $uri_explode['0']."/";

		isset($uri_explode['1'])?parse_str($uri_explode['1'],$get):$i=array();

		if(debug::getRewrite()==1)
			ppr($get,'the extracted get arguments');
		if(isset($get['response']))
			unset($get['response']);        //remove the response element

		//reconstruct the get part of the uri
		$var = null;
		$first = true;
		foreach($get as $key=>$value){
			if($first)
				$var .= $key."=".$value;
			else
				$var .= "&".$key."=".$value;
			$first = false;
		}
		$get = $var;

		//reconstruct the uri
		if($get == '')
			$_SERVER['REQUEST_URI'] = $uri_explode['0'];
		else
			$_SERVER['REQUEST_URI'] = $uri_explode['0']."?".$get;
		self::$uri = $_SERVER['REQUEST_URI'];

		if(debug::getRewrite()==1)
			echo "<pre>The modified request_uri with key arguments removed: ".$_SERVER['REQUEST_URI']."</pre>";
	}

	const SQL_ERROR = -1;
	const MODULE_NOT_REGISTERED = 0;	//Module isn't registered
	const NOT_A_MODULE = 1;				//Not a module
	const MODULE_DISABLED = 2;			//Module InActive / Module Disabled
	const MODULE_ACTIVE = 3;			//Module Confirmed & Active

	/**
	 *
	 * @return -1 - SQL_ERROR
	 *          0 - MODULE_NOT_REGISTERED
	 *          1 - NOT_A_MODULE
	 *          2 - MODULE_DISABLED
	 *          3 - MODULE_ACTIVE
	 */
	public static function checkModule(){
		if(debug::getRewrite()==1)
			echo"<pre>check_module()</pre>";
		$module_dir = explode('/', self::$content,2);
		if(debug::getRewrite()==1)
			ppr($module_dir,'check module');
		if ($module_dir[0]=="modules"){
			$i=1;
			$temp = $module_dir[1];
			$module_dir = explode('/', self::$content,3);
			if(debug::getRewrite()==1)
				ppr($module_dir,'Module Requested');
			self::$content = $temp;

			//a module has been requested directly through the modules uri.
			//i.e. http://www.voltnet.co.uk/modules/admin/index
		} else
			$i=0;

		$q = "SELECT * FROM `_modules` WHERE `dirname` LIKE '".$module_dir[$i]."' LIMIT 0 , 1";
		if(debug::getSqlStatement() || debug::getRewrite())
			echo "<pre>".$q."</pre>";
		$result = Database::getInstance()->query($q);


		if($result == false){
			//Error occurred, output sql error
			if(debug::getSqlError() || debug::getRewrite())
				Debug::ppr(mysql_error(),'sqlError');
			return self::SQL_ERROR;
		} else
			$num_rows = mysql_num_rows($result);

		if(!$result || ($num_rows <= 0)){
			if(file_exists( Config::getDocumentRoot() . "/modules/".$module_dir[$i])){
				if(debug::getRewrite()==1)
					echo "error: module not registered";
				self::$module = $module_dir[$i];
				self::$content = "modules/not_registered";
				return self::MODULE_NOT_REGISTERED;            //module not registered
			} else {
				if(debug::getRewrite()==1)
					echo 'this is not a module';
				return self::NOT_A_MODULE;
			}

		}
		//module is registered

		$module = mysql_fetch_array($result, MYSQL_ASSOC);

		//check to see if a module is active or not
		if($module['isactive']==0){
			if(debug::getRewrite()==1)
				echo "error: module isn't active";
			self::$module = $module_dir[$i];
			self::$content = "modules/not_active";
			return self::MODULE_DISABLED;
		} elseif(!file_exists( Config::getDocumentRoot() . "/modules/".$module_dir[0])){
			trigger_error('a module was listed in the database but hasn\'t been found');
			if(debug::getRewrite()==1)
				echo 'this is not a module';
			return self::NOT_A_MODULE;
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
	public static function checkRewriteModule(){
		if(debug::getRewrite())
			echo"<hr /><pre>checkRewriteModule()</pre>";
		if(!function_exists('apache_get_modules')){
			if(debug::getRewrite())
				echo"<pre>The function apache_get_modules()"
				."doesn't exist so we can only assume that mod_rewrite is enabled</pre>";
			//if here then apache_get_modules cannot be used
			//we can therefore only assume that mod_rewrite is enabled
			return true;
		}
		if (in_array("mod_rewrite", apache_get_modules())) {
			if(debug::getRewrite())
				echo"<pre>Apache module: mod_rewrite loaded correctly</pre>";
			//echo "mod_rewrite loaded";
			return true;
		} else {
			//echo "mod_rewrite not loaded";
			$msg = 'This website requires the Apache module mod rewrite to operate correctly';
			ErrorHandler::fatalError($msg,'Mod Rewrite Error','Mod Rewrite Error');
			exit();
		}
	}

	/**
	 *
	 * the requested uri of the site.
	 */
	public static function getUri(){
		return self::$uri;
	}

	/**
	 *
	 * the url with all GET variables stripped off
	 */
	public static function getShortUri(){
		return self::$shortUri;
	}

	/**
	 *
	 * the page that will be displayed in the content pane
	 */
	public static function getContent(){
		return self::$content;
	}

	/**
	 *
	 * set the page that will be displayed in the content pane
	 * @param unknown_type $s
	 */
	public static function setContent($s){
		self::$content = $s;
	}

	/**
	 *
	 * Retrieve the name of the module currently being accessed
	 */
	public static function getModule(){
		return self::$module;
	}
}
