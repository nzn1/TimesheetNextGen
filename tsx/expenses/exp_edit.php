<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_EXPENSE')."</title>");
PageElements::setTheme('txsheet2');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclExpenses'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

//load client id from superglobals
$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id']: 1;

$expense_id = isset($_REQUEST['eid']) ? $_REQUEST['eid']: 0;
// get the details of this expense item
	$query = "SELECT cat_id, cat_name, cat_description FROM ". 
			tbl::getExpenseCategoryTable();
	list($qcat, $num_cat) = dbQuery($query);
	
// get a list of all the expense categories
	$query = "SELECT cat_id, proj_id, client_id, billable, unix_timestamp(date) AS start_stamp, DATE(date) AS date, description, amount, status FROM ". 
			tbl::getExpenseTable(). " WHERE eid = '". $expense_id . "'";
	list($qexp, $num_exp) = dbQuery($query);
	$exp = dbResult($qexp);
?>

<form action="<?php echo Config::getRelativeRoot(); ?>/expenses/exp_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="eid" value="<?php echo $expense_id?>" />
<h1><?php echo JText::_('EDIT_EXPENSE');?></h1>
<div id="inputArea">
<div><label><?php echo JText::_('CLIENT'); ?>:</label><?php Common::client_select_list($exp['client_id'], 0, false, false, false, true, "", "submit();"); ?></div>
<div><label><?php echo JText::_('PROJECT'); ?>:</label><?php Common::project_select_list($exp['client_id'], false, $exp['proj_id'], gbl::getContextUser(), false, true, false); ?></div>
<div><label><?php echo JText::_('CATEGORY'); ?>:</label>
	<select name="category">
		<?php 
			for ($i=1; $i<=$num_cat; $i++) {
				$data = dbResult($qcat);
				if ($data['cat_id'] ==  $exp['cat_id'])
					print "<option value=\"". $data['cat_id'] . "\" selected=\"selected\">" . $data['cat_name'] . "</option>";
				else
					print "<option value=\"". $data['cat_id'] . "\">" . $data['cat_name'] . "</option>";
			}
		?>
	</select>
</div>
<div><label><?php echo JText::_('DESCRIPTION'); ?>:</label><textarea name="description" rows="4" cols="40" wrap="virtual"><?php print $exp['description']?></textarea></div>
<div><label><?php echo JText::_('DATE_EXPENSE_INCURRED'); ?>:</label>
	<?php Common::day_button("exp_day", $exp['start_stamp']); Common::month_button("exp_month", date('m',strtotime($exp['date']))); Common::year_button("exp_year", date('Y',strtotime($exp['date']))); ?>
</div>
<div><label><?php echo JText::_('BILLABLE'); ?>:</label>
	<select name="billable">
		<?php 
			echo "<option value=\"0\"";
			if ($exp['billable'] == 'Billable')
				print " selected=\"selected\"";
			echo ">" . JText::_('BILLABLE'). "</option>"; 
			echo "<option value=\"1\"";
			if ($exp['billable'] == 'Internal')
				print " selected=\"selected\"";
			echo ">" . JText::_('INTERNAL'). "</option>"; 
			echo "<option value=\"2\"";
			if ($exp['billable'] == 'Personal')
				print " selected=\"selected\"";
			echo ">" . JText::_('PERSONAL'). "</option>"; 
		?>
	</select>
</div>
<div><label><?php echo JText::_('AMOUNT'); ?>:</label><input type="text" name="amount" size="15" value="<?php print $exp['amount'];?>"/></div>
<div><label></label><input type="submit" name="add" value="<?php echo JText::_('SUBMIT'); ?>"</div>

</div>
</form>