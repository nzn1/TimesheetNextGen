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
	protected $pageElements;
	protected $output;

	/**
	 * templateParser() - loads in the default template
	 */
	public function __construct(){
		$this->pageElements = new PageElements();
		$this->pageElements->setTemplate(Config::getDefaultTemplate());

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
				//check the path as it came in on data
				$uriPath = $data->getFile().$ext;
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
				if(debug::getTemplateTags())ppr($str,'check content folder:');

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
				if($found)echo "<p><strong>Success! found the file: ".$path."</strong></p>";
				else echo "<p><strong>Error couldn't find file: ".$data->getFile()."</strong></p>";
			}
			 
			//if the file is found then update the tag as appropriate
			if($found==true)$this->pageElements->getTagById($id)->setFile($path);
			//if the main content tag is not found add the error page instead
			else if($found==false && $data->getName()=='content'){
				if(debug::getTemplateTags())echo "<p><strong>Changing the requested page to: ".Config::getError404()."</strong></p>";
							
				$this->pageElements->getTagById($id)->setFile(Config::getError404());
			}
			//otherwise print out 'File Not Found'
			else{
				$this->pageElements->getTagById($id)->setFileError('File Not Found');
			}
		}//end getTags foreach loop
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
	
	
	public function getPageElements(){
		return $this->pageElements;
	}
}
?>