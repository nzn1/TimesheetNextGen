<?php 
	PageElements::setHead("<title>".Config::getMainTitle()." | Install | Upgrade</title>");
?>
<div class="pad5">
<h1>Upgrade TimesheetNG</h1>
<p>You have arrived at this page because your database version does not match the version of the site.</p>
<p>Your current version of Timesheet (codebase) is: <?php echo Config::getVersion();?></p>
<p>Your current version of Timesheet (database) is: <?php echo Config::getDatabaseVersion();?></p>


<p>Please check in the install folder for any sql files that need to be run.  If all is ok, then set the version config property in the database to the codebase version</p>

<h3>Functionality Not yet Implemented</h3>
<p>The upgrade TimesheetNG functionality has not yet been implemented.  Check the sql folder for any files 
that need to be run against your existing database to bring it up to the latest version</p>

<p>If there are no sql files that need running, then edit the version number in the config table to match
the codebase version listed above (<?php echo Config::getVersion();?>)</p>



<?php 
	if(Config::getDatabaseVersion() == ''){
		echo '<p><strong>You probably need to run the install/sql/v1.5.3.sql file</strong></p>';
		echo '<p><strong>ALSO ADD THIS LINE TO YOUR table_names.inc file: <br />
		<code>$NEW_CONFIG_TABLE = "configuration";</code></strong></p>';
	}
?>

<p><a href="<?php echo Config::getRelativeRoot();?>">Click Here to Reload the site and try again</a></p>

</div>




