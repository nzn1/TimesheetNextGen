<?php

include("include/tsx/timesheet_menu.inc");

$banner = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
  <tr>
	<td colspan=\"2\" style=\"background-image: url('".config::getRelativeRoot()."/images/timesheet_background_pattern.gif');\">
	  <img src=\"".config::getRelativeRoot()."/images/timesheet_banner.gif\" alt=\"Timesheet Banner\" /></td>
  </tr>
  <tr>
	<td style=\"background-color: #F2F3FF; padding: 3;\">%commandmenu%</td>
	<td style=\"background-color: #F2F3FF; padding: 3;\" align=\"right\" width=\"145\" valign=\"top\">".
		JText::_('LOGGED_IN_AS')."
	</td>
  </tr>
  <tr>
	<td colspan=\"2\" height=\"1\" style=\"background-color: #758DD6;\">
	  <img src=\"".config::getRelativeRoot()."/images/spacer.gif\" alt=\"\" width=\"1\" height=\"1\" /></td>
  </tr>
</table>";

Common::parse_and_echo(stripslashes($banner));

// vim:ai:ts=4:sw=4:filetype=php
?>
