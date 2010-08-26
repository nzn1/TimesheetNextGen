<?php
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT errorhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["errorhtml"]));
?>
