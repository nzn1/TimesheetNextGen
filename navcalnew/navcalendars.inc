<?php
if(!class_exists('Site'))die('Restricted Access');
include("navcalnew/common.inc");
?>
<!-- Navigation Calendars -->
	<table width="100%">
		<tr>
			<td width="25%">
				<table>
					<tr>
						<td>
						<?php 
							if(gbl::getMonth()<7)
								draw_month_year_navigation(gbl::getYear()-1);
							else
								draw_month_year_navigation(gbl::getYear());
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php include("navcalnew/navcal_core.inc"); ?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if(gbl::getMonth()<7)
								draw_month_year_navigation(gbl::getYear());
							else
								draw_month_year_navigation(gbl::getYear()+1);
						?>
						</td>
					</tr>
				</table>
			</td>
			<?php if(Common::getMotd()) include("navcalnew/motd.inc"); ?>
		</tr>
	</table>
<!-- End Navigation Calendars -->

<?php
// vim:ai:ts=4:sw=4:filetype=php
?>
