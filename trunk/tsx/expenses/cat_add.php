<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_EXPENSE_CATEGORY')."</title>");
PageElements::setTheme('txsheet2');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclECategories'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));


?>
<h1><?php echo JText::_('ADD_NEW_CATEGORY'); ?></h1>
<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/cat_add_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">

	<div><label><?php echo JText::_('CATNAME'); ?>:</label>
		<textarea name="name" rows="1" cols="64" wrap="virtual"></textarea></div>
	<div><label><?php echo JText::_('CATDESC'); ?>:</label>
		<textarea name="description" rows="4" cols="64" wrap="virtual"></textarea></dIV>
	<div><label></label><input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_CATEGORY'); ?>" /></div>
</div>
</form>