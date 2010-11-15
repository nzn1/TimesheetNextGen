<?php
//Get the result set for the config set 1

//THERE IS NO REAL NEED FOR THIS.  Its function can now be completed in the applied theme
if(!class_exists('Site')){
	list($qhq, $numq) = dbQuery("SELECT bodyhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
	$configdata = dbResult($qhq);
	echo stripslashes($configdata["bodyhtml"]);
}
else{
	trigger_error('body.inc is deprecated.  Please remove');
}
// vim:ai:ts=4:sw=4:filetype=php
?>
