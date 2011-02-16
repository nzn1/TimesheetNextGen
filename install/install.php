<?php 
	PageElements::setHead("<title>".Config::getMainTitle()." | Install | New Install</title>");
?>
<div class="pad5">
<h1>Install TimesheetNG</h1>
<p>You have arrived at this page because you have not yet installed TimesheetNG</p>
<p>Your current version of Timesheet (codebase) is: <?php echo Config::getVersion();?></p>
<p>Your current version of Timesheet (database) is: na</p>
<hr />

<h3>This install script is not yet functional</h3>
<p>To install, I would recommend installing the db from the trunk and then copying the two files: table_names.inc and database_credentials.inc into the directory of the txhsset_2.0_demo</p> 
<?php
return; 
require_once('install.class.php');
$install = new Install();



//stage 1 check that the config file can be written to
if(false == $install->checkWriteable(Config::getDocumentRoot()."/include/config/config.php")){
	?>
	<p>Stage 1: ERROR: Config.php is not writeable or does not exist</p>
	
	<?php 
	//install not successful.
	return;
}
else{
	?>
	<p>Stage 1: SUCCESS: Config.php is writeable</p>
	<hr />
	
	<?php 
}

//stage 2
//There are two options here.  Either:
//a) The db user credentials have already been created and all that is needed is for the parameters to be entered
//b) A new db user needs to be created.  so the admin db details must be entered first.

$install->createTableRequestForm();

?>

</div>