<?php 
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

?>
<script type="text/javascript">
	//define the hash table
	var projectTasksHash;
	var clientProjectsHash;

<?php

if(gbl::getContextUser() == ''){
	ErrorHandler::fatalError("context user hasn't been set properly.".getShortDebugTrace(2).ppr($_SESSION,'',true));
}
include('client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();

	//set initial values
	echo "var initialClientId = ".gbl::getClientId().";\n";
	echo "var initialProjectId = ".gbl::getProjId().";\n";
	echo "var initialTaskId = ".gbl::getTaskId().";\n";
	
	$js->printJSONObjects();
 
# any javascript files loaded like this become completely separate scripts, 
# and they do not have access to any of the variables setuphere, so, instead
# we need to just include the javascript file.
# echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/js/client_proj_task_javascript.js\"></script>\n";

include(Config::getDocumentRoot()."/js/client_proj_task_javascript.js");

echo"</script>";

?>
