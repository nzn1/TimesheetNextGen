<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;


$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

//define the command menu

?>
<head><title>Rates Management Page</title>
<?php
$layout = Common::getLayout();

PageElements::setHead("<title>".Config::getMainTitle()." | Simple Weekly Timesheet for ".gbl::getContextUser()."</title>");

if (isset($popup))
	PageElements::setBodyOnLoad("onLoad=window.open(\"clock_popup.php?proj_id=".gbl::getProjId()."&task_id=$task_id\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");");

?>
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
			alert("You must enter a billing rate.");
		else
		{
			document.rateForm.action.value = "addupdate";
			document.rateForm.submit();
		}
	}
</script>
</head>

<form action="<?php echo Config::getRelativeRoot(); ?>/rate_action" name="rateForm" method="post">
<input type="hidden" name="action" value="" />
<input type="hidden" name="rate_id" value="" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
			<h1>Rates:</h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr class="inner_table_head">
		<td class="inner_table_column_heading">&nbsp;Rate Id</td>
		<td class="inner_table_column_heading">&nbsp;Bill Rate(per hour)</td>
		<td class="inner_table_column_heading">&nbsp;<i>Actions</i></td>
	</tr>
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
		<td align="left" nowrap class="outer_table_heading">
			<a name="AddEdit">	Add/Update Rates:</a>
		</td>
	</tr>

	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
		<td>Bill rate(per hour):&nbsp;<input size="5" name="bill_rate" style="width: 25%;" />
				<input type="button" name="addupdate" value="Add/Update Rates" onclick="javascript:addRate()" class="bottom_panel_button" /></td>
	</tr>
	<tr>
		<td align="left">
		    <a href="<?php echo Config::getRelativeRoot(); ?>/project_user_rates">Rate Selection</a>
		</td>
	</tr>

</table>

</form>
