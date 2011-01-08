<?php
 ErrorHandler::fatalError('Error: This file ('.__FILE__.'has been deprecated.  Please stop using it<br />'.ppr(getShortDebugTrace(3),'trace',true));
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT errorhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configData = dbResult($qhq);
parse_and_echo(stripslashes($configData["errorhtml"]));

// vim:ai:ts=4:sw=4:filetype=php
?>
