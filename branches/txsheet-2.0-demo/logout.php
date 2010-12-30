<?php


//log the user out
Site::getAuthenticationManager()->logout();
//go to the login page
Header("Location: login");

// vim:ai:ts=4:sw=4
?>
