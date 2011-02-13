<?php
 if(!class_exists('Site'))die('Restricted Access');
?>
<div id="nav-container">
<ul>
	<li><a href="<?php echo config::getRelativeRoot();?>">Home</a></li>
	<li><a href="<?php echo config::getRelativeRoot();?>/monthly.php">Timesheet
	Monthly (old)</a></li>
	<li><a href="<?php echo config::getRelativeRoot();?>/monthly">Timesheet
	Monthly (new)</a></li>
	<li><a href="<?php echo config::getRelativeRoot();?>/blank">Blank Page</a></li>
</ul>
</div>

<?php 
//at somepoint banner.inc should move here
//include("banner.inc");
?>