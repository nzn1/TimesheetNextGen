<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_CLIENT')."</title>");

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

?>
<html>
<head>
</head>
<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">
<input type="hidden" name="action" value="add" />

<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="left" nowrap class="outer_table_heading" nowrap>
			<h1><?php echo JText::_('ADD_NEW_CLIENT') ?></h1>
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ORGANISATION'))?>:</td>
		<td><input size="60" name="organisation" style="width: 100%;" maxlength="64"/></td>
	</tr>
	<tr>
		<td valign="top" align="right"><?php echo ucwords(JText::_('DESCRIPTION'))?>:</td>
		<td><textarea name="description" rows="4" cols="58" style="width: 100%;"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ADDRESS1'))?>:</td>
		<td><input size="60" name="address1" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ADDRESS2'))?>:</td>
		<td><input size="60" name="address2" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CITY'))?>:</td>
		<td><input size="60" name="city" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('COUNTRY'))?>:</td>
		<td><input size="60" name="country" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('POSTAL_CODE'))?>:</td>
		<td><input size="13" name="postal_code" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CONTACT_FIRSTNAME'))?>:</td>
		<td><input size="60" name="contact_first_name" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CONTACT_LASTNAME'))?>:</td>
		<td><input size="60" name="contact_last_name" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('EMAIL_ADDRESS'))?>:</td>
		<td><input size="60" name="contact_email" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('PHONE_NUMBER'))?>:</td>
		<td><input size="20" name="phone_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('FAX_NUMBER'))?>:</td>
		<td><input size="20" name="fax_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('MOBILE_NUMBER'))?>:</td>
		<td><input size="20" name="gsm_number" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('WEBSITE'))?>:</td>
		<td><input size="60" name="http_url" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_CLIENT')?>" />
		</td>
	</tr>
</table>
</form>
</div>
