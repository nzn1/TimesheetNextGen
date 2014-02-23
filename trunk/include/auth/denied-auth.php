<?php

if (!defined('SESSION_INCLUDED'))die("Restricted Access");
//PageElements::setTemplate(Config::getDefaultTemplate());
echo "<div class=\"pad5\">";
echo "<h3>Error: Access Denied</h3>";
echo"<p>You don't have the privileges required to view this page.  This page means that your user group or your user account is specifically being denied access to this page.</p>";
echo"<p>You are currently logged on as <strong>".$_SESSION['loggedInUser']."</strong></p>";

echo"<p>You require the privilege <strong>".PageElements::getPageAuth()->authGroup." - ".PageElements::getPageAuth()->authName."</strong></p>";
echo "<p>Please contact the webmaster with the following reference: <strong>".$_SERVER['REQUEST_URI']."</strong></p><br />";
echo "<p>Please use the main menu to navigate to another page.</p>
  <hr />
  <p>To report this error, please contact the webmaster <a href=\"mailto:".Config::getWebmasterEmail()."\">here</a></p>";
echo "</div>";