<?php
require_once('tag.class.php');

/**
 *
 * A tag is an object {head} that is contained in the template file
 * Each tag must be handled by the template parser in some way.
 * 
 * A String Tag is simply a string variable that will replace
 * any {name} tag that is found by the templateParser.
 * 
 * This class is used to simplify the design of the templateparser
 * so that it is further abstracted from the custom application
 * @author Mark
 *
 */
class StringTag extends Tag{

	private $string;
	/**
	 * __construct() - class constructor
	 *
	 * @param $name - name of the tag
	 * @param $defaultString - the default contents of the string
	 * (if any)
	 */
	function __construct($name,$defaultString = ''){
		$this->name = $name;
		$this->string = $defaultString;
	}

	public function getString(){
		return $this->string;
	}
	public function setString($s){
		$this->string = $s;
	}
	public function appendString($s){
		$this->string .= $s;
	}
	public function parse(){
		$this->output = $this->string;
	}

}
?>