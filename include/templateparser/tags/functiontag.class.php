<?php
require_once('tag.class.php');

/**
 *
 * A tag is an object {head} that is contained in the template file
 * Each tag must be handled by the template parser in some way.
 * @author Mark
 *
 */
class FunctionTag extends Tag{

	private $function;
	private $class;
	private $functionType;
	
	const TYPE_FUNC = 1;
	const TYPE_STATIC = 2;
	const TYPE_INSTANCE = 3;
	/**
	 * __construct() - class constructor
	 * 
	 * @TODO - type TYPE_INSTANCE is not yet tested
	 *
	 *
	 * @param $name - name of the tag
	 * @param $function - name of the function to be run which output
	 * will be used for this tag.
	 * @param $functionType - the type of the function that has been 
	 * passed in - i.e. TYPE_FUNC, TYPE_STATIC, TYPE_INSTANCE
	 */
	public function __construct($name,$function,$functionType){
		if($function == '' || $function == null ){
			ErrorHandler::fatalError('Invalid function for Function Tag');
		}
		if($functionType == '' || $functionType == null ){
			ErrorHandler::fatalError('Invalid functionType for Function Tag'.getShortDebugTrace());
		}
		$this->name = $name;
		$this->functionType = $functionType;
		
		if($functionType == self::TYPE_FUNC){
			if(is_string($function)){
				$this->function = $function;
				$this->class = '';
			}
			else{
				trigger_error('a string was not passed in whtn TYPE_FUNC was specified');
			}
		}
		else if($functionType == self::TYPE_STATIC){
			list($this->class, $this->function) = explode('::', $function);			
		}
		else if($functionType == self::TYPE_INSTANCE){
			if(is_array($function)){
				list($this->class, $this->function) = $function;
			}
			else{
				trigger_error('an array was not passed in when TYPE_INSTANCE was specified');
			}
		}
		else{
			trigger_error('unknown functionType has been specified');
		}
	}

	public function parse(){
		ob_start();
				
		if($this->functionType == self::TYPE_FUNC){
			$func = $this->function;
			$func();									//call the function	
		}
		else if($this->functionType == self::TYPE_STATIC){
			call_user_func(array($this->class,$this->function));
		}
		else if($this->functionType == self::TYPE_INSTANCE){
			call_user_func(array($this->class,$this->function));
		}
		

		$this->appendOutput(ob_get_contents());		//save the output
		ob_end_clean();								//clear the buffer
		
	}

}
?>