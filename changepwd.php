<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclSimple'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	}
	
	exit;
}

$contextUser = strtolower($_SESSION['contextUser']);
$loggedInUser = strtolower($_SESSION['loggedInUser']);

$passwd1 = "";
$passwd2 = "";
$old_pass = "";

//load local vars from superglobals
if (isset($_POST["action"])) {
	if (!isset($_POST["passwd1"]) || !isset($_POST["passwd2"]) || !isset($_POST["old_pass"]))
		errorPage("Please fill out all fields.");
	$passwd1 = $_POST['passwd1'];
	$passwd2 = $_POST['passwd2'];
	$old_pass = $_POST['old_pass'];
}

//check for guest user
if ($loggedInUser == 'guest')
	$errormsg = "Guest may not change password.";

//check that passwords match
if ($passwd1 != $passwd2)
	$errormsg = "Passwords do not match, please try again";

if (empty($errormsg) && !empty($old_pass)) {
	$qh = mysql_query("SELECT password, $DATABASE_PASSWORD_FUNCTION('$old_pass') FROM $USER_TABLE WHERE username='$contextUser'") or die("Unable to select ". mysql_error());
	list($check1, $check2) = mysql_fetch_row($qh);
	if ($check1 != $check2) {
		$errormsg = "Wrong password, sorry!";
	}
	else {
		$qh = mysql_query("UPDATE $USER_TABLE SET password=$DATABASE_PASSWORD_FUNCTION('$passwd1') WHERE username='$contextUser'");
		gotoStartPage();
		exit;
	}
}

//if errors, redirect to an error page.
if (!empty($errormsg)) {
	Header("Location: error.php?errormsg=$errormsg");
	exit;
}

?>
<html>
<head>
<title>Change Password for user <?php echo $contextUser; ?></title>

</head>


<form action="changepwd.php" method="post">
<input type="hidden" name="action" value="changePassword" />
<div id="inputArea">
<table width="436" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
			Change Password:
		</td>
	</tr>
	<tr>
		<td width="150" align="right" nowrap>Old Password:</td>
		<td><input type="password" name="old_pass" style="width: 100%;" /></td>
		</tr>
		<tr>
			<td width="150" align="right" nowrap>New Password:</td>
			<td><input type="password" name="passwd1" style="width: 100%; AUTOCOMPLETE="OFF"" /></td>
		</tr>
		<tr>
			<td width="150" align="right" nowrap>New Password (again):</td>
			<td><input type="password" name="passwd2" style="width: 100%; AUTOCOMPLETE="OFF"" /></td>
		</tr>
		<tr>
			<td align="center">
				<input type="submit" value="Change!" />
			</td>
		</tr>
</table>
</div>
</form>
