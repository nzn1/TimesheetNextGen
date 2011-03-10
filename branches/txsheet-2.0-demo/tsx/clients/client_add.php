<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");

//define the command menu
Site::getCommandMenu()->add(new TextCommand("Back", true, "javascript:history.back()"));

?>
<html>
<head>
<title>Add a new Client</title>
<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/client_action" method="post">
<input type="hidden" name="action" value="add" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
			<h1>Add New Client</h1>
		</td>
	</tr>
	<tr>
		<td align="right">Organisation:</td>
		<td><input size="60" name="organisation" style="width: 100%;" maxlength="64"/></td>
	</tr>
	<tr>
		<td valign="top" align="right">Description:</td>
		<td><textarea name="description" rows="4" cols="58" style="width: 100%;"></textarea></td>
	</tr>
	<tr>
		<td align="right">Address1:</td>
		<td><input size="60" name="address1" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Address2:</td>
		<td><input size="60" name="address2" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">City:</td>
		<td><input size="60" name="city" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Country:</td>
		<td><input size="60" name="country" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Postal Code:</td>
		<td><input size="13" name="postal_code" /></td>
	</tr>
	<tr>
		<td align="right">Contact Firstname:</td>
		<td><input size="60" name="contact_first_name" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Contact Lastname:</td>
		<td><input size="60" name="contact_last_name" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Username:</td>
		<td><input size="32" name="client_username" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Contact email:</td>
		<td><input size="60" name="contact_email" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Phone Number:</td>
		<td><input size="20" name="phone_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Fax Number:</td>
		<td><input size="20" name="fax_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Mobile Number:</td>
		<td><input size="20" name="gsm_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right">Website:</td>
		<td><input size="60" name="http_url" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" name="add" value="Add New Client" />
		</td>
	</tr>
</table>
</form>
</div>