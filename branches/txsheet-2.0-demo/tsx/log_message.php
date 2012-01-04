<?php
if(!class_exists('Site'))die('Restricted Access');
	
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

//load local vars from request/post/get


require_once Config::getDocumentRoot().'/tsx/clock_action.class.php';
$ca = new ClockAction();

// create the command menu cancel option
Site::getCommandMenu()->add(new TextCommand("Cancel", true, $ca->getDestination()."?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()));

PageElements::setHead("<title>".Config::getMainTitle()." | Clock off - Enter log message</title>");
?>

<form action="<?php echo Config::getRelativeRoot(); ?>/clock_action" method="post">
	<input type="hidden" name="origin" value="<?php echo $ca->getOrigin(); ?>" />
	<input type="hidden" name="destination" value="<?php echo $ca->getDestination(); ?>" />
	<input type="hidden" name="clock_on_time_hour" value="<?php echo $ca->getClockOnTimeHour(); ?>" />
	<input type="hidden" name="clock_off_time_hour" value="<?php echo $ca->getClockOffTimeHour(); ?>" />
	<input type="hidden" name="clock_on_time_min" value="<?php echo $ca->getClockOnTimeMin(); ?>" />
	<input type="hidden" name="clock_off_time_min" value="<?php echo $ca->getClockOffTimeMin(); ?>" />
	<input type="hidden" name="year" value="<?php echo gbl::getYear() ?>" />
	<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
	<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
	<input type="hidden" name="client_id" value="<?php echo gbl::getClientId(); ?>" />
	<input type="hidden" name="proj_id" value="<?php echo gbl::getProjId(); ?>" />
	<input type="hidden" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />
	<input type="hidden" name="clockonoff" value="<?php echo $ca->getClockOnOff(); ?>" />
	<input type="hidden" name="log_message_presented" value="1" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" class="outer_table_heading">
							Enter Log Message
						</td>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body">
					<tr class="inner_table_head">
						<td class="inner_table_column_heading">Please Enter Log message: (max 255 characters)</td>
					</tr>
					<tr>
						<td>
							<textarea name="log_message" cols="60" rows="4" style="width: 100%;"></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" class="table_bottom_panel">
					<tr>
						<td align="center">
							<input type="submit" value="Done" />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
		</td>
	</tr>
</table>

</form>
