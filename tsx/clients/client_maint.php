<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CLIENTS')."</title>");

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

?>

<HTML>
<head>

<script type="text/javascript">

	function delete_client(clientId) {
				if (confirm("<?php echo JText::_('JS_CONFIRM_DELETE_CLIENT'); ?>"))
					location.href = '<?php echo Config::getRelativeRoot(); ?>/clients/client_action?client_id=' + clientId + '&action=delete';
	}

</script>
</head>
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">

<h1><?php echo JText::_('CLIENTS'); ?></h1>

	<table width="100%" border="0">
		<tr>
		<td align="right">
			<a href="client_add" class="outer_table_action"><?php echo JText::_('ADD_NEW_CLIENT')?></a>
		</td>
		</tr>
	</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<!--  table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body" -->
<?php

//execute the query
list($qh,$num) = dbQuery("SELECT * FROM ".tbl::getClientTable()." ORDER BY organisation");

//are there any results?
if ($num == 0) {
	print "<tr><td align=\"center\" colspan=\"5\"><br />".JText::_('NO_CLIENTS')." &nbsp; ";
	print "<a href=\"client_add\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br><br></td></tr>";
} else {

	print "<tr class=\"inner_table_head\">";
	print "<td class=\"inner_table_column_heading\">".ucfirst(JText::_('ORGANISATION'))."</td>";
	print "<td class=\"inner_table_column_heading\">".ucfirst(JText::_('CONTACT_NAME'))."</td>";
	print "<td class=\"inner_table_column_heading\">".ucfirst(JText::_('EMAIL'))."</td>";
	print "<td class=\"inner_table_column_heading\">".ucfirst(JText::_('PHONE'))."</td>";
	print "<td class=\"inner_table_column_heading\" align=\"right\" width=\"10%\" ><i>".ucfirst(JText::_('ACTIONS'))."</i></td>";

	$count = 0;
	while ($data = dbResult($qh)) {
		$organisationField = stripslashes($data["organisation"]);
		if (empty($organisationField))
			$organisationField = "&nbsp;";
		$contactNameField = $data["contact_first_name"] . "&nbsp;" . $data["contact_last_name"];
		$phoneField = $data["phone_number"];
		if (empty($phoneField))
			$phoneField = "&nbsp;";
		$emailField = $data["contact_email"];
		if (empty($emailField))
			$emailField = "&nbsp;";
		if (($count % 2) == 1)
			print "<tr class=\"diff\">";
		else
			print "<tr>";
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=window.open(\"client_info?client_id=$data[client_id]\",\"ClientInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=260\")>$organisationField</a></td>";
		print "<td class=\"calendar_cell_middle\">$contactNameField</td>";
		print "<td class=\"calendar_cell_middle\">$emailField</td>";
		print "<td class=\"calendar_cell_middle\">$phoneField</td>";
		print "<td class=\"calendar_cell_disabled_right\">\n";
		print "	<a href=\"client_edit?client_id=$data[client_id]\">".JText::_('EDIT')."</a>,&nbsp;\n";
		print "	<a href=\"javascript:delete_client($data[client_id]);\">".JText::_('DELETE')."</a>\n";
		print "</td>\n";
		$count++;
	}
}
?>

</table>

</form>
