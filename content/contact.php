<?php
if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | Contact</title>");
?>

<div class="pad5">
  <h1><strong>Contact Us Page</strong></h1>
  <p>This could be access to help features for example</p>
  <hr />
  <p>...</p>
  <hr />
</div><!-- Close padding div -->
