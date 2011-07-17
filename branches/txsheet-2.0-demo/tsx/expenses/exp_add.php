<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_EXPENSE')."</title>");
PageElements::setTheme('newcss');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");



//load client id from superglobals
$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id']: 1;

$startDate = mktime(0,0,0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$start_day = date("d", $startDate);
$start_month = date("n", $startDate);
$start_year = date("Y", $startDate);;

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/exp_add_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">

<table class="noborder">
	<tbody class="nobground">
	<tr>
		<td class="outer_table_heading">
		<td>	<h1><?php echo JText::_('ADD_EXPENSE'); ?></h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right"><?php echo JText::_('PROJECT'); ?>:</td>
		<td><input type="text" name="title" size="42" maxlength="200" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('CLIENT'); ?>:</td>
		<td><?php Common::client_select_list($client_id, 0, false, false, false, true, "", false); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('DESCRIPTION'); ?>:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('DATE_EXPENSE_INCURRED'); ?>:</td>
		<td><?php Common::day_button("start_day",0,0); Common::month_button("start_month", $start_month); Common::year_button("start_year", $start_year); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('BILLABLE'); ?>:</td>
		<td><input type="text" name="url" size="42" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('AMOUNT'); ?>:</td>
		<td><?php Common::multi_user_select_list("assigned[]"); ?></td>
	</tr>
	<tr>
			<!-- table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_EXPENSE'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>