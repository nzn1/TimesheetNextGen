<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
		gotoLocation(Config::getRelativeRoot()."/login?redirect=".urlencode($_SERVER['REQUEST_URI'])."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	  exit;
}

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
	$qh = mysql_query("SELECT password, $DATABASE_PASSWORD_FUNCTION('$old_pass') FROM ".tbl::getuserTable()." WHERE username='".gbl::getContextUser()."'") or die("Unable to select ". mysql_error());
	list($check1, $check2) = mysql_fetch_row($qh);
	if ($check1 != $check2) {
		$errormsg = "Wrong password, sorry!";
	}
	else {
		$qh = mysql_query("UPDATE ".tbl::getUserTable()." SET password=$DATABASE_PASSWORD_FUNCTION('$passwd1') WHERE username='".gbl::getContextUser()."'");
		gotoStartPage();
		exit;
	}
}

//if errors, redirect to an error page.
if (!empty($errormsg)) {
	gotoLocation(Config::getRelativeRoot()."/error?errormsg=$errormsg");
	exit;
}

?>
<html>
<head>
<title>Change Password for user <?php echo gbl::getContextUser(); ?></title>

</head>


<form action="<?php echo Config::getRelativeRoot(); ?>/changepwd" method="post">
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
