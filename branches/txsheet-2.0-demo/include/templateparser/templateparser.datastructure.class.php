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

class PageElements{
	/**
	 * $elements - an array of instances of the class Tag
	 */
	private $elements;
	/**
	 * $pageAuth - the main page authorisation id
	 */
	private static $pageAuth;
	/**
	 * $head - the <head></head> section of a site.
	 * This is defined in most content files
	 */
	private static $head;
	/**
	 * $pageTitle - the <title></title> section of a site.
	 * This is defined in most content files
	 */
	private static $pageTitle;
	/**
	 * $docType
	 */
	private static $docType;
	/**
	 * $bodyOnLoad
	 */
	private static $bodyOnLoad;
	/**
	 * $response
	 */
	private static $response;
	/**
	 * $template - path of the template file
	 */
	private static $template;

	/**
	 *
	 * This datastructure contains all the data required to build the page
	 */
	function __construct(){
		$this->elements = array();
	}
	/**
	 *
	 * This function, primarily used in index.php is used to add files
	 * along with their respective tags into the datastructure.
	 * These files will be parsed to build different elements of a page
	 * @param string $name
	 * @param string $file
	 */
	public function addFile($name, $file){
		$this->elements[] = new Tag($name,$file);
		$this->getTagByName($name)->setType('file');
	}
	/**
	 *
	 * The add function adds a new tag without any additional parameters
	 * @param string $name
	 */
	public function add($name){
		$this->elements[] = new Tag($name);
	}

	/**
	 *
	 * Retrieve the array of tags
	 */
	public function getTags(){
		return $this->elements;
	}
	/**
	 *
	 * Retrieves a specific tag from it's array key id
	 * @param int $id
	 */
	public function getTagById($id){
		if(array_key_exists($id,$this->elements)){
			return $this->elements[$id];
		}
	}
	/**
	 *
	 * Retrieves a specific tag from its name
	 * @param string $name
	 */
	public function getTagByName($name){
		foreach($this->elements as $e){
			if($e->getName() == $name){
				return $e;
			}
		}
		return null;
	}

	/**
	 *
	 * Retrieve the Page Authorisation Privilege Name.
	 * This is the name cross referenced against the field 'privilege'
	 * in the privilege database table
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
	public static function setPageAuth($s){
		self::$pageAuth = $s;
	}
	/**
	 *
	 * Retrieve the <head></head> portion of the page to
	 * be displayed.  This content will fill the {head}
	 * tag.
	 */
	public static function getHead(){
		return self::$head;
	}
	/**
	 *
	 * Set the content to be placed within the {head}
	 * tag in the template.  Note this will overwrite
	 * any contents already stored in self::$head
	 * @param string $s
	 */
	public static function setHead($s){
		self::$head = $s;
	}
	 
	/**
	 *
	 * Retrieve the Doc Type given at the top of the page
	 */
	public static function getDocType(){
		return self::$docType;
	}
	/**
	 *
	 * Specify the Doc Type to be sent to the browser
	 * @param string $s
	 */
	public static function setDocType($s){
		self::$docType = $s;
	}
	/**
	 *
	 * retrieve the contents of the Body On Load
	 * section of the <body> tag
	 */
	public static function getBodyOnLoad(){
		return self::$bodyOnLoad;
	}
	/**
	 *
	 * specify something to run when the body is loaded
	 * by the browser
	 *
	 * @param string $s
	 */
	public static function setBodyOnLoad($s){
		self::$bodyOnLoad = $s;
	}
	/**
	 *
	 * Retrieve the message response to be given to the user
	 * as a result of an action that has occurred on the site.
	 * the response message is typically trigger by
	 * ?respone = 104 or similar in the url
	 */
	public static function getResponse(){
		return self::$response;
	}
	/**
	 *
	 * Set a response to be display as a result of a user action
	 *
	 * @param string $s
	 */
	public static function setResponse($s){
		self::$response = $s;
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
	 * @param string $s - path to the template file
	 */
	public static function setTemplate($s){
		self::$template = $s;
	}

}


/**
 *
 * A tag is an object {head} that is contained in the template file
 * Each tag must be handled by the template parser in some way.
 * @author Mark
 *
 */
class Tag{
	/**
	 * $name - name of the tag
	 */
	private $name;
	/**
	 * $file - path to the file
	 */
	private $file;
	/**
	 * $fileError - any file related error messages
	 * N.B. not really implemented
	 */
	private $fileError = '';
	/**
	 * $output - the html output data for the tag
	 */
	private $output = null;
	/**
	 * $type - the type of the tag
	 * N.B. not really implemented
	 */
	private $type;

	/**
	 * __construct() - class constructor
	 *
	 * @param $name - name of the tag
	 * @param $file - name of the file to be parsed
	 */
	function __construct($name,$file=null){
		$this->name = $name;
		$this->file = $file;
	}

	/**
	 * parseFile() - parse the file stored in $this->file and append the output
	 * to the output variable;
	 */
	public function parseFile(){
		ob_start();
		if(file_exists($this->file)){
			include($this->file);
			$content=ob_get_contents();
			ob_end_clean();
			$this->appendOutput($content);
		}
		else{
			$this->appendOutput($this->file);
		}
	}

	/**
	 * getName() - get the name of the tag
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * getFile() - get the filename
	 */
	public function getFile(){
		return $this->file;
	}
	/**
	 * setFile() - set the filename
	 */
	public function setFile($s){
		$this->file = $s;
	}
	/**
	 * getFileError() - the any file related error messages.
	 * N.B. not really implemented
	 */
	public function getFileError(){
		return $this->fileError;
	}
	/**
	 * setFileError() - record any error messages
	 * N.B. not really implemented
	 *
	 *@param $s - string containing the error msg
	 */
	public function setFileError($s){
		$this->fileError = $s;
	}
	/**
	 * getOutput() - get the output data for this tag
	 */
	public function getOutput(){
		return $this->output;
	}
	/**
	 * setOutput() - set the output data for this tag
	 *
	 * @param - $i - string containing the html to be output
	 * for this tag
	 */
	public function setOutput($i){
		$this->output = $i;
	}
	/**
	 * appendOutput() - append output data to the output instance variable
	 *
	 * @param $i - string to be appended to the output variable
	 */
	public function appendOutput($i){
		$this->output .= $i;
	}
	/**
	 * getType() - get the type of tag
	 * N.B. not really implemented yet
	 */
	public function getType(){
		return $this->type;
	}
	/**
	 * setType() - set the type of tag
	 * N.B. not really implemented yet
	 *
	 * @param $s - ....
	 */
	public function setType($s){
		$this->type = $s;
	}
}
?>