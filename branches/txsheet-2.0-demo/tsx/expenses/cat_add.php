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

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/cat_add_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">

<table class="noborder">
	<tbody class="nobground">
	<tr>
		<td class="outer_table_heading">
		<td>	<h1><?php echo JText::_('ADD_NEW_CATEGORY'); ?></h1>
		</td>
	</tr>

	<tr>
		<td align="right" valign="top"><?php echo JText::_('DESCRIPTION'); ?>:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual"></textarea></td>
	</tr>
	<tr>
			<!-- table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_CATEGORY'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>