<?php

require_once('tag.class.php');
/**
 *
 * A tag is an object {head} that is contained in the template file
 * Each tag must be handled by the template parser in some way.
 * @author Mark
 *
 */
class FileTag extends Tag{

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
		trigger_error('FileTag::parseFile() is now deprecated.  Use FileTag::parse() instead<br />'.getShortDebugTrace());
		$this->parse();		
	}
	
	public function parse(){
		if(file_exists($this->file)){
			ob_start();
			include($this->file);
			$content=ob_get_contents();
			ob_end_clean();
			//we must always append the output as we can add stuff in beforehand
			//i.e. templateparser.php stuff gets added into the debugInfoTop tag
			$this->appendOutput($content);
		}
		else{
			//we must always append the output as we can add stuff in beforehand
			//i.e. templateparser.php stuff gets added into the debugInfoTop tag
			$this->appendOutput($this->file);
		}	
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
}
?>