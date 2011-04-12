<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//define the command menu
Site::getCommandMenu()->add(new TextCommand("Back", true, "javascript:history.back()"));

//load local vars from superglobals
$client_id = gbl::getClientId();

$CLIENT_TABLE = tbl::getClientTable();
//build the query
$query = "SELECT client_id, organisation, description, address1, address2,".
			"city, country, postal_code, contact_first_name, contact_last_name,".
			"username, contact_email, phone_number, fax_number, gsm_number, ".
			"http_url ".
		"FROM $CLIENT_TABLE ".
		"WHERE $CLIENT_TABLE.client_id=$client_id";

//run the query
list($qh, $num) = dbQuery($query);
$data = dbResult($qh);

?>
<html>
<head>
<title><?php echo Config::getMainTitle()." - ".ucfirst(JText::_('CLIENT_MANAGEMENT'));?></title>
</head>
<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="client_id" value="<?php echo $client_id ?>" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
				<h1><?php echo JText::_('EDIT_CLIENT').": ".$data["organisation"]; ?> </h1>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="organisation"><?php echo ucwords(JText::_('ORGANISATION'))?>:</td>
		<td><input size="60" name="organisation" value="<?php echo $data["organisation"]; ?>" style="width: 100%;" maxlength="64" /></td></label>
	</tr>
	<tr>
		<td valign="top" align="right"><label for="description"><?php echo ucwords(JText::_('DESCRIPTION'))?>:</td>
		<td>
			<textarea name="description" rows="4" cols="58" style="width: 100%;"><?php echo trim($data["description"]); ?></textarea></label>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="address1"><?php echo ucwords(JText::_('ADDRESS1'))?>:</td>
		<td><input size="60" name="address1" value="<?php echo $data["address1"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="address2"><?php echo ucwords(JText::_('ADDRESS2'))?>:</td>
		<td><input size="60" name="address2" value="<?php echo $data["address2"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="city"><?php echo ucwords(JText::_('CITY'))?>:</td>
		<td><input size="60" name="city"  value="<?php echo $data["city"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="country"><?php echo ucwords(JText::_('COUNTRY'))?>:</td>
		<td><input size="60" name="country" value="<?php echo $data["country"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="postal_code"><?php echo ucwords(JText::_('POSTAL_CODE'))?>:</td>
		<td><input size="13" name="postal_code" value="<?php echo $data["postal_code"]; ?>" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_first_name"><?php echo ucwords(JText::_('CONTACT_FIRSTNAME'))?>:</td>
		<td><input size="60" name="contact_first_name" value="<?php echo $data["contact_first_name"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_last_name"><?php echo ucwords(JText::_('CONTACT_LASTNAME'))?>:</td>
		<td><input size="60" name="contact_last_name" value="<?php echo $data["contact_last_name"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_email"><?php echo ucwords(JText::_('EMAIL_ADDRESS'))?>:</td>
		<td><input size="60" name="contact_email" value="<?php echo $data["contact_email"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="phone_number"><?php echo ucwords(JText::_('PHONE_NUMBER'))?>:</td>
		<td><input size="20" name="phone_number" value="<?php echo $data["phone_number"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="fax_number"><?php echo ucwords(JText::_('FAX_NUMBER'))?>:</td>
		<td><input size="20" name="fax_number" value="<?php echo $data["fax_number"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="gsm_number"><?php echo ucwords(JText::_('MOBILE_NUMBER'))?>:</td>
		<td><input size="20" name="gsm_number" value="<?php echo $data["gsm_number"]; ?>" style="width: 100%;" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="http_url"><?php echo ucwords(JText::_('WEBSITE'))?>:</td>
		<td><input size="60" name="http_url" value="<?php echo $data["http_url"]; ?>" style="width: 100%;" /></td></label>
	</tr>
			<!--   table width="100%" border="0" class="table_bottom_panel" -->
	<tr>
		<td align="center">
			<input type="submit" name="edit" value="<?php echo ucwords(JText::_('SUBMIT_CHANGES'))?>" />
		</td>
	</tr>
</table>

</form>
</div>