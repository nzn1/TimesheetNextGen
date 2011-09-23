<?php
class Install{
	
	public function __construct(){
		

	}
	
	
	
	public function checkWriteable($path){
		
		if(file_exists($path) && is_writable($path)){
			return true;
		}
		else { 
			return false;
		}
		
	}
	
	public function createTableRequestForm(){
		
		$db_host = '';
		$db_name = '';
		$db_user = '';
		$db_pass = '';
		$db_prefix = '';
		
		?>
		<div class="form">
		
		<form method="post">

<table border="1">
<tr>
	<td colspan="2">
		<p>Do you need to create a new database for TimesheetNG?</p>
	</td>
</tr>
<tr>
	<td colspan="2">
		<input type="radio" name="db_name_exist" value="yes" /> This database exists, no need to create<br />
		<input type="radio" name="db_name_exist" value="no"  /> This database does not exist, please create it now. <i><font color="darkgreen" size="1">You must have DB Admin credentials for next step.</font></i>
	</td>
</tr>

<tr>
	<td colspan="2">
		<p>Please enter your admin database credentials (if you need to create a new db). <br />
		These credentials will only be used for the purpose of creating the database</p>
	</td>
</tr>
<tr>
	<th>Host Name</th><td><input type="text" name="db_host" value="<?php echo $db_host; ?>" /></td>
</tr>
<tr>
	<th>Database Name</th><td><input type="text" name="db_name" value="<?php echo $db_name; ?>" /></td>
</tr>
<tr>
	<th>Username</th><td><input type="text" name="db_user" value="<?php echo $db_user; ?>" /></td>
</tr>
<tr>
	<th>Password</th><td><input type="password" name="db_pass" value="<?php echo $db_pass; ?>" /></td>
</tr>
</table>
<input type="submit" value="Submit!" />
<input type="hidden" name="installstep" value="2" />
</form>
		
	<?php 	
	}
	
	public function createDatabase($dbHost, $dbName, $dbUser, $dbPass){
		
		
	}
	
	public function enterCredentialsForm(){
		
		$db_name_exist = 'yes';
		$db_host = '';
		$db_name = '';
		$db_user = '';
		$db_pass = '';
		$db_prefix = '';
		?>
		<div class="form">
		
		<form method="post">
<p>Please enter your database credentials:</p>
<table>
<tr>
<th>Host Name</th><td><input type="text" name="db_host" value="<?php echo $db_host; ?>" /></td>
</tr>
<tr>
<th>Database Name</th><td><input type="text" name="db_name" value="<?php echo $db_name; ?>" /></td>
<td>
<input type="radio" name="db_name_exist" value="yes" <?php if($db_name_exist=="yes") echo "checked"; ?> /> This database exists, no need to create<br />
<input type="radio" name="db_name_exist" value="no"  <?php if($db_name_exist=="no") echo "checked"; ?> /> This database does not exist, please create it now. <i><font color="darkgreen" size="1">You must have DB Admin credentials for next step.</font></i>
</td>
</tr>
<tr>

<th>Username</th><td><input type="text" name="db_user" value="<?php echo $db_user; ?>" /></td>
</tr>
<tr>
<th>Password</th><td><input type="password" name="db_pass" value="<?php echo $db_pass; ?>" /></td>
</tr>
<tr>
<th>Password Function</th>
<td><select name="db_pass_func">
<option value="SHA1">SHA1</option>
<option value="PASSWORD">PASSWORD</option>
<option value="OLD_PASSWORD">OLD PASSWORD</option>
</select></td>
<td>This is the function the database uses to encrypt the passwords. If your MySQL version is 4.1 or above
you should use SHA1. PASSWORD should be used on MySQL version 4.0 or below, and OLD PASSWORD for MySQL
version 4.1 or above where SHA1 is not available.<br /><em>If in doubt, use SHA1.</em></td></tr>
<tr>
<th>Table Prefix</th><td><input type="text" name="db_prefix" value="<?php echo $db_prefix; ?>" /></td>
<td>This prefix is used for all table names</td>
</tr>
<tr><td colspan="3">
<?php /* <input type="button" value="Test Configuration" onclick="alert('Sorry, this doesn\'t work yet');"/> */ ?>
<input type="submit" value="Proceed to Step Three" />
</td></tr>
</table>
<input type="hidden" name="step" value="two" />
</form>
		
		
		
		
		</div>
		
		
		
		<?php 
		
	}
	
	
	
	
	
	
	
	
}

?>