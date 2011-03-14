<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

//get the logged in user
$loggedInUser = $_SESSION['loggedInUser'];

//load local vars from superglobals
$errormsg = stripslashes($_REQUEST['errormsg']);

//define the command menu
//$commandMenu->add(new TextCommand("Back", true, "javascript:back()"));

echo '<br><br><a href="javascript:history.back()"><h2>&nbsp;&nbsp;<font color="red">'.$errormsg.'</font></h2><br>';
echo '<font color="red">&nbsp;&nbsp;&nbsp;&nbsp;'.JText::_('CLICK_TO_GO_BACK').'</font></a><br>&nbsp;';

?>
