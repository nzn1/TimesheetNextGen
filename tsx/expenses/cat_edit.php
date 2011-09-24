<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_EXPENSE_CATEGORY')."</title>");
PageElements::setTheme('newcss');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclExpenses'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));
if (isset($_REQUEST['cat_id'])) {
	$cat_id = $_REQUEST['cat_id'];
	$query_categories = "SELECT description FROM ". tbl::getExpenseCategoryTable() . " WHERE cat_id = '". $cat_id . "'" ;
	list($qh, $num) = dbQuery($query_categories);
	$data_cat = dbResult($qh);
	$description = $data_cat['description'];
}

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/cat_edit_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">

<table class="noborder">
	<tbody class="nobground">
	<tr>
		<td class="outer_table_heading">
		<td>	<h1><?php echo JText::_('EDIT_CATEGORY'); ?></h1>
		</td>
	</tr>

	<tr>
		<td align="right" valign="top"><?php echo JText::_('DESCRIPTION'); ?>:</td>
			<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
		<td><textarea name="description" rows="4" cols="40" wrap="virtual"><?php echo $description; ?></textarea></td>
	</tr>
	<tr>
			<!-- table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('SUBMIT'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>