<?php
if(!class_exists('Site')){
  require("table_names.inc");
}

//Get the result set for the config set 1

if(!class_exists('Site')){
	list($qhq, $numq) = dbQuery("SELECT headerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
	$configData = dbResult($qhq);
	parse_and_echo(stripslashes($configData["headerhtml"]));
}
else {
$header = "<meta name=\"description\" content=\"Timesheet Next Gen\" />
<link href=\"".Config::getRelativeRoot()."/css/timesheet.css\" rel=\"stylesheet\" type=\"text/css\" />
<link rel=\"shortcut icon\" href=\"".Config::getRelativeRoot()."/images/favicon.ico\" />";
	
	Common::parse_and_echo(stripslashes($header));
}


// vim:ai:ts=4:sw=4:filetype=php
?>
