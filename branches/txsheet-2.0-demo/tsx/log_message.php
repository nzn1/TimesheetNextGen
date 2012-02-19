<?php
if(!class_exists('Site'))die('Restricted Access');
	
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

//load local vars from request/post/get


require_once Config::getDocumentRoot().'/tsx/clock_action.class.php';
$ca = new ClockAction();

// create the command menu cancel option
Site::getCommandMenu()->add(new TextCommand("Cancel", true, $ca->getDestination()."?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay()));

PageElements::setHead("<title>".Config::getMainTitle()." | " .JText::_('LOG_MESSAGE')."</title>");
PageElements::setTheme('txsheet2');
?>
<h1><?php echo JText::_('LOG_MESSAGE'); ?></h1>
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

<div id="inputArea">
	<div><label><?php echo JText::_('ENTER_LOG_MESSAGE'); ?></label>
			<textarea name="log_message" cols="60" rows="4"></textarea></div>
<div><label></label><input type="submit" name="add" value="<?php echo JText::_('SUBMIT'); ?>" /></div>
</div>

</form>
