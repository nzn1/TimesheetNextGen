<?php
die('NOT CONVERTED TO OO YET');
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
require("class.AuthenticationManager.php");
if (!$authenticationManager->isLoggedIn()) {
	gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI']));
	exit;
}

// Connect to database.
$dbh = dbConnect();
$contextUser = strtolower($_SESSION['contextUser']);

//load local vars from superglobals
$trans_num = $_REQUEST['trans_num'];
$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: (int)date("Y");
$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: (int)date("m");
$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: (int)date("j");
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;

dbQuery("DELETE FROM $TIMES_TABLE WHERE trans_num=$trans_num AND uid='$contextUser'");

//seems broken: Header("Location: $_SERVER[HTTP_REFERER]");
gotoLocation(Config::getRelativeRoot()."/daily?month=$month&amp;year=$year&amp;day=$day");

// vim:ai:ts=4:sw=4
?>
