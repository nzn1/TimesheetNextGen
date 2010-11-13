<?php

$var=null;

if(debug::getAuthBasic()==1){
	if(PageElements::getPageAuth() == '')$var = "<small>Page View privileges have not been setup</small>";
}

if(debug::getRequestUri()==1)   $var .= ppr($_SERVER['REQUEST_URI'],'Request URI',true);
if(debug::getSessionTop()==1)   $var .= ppr($_SESSION,'SESSION',true);
if(debug::getFiles()==1) $var .= ppr($_FILES,'files',true);
if(debug::getGet()==1)   $var .= ppr($_GET,'get',true);
if(debug::getPost()==1)   if(isset($_POST))$var .= ppr($_POST,'post',true);
if(debug::getServer()==1)$var .= ppr($_SERVER,'server',true);
if(debug::getCookie()==1)$var .= ppr($_COOKIE,'cookie',true);

if(!($var=='' || $var==null)){
	$var = "<div class=\"placeholders\">".$var."</div><!--close placeholders-->";
}
echo $var;


?>
