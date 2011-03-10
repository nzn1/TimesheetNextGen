<?php
if (!defined('SESSION_INCLUDED'))die("Restricted Access");

echo "<div class=\"pad5\">";
echo "<h3>Error: Authorisation Privileges haven't been Configured</h3>";
echo "<p>This page does not have any privileges configured.  Consequently, it cannot be viewed.</p>";
echo "<p>Please contact the webmaster with the following reference: <strong>".$_SERVER['REQUEST_URI']."</strong></p><br />";

echo"<p>You are currently logged on as <strong>".Site::getSession()->getUserInfo()->getUsername()."</strong></p>";
echo"<p>You require the privilege <strong>".PageElements::getPageAuth()->authGroup." - ".PageElements::getPageAuth()->authName."</strong></p>";
echo "<p>Please use the main menu to navigate to another page.</p>
  <hr />
  <p>To report this error, please contact the webmaster <a href=\"mailto:".Config::getWebmasterEmail()."\">here</a></p>";
echo "</div>";