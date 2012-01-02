<?php
/*******************************************************************************
 * Name:                    templateparser.datastructure.class.php
 * Recommended Location:    /include
 * Last Updated:            July 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * Template Parser Datastructure
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

require_once('tags/filetag.class.php');
require_once('tags/functiontag.class.php');
require_once('tags/stringtag.class.php');
/**
 * @todo This class is almost completely static.  Only the constructor remains. AbstractTemplateParser also calls the constructor.
 * Enter description here ...
 * @author Mark
 *
 */
class PageElements{
	/**
	 * $elements - an array of instances of the class Tag
	 */
	private static $elements;
	/**
	 * $pageAuth - the main page authorisation
	 */
	private static $pageAuth = array();
	/**
	 * $template - path of the template file
	 */
	private static $template;	
	private static $theme;	
	private static $error404;
	private static $deniedAuth = 'include/auth/denied-auth.php';
	private static $unknownAuth = 'include/auth/unknown-auth.php';

	/**
	 *
	 * This datastructure contains all the data required to build the page
	 * 
	 * @todo - this is now basically a static class but it still needs
	 * to be instantiated to add in a few StringTag Elements.
	 * This needs to be thought through a little more
	 */
	public function __construct(){
		self::$elements = array();
		self::addElement(new StringTag('head'));
		self::addElement(new FunctionTag('onload','PageElements::createBodyOnLoadOutput',FunctionTag::TYPE_STATIC));
		self::setTemplate(Config::getDefaultTemplate());
		self::setTheme(Config::getDefaultTheme());
		
	}
	/**
	 *
	 * This function, primarily used in index.php is used to add files
	 * along with their respective tags into the datastructure.
	 * These files will be parsed to build different elements of a page
	 * @param string $name
	 * @param string $file
	 */
	public static function addFile($name, $file){
		$match = false;
		
		//search through array to make sure that the tag doesn't already exist.
		//if it exists then overwrite it
		foreach (self::$elements as $key=>$obj){
			/* @var $obj Tag */
			if($obj->getName() == $name){
				
				if(!($obj instanceof FileTag)){
					ErrorHandler::fatalError('Tag type cannot be changed to file type');
				}
				
				if($obj->getOutput() != null){
          //the tag being replaced has already been parsed!
          //so lets just ignore the action for now.
          
          //TODO handle tags being changed and re add them to the parsing queue.
          $match = true;
				  break;
        }
        else{
				self::$elements[$key] = new FileTag($name,$file);				
				$match = true;
				break;
				}
			}
		}
		if(false == $match){
			self::$elements[] = new FileTag($name,$file);
		}
	}
	/**
	 *
	 * The add function adds a new tag without any additional parameters
	 * @param string $name
	 */
	public static function add($name){
		trigger_error('function deprecated - PageElements::add');
		return false;
		//self::$elements[] = new Tag($name);
	}

	public static function addElement(Tag $tag){
		$match = false;		
		//search through array to make sure that the tag doesn't already exist.
		//if it exists then overwrite it
		foreach (self::$elements as $key=>$obj){
			/* @var $obj Tag */
			if($obj->getName() == $tag->getName()){
				//trigger_error('this tag name ('.$tag->getName().') already exists in the datastructure');
				
				//TODO handle this correctly as it may not actually be an error.
				//i.e. when a template is changed, and a tag is readded, it is technically not an error
				$match = true;
				break;
			}
		}
		if(false == $match){
			self::$elements[] = $tag;			
		}
	}
	/**
	 *
	 * Retrieve the array of tags
	 */
	public static function getTags(){
		return self::$elements;
	}
	/**
	 *
	 * Retrieves a specific tag from it's array key id
	 * @param int $id
	 */
	public static function getTagById($id){
		if(array_key_exists($id,self::$elements)){
			return self::$elements[$id];
		}
	}
	/**
	 *
	 * Retrieves a specific tag from its name
	 * @param string $name
	 * 
	 * @return Tag
	 */
	public static function getTagByName($name){
		foreach(self::$elements as $e){
			/* @var $e Tag */
			if($e->getName() == $name){
				return $e;
			}
		}
		return null;
	}

	/**
	 *
	 * Retrieve the Page Authorisation Privilege
	 * 
	 * @return stdClass - two object variables:
	 * authGroup - the privilege group
	 * authName - the privilege Name
	 */
	public static function getPageAuth(){
		return self::$pageAuth;
	}
	/**
	 *
	 * This function sets the main privilege name for the page
	 * that is being viewed.
	 * @param string $s
	 */
	public static function setPageAuth(stdClass $s){
		self::$pageAuth = $s;
		
		
		if(!($s instanceof stdClass)){
			trigger_error('setPageAuth argument must be an array');
		}
		else{
			self::$pageAuth = $s;
		}
	}

