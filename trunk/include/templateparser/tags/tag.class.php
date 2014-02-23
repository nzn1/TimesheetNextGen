<?php
/**
 *
 * A tag is an object {head} that is contained in the template file
 * Each tag must be handled by the template parser in some way.
 * @author Mark
 *
 */
abstract class Tag{
	/**
	 * $name - name of the tag
	 */
	protected $name;
	/**
	 * $output - the html output data for the tag
	 */
	protected $output = null;


	/**
	 * 
	 * The parse function is responsible for parsing the tag to generate the output.
	 */
	abstract function parse();
	/**
	 * __construct() - class constructor
	 *
	 * @param $name - name of the tag
	 */
	function __construct($name){
		$this->name = $name;
	}

	/**
	 * getName() - get the name of the tag
	 */
	public function getName(){
		return $this->name;
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

}
?>