<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_CLIENT')."</title>");
PageElements::setTheme('newcss');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

?>

<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/clients/client_action" method="post">
<input type="hidden" name="action" value="add" />

<table class="noborder">
	<tr>
		<td>
			<h1><?php echo JText::_('ADD_NEW_CLIENT') ?></h1>
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ORGANISATION'))?>:</td>
		<td><input size="60" name="organisation" maxlength="64"/></td>
	</tr>
	<tr>
		<td valign="top" align="right"><?php echo ucwords(JText::_('DESCRIPTION'))?>:</td>
		<td><textarea name="description" rows="4" cols="58"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ADDRESS1'))?>:</td>
		<td><input size="60" name="address1" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('ADDRESS2'))?>:</td>
		<td><input size="60" name="address2" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CITY'))?>:</td>
		<td><input size="60" name="city" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('COUNTRY'))?>:</td>
		<td><input size="60" name="country" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('POSTAL_CODE'))?>:</td>
		<td><input size="13" name="postal_code" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CONTACT_FIRSTNAME'))?>:</td>
		<td><input size="60" name="contact_first_name" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('CONTACT_LASTNAME'))?>:</td>
		<td><input size="60" name="contact_last_name" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('EMAIL_ADDRESS'))?>:</td>
		<td><input size="60" name="contact_email" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('PHONE_NUMBER'))?>:</td>
		<td><input size="20" name="phone_number" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('FAX_NUMBER'))?>:</td>
		<td><input size="20" name="fax_number" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('MOBILE_NUMBER'))?>:</td>
		<td><input size="20" name="gsm_number" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo ucwords(JText::_('WEBSITE'))?>:</td>
		<td><input size="60" name="http_url" /></td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_CLIENT')?>" />
		</td>
	</tr>
</table>
</form>
</div>
