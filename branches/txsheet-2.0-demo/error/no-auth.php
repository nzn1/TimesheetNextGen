<?php
if (!defined('SESSION_INCLUDED'))die("Restricted Access");
?>
<div class="pad5">
<h3>Error: Authorisation Privileges haven't been Configured</h3>
<p>This page does not have any privileges configured.  Consequently, it cannot be viewed.</p>
<p>Please contact the webmaster with the following reference: <strong><?php echo $_SERVER['REQUEST_URI'];?></strong></p><br />

<p>You are currently logged on as <strong><?php echo Site::getSession()->getUserInfo()->getUsername();?></strong></p>
<p>You require the privilege <strong><?php echo PageElements::getPageAuth()->authGroup." - ".PageElements::getPageAuth()->authName;?></strong></p>
<p>Please use the main menu to navigate to another page.</p>
  <hr />
  <p>To report this error, please contact the webmaster <a href="mailto:<?php echo Config::getWebmasterEmail();?>">here</a></p>
</div>