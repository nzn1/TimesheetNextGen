<?PHP
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT footerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["footerhtml"]));
?>
<p>
<table width="100%" class="grey_area">
	<tr>
		<td class="timesheet_logo">
			Timesheet.php
		</td>
		<td align="left">
			&copy; 2002-2005 Dominic J. Gamble, <a href="http://www.advancen.com">Advancen</a><br>
			&copy; 1998-1999 Peter D. Kovacs.
		</td>
		<td align="right">
			Usage under terms of <A HREF="http://www.gnu.org/copyleft/gpl.html">GPL</a>.
		</td>
	</tr>
</table>
