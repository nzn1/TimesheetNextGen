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
abstract class AbstractTemplateParser{
	
	/**
	 * 
	 * PageElements Class Object
	 * @var PageElements
	 */
	protected $pageElements;
	protected $output;
	
	/**
	 * 
	 * basePath is an array of paths relative to the Config::getDocumentRoot directory
	 * that are checked for pages.  Crucially this list allows for fake paths to exist.
	 * 
	 * i.e. 
	 * If there is a physical content folder with the file home.php and the 
	 * basePath 'content/' is set, then a url access of:
	 * mysite.com/relativeRoot/home will make the page content/home.php load
	 * The file is also technically accessible by:
	 * mysite.com/relativeRoot/content/home however the content section of the url
	 * is untidy and generally meaningless.
	 * 
	 * 
	 * @var unknown_type
	 */
	protected $basePath = array();
	
	/**
	 * templateParser() - loads in the default template
	 */
	public function __construct(){
		$this->pageElements = new PageElements();
		
		
		$this->basePath[] = '';
		$this->basePath[] = 'content/';
    	$this->basePath[] = 'tsx/';	
	}

	protected function parseFile($file){
		
		if(file_exists($file)){
			ob_start();
			include($file);
			$content=ob_get_contents();
			ob_end_clean();
			return $content;
		}
		else{
			return $file;
		}
	}
	/**
	 * 
	 * get the main output from the templateparser
	 */
	public function display(){
		return $this->output;
	}

	/**
	 * Echo "</body></html>".
	 * The last function to be called before the execution
	 * of the site stops.
	 */
	public function finishFile(){
		echo"</body></html>";
		exit;
	}

	
	const _CONTINUE = 1;
	const _BREAK = 2;
	
	/**
	 * checkTags() - this function checks through the
	 * tags array to ensure that all of the files exist.
	 * It also searches for tags that don't have specific file extensions
	 */
	protected function checkTags(){
		if(debug::getTemplateTags())echo"<pre>checkTags()<br />Check all the tags to ensure the files exist and to resolve missing file extensions</pre>";
		if(count($this->pageElements->getTags())<=0) {
			$msg = 'Error: No tags were provided for replacement';
			ErrorHandler::fatalError($msg,'Template Parser Error','Template Parser Error');
			exit;
		}
		if(debug::getTemplateTags())echo"<pre>Loop through all \$this->pageElements->getTags()</pre>";
		//loop through each tag to check the file exists
		foreach($this->pageElements->getTags() as $id=>$data){
			
						
			if(!($data instanceof FileTag)){
				continue;				
			}	
			/* @var $data FileTag */
			if(debug::getTemplateTags())echo"<hr />";
			if(debug::getTemplateTags())ppr($data,'Tag id ' .$id);
			$found = false;

			if(debug::getTemplateTags())ppr($data->getFile(),'search for the file');

			//search for the following file extensions
			$checkList = array();
			$checkList[] = '.php';
			$checkList[] = '.htm';
			$checkList[] = '';        //check for a file without adding an extension
			if(debug::getTemplateTags())ppr($checkList,'Check for the following extensions');
			
			//loop through the file extension list
			foreach($checkList as $i=>$ext){
				if($found==true)break;              //file has been found so break

				//if $data->getFile() has a file extension then set $ext to ''
				if ($ext !='' && strpos($data->getFile(),$ext)){
					$ext = '';
				}
												
				foreach($this->basePath as $basePath){
					
					$retval = $this->checkThisTag($data,$ext,$basePath);
					
					if($retval['return']==AbstractTemplateParser::_BREAK || $retval['return']==AbstractTemplateParser::_CONTINUE){
						break;
					}
				}
				if($retval['return']==AbstractTemplateParser::_BREAK){
					$path = $retval['path'];
					$found =true;
					break;
				}
				elseif($retval['return']==AbstractTemplateParser::_CONTINUE)continue;
								
			}//end checkList foreach loop

			if(debug::getTemplateTags()){
				if($found)echo "<p><strong>Success! found the file: ".$path."</strong></p>";
				else echo "<p><strong>Error couldn't find file: ".$data->getFile()."</strong></p>";
			}
			 
			//if the file is found then update the tag as appropriate
			if($found==true)$this->pageElements->getTagById($id)->setFile($path);
			//if the main content tag is not found add the error page instead
			else if($found==false && $data->getName()=='content'){
				if(debug::getTemplateTags())echo "<p><strong>Changing the requested page to: ".PageElements::getError404()."</strong></p>";
							
				$this->pageElements->getTagById($id)->setFile(PageElements::getError404());
			}
			//otherwise print out 'File Not Found'
			else{
				$this->pageElements->getTagById($id)->setFileError('File Not Found');
			}
		}//end getTags foreach loop
	}


	/**
	 * 
	 * This function is used by checkTags() to simplify
	 * so of the main logic that finds files in subdirectories
	 * that have urls that appear to be in a root directory.
	 * @param FileTag $data - a filedata object
	 * @param String $ext - the file extension to be checked
	 * @param String $basePath - i.e. content/ - the path from the root to
	 * the subdirectory in question
	 */
	private function checkThisTag(FileTag $data,$ext,$basePath){
		
		$uriPath = $basePath.$data->getFile().$ext;
		$str = Config::getDocumentRoot().'/'.$uriPath;
	
		if(debug::getTemplateTags())ppr($str,'check Path');
	
		//if the path is a directory, then add /index.php to the end and check again
		if(file_exists($str) && is_dir($str)){
			//rtrim function could be used here
			if($str[strlen($str)-1] == "/"){
				$str = substr_replace($str,"",-1);
			}
			$str = $str."/"."index.php";
			if (file_exists($str)){
				if($uriPath[strlen($uriPath)-1] == "/"){
					$uriPath = substr_replace($uriPath,"",-1);
				}
				$path = $uriPath."/index.php";
				return array('return'=>self::_BREAK,'path'=>$path);
			}
			else{
				return self::_CONTINUE;
			}
		}
		else if (file_exists($str)){			
			$path = $uriPath;
			return array('return'=>self::_BREAK,'path'=>$path);;
		}
	}
	
	abstract public function parseTemplate();
	/**
	 * buildPage() -
	 *
	 */
	abstract protected function buildPage();
	/**
	 * 
	 * Function called from the content page to check
	 * the page authorisation.
	 * @param unknown_type $auth
	 */
	abstract protected function requestPageAuth($auth);
	
	/**
	 * checkPageAuth() - check the authorisation for this page
	 */
	abstract protected function checkPageAuth();
	
	/**
	 * 
	 * Retrieve the page elements
	 * @return PageElements
	 */
	public function getPageElements(){
		return $this->pageElements;
	}
}
?>