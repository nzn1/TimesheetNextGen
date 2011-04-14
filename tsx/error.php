<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:back()"));

//get the logged in user
$loggedInUser = $_SESSION['loggedInUser'];

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//load local vars from request/post/get
$errormsg = stripslashes($_REQUEST['errormsg']);

//define the command menu
//$commandMenu->add(new TextCommand("Back", true, "javascript:back()"));

echo '<br><br><a href="javascript:history.back()"><h2>&nbsp;&nbsp;<font color="red">'.$errormsg.'</font></h2><br>';
echo '<font color="red">&nbsp;&nbsp;&nbsp;&nbsp;'.JText::_('CLICK_TO_GO_BACK').'</font></a><br>&nbsp;';

?>
