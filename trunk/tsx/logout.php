<?php
if(!class_exists('Site'))die('Restricted Access');

//log the user out
Site::getAuthenticationManager()->logout();
//go to the login page

gotoLocation(Config::getRelativeRoot()."/login");

// vim:ai:ts=4:sw=4
?>
