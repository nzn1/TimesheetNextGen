<?php

/**
* Abstract class Command representing a command in a command menu
*/
class Command {
	protected $text;
	protected $enabled;

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $text
	 * @param unknown_type $enabled
	 * @param unknown_type $sep
	 */
	public function __construct($text, $enabled, $sep=true) {
		$this->text = $text;
		$this->enabled = $enabled;
		$this->wantsep = $sep;
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function toString() {
		if (!$this->enabled)
			return "<span class=\"command_current nobr\">".$this->text."</span>";
		else
			return $this->text;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $enabled
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
	}
}

/*	A class which represents a single command in a command menu.
*		It has a url and a visual reprenstation (text)
*/
class TextCommand extends Command {
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $url;

	/**
	* Constructor
	*/
	public function __construct($text, $enabled, $url) {
		parent::__construct($text, $enabled);
		$this->url = $url;
	}

	public function toString() {
		if (!$this->enabled)
			return parent::toString();
		else
			return "<a class=\"command nobr\" href=\"" . $this->url . "\" >" . $this->text . "</a>";
	}
}

class IconTextCommand extends TextCommand {
	protected $img;

	/**
	* Constructor
	*/
	public function __construct($text, $enabled, $url, $img) {
		parent::__construct($text, $enabled, $url);
		$this->img = $img;
	}

	/**
	 * (non-PHPdoc)
	 * @see TextCommand::toString()
	 */
	public function toString() {

		if(!class_exists('Site')){
			if (!file_exists($this->img))
				return parent::toString();
			else
				return "<a class=\"command nobr\" href=\"" . $this->url . "\" ><img src=\"" . $this->img . "\" alt=\"\" border=\"0\" align=\"bottom\" />" . $this->text . "</a>\n";
		}
		else{
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].$this->img))
				return parent::toString();
			else
				return "<a class=\"command nobr\" href=\"" . $this->url . "\" ><img src=\"" . $this->img . "\" alt=\"\" border=\"0\" align=\"bottom\" />" . $this->text . "</a>\n";
		}
	}
}

/*	A class representing a menu of commands.
*		It's responsible for printing the menu with a separator
*/
class CommandMenu {

	/**
	 * array which holds the commands in the menu 	
	 */
	private $commands = array();

	public function __construct(){}
	/**
	 *  adds a command to the menu 
	 */
	public function add($command) {
		$this->commands[] = $command;
	}

	/**
	 * 
	 * returns the command menu as html
	 */
	public function toString() {
		$printedFirstCommand = false;
		$returnString = "";

		//iterate through commands
		$count = count($this->commands);
		for ($i=0; $i < $count; $i++) {
			//append this command to the string
			$returnString = $returnString . $this->commands[$i]->toString();
			if($this->commands[$i]->wantsep)
				$returnString = $returnString . "&nbsp;&nbsp; ";
		}
		//return the command menu as a string
		return $returnString;
	}

	/**
	* Disables a menu command with the given text
	*/
	public function disableCommand($text) {
		//iterate through commands
		$count = count($this->commands);
		for ($i=0; $i < $count; $i++) {
			if ($this->commands[$i]->text == $text)
				$this->commands[$i]->setEnabled(false);
		}
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public function disableSelf() {
		//iterate through commands
		$count = count($this->commands);
		for ($i=0; $i < $count; $i++) {
			$self = Rewrite::getShortUri();
			$slashPos = strrpos($self, "/");
			if (!is_bool($slashPos))
				$self = substr($self, $slashPos + 1);
			$url = empty($this->commands[$i]->url) ? 'noURL' : $this->commands[$i]->url;
			$pos = strpos($url, $self);
			if (!is_bool($pos) && $pos == 0)
				$this->commands[$i]->setEnabled(false);
		}
	}
}

//create the command menu object so that those files which include this one dont need to
//don't create commandMenu for new OO version of txsheet
if(!class_exists('Site')){
	$commandMenu = new CommandMenu;	
}
// vim:ai:ts=4:sw=4
?>
