<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_CLIENT')."</title>");

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

PageElements::setTheme('newcss');
ob_end_clean();

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));


//build the query
$query = "SELECT client_id, organisation, description, address1, address2,".
			"city, country, postal_code, contact_first_name, contact_last_name,".
			"username, contact_email, phone_number, fax_number, gsm_number, ".
			"http_url ".
		"FROM ".tbl::getClientTable()." ct ".
		"WHERE ct.client_id=".gbl::getClientId();

//run the query
list($qh, $num) = dbQuery($query);
$data = dbResult($qh);

?>
<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="client_id" value="<?php echo gbl::getClientId() ?>" />

<table class="noborder">
	<tbody>
	<tr>
		<td>&nbsp;</td><td>
				<h1><?php echo JText::_('EDIT_CLIENT').": ".$data["organisation"]; ?> </h1>
		</td>
		
	</tr>
	<tr>
		<td align="right"><label for="organisation"><?php echo ucwords(JText::_('ORGANISATION'))?>:</td>
		<td><input size="60" name="organisation" value="<?php echo $data["organisation"]; ?>"  maxlength="64" /></td></label>
	</tr>
	<tr>
		<td valign="top" align="right"><label for="description"><?php echo ucwords(JText::_('DESCRIPTION'))?>:</td>
		<td>
			<textarea name="description" rows="4" cols="58"><?php echo trim($data["description"]); ?></textarea></label>
		</td>
	</tr>
	<tr>
		<td align="right"><label for="address1"><?php echo ucwords(JText::_('ADDRESS1'))?>:</td>
		<td><input size="60" name="address1" value="<?php echo $data["address1"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="address2"><?php echo ucwords(JText::_('ADDRESS2'))?>:</td>
		<td><input size="60" name="address2" value="<?php echo $data["address2"]; ?>" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="city"><?php echo ucwords(JText::_('CITY'))?>:</td>
		<td><input size="60" name="city"  value="<?php echo $data["city"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="country"><?php echo ucwords(JText::_('COUNTRY'))?>:</td>
		<td><input size="60" name="country" value="<?php echo $data["country"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="postal_code"><?php echo ucwords(JText::_('POSTAL_CODE'))?>:</td>
		<td><input size="13" name="postal_code" value="<?php echo $data["postal_code"]; ?>" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_first_name"><?php echo ucwords(JText::_('CONTACT_FIRSTNAME'))?>:</td>
		<td><input size="60" name="contact_first_name" value="<?php echo $data["contact_first_name"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_last_name"><?php echo ucwords(JText::_('CONTACT_LASTNAME'))?>:</td>
		<td><input size="60" name="contact_last_name" value="<?php echo $data["contact_last_name"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="contact_email"><?php echo ucwords(JText::_('EMAIL_ADDRESS'))?>:</td>
		<td><input size="60" name="contact_email" value="<?php echo $data["contact_email"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="phone_number"><?php echo ucwords(JText::_('PHONE_NUMBER'))?>:</td>
		<td><input size="20" name="phone_number" value="<?php echo $data["phone_number"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="fax_number"><?php echo ucwords(JText::_('FAX_NUMBER'))?>:</td>
		<td><input size="20" name="fax_number" value="<?php echo $data["fax_number"]; ?>"  /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="gsm_number"><?php echo ucwords(JText::_('MOBILE_NUMBER'))?>:</td>
		<td><input size="20" name="gsm_number" value="<?php echo $data["gsm_number"]; ?>" /></td></label>
	</tr>
	<tr>
		<td align="right"><label for="http_url"><?php echo ucwords(JText::_('WEBSITE'))?>:</td>
		<td><input size="60" name="http_url" value="<?php echo $data["http_url"]; ?>"  /></td></label>
	</tr>
			<!--   table width="100%" border="0" class="table_bottom_panel" -->
	<tr>
		<td align="center">
			<input type="submit" name="edit" value="<?php echo ucwords(JText::_('SUBMIT_CHANGES'))?>" />
		</td>
		<td>&nbsp;</td>
	</tr>
</table>

</form>
</div>
