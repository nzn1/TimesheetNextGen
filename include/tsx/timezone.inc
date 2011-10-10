<?php
	//set the locale
	if (Config::getLocale() != "")
		setlocale(LC_ALL, stripslashes(Config::getLocale()));

	//set the timezone
	if (Config::getTimeZone() != "") {
		//for >= PHP 5.1
		if(function_exists("date_default_timezone_set")) {
			date_default_timezone_set(Config::getTimeZone());
		// for PHP < 5.1
		} else {
			putenv("PHP_TZ=" . stripslashes($configdata["timezone"]));
			putenv("TZ=" . stripslashes($configdata["timezone"]));
		}
	} else {
		//for >= PHP 5.1
		if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
			date_default_timezone_set(date_default_timezone_get());
	}

// vim:ai:ts=4:sw=4:filetype=php
?>
