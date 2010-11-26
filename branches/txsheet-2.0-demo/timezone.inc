<?php
	//if we are working from the OO version then the table variables
	//aren't accessible. //this will allow access for now without
	//rewriting lots of stuff
	if(class_exists('Site')){
		include('table_names.inc');
	}

	//Get the result set for the config set 1
	if(!class_exists('Site')){
		$dbh = dbConnect();	
	}
	
	list($qhq, $numq) = dbQuery("SELECT locale, timezone FROM $CONFIG_TABLE WHERE config_set_id = '1'");
	$configdata = dbResult($qhq);

	//set the locale
	if ($configdata["locale"] != "")
		setlocale(LC_ALL, stripslashes($configdata["locale"]));

	//set the timezone
	if ($configdata["timezone"] != "") {
		//for >= PHP 5.1
		if(function_exists("date_default_timezone_set")) {
			date_default_timezone_set($configdata["timezone"]);
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