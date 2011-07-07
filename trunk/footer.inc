<?php
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT footerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["footerhtml"]));
?>
<p>
<table width="100%" class="grey_area">
	<tr>
		<td width="10px"> </td>
		<td class="timesheet_logo" width="30%">
			<?php 
			$ver = getVersion();
			echo "TimesheetNextGen &nbsp;&nbsp; v$ver";
			?>
		</td>
		<td> </td>
		<td align="center" width="30%">
			&copy; 2008-2011 <a href="http://sourceforge.net/projects/tsheetx">Tsheetx Development Team</a><br />
		</td>
		<td> </td>
		<td align="right" width="30%">
			Usage under terms of <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a>.
		</td>
		<td width="10px"> </td>
	</tr>
</table>
