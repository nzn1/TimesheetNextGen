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

include('templateparser.datastructure.class.php');
class templateParser{
	private $pageElements;
	private $output;

	/**
	 * templateParser() - loads in the default template, checks it exists
	 * and parses the template into $this->output
	 */
	public function __construct(){
		$this->pageElements = new PageElements();
		$this->pageElements->setTemplate(Config::getDefaultTemplate());
		if(file_exists($this->pageElements->getTemplate())){
			$this->output = $this->parseFile($this->pageElements->getTemplate());
		}
		else {
			ErrorHandler::fatalError('Error:Template file '.$this->pageElements->getTemplate().' not found');
		}
	}
	public function parseTemplate(){
		ob_start();
		$this->checkTags();

		$file = $this->pageElements->getTagByName('content')->getFile();
		if(file_exists($file)){
//			try{
				$this->pageElements->getTagByName('content')->setOutput($this->parseFile($file));
//			}
//			catch(Exception $e){
//				$msg = 'wow something went proper wrong and got caught by the templateparser';
//				$msg .= ppr($e,'Error:',true);
//				ErrorHandler::fatalError($msg);
//			}
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

		//content file has been found and parsed
		if($this->pageElements->getTemplate() != Config::getDefaultTemplate()){
			$this->output = $this->parseFile($this->pageElements->getTemplate());
		}
		 
		//check the page authorisation
		$this->checkPageAuth();

		//
		$this->buildPage();

		//ppr($this->pageElements);

		$this->pageElements->getTagByName('debugInfoTop')->appendOutput(ob_get_contents());
		ob_end_clean();
		ob_start();
		/*content pane is now populated. Can now safely continue onto any other remaining tags*/
		foreach($this->pageElements->getTags() as $t){
			if($t->getName() !='content')$t->ParseFile();
			$this->output=str_replace('{'.$t->getName().'}',$t->getOutput(),$this->output);
		}

	}

	private function parseFile($file){
		ob_start();
		include($file);
		$content=ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public function display(){
		return $this->output;
	}

	public function finishFile(){
		echo"</body></html>";
		exit;
	}

	/**
	 * checkTags() - this function checks through the
	 * tags array to ensure that all of the files exist.
	 * It also searches for tags that don't have specific file extensions
	 */
	private function checkTags(){
		if(count($this->pageElements->getTags())<=0) {
			$msg = 'Error: No tags were provided for replacement';
			ErrorHandler::fatalError($msg,'Template Parser Error','Template Parser Error');
			exit;
		}
		//loop through each tag to check the file exists
		foreach($this->pageElements->getTags() as $id=>$data){

			if(debug::getTemplateTags())ppr($data,'data id' .$id);
			$found = false;

			if(debug::getTemplateTags())ppr($data->getFile(),'search for');

			//search for the following file extensions
			$checkList = array();
			$checkList[] = '.php';
			$checkList[] = '.htm';
			$checkList[] = '';        //check for a file without adding an extension
			if(debug::getTemplateTags())ppr($checkList,'ext checklist');

			//loop through the file extension list
			foreach($checkList as $i=>$ext){
				if($found==true)break;              //file has been found so break

				//if $data->getFile() has a file extension then set $ext to ''
				if ($ext !='' && strpos($data->getFile(),$ext)){
					$ext = '';
				}
				//check the path as it came in on data
				$uriPath = $data->getFile().$ext;
				$str = Config::getDocumentRoot().'/'.$uriPath;
				if(debug::getTemplateTags())ppr($str);

				//if the path is a directory, then add /index.php to the end and check again
				if(file_exists($str) && is_dir($str)){
					if($str[strlen($str)-1] == "/"){
						$str = substr_replace($str,"",-1);
					}
					$str = $str."/"."index.php";
					if (file_exists($str)){
						if($uriPath[strlen($uriPath)-1] == "/"){
							$uriPath = substr_replace($uriPath,"",-1);
						}
						$path = $uriPath."/index.php";
						$found =true;
						break;
					}
					else{
						continue;
					}
				}
				else
				if (file_exists($str)){
					$path = $data->getFile().$ext;
					$found =true;
					break;
				}
				//check the path as if it were in the content folder
				$uriPath = 'content/'.$data->getFile().$ext;
				$str = Config::getDocumentRoot().'/'.$uriPath;
				if(debug::getTemplateTags())ppr($str);

				//if the path is a directory, then add /index.php to the end and check again
				if(file_exists($str) && is_dir($str)){
					if($str[strlen($str)-1] == "/"){
						$str = substr_replace($str,"",-1);
					}
					$str = $str."/"."index.php";
					if (file_exists($str)){
						if($uriPath[strlen($uriPath)-1] == "/"){
							$uriPath = substr_replace($uriPath,"",-1);
						}
						$path = $uriPath."/index.php";
						$found =true;
						break;
					}
					else{
						continue;
					}
				}
				else if (file_exists($str)){
					$path = 'content/'.$data->getFile().$ext;//file exists as php
					$found =true;
					break;
				}
			}//end checkList foreach loop

			if(debug::getTemplateTags()){
				if(isset($path))ppr($path,'path');
			}
			 
			/*
			 //test code lines
			 if($found==true)die('found');
			 else die('not found');
			 */

			//if the file is found then update the tag as appropriate
			if($found==true)$this->pageElements->getTagById($id)->setFile($path);
			//if the main content tag is not found add the error page instead
			else if($found==false && $data->getName()=='content'){
				$this->pageElements->getTagById($id)->setFile(Config::getError404());
			}
			//otherwise print out 'File Not Found'
			else{
				$this->pageElements->getTagById($id)->setFileError('File Not Found');
			}
		}//end getTags foreach loop


	}

	/**
	 * buildPage() -
	 *
	 */
	private function buildPage(){
		$this->output=str_replace('{content}',$this->pageElements->getTagByName('content')->getOutput(),$this->output);

		if($this->pageElements->getDocType()==''){
			$this->pageElements->setDocType(Config::getDocType());
		}
		$this->output=str_replace('{doctype}',$this->pageElements->getDocType(),$this->output);

		if($this->pageElements->getBodyOnload() !=''){
			$this->pageElements->setBodyOnload(" onload=\"".$this->pageElements->getBodyOnload()."\"");
		}
		$this->output=str_replace('{onload}',$this->pageElements->getBodyOnload(),$this->output);

		if($this->pageElements->getHead()==''){
			$this->pageElements->setHead("<title>".Config::getMainTitle()."</title>");
		}
		$this->output=str_replace('{head}',$this->pageElements->getHead(),$this->output);

		/*    if($this->pageElements->getPageTitle()==''){
		 $this->pageElements->setPageTitle("<h1>-</h1>");
		 }
		 $this->output=str_replace('{page_title}',$this->pageElements->getPageTitle(),$this->output);*/


		if(isset($_GET['response'])){
			$response = Site::getDatabase()->getResponse($_GET['response']);
			$this->pageElements->setResponse("<div class=\"response\"><p>$response</p></div>");
		}
		$this->output=str_replace('{response}',$this->pageElements->getResponse(),$this->output);
	}

	/**
	 * checkPageAuth() - check the authorisation for this page
	 */
	private function checkPageAuth(){
		return;  //THIS FUNCTION ISN'T REQUIRED FOR TXSHEET
		if($this->pageElements->getPageAuth() == null){
			$msg = "An error has occured.<br />The page authorisation has "
			."returned as null.  The page therefore cannot be displayed.<br />"
			.'This could be because the line: PageElements::setPageAuth() = \'value\'; is not '
			."in the requested file<br />"
			."<a href=\"".Config::getRelativeRoot()."/\" >"
			."Click here to visit the home page</a>";
			ErrorHandler::fatalError($msg,'Page Auth Error','Page Auth Error');
			exit;
		}
		$auth = Site::getSession()->checkAuth($this->pageElements->getPageAuth(),'view');

		if($auth == null && gettype($auth)=='NULL'){
			if(file_exists(Config::getErrorNoAuth())){
				$this->pageElements->getTagByName('content')->setOutput($this->parseFile(Config::getErrorNoAuth()));
			}
			else{
				$msg = "No Authorisation & missing no-auth Error Page";
				ErrorHandler::fatalError($msg);
			}
		}
		else if($auth == 0){
			if(Site::getSession()->isLoggedIn()){
				if(file_exists(Config::getErrorAuth())){
					$this->pageElements->getTagByName('content')->setOutput($this->parseFile(Config::getErrorAuth()));
				}
				else{
					$msg = "No Authorisation & missing no-auth Error Page";
					ErrorHandler::fatalError($msg);
				}
			}
			else {
				die(header("Location: ".Config::getRelativeRoot()."/login?redir=".urlencode($_SERVER['REQUEST_URI'])));
			}
		}
		else if($auth != 1){
			$msg = 'authorisation error. Contact Webmaster';
			ErrorHandler::fatalError($msg);
		}
	}
	public function getPageElements(){
		return $this->pageElements;
	}
}
?>