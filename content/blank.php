<?php
if(!class_exists('Site'))die('Restricted Access');
PageElements::setPageAuth('Open');
PageElements::setHead("<title>".Config::getMainTitle()." - Blank</title>");
?>

<div class="pad5">
  <h1><strong>Blank Page</strong></h1>
  <p>...</p>
  <hr />
  <p>...</p>
  <hr />
</div><!-- Close padding div -->
