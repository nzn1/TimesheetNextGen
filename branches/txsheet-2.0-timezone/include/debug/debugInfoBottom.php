<?php
if(debug::getAuthBasic()==1){
	Debug::ppr(AuthData::getAuth(),'authData');
}
if(debug::getSession()==1){
	Debug::ppr($_SESSION,'Session');
	Debug::ppr(session_id(),'Session');
	Debug::ppr($_SERVER['REQUEST_URI'],'REQUEST_URI');
	Debug::ppr(Rewrite::getContent(),'Rewrite');
}

?>
