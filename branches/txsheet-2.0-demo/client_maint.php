<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclSimple'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	}
	
	exit;
}

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

if (empty($contextUser))
	errorPage("Could not determine the context user");

//make sure "No Client exists with client_id of 1
//execute the query
$CLIENT_TABLE = tbl::getClientTable();
tryDbQuery("INSERT INTO $CLIENT_TABLE VALUES (1,'No Client', 'This is required, do not edit or delete this client record', '', '', '', '', '', '', '', '', '', '', '', '', '', '');");
tryDbQuery("UPDATE $CLIENT_TABLE set organisation='No Client' WHERE client_id='1'");

?>

<HTML>
<head>
<title>Client Management Page</title>

<script type="text/javascript">

	function delete_client(clientId) {
				if (confirm('Are you sure you want to delete this client?'))
					location.href = 'client_action.php?client_id=' + clientId + '&action=delete';
	}

</script>
</head>
<form action="client_action.php" method="post">

	<table width="100%" border="0">
		<tr>
		<td align="left" nowrap class="outer_table_heading">
			Clients
		</td>
		<td align="right">
			<a href="client_add" class="outer_table_action">Add new client</a>
		</td>
		</tr>
	</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<!--  table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body" -->
<?php

//execute the query
list($qh,$num) = dbQuery("SELECT * FROM $CLIENT_TABLE WHERE client_id > 1 ORDER BY organisation");

//are there any results?
if ($num == 0) {
	print "<tr><td align=\"center\" colspan=\"5\"><br />There are currently no clients.<br /><br /></td></tr>";
}
else {

?>
			<tr class="inner_table_head">
				<td class="inner_table_column_heading">Organisation</td>
				<td class="inner_table_column_heading">Contact Name</td>
				<td class="inner_table_column_heading">Phone</td>
				<td class="inner_table_column_heading">Contact Email</td>
				<td class="inner_table_column_heading"><i>Actions</i></td>
			</tr>
<?php
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
		print "<td class=\"calendar_cell_middle\"><a href=\"javascript:void(0)\" onclick=window.open(\"client_info?client_id=$data[client_id]\",\"ClientInfo\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=480,height=240\")>$organisationField</a></td>";
		print "<td class=\"calendar_cell_middle\">$contactNameField</td>";
		print "<td class=\"calendar_cell_middle\">$phoneField</td>";
		print "<td class=\"calendar_cell_middle\">$emailField</td>";
		print "<td class=\"calendar_cell_disabled_right\">\n";
		print "	<a href=\"client_edit?client_id=$data[client_id]\">Edit</a>,&nbsp;\n";
		print "	<a href=\"javascript:delete_client($data[client_id]);\">Delete</a>\n";
		print "</td>\n";
		$count++;
	}
}
?>
				
			</td>
		</tr>
	</table>

		</td>
	</tr>
</table>

</form>

