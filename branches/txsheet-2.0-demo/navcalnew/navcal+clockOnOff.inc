<?php
if(!class_exists('Site'))die('Restricted Access');
include("navcalnew/common.inc");
?>
<!-- Navigation Calendar with clockOnOff form -->
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
			<td width="10">&nbsp;
			</td>
			<td align="left" valign="middle" class="outer_table_heading">
				<?php include ("include/tsx/clockOnOff.inc"); ?>
			</td>
			<td width="30%">&nbsp;
			</td>
		</tr>
	</table>
<!-- End Navigation Calendar with clockOnOff form -->
<?php
// vim:ai:ts=4:sw=4:filetype=php
?>
