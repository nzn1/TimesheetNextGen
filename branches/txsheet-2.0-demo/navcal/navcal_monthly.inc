<?php
if(class_exists('Site'))die('Error: old file.  please use navcalnew/navcal_monthly.inc');
include("navcal/common.inc");
?>
<!-- Monthly Navigation Calendars > -->
	<table width="100%">
		<tr>
			<td width="25%">
				<table>
					<tr>
						<td>
						<?php 
						if(!class_exists('Site')){
							if($month<7)
								draw_month_year_navigation($year-1,"wide");
							else
								draw_month_year_navigation($year,"wide");
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if($month<7)
								draw_month_year_navigation($year,"wide");
							else
								draw_month_year_navigation($year+1,"wide");
						}
						else		
						{
							if(gbl::getMonth()<7)
								draw_month_year_navigation(gbl::getYear()-1,"wide");
							else
								draw_month_year_navigation(gbl::getYear(),"wide");
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if(gbl::getMonth()<7)
								draw_month_year_navigation(gbl::getYear(),"wide");
							else
								draw_month_year_navigation(gbl::getYear()+1,"wide");
						}
								?>
						
						</td>
					</tr>
				</table>
			</td>
			<?php 
				if(!class_exists('Site')){
					if($motd) include("navcal/motd.inc");
				}
				else{
					if(Common::getMotd()) include("navcal/motd.inc");
				}
				 
			?>
		</tr>
	</table>
<!-- End Montly Navigation Calendars -->
