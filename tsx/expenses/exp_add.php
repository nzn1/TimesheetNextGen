<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_EXPENSE')."</title>");
PageElements::setTheme('newcss');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclExpenses'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");



//load client id from superglobals
$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id']: 1;

$startDate = mktime(0,0,0, gbl::getMonth(), gbl::getDay(), gbl::getYear());
$start_day = date("d", $startDate);
$start_month = date("n", $startDate);
$start_year = date("Y", $startDate);;

// get a list of expense categories
	$query = "SELECT cat_id, description FROM ". tbl::getExpenseCategoryTable();
	list($qcat, $num_cat) = dbQuery($query);

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/exp_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">
<div><label><?php echo JText::_('CLIENT'); ?>:</label><?php Common::client_select_list($client_id, 0, false, false, false, true, "", "submit();"); ?></div>
<div><label><?php echo JText::_('PROJECT'); ?>:</label><?php Common::project_select_list(gbl::getClientId(), false, gbl::getProjId(), gbl::getContextUser(), false, true, false); ?></div>
<div><label><?php echo JText::_('CATEGORY'); ?>:</label>
	<select name="category">
		<?php 
			for ($i=1; $i<=$num_cat; $i++) {
				$data = dbResult($qcat);
				print "<option value=\"". $data['cat_id'] . "\">" . $data['description'] . "</option>";
			}
		?>
	</select>
</div>
<div><label><?php echo JText::_('DESCRIPTION'); ?>:</label><textarea name="description" rows="4" cols="40" wrap="virtual"></textarea></div>
<div><label><?php echo JText::_('DATE_EXPENSE_INCURRED'); ?>:</label>
	<?php Common::day_button("exp_day", $start_day); Common::month_button("exp_month", $start_month); Common::year_button("exp_year", $start_year); ?>
</div>
<div><label><?php echo JText::_('BILLABLE'); ?>:</label>
	<select name="billable">
		<option value="0"><?php echo JText::_('BILLABLE'); ?></option>
		<option value="1"><?php echo JText::_('INTERNAL'); ?></option>
		<option value="2"><?php echo JText::_('PERSONAL'); ?></option>
	</select>
</div>
<div><label><?php echo JText::_('AMOUNT'); ?>:</label><input type="text" name="amount" size="15" /></div>
<div><label></label><input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_EXPENSE'); ?>"</div>

</div>
</form>