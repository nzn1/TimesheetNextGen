<?php

/**
* Abstract class Command representing a command in a command menu
*/
class Command {
	var $text;
	var $enabled;

	function Command($text, $enabled, $sep=true) {
		$this->text = $text;
		$this->enabled = $enabled;
		$this->wantsep = $sep;
	}

	function toString() {
		if (!$this->enabled)
			return "<span class=\"command_current nobr\">$this->text</span>";
			//NOBR is not a valid html tag
      /**
			 *@todo - find a way to replace the nobr tags whilst keeping the menu icons next to the names
			 */       	
		else
			return $this->text;
	}

	function setEnabled($enabled) {
		$this->enabled = $enabled;
	}
}

/*	A class which represents a single command in a command menu.
*		It has a url and a visual reprenstation (text)
*/
class TextCommand extends Command {
	var $url;

	/**
	* Constructor
	*/
	function TextCommand($text, $enabled, $url) {
		parent::Command($text, $enabled);
		$this->url = $url;
	}

	function toString() {
		if (!$this->enabled)
			return parent::toString();
		else
			return "<a class=\"nobr\" href=\"" . $this->url . "\" class=\"command\">" . $this->text . "</a>";
			//NOBR is not a valid html tag
			/**
			 *@todo - find a way to replace the nobr tags whilst keeping the menu icons next to the names
			 */       	
	}
}

class IconTextCommand extends TextCommand {
	var $img;

	/**
	* Constructor
	*/
	function IconTextCommand($text, $enabled, $url, $img) {
		parent::TextCommand($text, $enabled, $url);
		$this->img = $img;
	}

	function toString() {
//		ppr($this->img);
		if(!class_exists('Site')){
			if (!file_exists($this->img))
				return parent::toString();
			else               
				return "<a class=\"command nobr\" href=\"" . $this->url . "\" ><img src=\"" . $this->img . "\" alt=\"\" border=\"0\" align=\"bottom\" />" . $this->text . "</a>\n";			
				
		}
		else{
			if (!file_exists(Config::getDocumentRoot()."/".$this->img))
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

	//array which holds the commands in the menu
	var $commands = array();

	/* adds a command to the menu */
	function add($command) {
		$this->commands[] = $command;
	}

	/* returns the command menu as html */
	function toString() {
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
	function disableCommand($text) {
		//iterate through commands
		$count = count($this->commands);
		for ($i=0; $i < $count; $i++) {
			if ($this->commands[$i]->text == $text)
				$this->commands[$i]->setEnabled(false);
		}
	}

	function disableSelf() {
		//iterate through commands
		$count = count($this->commands);
		for ($i=0; $i < $count; $i++) {
			$self = $_SERVER["PHP_SELF"];
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
