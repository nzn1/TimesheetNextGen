<?php 

	PageElements::setHead("<title>".Config::getMainTitle()." | Install | New Install</title>");
?>
<div class="pad5">
<h1>TimesheetNG Installer</h1>
<p>Welcome to the TimeSheetNG Installer</p>
<p>Your current version of Timesheet (codebase) is: <?php echo Config::getVersion();?></p>
<p>Your current version of Timesheet (database) is: <?php echo Config::getDatabaseVersion();?></p>

<?php 

if(Config::getVersion() == Config::getDatabaseVersion()){
	echo "<p>Your Database Version currently matches the CodeBase version.  No update is required.</p>";
}
else{
	echo "<p>Your Database Version does not match the CodeBase version.</p>";
	
	if(Config::isInstalled()){
		echo "<p>Your Config shows that you have already installed a version of TimeSheetNG</p>";
		echo "<p><a href=\"".Config::getRelativeRoot()."/install.php?page=upgrade\">Click Here</a> to upgrade your database</p>";
		echo "<p>Alternatively <a href=\"".Config::getRelativeRoot()."/install.php?page=install\">Click Here</a> to start a fresh install</p>";
	}
	else{
		echo "<p><a href=\"".Config::getRelativeRoot()."/install.php?page=install\">Click Here</a> to start a fresh install</p>";
	}
	
}
?>

</div>