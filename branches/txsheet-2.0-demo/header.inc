<?php
require("table_names.inc");

//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT headerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
if(!class_exists('Site')){
	parse_and_echo(stripslashes($configData["headerhtml"]));
}
else Common::parse_and_echo(stripslashes($configData["headerhtml"]));


// vim:ai:ts=4:sw=4:filetype=php
?>
