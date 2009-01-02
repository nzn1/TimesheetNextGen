<?
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT footerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["footerhtml"]));
?>
<p>
<table width="100%" class="grey_area">
	<tr>
		<td class="timesheet_logo">
			TimesheetNextGen
		</td>
		<td align="left">
			&copy; 2008 <a href="http://sourceforge.net/projects/tsheetx">Tsheetx Development Team</a><br>
		</td>
		<td align="right">
			Usage under terms of <A HREF="http://www.gnu.org/copyleft/gpl.html">GPL</a>.
		</td>
	</tr>
</table>
