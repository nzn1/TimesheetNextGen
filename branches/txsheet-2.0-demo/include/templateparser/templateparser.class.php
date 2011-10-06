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

require_once('abstract.templateparser.class.php');
class TemplateParser extends AbstractTemplateParser{

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
		if(!Site::isInstaller())$this->checkPageAuth();

		//parse the template file
		if(file_exists($this->pageElements->getTemplatePath())){
			$this->output = $this->parseFile($this->pageElements->getTemplatePath());
		}
		else {
			ErrorHandler::fatalError('Error:Template file '.$this->pageElements->getTemplatePath().' not found');
		}
		
		//ppr($this->pageElements);

		$this->pageElements->getTagByName('debugInfoTop')->appendOutput(ob_get_contents());		
		ob_end_clean();
		
		//die(ppr($this->pageElements->getTagByName('debugInfoTop')));
		ob_start();
		$var = null;
		/*content pane is now populated. Can now safely continue onto any other remaining tags*/
		//build the page
		$this->buildPage();
		
		$var = ob_get_contents();
		ob_end_clean();
		if(!($var=='' || $var==null)){
			$var ="<div class=\"placeholders\">".$var."</div><!--close placeholders-->";
		}
		$this->output=str_replace('{templateParserDebug}',$var,$this->output,$count);
		if($count == 0){
			trigger_error('The tag \'{templateParserDebug}\' could not be found in the template');
		}

	}	//end parseTemplate

	/**
	 * buildPage() -
	 *
	 */
	protected function buildPage(){
		//ppr($this->pageElements->getTags());
		
		/**
		 * @todo Define a method of priority for tags
		 * Certain tags need to be complete before others.
		 * The priority at the moment is:
		 * 1. FileTag
		 * 2. FunctionTag
		 * 3. StringTag
		 * 
		 * */ 
		/*Parse the FileTag Objects*/
		foreach($this->pageElements->getTags() as $t){						
			if($t instanceof FileTag){
				/* @var $t FileTag */
				//Debug::ppr($t->getName(),'parse');
				if($t->getName() != 'content')$t->parse();
				$this->output=str_replace('{'.$t->getName().'}',$t->getOutput(),$this->output);
			}
		}
		
		/*Parse the FunctionTag Objects*/
		foreach($this->pageElements->getTags() as $t){						
			if($t instanceof FunctionTag){
				/* @var $t FunctionTag */
				$t->parse();				
				$this->output=str_replace('{'.$t->getName().'}',$t->getOutput(),$this->output);
			}
		}
		/*Parse the StringTag Objects*/
		foreach($this->pageElements->getTags() as $t){					
			if($t instanceof StringTag){
				/* @var $t StringTag */
				//ppr($t);
				$t->parse();				
				$this->output=str_replace('{'.$t->getName().'}',$t->getOutput(),$this->output);
			}
		}
	}

	/**
	 * 
	 * Check the auth for this page and display
	 * an error page if required.
	 */
	protected function checkPageAuth(){
		$auth = $this->pageElements->getPageAuth();
		if($auth == null){
			$msg = "An error has occured.<br />The page authorisation has "
			."returned as null.  The page therefore cannot be displayed.<br />"
			.'This could be because the line: <br />'
			.'<pre>if(Auth::ACCESS_GRANTED != $this->requestPageAuth(\'level\'))return;</pre>'
			.' is not '
			."in the requested file<br />"
			."<a href=\"".Config::getRelativeRoot()."/\" >"
			."Click here to visit the home page</a>";
			ErrorHandler::fatalError($msg,'Page Auth Error','Page Auth Error');
			exit;
		}
		
		$state = Auth::requestAuth($auth->authGroup,$auth->authName);
		if($state == Auth::ACCESS_GRANTED){
			return;
		}
		
		else if($state == Auth::ACCESS_DENIED || (!Config::getShowAuthUnknownPage() && $state == Auth::ACCESS_UNKNOWN)){
			//if user is logged in then show error page
			if(Site::getSession()->isLoggedIn()){
				if(file_exists(PageElements::getDeniedAuth())){
					$this->pageElements->getTagByName('content')->setOutput($this->parseFile(PageElements::getDeniedAuth()));
				}
				else{
					$msg = "No Authorisation & missing Auth Error Page";
					ErrorHandler::fatalError($msg);
				}
			}
			//user isn't logged in so redirect to login page
			else {
        	$url = Config::getRelativeRoot()."/login?redir=".urlencode($_SERVER['REQUEST_URI']);
			  gotoLocation($url);
			  exit();
			}
			
		}
		else if($state == Auth::ACCESS_UNKNOWN){
			//echo 'access unknown';
			//ppr(Config::getErrorNoAuth());
			if(Site::getSession()->isLoggedIn()){
				if(file_exists(PageElements::getUnknownAuth())){
				//$tag = $this->pageElements->getTagByName('content');
				/* @var $tag Tag */
				//$tag->setFile(Config::getErrorNoAuth());
				//$tag->parseFile();
				$this->pageElements->getTagByName('content')->setOutput($this->parseFile(PageElements::getUnknownAuth()));

				}
				else{
					$msg = "No Authorisation & missing no-auth Error Page";
					ErrorHandler::fatalError($msg);
				}	
			}
			//user isn't logged in so redirect to login page
			else {
        		$url = Config::getRelativeRoot()."/login?redir=".urlencode($_SERVER['REQUEST_URI']);
			 	gotoLocation($url);
			 	exit();
			}		
		}
		else{
			$msg = 'authorisation error. Contact Webmaster';
			ErrorHandler::fatalError($msg);
		}
		return $state;

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