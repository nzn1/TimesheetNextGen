<?php
if(!class_exists('Site'))die('Restricted Access');

require_once('navcal-common.class.php');
// navigational calendar functions
class NavCal extends NavCalCommon{
  
  public function __construct(){
  }
  
	public function navCalCore(){
    $todayDate = mktime(0,0,0,gbl::getMonth(),gbl::getDay(),gbl::getYear());
    $startDateCalendar = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());
    $startDayOfWeek = Common::getWeekStartDay();	
    $dowForFirstOfMonth = date('w',mktime(0,0,0,gbl::getMonth(),1,gbl::getYear()));
    $leadInDays = $dowForFirstOfMonth - $startDayOfWeek;
    if ($leadInDays < 0)
    	$leadInDays += 7;
    $firstPrintedDate = strtotime(date("d M Y H:i:s",$startDateCalendar) . " -$leadInDays days");

?>

	<table width="224" border="1" bordercolor="black" cellspacing="0" cellpadding="0">
		<tr>
			<td width="100%" class="face_padding_cell" style="background-color: #000788">

			<!-- print calendar header (prev month year next) -->

				<table width="100%" border="0">
					<tr >
						<td align="left" nowrap class="navcal_header">
							<?php 
							list($prev_month,$next_month) = $this->getPrevNextMonth($todayDate);
							
							$dti=getdate($prev_month);
							echo "<a href=\"".Rewrite::getShortUri()."?".gbl::getPost()
              ."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;client_id=".gbl::getClientId()
              ."&amp;year=".$dti["year"]."&amp;month=".$dti["mon"]."&amp;day=".$dti["mday"]."\">Prev</a>";
							?>
						</td>
						<td align="center" nowrap class="navcal_header">
							<?php echo date('M Y',$startDateCalendar); ?>
						</td>
						<td align="right" nowrap class="navcal_header">
							<?php
							$dti=getdate($next_month);
              echo "<a href=\"".Rewrite::getShortUri()."?".gbl::getPost()
              ."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;client_id=".gbl::getClientId()
							."&amp;year=".$dti["year"]."&amp;month=".$dti["mon"]."&amp;day=".$dti["mday"]."\">Next</a>";
							?>
						</td>
					</tr>
				</table>

				<!-- print calendar dates  -->
				<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
					<tr>
						<td>
							<table border="0" cellspacing="0" cellpadding="2" class="table_body">
								<tr class="inner_table_head">
									<?php
										//print the days of the week
										$currentDayDate = $firstPrintedDate;
										for ($i=0; $i<7; $i++) {
											$currentDayStr = strftime("%a", $currentDayDate);
											print " <td width=\"25px\" class=\"inner_table_column_heading\" align=\"center\">$currentDayStr</td>\n";
											$currentDayDate = strtotime(date("d M Y H:i:s",$currentDayDate) . " +1 days");
										}
									?>
								</tr>
								<tr>
									<?php {

										//define the variable dayRow
										$dayRow = 0;

										// Print last months' days spots.
										for ($i=0; $i<$leadInDays; $i++) {
											print "<td width=\"25px\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
											$dayRow++;
										}

										$i=0; $navday = 1;
										while (checkdate(gbl::getMonth(), $navday, gbl::getYear())) {

											// New Week.
											if ((($dayRow % 7) == 0) && ($dowForFirstOfMonth != 0)) {
												print "</tr>\n<tr>\n";
											} else
												$dowForFirstOfMonth = 1;

											//define subtable
											if (($dayRow % 7) == 6)
												print "<td width=\"25px\" height=\"25%\" align=\"center\" valign=\"top\" class=\"calendar_cell_right\">";
											else
												print "<td width=\"25px\" height=\"25%\" align=\"center\" valign=\"top\" class=\"calendar_cell_middle\">";

											if($navday == gbl::getDay()) 
												print "<font color=\"#CC9900\"><b>$navday</b></font>";
											else{
											//
											echo "<a href=\"".Rewrite::getShortUri()."?".gbl::getPost()											
                      ."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;client_id=".gbl::getClientId()
                      ."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=$navday\">$navday</a>";	
											}

											print " </td>\n";

											$navday++;
											$dayRow++;
										}
										// Print the rest of the calendar.
										while (($dayRow % 7) != 0) {
											if (($dayRow % 7) == 6)
												print " <td width=\"25px\" height=\"25%\" class=\"calendar_cell_disabled_right\">&nbsp;</td>\n ";
											else
												print " <td width=\"25px\" height=\"25%\" class=\"calendar_cell_disabled_middle\">&nbsp;</td>\n ";
											$dayRow++;
										}
									} ?>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<!-- End calendar dates -->
			</td>
		</tr>
	</table>
<!-- End Daily Navigation Calendar -->
  <?php
  }
  
  
  public function navCalClockOnOff($currentDate,$fromPopup){
?>
<!-- Navigation Calendar with clockOnOff form -->
	<table width="100%">
		<tr>
			<td width="25%" style="vertical-align:top;">
			 <p><a href="javascript:void(0)" onclick="javascript:navcalShowHide('navCal')"><?php echo JText::_('SHOW_HIDE_CALENDAR'); ?></a></p>
			 <script type="text/javascript">
			    function navcalShowHide(id){
            obj = document.getElementById(id);
            var stlSection = obj.style;
            var isCollapsed = obj.style.display.length;
            if (isCollapsed) stlSection.display = '';
            else stlSection.display = 'none';
          }
			 </script>
				<table id="navCal" style="display:none;">
					<tr>
						<td >
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear()-1);
							else
								$this->draw_month_year_navigation(gbl::getYear());
						?>
						</td><td width="2">&nbsp;</td>
            <td style="vertical-align:top;">
						<?php  
              $this->navCalCore();
            ?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear());
							else
								$this->draw_month_year_navigation(gbl::getYear()+1);
						?>
						</td>
					</tr>
				</table>
			</td>
			<td width="10">&nbsp;
			</td>
			<td align="left" style="vertical-align:top;" class="outer_table_heading">
				<?php 
        //include ("include/tsx/clockOnOff.inc"); 
        require("include/tsx/clocking.class.php");
        $clock = new Clocking();
        $clock->createClockOnOff($currentDate);
        
        ?>
			</td>
			<td width="30%">&nbsp;
			</td>
		</tr>
	</table>
