<?php
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT tablehtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configdata = dbResult($qhq);
echo stripslashes($configdata["tablehtml"]);
?>
