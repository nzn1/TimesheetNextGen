<?php
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT bannerhtml FROM $CONFIG_table WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["bannerhtml"]));

// vim:ai:ts=4:sw=4:filetype=php
?>
