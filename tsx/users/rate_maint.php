<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

$layout = Common::getLayout();
ob_start();
//PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('USER_RATES')."</title>");

//PageElements::setHead(PageElements::getHead().ob_get_contents());
PageElements::setTheme('newcss');

?>
<title><?php echo Config::getMainTitle();?> - Simple Weekly Timesheet for <?php echo gbl::getContextUser();?></title>
<script type="text/javascript" type="text/javascript">

	function editRate(rateId, billRate)
	{
		document.rateForm.rate_id.value = rateId;
		document.rateForm.bill_rate.value = billRate;
		document.location.href = "#AddEdit";
	}

	function addRate()
	{
		//validation
		if (document.rateForm.bill_rate.value == "")
			alert("<?php echo JText::_('JS_ALERT_ADD_BILLING_RATE')?>");
		else
		{
			document.rateForm.action.value = "addupdate";
			document.rateForm.submit();
		}
	}
</script>
<?php 
	ob_end_clean();
?>

<form action="<?php echo Config::getRelativeRoot(); ?>/users/rate_action" name="rateForm" method="post">
<input type="hidden" name="action" value="" />
<input type="hidden" name="rate_id" value="" />

<h1><?php echo JText::_('USER_RATES'); ?>:</h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<thead>
	<tr>
		<th><?php echo JText::_('RATE_ID'); ?></td>
		<th><?php echo JText::_('BILLING_RATE_BYHOUR'); ?></td>
		<th><?php echo JText::_('ACTIONS'); ?> </i></td>
	</tr>
	</thead>
	<tbody>
<?php

list($qh,$num) = dbQuery("select * from ".tbl::getRateTable()." where rate_id != 1 order by rate_id");

$count = 0;
while ($data = dbResult($qh)) {
	$billRateIdField = empty($data["rate_id"]) ? "&nbsp;": $data["rate_id"];
	$billRateField = empty($data["bill_rate"]) ? "&nbsp;": $data["bill_rate"];
	if (($count % 2) == 1)
			print "<tr class=\"diff\">\n";
		else
			print "<tr>\n";
	print "<td class=\"calendar_cell_middle\">&nbsp;$billRateIdField</td>";
	print "<td class=\"calendar_cell_middle\">&nbsp;$billRateField</td>";
	print "<td class=\"calendar_cell_disabled_right\">";
	print "	<a href=\"javascript:editRate('$data[rate_id]', '$data[bill_rate]')\">&nbsp;Edit</a>\n";
	print "</td>\n";
	print "</tr>\n";
	$count++;
}
?>

	</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!--  td width="100%" class="face_padding_cell" -->
		<td align="left" class="outer_table_heading">
			<a name="AddEdit">	<?php echo JText::_('ADD_RATES'); ?></a>
		</td>
	</tr>

	<tr>
		<td><?php echo JText::_('BILLING_RATE_BYHOUR'); ?>&nbsp;<input size="5" name="bill_rate" style="width: 25%;" />
				<input type="button" name="addupdate" value="<?php echo JText::_('ADD_RATES'); ?>" onclick="javascript:addRate()" class="bottom_panel_button" /></td>
	</tr>
	<tr>
		<td align="left">
		    <a href="<?php echo Config::getRelativeRoot(); ?>/projects/project_user_rates"><?php echo JText::_('RATE_SELECTION'); ?></a>
		</td>
	</tr>

</table>

</form>
