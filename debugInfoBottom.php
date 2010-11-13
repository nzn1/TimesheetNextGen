<?php
if(!isset($var))$var=null;
if(debug::getAuthBasic()==1){
	$var .= Site::getSession()->getAuthorisation()->pprAuth();
}
if(debug::getSession()==1){
	$var .=ppr($_SESSION,'Session',true);
	$var .=ppr(session_id(),'Session',true);
	$var .=ppr($_SERVER['REQUEST_URI'],'REQUEST_URI',true);
	$var .= "<pre>c = ".Site::getRewrite()->getContent()."</pre>";
}

$var .= ob_get_contents();
ob_end_clean();
if(!($var=='' || $var==null)){
	$var ="<div class=\"placeholders\">".$var."</div><!--close placeholders-->";
}
echo $var;

?>