	/**
	 *
	 * Retrieve the <head></head> portion of the page to
	 * be displayed.  This content will fill the {head}
	 * tag.
	 */
	public static function getHead(){
		/* @var $tag StringTag */
		$tag = self::getTagByName('head');
		return $tag->getString();
	}
	/**
	 *
	 * Set the content to be placed within the {head}
	 * tag in the template.  Note this will overwrite
	 * any contents already stored in self::$head
	 * @param string $s
	 */
	public static function setHead($s){
		/* @var $tag StringTag */
		$tag = self::getTagByName('head');
		$tag->setString($s);
	}
	
	public static function appendHead($s){

		//self::$head .= $s;
		/* @var $tag StringTag */
		$tag = self::getTagByName('head');
		$tag->appendString($s);
	}

	
	/**
	 *
	 * retrieve the contents of the Body On Load
	 * section of the <body> tag
	 */
	public static function getBodyOnLoad(){
		/* @var $tag StringTag */
		$tag = self::getTagByName('onload');
		return $tag->getOutput();
	}
	/**
	 *
	 * specify something to run when the body is loaded
	 * by the browser
	 *
	 * @param string $s
	 */
	public static function setBodyOnLoad($s){
		/* @var $tag StringTag */
		$tag = self::getTagByName('onload');
		$tag->setOutput($s);
	}

	public static function createResponseOutput(){
		if(isset($_GET['response'])){
			$response = Database::getInstance()->getResponse($_GET['response']);
			echo "<div class=\"response\"><p>$response</p></div>";
		}
	}
	public static function createBodyOnLoadOutput(){

		if(PageElements::getBodyOnload() !=''){
			$ret =  ' onload="'.PageElements::getBodyOnload().'"';
		}
		else $ret = '';
		//clear the output variable
		$tag = self::getTagByName('onload');
		$tag->setOutput('');
		//it will be reloaded when this function returns
		echo $ret;
	}	
	
	public static function getGoogleAnalyticsCode(){
		return Config::getGoogleAnalyticsCode();
	}
	
	/**
	 *
	 * Retrieve the name of the template to be used by the
	 * template parser
	 */
	public static function getTemplate(){
		return self::$template;
	}
	/**
	 *
	 * Specify a different template to be used for this particular page.
	 * This will override the default template.
	 * @param string $s - path to the theme dir
	 */
	public static function setTemplate($s){
		self::$template = $s;
	}
	
	public static function getTheme(){
		return self::$theme;
	}
	/**
	 *
	 * Specify a different theme to be used for this particular page.
	 * This will override the default theme.
	 * @param string $s - path to the theme dir
	 */
	public static function setTheme($s){
		self::$theme = $s;
		//apply the theme configuration
		require('themes/'.self::$theme.'/config.php');
	}	
	
	
	public static function getTemplatePath(){
		$path = 'themes/'.self::$theme.'/'.self::$template;
		return $path;
	}
	
	/**
	 * 
	 * Get the theme path from the relativeRoot of the site.
	 * i.e. /core/themes/voltnet-default
	 * @return string
	 */
	public static function getRelThemePath(){
		$path = Config::getRelativeRoot().'/themes/'.self::$theme;
		return $path;
	}
	
	
	public static function getError404(){	
		return self::$error404;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $file
	 * @param boolean $relToTheme - file relative to theme dir or not
	 */
	public static function setError404($file,$relToTheme=true){
		$s = self::checkFile($file, $relToTheme);
		if(-1 != $s){
			self::$error404 = $s;
		}
	}

	public static function getDeniedAuth(){	
		return self::$deniedAuth;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $file
	 * @param boolean $relToTheme - file relative to theme dir or not
	 */
	public static function setDeniedAuth($file,$relToTheme=true){
		$s = self::checkFile($file, $relToTheme);
		if(-1 != $s){
			self::$deniedAuth = $s;
		}
	}

	public static function getunknownAuth(){	
		return self::$unknownAuth;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $file
	 * @param boolean $relToTheme - file relative to theme dir or not
	 */
	public static function setunknownAuth($file,$relToTheme=true){
		$s = self::checkFile($file, $relToTheme);
		if(-1 != $s){
			self::$unknownAuth = $s;
		}
	}

	
	private static function checkFile($file,$relToTheme){
	if($relToTheme){
			$path = 'themes/'.self::$theme.'/'.$file;
		}
		else{
			$path = $file;
		}
		if(file_exists($path)){
			return $path;
		}
		else{
			trigger_error('Could not set error 404 page as it does not exist');
			return -1;
		}
	}
}
?>