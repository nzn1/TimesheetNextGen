<?php
//Get the result set for the config set 1

if(!class_exists('Site')){
	list($qhq, $numq) = dbQuery("SELECT footerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
	$configData = dbResult($qhq);
	parse_and_echo(stripslashes($configData['footerhtml']));
}
else {

	$footer = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
  <tr>
    <td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">TimesheetNextGen 
      <br />
      <span style=\"font-size: 9px;\"><b>Page generated %time% %date% (%timezone% time)</b>
      </span></td>
  </tr>
</table>";
	
	Common::parse_and_echo(stripslashes($footer));
}
?>
<table width="100%" class="grey_area">
	<tr>
		<td width="10px"> </td>
		<td class="timesheet_logo" width="30%">
			<?php 
			if(!class_exists('Site')){
				$ver = getVersion();
			}
			else{
				$ver = Common::getVersion();
			}
			
			echo "TimesheetNextGen &nbsp;&nbsp; v$ver";
			?>
		</td>
		<td> </td>
		<td align="center" width="30%">
			&copy; 2008-2010 <a href="http://sourceforge.net/projects/tsheetx">Tsheetx Development Team</a><br />
		</td>
		<td> </td>
		<td align="right" width="30%">
			Usage under terms of <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a>.
		</td>
		<td width="10px"> </td>
	</tr>
</table>
