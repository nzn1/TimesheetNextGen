<?php
/*******************************************************************************
 * Name:                    templateparser.class.php
 * Recommended Location:    /include
 * Last Updated:            April 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * Parses the template
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/

include('templateparser.class.php');
class InstallTemplateParser extends TemplateParser{

	public function __construct(){
		parent::__construct();
	}
	public function parseTemplate(){
		//FIRST LINE HERE MUST BE OB_START() as otherwise any echo statements
		//will appear outside the template, will cause HTML errors and it
		//will also appear as if echo statements here appeared before other stuff
		ob_start();
		if(debug::getTemplateTags()){
			echo"<hr /><p><strong>Start TemplateParser::parseTemplate()</strong></p>";
			echo"<pre>These two variables are vital to the templateParser:</pre>";
			ppr(Config::getRelativeRoot(),'config::relativeRoot');
			ppr(Config::getDocumentRoot(),'config::documentRoot');
		}
		
		//check through the tags array to ensure that all of the files exist
		$this->checkTags();
		if(file_exists($this->pageElements->getTagByName('content')->getFile())){
			
			$this->pageElements->getTagByName('content')->setOutput($this->parseFile($this->pageElements->getTagByName('content')->getFile()));	
			//$this->pageElements->getTagByName('content')->parseFile();
		}
		else{
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			$msg = "An error has occured.<br />The page you are trying to view "
			."doesn't exist &amp; the 404 error page is also missing.<br />"
			."<a href=\"".Config::getRelativeRoot()."/\" >"
			."Click here to visit the home page</a>";
			ErrorHandler::fatalError($msg,'Error 404','Error 404');
			exit;
		}
		
		//check that the user is authorised
		//$this->checkPageAuth();

		//parse the template file
		if(file_exists($this->pageElements->getTemplate())){
			$this->output = $this->parseFile($this->pageElements->getTemplate());
		}
		else {
			ErrorHandler::fatalError('Error:Template file '.$this->pageElements->getTemplate().' not found');
		}
		
		//ppr($this->pageElements);

		$this->pageElements->getTagByName('debugInfoTop')->appendOutput(ob_get_contents());
		
		
		ob_end_clean();
		//die(ppr($this->pageElements->getTagByName('debugInfoTop')));
		ob_start();
		$var= null;
		/*content pane is now populated. Can now safely continue onto any other remaining tags*/
		foreach($this->pageElements->getTags() as $t){
			/* @var $t Tag */
			
			//Debug::ppr($t->getName(),'parse');
			if($t->getName() != 'content')$t->ParseFile();
			$this->output=str_replace('{'.$t->getName().'}',$t->getOutput(),$this->output);
		}

		//build the page
		$this->buildPage();
		
		$var .= ob_get_contents();
		ob_end_clean();
		if(!($var=='' || $var==null)){
			$var ="<div class=\"placeholders\">".$var."</div><!--close placeholders-->";
		}
		$this->output=str_replace('{templateParserDebug}',$var,$this->output);

	}	//end parseTemplate

	/**
	 * buildPage() -
	 *
	 */
	protected function buildPage(){
		$this->output=str_replace('{content}',$this->pageElements->getTagByName('content')->getOutput(),$this->output);

		if($this->pageElements->getBodyOnload() !=''){
			$this->pageElements->setBodyOnload(" onload=\"".$this->pageElements->getBodyOnload()."\"");
		}
		$this->output=str_replace('{onload}',$this->pageElements->getBodyOnload(),$this->output);

		if($this->pageElements->getHead()==''){
			$this->pageElements->setHead("<title>".Config::getMainTitle()."</title>");
		}
		$this->output=str_replace('{head}',$this->pageElements->getHead(),$this->output);

		if(isset($_GET['response'])){
			$response = Site::getDatabase()->getResponse($_GET['response']);
			$this->pageElements->setResponse("<div class=\"response\"><p>$response</p></div>");
		}
		$this->output=str_replace('{response}',$this->pageElements->getResponse(),$this->output);
	}

	/**
	 * 
	 * Check the auth for this page and display
	 * an error page if required.
	 */
	protected function checkPageAuth(){
	   return;
    }
	
	protected function requestPageAuth($authGroup,$authName='page'){
		$auth = new stdClass();
		$auth->authGroup = $authGroup;
		$auth->authName = $authName;		
		$this->pageElements->setPageAuth($auth);
			
		$state = Auth::requestAuth($authGroup, $authName);
		return $state;

	}
}
?>