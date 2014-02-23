<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

//require("class.AuthenticationManager.php");

//if ($authenticationManager->isLoggedIn()) {
//	include_once("common.inc");
//	gotoStartPage();
//} else {
//	header("Location: login.php");
//}

define('JPATH_BASE', dirname(__FILE__).'/include');

require_once('include/site.class.php');
$site = new Site();
?>
