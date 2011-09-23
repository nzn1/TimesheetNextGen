<?php


ob_start();

if(debug::getRequestUri()==1)   Debug::ppr($_SERVER['REQUEST_URI'],'Request URI');


if(debug::getSessionTop()==1)   Debug::ppr($_SESSION,'SESSION');

if(debug::getFiles()==1) Debug::ppr($_FILES,'files');
if(debug::getGet()==1)   Debug::ppr($_GET,'get');
if(debug::getPost()==1)   if(isset($_POST)) Debug::ppr($_POST,'post');
if(debug::getServer()==1) Debug::ppr($_SERVER,'server');
if(debug::getCookie()==1) Debug::ppr($_COOKIE,'cookie');

$var = ob_get_contents();
ob_end_clean();
if($var != ''){
	echo "<div class=\"placeholders\">".$var."</div><!--close placeholders-->";
}

?>
