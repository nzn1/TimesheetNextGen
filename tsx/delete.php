<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

dbQuery("DELETE FROM ".tbl::getTimesTable()." WHERE trans_num=".$_REQUEST['trans_num']." AND username='".gbl::getContextUser()."'");

//seems broken: Header("Location: $_SERVER[HTTP_REFERER]");
gotoLocation(Config::getRelativeRoot()."/daily?month=".gbl::getMonth()."&amp;year=".gbl::getYear()."&amp;day=".gbl::getDay());

?>
