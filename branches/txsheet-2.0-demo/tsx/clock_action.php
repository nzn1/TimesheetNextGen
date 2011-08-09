<?php 
            
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
trigger_error('WARNING - LOTS OF STUFF IN clock_action has not be converted to OO!');
trigger_error('lots of globals are in here. this is a problem!');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

// Authenticate
//require(Config::getDocumentRoot()."/include/tsx/debuglog.php");
if (!Site::getAuthenticationManager()->isLoggedIn()) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['POST_URI']));
	exit;
}

require_once Config::getDocumentRoot().'/tsx/clock_action.class.php';

$ca = new ClockAction();

if($ca->getSimpleDebug()) {
	$test=http_build_query($_POST);
	$tsize = strlen($test);

	LogFile::write("post size is $tsize\n");
}


if($ca->getSimpleDebug()) {
	LogFile::write(print_r($_POST, TRUE));
}

//LogFile::write("destination = \"".$ca->getDestination()."\"\n");
//LogFile::write("fromPopupWindow = \"".$ca->getFromPopupWindow()."\"\n");

/**
 * @todo the &var= stuff needs to be changed to &amp;var= BUT 
 * if the link is used in javascript then this must be carefully checked
 * as I think javascript doesn't like &amp;  
 */ 
//set the return location
$ca->setLocation($ca->getDestination()."?month=".gbl::getMonth()."&amp;year=".gbl::getYear()."&amp;day=".gbl::getDay()."&amp;destination=".$ca->getDestination());


if ($ca->getDestination() == "stopwatch" || $ca->getDestination() == "daily"){
	$ca->setLocation($ca->getDestination()."?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;month=".gbl::getMonth()."&amp;year=".gbl::getYear()."&amp;day=".gbl::getDay()."&amp;destination=".$ca->getDestination());
}

//determine the action
                 
if ($ca->getClockOnOff() == '') {
	if ($ca->getClockOnCheck() != '' && $ca->getClockOffCheck() != '' ){
		$ca->setClockOnOff("clockonandoff");
	}
	elseif($ca->getClockOnCheck()  != '' ) {
		if ($ca->getClockOnRadio() == "now"){
			$ca->setClockOnOff("clockonnow");
		}
		else{
			$ca->setClockOnOff("clockonat");
		}
	} 
  elseif($ca->getClockOffCheck() != '' ) {
		if ($ca->getClockOffRadio() == "now"){
			$ca->setClockOnOff("clockoffnow");
		}
		else{
			$ca->setClockOnOff("clockoffat");
		}
	} 
  else{
		Common::errorPage("You must select at least one checkbox to indicate your action: clock on, clock off, or both.", $ca->getFromPopupWindow());
	}
}

if ($ca->getClockOnRadio() == "now" && $ca->getClockOffRadio() == "now"){
	Common::errorPage("You cannot clock on and off at with the same clock-on and clock-off time.", $ca->getFromPopupWindow());
}

//get the clock on/off "now" dates and times
//We're forced to recalculate the date as well as the time because the dialog
//box that says "now" could have been opened during the previous day
//(ie. clock on at 11:30pm, clock off at 2:15am)
if ($ca->getClockOnRadio() == "now" || $ca->getClockOnOff() == "clockonnow") {
	$realToday = getdate(time());
	gbl::setMonth($realToday['mon']); 
	gbl::setDay($realToday['mday']);
	gbl::setYear($realToday['year']);
	$ca->setClockOnTimeHour($realToday["hours"]);
	$ca->setClockOnTimeMin($realToday["minutes"]);
}

if ($ca->getClockOffRadio() == "now" || $ca->getClockOnOff() == "clockoffnow") {
	$realToday = getdate(time());
	gbl::setMonth($realToday['mon']); 
	gbl::setDay($realToday['mday']);
	gbl::setYear($realToday['year']);
	$ca->setClockOffTimeHour($realToday["hours"]);
	$ca->setClockOffTimeMin($realToday["minutes"]);
}

$ca->setOnStamp(mktime($ca->getClockOnTimeHour(), $ca->getClockOnTimeMin(), 0, gbl::getMonth(), gbl::getDay(), gbl::getYear()));
$ca->setOffStamp(mktime($ca->getClockOffTimeHour(), $ca->getClockOffTimeMin(), 0, gbl::getMonth(), gbl::getDay(), gbl::getYear()));

if($ca->getSimpleDebug()) {
	LogFile::write("onStamp = ".$ca->getOnStamp());
	LogFile::write("offStamp = ".$ca->getOffStamp());
}

//call appropriate functions
if ($ca->getClockOnOff() == "clockonandoff"){
	$ca->clockonandoff();
}
else if ($ca->getClockOnOff() == "clockonat") {
	$ca->clockon();
} 
else if ($ca->getClockOnOff() == "clockoffat") {
	$ca->clockoff();
} 
else if ($ca->getClockOnOff() == "clockonnow") {
	//if we're coming from the popup window then set the return location to the origin
	if ($ca->getFromPopupWindow() == "true"){
		//set the return location
		$ca->setLocation($ca->getOrigin()."?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."&amp;month=".gbl::getMonth()."&amp;year=".gbl::getYear()."&amp;day=".gbl::getDay()."&amp;destination=".$ca->getDestination()."");
	}

	$ca->clockon();
} 
else if ($ca->getClockOnOff() == "clockoffnow") {
	$ca->clockoff();
} 
else{
  //redirects to a page where the user can enter the log message. Then returns here.
	Common::errorPage("Could not determine the clock on/off action. Please report this as a bug", $ca->getFromPopupWindow());
}
?>

