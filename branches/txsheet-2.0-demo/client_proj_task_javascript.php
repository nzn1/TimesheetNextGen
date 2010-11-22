<?php 
if(!class_exists('Site'))die('Error: not accessed through the class structure.  For the old site version please use client_proj_task_javascript.inc instead');

?>
<script type="text/javascript">
	//define the hash table
	var projectTasksHash;
	var clientProjectsHash;

<?php

if(gbl::getContextUser() == ''){
	ErrorHandler::fatalError("context user hasn't been set properly.".getShortDebugTrace(2));
}
include('client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();

	//set initial values
	echo "var initialClientId = ".gbl::getClientId().";\n";
	echo "var initialProjectId = ".gbl::getProjId().";\n";
	echo "var initialTaskId = ".gbl::getTaskId().";\n";
	
	$js->printJSONObjects();
echo"</script>";
 
echo "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/client_proj_task_javascript.js\"></script>\n";

?>
