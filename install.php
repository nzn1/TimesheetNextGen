<?php

require_once('include/site/site.class.php');
$site = new Site();
$site->setInstallMode();
$site->load();
?>