<?php
require("table_names.inc");

//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT headerhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configdata = dbResult($qhq);
parse_and_echo(stripslashes($configdata["headerhtml"]));

// vim:ai:ts=4:sw=4:filetype=php
?>
