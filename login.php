<?php
// Authenticate
require("class.AuthenticationManager.php");

//check that this form has been submitted
if (isset($_POST["username"]) && isset($_POST["password"])) {
	//try logging the user in
	if (!$authenticationManager->login($_POST["username"], $_POST["password"]))
		$loginFailure = true;
	else {
		gotoStartPage();

		exit();
	}
}
else
	//destroy the session by logging out
	$authenticationManager->logout();

function printMessage($message) {
	print "<tr>" .
				"	<td>&nbsp;</td>" .
				"	<td colspan=\"3\">" .
				"		<table width=\"100%\" border=\"0\" bgcolor=\"black\" cellspacing=\"0\" cellpadding=\"1\">" .
				"			<tr>" .
				"				<td>" .
				"					<table width=\"100%\" border=\"0\" bgcolor=\"yellow\">" .
				"						<tr><td class=\"login_error\">$message</td></tr>" .
				"					</table>" .
				"				</td>" .
				"			</tr>" .
				"		</table>" .
				"	</td>" .
				"</tr>";
}

?>

<html>
<head>
<title>Timesheet Login</title>
<?php
include ("header.inc");
?>
</head>
<body onLoad="document.loginForm.username.focus();">

<form action="login.php" method="POST" name="loginForm" style="margin: 0px;">

<table border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
<?php if($siteclosed) { ?>
		<td style="padding-top: 40;">
			<font face="Verdana, Arial, Helvetica, sans-serif">
			<p align="center"><font color="red"><strong>The Site is temporarily closed.</strong></font></p>
			<p align="center">The timesheet system is temporarily closed for maintenance.</p>
			<p align="center">If you are not an Administrator, you will not be allowed to login; please check back later.</p>
			</font>
<?php } else { ?>
		<td style="padding-top: 100;">
<?php } ?>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_1.inc"); ?>

				<table border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Timesheet Login
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the heading start section -->
<?php include("timesheet_face_part_2.inc"); ?>

			<table width="300" cellspacing="0" cellpadding="5" class="box">
				<tr>
					<td><img class="login_image" src="images/spacer.gif" alt="" ></td>
					<td class="label">Username:<br /><input type="text" name="username" size="25" maxlength="25" /></td>
					<td class="label">Password:<br /><input type="password" name="password" size="25" maxlength="25" /></td>
					<td class="label"><br /><input type="submit" name="Login" value="submit" /></td>
				</tr>
				<?php	if (isset($loginFailure))
							printMessage($authenticationManager->getErrorMessage());
						else if (isset($_REQUEST["clearanceRequired"]))
							printMessage("$_REQUEST[clearanceRequired] clearance is required for the page you have tried to access.");
				?>
			</table>

<!-- include the timesheet face up until the end -->
<?php include("timesheet_face_part_3.inc"); ?>

		</td>
	</tr>
</table>

</form>

</body>
</html>
<?php
// vim:ai:ts=4:sw=4
?>
