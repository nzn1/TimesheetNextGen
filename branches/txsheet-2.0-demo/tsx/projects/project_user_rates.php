<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('PROJECT_RATES')." | ".gbl::getContextUser()."</title>");

?>
<head>


</head>

<h1><?php echo JText::_('PROJECT_RATES'); ?></h1>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<td align="left" class="outer_table_heading">
				<?php echo JText::_('ALL_PROJECTS') ?>
		</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
	</tr>

	<tr class="inner_table_head">
		<td class="inner_table_column_heading">&nbsp;<?php echo JText::_('PROJECT_TITLE') ?></td>
		<td class="inner_table_column_heading">&nbsp;<?php echo JText::_('CLIENT') ?></td>
		<td class="inner_table_column_heading">&nbsp;<i><?php echo JText::_('ACTIONS') ?></i></td>
	</tr>
<?php


list($qh,$num) = dbQuery(
					"SELECT p.proj_id, p.title, c.organisation ".
					"FROM  ".tbl::getProjectTable()."  p,  ".tbl::getClientTable()."  c ".
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
	print "<td class=\"calendar_cell_middle\">";
	print "	<a href=\"project_user_rates_action?proj_id=$data[proj_id]&amp;action=show_users\">&nbsp;".JText::_('EDIT_RATES')."</a>\n";
	print "</td>\n";
	print "</tr>\n";
	$n++;
}
?>

	</td>
	</tr>
</table>
