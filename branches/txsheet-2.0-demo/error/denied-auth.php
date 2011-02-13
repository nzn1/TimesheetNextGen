<?php

if (!defined('SESSION_INCLUDED'))die("Restricted Access");
//PageElements::setTemplate(Config::getDefaultTemplate());
?>
<div class="pad5">
<h3>Error: Access Denied</h3>
<p>You don't have the privileges required to view this page.  This page means that your user group or your user account is specifically being denied access to this page.</p>
<p>You are currently logged on as 
<strong><?php echo Site::getSession()->getUserInfo()->getUsername();?></strong>
</p>

<p>You require the privilege 
  <strong><?php echo PageElements::getPageAuth()->authGroup." - ".PageElements::getPageAuth()->authName;?></strong>
</p>
<p>Please contact the webmaster with the following reference: 
<strong> <?php echo $_SERVER['REQUEST_URI'];?></strong></p><br />
<p>Please use the main menu to navigate to another page.</p>
  <hr />
  <p>To report this error, please contact the webmaster <a href="mailto:<?php echo Config::getWebmasterEmail();?>">here</a></p>
</div>