<!-- End Navigation Calendar with clockOnOff form -->  
  
<?php  
  
  }
  
  public function navCalMonthly(){
  ?>
  
  <!-- Monthly Navigation Calendars > -->

  <p><a href="javascript:void(0)" onclick="javascript:navcalShowHide('navCal')"><?php echo JText::_('SHOW_HIDE_CALENDAR'); ?></a></p>
  <script type="text/javascript">
    function navcalShowHide(id){
      obj = document.getElementById(id);
      var stlSection = obj.style;
      var isCollapsed = obj.style.display.length;
      if (isCollapsed) stlSection.display = '';
      else stlSection.display = 'none';
    }
  </script>
	<table id="navCal" style="width:100%;display:none;">
		<tr>
			<td width="25%">
				<table>
					<tr>
						<td>
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear()-1,"wide");
							else
								$this->draw_month_year_navigation(gbl::getYear(),"wide");
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear(),"wide");
							else
								$this->draw_month_year_navigation(gbl::getYear()+1,"wide");

								?>
						
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<!-- End Montly Navigation Calendars -->

  <?php
  }
  
  
  public function navCalWithEndDates($start_time,$end_time,$start_month){
  ?>
  
  <!-- Monthly Navigation Calendars > -->
  <p><a href="javascript:void(0)" onclick="javascript:navcalShowHide('navCal')"><?php echo JText::_('SHOW_HIDE_CALENDAR'); ?></a></p>
  <script type="text/javascript">
    function navcalShowHide(id){
      obj = document.getElementById(id);
      var stlSection = obj.style;
      var isCollapsed = obj.style.display.length;
      if (isCollapsed) stlSection.display = '';
      else stlSection.display = 'none';
    }
  </script>
	<table width="100%" id="navCal" style="display:none;">
		<tr>
			<td width="25%">
				<table>
					<tr>
						<td>
						<?php 
							list($s_prev_year,$s_next_year) = $this->getPrevNextYear($start_time);
							list($e_prev_year,$e_next_year) = $this->getPrevNextYear($end_time);
							if($start_month<7)
								$this->draw_month_year_navigation_with_end_dates($s_prev_year,$e_prev_year,"wide");
							else
								$this->draw_month_year_navigation_with_end_dates($start_time,$end_time,"wide");
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if($start_month<7)
								$this->draw_month_year_navigation_with_end_dates($start_time,$end_time,"wide");
							else 
								$this->draw_month_year_navigation_with_end_dates($s_next_year,$e_next_year,"wide");
						?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<!-- End Montly Navigation Calendars -->
  
  <?php
  }
  
  
  public function navCalNormal(){
  ?>
  
  <!-- Navigation Calendars -->
  <p><a href="javascript:void(0)" onclick="javascript:navcalShowHide('navCal')"><?php echo JText::_('SHOW_HIDE_CALENDAR'); ?></a></p>
  <script type="text/javascript">
    function navcalShowHide(id){
      obj = document.getElementById(id);
      var stlSection = obj.style;
      var isCollapsed = obj.style.display.length;
      if (isCollapsed) stlSection.display = '';
      else stlSection.display = 'none';
    }
  </script>
	<table width="100%" id="navCal" style="display:none;">
		<tr>
			<td width="25%">
				<table>
					<tr>
						<td>
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear()-1);
							else
								$this->draw_month_year_navigation(gbl::getYear());
						?>
						</td><td width="2">&nbsp;</td><td>
						<?php  
              $this->navCalCore();  
            ?>
						</td><td width="2">&nbsp;</td><td>
						<?php 
							if(gbl::getMonth()<7)
								$this->draw_month_year_navigation(gbl::getYear());
							else
								$this->draw_month_year_navigation(gbl::getYear()+1);
						?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<!-- End Navigation Calendars -->

  <?php
  
  }
}

?>
