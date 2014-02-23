<?php

if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

$passwd1 = "";
$passwd2 = "";
$old_pass = "";

//load local vars from request/post/get
if (isset($_POST["action"])) {
	if (!isset($_POST["passwd1"]) || !isset($_POST["passwd2"]) || !isset($_POST["old_pass"]))
		errorPage("Please fill out all fields.");
	$passwd1 = mysql_real_escape_string($_POST['passwd1']);
	$passwd2 = mysql_real_escape_string($_POST['passwd2']);
	$old_pass = mysql_real_escape_string($_POST['old_pass']);
}

//check for guest user
if (gbl::getLoggedInUser() == 'guest')
	$errormsg = "Guest may not change password.";

//check that passwords match
if ($passwd1 != $passwd2)
	$errormsg = "Passwords do not match, please try again";

if (empty($errormsg) && !empty($old_pass)) {
	$qh = mysql_query("SELECT password, ".config::getDbPwdFunction()."('$old_pass') FROM ".tbl::getuserTable()." WHERE username='".gbl::getContextUser()."'") or die("Unable to select ". mysql_error());
	list($check1, $check2) = mysql_fetch_row($qh);
	if ($check1 != $check2) {
		//$errormsg = "Wrong password, sorry!";
		$errormsg = JText::_('JGLOBAL_AUTH_INCORRECT');
	} else {
		$qh = mysql_query("UPDATE ".tbl::getUserTable()." SET password=".config::getDbPwdFunction()."('$passwd1') WHERE username='".gbl::getContextUser()."'");
		header("Location: ".Site::Config()->get('startPage'));
		exit;
	}
}

//if errors, redirect to an error page.
if (!empty($errormsg)) {
	gotoLocation(Config::getRelativeRoot()."/error?errormsg=$errormsg");
	exit;
}

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CHANGE_PASSWD')."</title>");
PageElements::setTheme('txsheet2');
?>

<h1> <?php echo (JText::_('CHANGE_PASSWD')) ?> </h1>

<form action="<?php echo Config::getRelativeRoot(); ?>/changepwd" method="post">
<input type="hidden" name="action" value="changePassword" />
<div id="inputArea">
<table width="436" align="center" border="0" cellspacing="0" cellpadding="0">

	<tr>
		<td width="150" align="right" ><?php echo (JText::_('OLD_PASSWD')) ?>:</td>
		<td><input type="password" name="old_pass" style="width: 100%;" /></td>
		</tr>
		<tr>
			<td width="150" align="right" ><?php echo (JText::_('NEW_PASSWD')) ?>:</td>
			<td><input type="password" name="passwd1" style="width: 100%; AUTOCOMPLETE="OFF"" /></td>
		</tr>
		<tr>
			<td width="150" align="right" ><?php echo (JText::_('NEW_PASSWD')) ?>:</td>
			<td><input type="password" name="passwd2" style="width: 100%; AUTOCOMPLETE="OFF"" /></td>
		</tr>
		<tr>
			<td align="center">
				<input type="submit" value="<?php echo (JText::_('CHANGE_PASSWD')) ?>!" />
			</td>
		</tr>
</table>
</div>
</form>
