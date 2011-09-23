<?php

	$footer = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
  <tr>
    <td style=\"background-color: #000788; padding: 3;\" class=\"bottom_bar_text\" align=\"center\">TimesheetNextGen 
      <br />
      <span style=\"font-size: 9px;\"><b>".JText::_('PAGE_GENERATED');
	$date=utf8_encode(strftime(JText::_('DFMT_WKDY_MONTH_DAY_YEAR')));  
	$footer.=$date." ".JText::_('TIMEZONE')."</b>
      </span></td>
  </tr>
</table>";
	
	Common::parse_and_echo(stripslashes($footer));
?>
<table width="100%" class="grey_area">
	<tr>
		<td> </td>
		<td class="timesheet_logo" width="30%">
			<?php 		
			 echo "TimesheetNextGen &nbsp;&nbsp; v".Config::getVersion();
			?>
		</td>
		<td> </td>
		<td align="center" width="30%">
			&copy; 2008-<?php $rt=gbl::getTodayDate(); echo $rt['year']; ?> &nbsp;<a href="http://sourceforge.net/projects/tsheetx"><?php echo JText::_('DEVELOPMENT_TEAM') ?></a><br />
		</td>
		<td> </td>
		<td align="right" width="30%">
			<?php echo JText::_('USAGE_TERMS') ?> <a href="http://www.gnu.org/copyleft/gpl.html"><?php echo JText::_('GPL') ?></a>.
		</td>
		<td> </td>
	</tr>
</table>