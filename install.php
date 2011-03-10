<?php

require_once('include/site/site.class.php');
$site = new Site();
$site->setInstallMode();
die('the installer does not yet work.  If you have not installed tsheetx yet, please navigate to /oldinstall');
$site->load();
?>