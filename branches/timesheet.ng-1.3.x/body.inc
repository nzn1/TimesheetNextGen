<?
//Get the result set for the config set 1
list($qhq, $numq) = dbQuery("SELECT bodyhtml FROM $CONFIG_TABLE WHERE config_set_id = '1'");
$configdata = dbResult($qhq);
echo stripslashes($configdata["bodyhtml"]);
?>
