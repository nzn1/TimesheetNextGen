<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_CLIENT')."</title>");

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

PageElements::setTheme('txsheet2');
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
<div id="inputArea">
	<h1><?php echo JText::_('EDIT_CLIENT').": <i>".$data["organisation"]; ?> </i></h1>
	<div><label><?php echo ucwords(JText::_('ORGANISATION'))?>:</label>
		<input size="60" name="organisation" value="<?php echo $data["organisation"]; ?>" maxlength="64" /></div>
	<div><label><?php echo ucwords(JText::_('DESCRIPTION'))?>:</label>
		<textarea name="description" rows="4" cols="58"><?php echo trim($data["description"]); ?></textarea></div>
	<div><label><?php echo ucwords(JText::_('ADDRESS1'))?>:</label>
		<input size="60" name="address1" value="<?php echo $data["address1"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('ADDRESS2'))?>:</label>
		<input size="60" name="address2" value="<?php echo $data["address2"]; ?>" /></div>
	<div><label><?php echo ucwords(JText::_('CITY'))?>:</label>
		<input size="60" name="city"  value="<?php echo $data["city"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('COUNTRY'))?>:</label>
		<input size="60" name="country" value="<?php echo $data["country"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('POSTAL_CODE'))?>:</label>
		<input size="13" name="postal_code" value="<?php echo $data["postal_code"]; ?>" /></div>
	<div><label><?php echo ucwords(JText::_('CONTACT_FIRSTNAME'))?>:</label>
		<input size="60" name="contact_first_name" value="<?php echo $data["contact_first_name"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('CONTACT_LASTNAME'))?>:</label>
		<input size="60" name="contact_last_name" value="<?php echo $data["contact_last_name"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('EMAIL_ADDRESS'))?>:</label>
		<input size="60" name="contact_email" value="<?php echo $data["contact_email"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('PHONE_NUMBER'))?>:</label>
		<input size="20" name="phone_number" value="<?php echo $data["phone_number"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('FAX_NUMBER'))?>:</label>
		<input size="20" name="fax_number" value="<?php echo $data["fax_number"]; ?>"  /></div>
	<div><label><?php echo ucwords(JText::_('MOBILE_NUMBER'))?>:</label>
		<input size="20" name="gsm_number" value="<?php echo $data["gsm_number"]; ?>" /></div>
	<div><label><?php echo ucwords(JText::_('WEBSITE'))?>:</label>
		<input size="60" name="http_url" value="<?php echo $data["http_url"]; ?>"  /></div>
	<input type="submit" name="edit" value="<?php echo ucwords(JText::_('SUBMIT_CHANGES'))?>" />
</div>
</form>
</div>
