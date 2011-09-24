<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CLIENTS')."</title>");

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

?>

<script type="text/javascript">

	function delete_client(clientId) {
				if (confirm("<?php echo JText::_('JS_CONFIRM_DELETE_CLIENT'); ?>"))
					location.href = '<?php echo Config::getRelativeRoot(); ?>/clients/client_action?client_id=' + clientId + '&action=delete';
	}

</script>
<?php 
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CLIENTS')." | ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('newcss');
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');
?>
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">

<h1><?php echo JText::_('CLIENTS'); ?></h1>

	<table>
		<tr>
		<td align="right">
			<a href="client_add" class="outer_table_action"><?php echo JText::_('ADD_NEW_CLIENT')?></a>
		</td>
		</tr>
	</table>

	<table>
		
<?php

//execute the query
list($qh,$num) = dbQuery("SELECT * FROM ".tbl::getClientTable()." ORDER BY organisation");

//are there any results?
if ($num == 0) {
	print "<tr><td align=\"center\" colspan=\"5\"><br />".JText::_('NO_CLIENTS')." &nbsp; ";
	print "<a href=\"client_add\" class=\"outer_table_action\">".JText::_('CLICK_HERE_TO_ADD_ONE')."</a><br /><br /></td></tr>";
} else {

	print "<thead><tr>";
	print "<th>".ucfirst(JText::_('ORGANISATION'))."</th>";
	print "<th>".ucfirst(JText::_('CONTACT_NAME'))."</th>";
	print "<th>".ucfirst(JText::_('EMAIL'))."</th>";
	print "<th>".ucfirst(JText::_('PHONE'))."</th>";
	print "<th align=\"right\" width=\"10%\" ><i>".ucfirst(JText::_('ACTIONS'))."</i></th>";
	print "</tr></thead><tbody>";

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
		print "<td><a href=\"javascript:void(0)\" onclick=\"window.open('client_info?client_id=$data[client_id]','ClientInfo','location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=260')\">$organisationField</a></td>";
		print "<td>$contactNameField</td>";
		print "<td>$emailField</td>";
		print "<td>$phoneField</td>";
		print "<td>\n";
		print "	<a href=\"client_edit?client_id=$data[client_id]\">".JText::_('EDIT')."</a>,&nbsp;\n";
		print "	<a href=\"javascript:delete_client($data[client_id]);\">".JText::_('DELETE')."</a>\n";
		print "</td>\n";
		$count++;
	}
}
?>

</table>

</form>
