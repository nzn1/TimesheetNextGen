<?php

class Clocking{

  public function __construct(){
  }
  
  public function createClockOnOff($currentDate,$fromPopup=false,$enableShowHideLink = true,$stopwatch=false){
  
  if($currentDate == null){
    $currentDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
  }
  ?>
  <!-- clock on/off form -->
  <?php
  if(true == $enableShowHideLink){
    echo "<p><a href=\"javascript:void(0)\" onclick=\"javascript:clockingShowHide('clockOnOffTable');\">".JText::_('SHOW_HIDE_CLOCK')."</a></p>";
    $style = "style=\"display:none;\"";
  }
  else $style = ''; 
?>
<table <?php echo $style;?> id="clockOnOffTable" width="436" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">
				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							<?php echo JText::_('CLOCK_ON_OFF'); ?>
						</td>
					</tr>
				</table>

<?php 
	$destination=$_SERVER["PHP_SELF"];
	//include('clockOnOff_core_new.inc')
	$this->stopWatchClockForm($currentDate,$fromPopup,$stopwatch,$destination);
?>

			</td>
		</tr>
	</table>
  <?php
  
  }

  private function stopWatchClockForm($currentDate,$fromPopup,$stopwatch,$destination){
  
  require_once('form_input.inc');
?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<form action="<?php echo Config::getRelativeRoot(); ?>/clock_action" method="post" name="theForm" id="theForm">
		<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
		<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
		<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
		<input type="hidden" id="client_id" name="client_id" value="<?php echo gbl::getClientId(); ?>" />
		<input type="hidden" id="proj_id" name="proj_id" value="<?php echo gbl::getProjId(); ?>" />
		<input type="hidden" id="task_id" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />
		<input type="hidden" name="clockonoff" value="" />
		<input type="hidden" name="fromPopupWindow" value="<?php echo $fromPopup; ?>" />
		<input type="hidden" name="origin" value="<?php echo $_SERVER["PHP_SELF"]; ?>" />
		<input type="hidden" name="destination" value="<?php echo $destination; ?>" />

		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr>
						<td>
							<table width="100%" border="0">
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td><?php echo JText::_('CLIENT')?>:</td></tr></table></td>
													<td width="100%">
														<select id="clientSelect" name="clientSelect" onChange="onChangeClientSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td align="left" width="100%" nowrap>
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td><?php echo JText::_('PROJECT')?>:</td></tr></table></td>
													<td width="100%">
														<select id="projectSelect" name="projectSelect" onChange="onChangeProjectSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td align="left" width="100%">
											<table width="100%" border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td><table width="50"><tr><td><?php echo JText::_('TASK')?>:</td></tr></table></td>
													<td width="100%">
														<select id="taskSelect" name="taskSelect" onChange="onChangeTaskSelect();" style="width: 100%;" />
													</td>
												</tr>
											</table>
									</td>
								</tr>
								<tr>
									<td>
									<?php if($stopwatch) { ?>
										<table width="100%" height="100%" border="0" cellpadding="0" cellaspacing="0">
											<tr height="100%">
												<td valign="center">
													<table width="100%" height="100%" border="0" cellpadding="0" cellaspacing="0">
														<tr height="100%">
															<td align="right">
																<a href="javascript:doClockonoff('clockonnow')"><img src="images/clock-green.gif" alt="" width="48" height="48" border="0" align="absmiddle" /></a>
															</td>
															<td nowrap>
																<a href="javascript:doClockonoff('clockonnow')"><font size="4" color="#0DB400" face="Arial"><?php echo JText::_('CLOCK_ON_NOW')?></font></a>
															</td>
															<td align="right">
																<a href="javascript:doClockonoff('clockoffnow')"><img src="images/clock-red.gif" width="48" height="48" border="0" align="absmiddle" alt="" /></a>
															</td>
															<td nowrap>
																<a href="javascript:doClockonoff('clockoffnow')"><font size="4" color="#E81500" face="Arial"><?php echo JText::_('CLOCK_OFF_NOW')?></font></a>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
										<?php } else { ?>
										<table width="100%" border="0">
											<tr>
												<td align="center">
													<table width="400" border="0" class="clock_on_box">
														<tr>
															<td valign="top" align="left" class="clock_on_text">
																<input type="checkbox" name="clock_on_check" id="clock_on_check" onclick="enableClockOn();" /><?php echo JText::_('CLOCK_ON_AT')?>
															</td>
															<td valign="middle">
																<?php // If the current day is today:
																if ($currentDate == Common::getRealTodayDate()): ?>
																	<input type="radio" name="clock_on_radio" id="clock_on_radio_date" value="date" onclick="enableClockOn();" checked="checked"  />
																<?php endif; ?>
																<?php $hourInput = new HourInput("clock_on_time_hour");
																	$hourInput->create(10); ?>
																:
																<?php $minuteInput = new MinuteInput("clock_on_time_min");
																	$minuteInput->create(); ?>
															</td>
															<td>
																<img src="<?php echo Config::getRelativeRoot();?>/images/clock-green-sml.gif" border="0" alt="" />
															</td>
														</tr>
														<?php // If the current day is today:
														if ($currentDate == Common::getRealTodayDate()): ?>
														<tr>
															<td>&nbsp;</td>
															<td valign="middle" align="left" class="clock_on_text">
																<input type="radio" name="clock_on_radio" id="clock_on_radio_now" value="now" onclick="enableClockOn();" />	now
															</td>
															<td>&nbsp;</td>
														</tr>
														<?php endif; ?>
													</table>
												</td>
											</tr>
											<tr>
												<td align="center">
													<table width="400" border="0" class="clock_off_box">
														<tr>
															<td valign="top" align="left" class="clock_off_text">
																<input type="checkbox" name="clock_off_check" id="clock_off_check" onclick="enableClockOff();" /><?php echo JText::_('CLOCK_OFF_AT')?>
															</td>
															<td valign="middle">
																<?php // If the current day is today:
																if ($currentDate == Common::getRealTodayDate()): ?>
																	<input type="radio" name="clock_off_radio" id="clock_off_radio_date" value="date" onclick="enableClockOff();" />
																<?php endif; ?>
																<?php $hourInput = new HourInput("clock_off_time_hour");
																	$hourInput->create(17); ?>
																:
																<?php $minuteInput = new MinuteInput("clock_off_time_min");
																	$minuteInput->create(); ?>
															</td>
															<td>
																<img src="<?php echo Config::getRelativeRoot();?>/images/clock-red-sml.gif" border="0" alt="" />
															</td>
														</tr>
														<?php // If the current day is today:
														if ($currentDate == Common::getRealTodayDate()): ?>
														<tr>
															<td>&nbsp;</td>
															<td valign="middle" align="left" class="clock_off_text">
																<input type="radio" name="clock_off_radio" id="clock_off_radio_now" value="now" onclick="enableClockOff();" checked="checked"  />now
															</td>
															<td>&nbsp;</td>
														</tr>
														<?php endif; ?>
													</table>
												</td>
											</tr>
											<tr>
												<td align="center">
													<input type="button" value="Clock on and/or off" name="submitButton" id="submitButton" onclick="validate();" />
												</td>
											</tr>
										</table>
										<?php } ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</form>
	</table>
	<?php
  
  }

}

?>