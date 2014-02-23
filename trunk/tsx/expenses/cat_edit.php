<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_EXPENSE_CATEGORY')."</title>");
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
if (isset($_REQUEST['cat_id'])) {
	$cat_id = $_REQUEST['cat_id'];
	$query_categories = "SELECT cat_name, cat_description FROM ". tbl::getExpenseCategoryTable() . 
		" WHERE cat_id = '". $cat_id . "' SORT BY cat_name" ;
	list($qh, $num) = dbQuery($query_categories);
	$data_cat = dbResult($qh);
	$description = $data_cat['cat_description'];
	$catName = $data_cat['cat_name'];
}

?>
<h1><?php echo JText::_('EDIT_CATEGORY'); ?></h1>

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/cat_edit_action" method="post">
<input type="hidden" name="action" value="add" />
<input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">

<div id="inputArea">
	<div><label><?php echo JText::_('CATNAME'); ?>:</label>
		<textarea name="name" rows="1" cols="64" wrap="virtual"><?php echo $catName; ?></textarea></div>
	<div><label><?php echo JText::_('CATDESC'); ?>:</label>
		<textarea name="description" rows="4" cols="64" wrap="virtual"><?php echo $description; ?></textarea></dIV>
	<div><label></label><input type="submit" name="add" value="<?php echo JText::_('SUBMIT'); ?>" /></div>
</div>
</form>