<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	exit;
}

?>
<head><title>Rates Management Page</title>

</head>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading">
				All Projects:
		</td>
	</tr>

	<tr class="inner_table_head">
		<td class="inner_table_column_heading">&nbsp;Title</td>
		<td class="inner_table_column_heading">&nbsp;Client</td>
		<td class="inner_table_column_heading">&nbsp;<i>Actions</i></td>
	</tr>
<?php

$PROJECT_TABLE = tbl::getProjectTable();
$CLIENT_TABLE = tbl::getClientTable();
list($qh,$num) = dbQuery(
					"SELECT p.proj_id, p.title, c.organisation ".
					"FROM $PROJECT_TABLE p, $CLIENT_TABLE c ".
					"WHERE p.client_id = c.client_id ".
					"ORDER BY c.organisation");

$n=0;
while ($data = dbResult($qh)) {
	$titleField = empty($data["title"]) ? "&nbsp;": $data["title"];
	$organisationField = empty($data["organisation"]) ? "&nbsp;": $data["organisation"];
	if (($n % 2) == 1)
			print "<tr class=\"diff\">\n";
		else
			print "<tr>\n";
	print "<td class=\"calendar_cell_middle\">&nbsp;$titleField</td>";
	print "<td class=\"calendar_cell_middle\">&nbsp;$organisationField</td>";
	print "<td class=\"calendar_cell_disabled_right\">";
	print "	<a href=\"project_user_rates_action?proj_id=$data[proj_id]&amp;action=show_users\">&nbsp;Edit Rates</a>\n";
	print "</td>\n";
	print "</tr>\n";
	$n++;
}
?>

	</td>
	</tr>
</table>

