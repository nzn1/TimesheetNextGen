<?php
if(!class_exists('Site'))die('Restricted Access');

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//Get the result set for the config set 1

list($qh, $num) = dbQuery("SELECT * FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
$resultset = dbResult($qh);

?>

<script type="text/javascript">

//store the current LDAP entry method in this variable
var currentLDAPEntryMethod = 'normal';

function onChangeLDAPEntryMethod() {
	if (document.configurationForm.LDAPEntryMethod.value == 'normal') {
		document.getElementById('normalLDAPEntry').style.display='block';
		document.getElementById('advancedLDAPEntry').style.display='none';
	}
	else {
		document.getElementById('normalLDAPEntry').style.display='none';
		document.getElementById('advancedLDAPEntry').style.display='block';
	}

	//copy data from one to the other when it changes
	if (currentLDAPEntryMethod == 'normal' && document.configurationForm.LDAPEntryMethod.value != 'normal')
		buildLDAPUrlFromForm();
	else if (currentLDAPEntryMethod != 'normal' && document.configurationForm.LDAPEntryMethod.value == 'normal')
		fillOutLDAPFieldsFromUrl();

	//update the current LDAP entry method variable
	currentLDAPEntryMethod = document.configurationForm.LDAPEntryMethod.value;
}

function enableLDAP(value) {
	document.getElementById('LDAPEntryMethod').disabled = !value;
	document.getElementById('LDAPScheme').disabled = !value;
	document.getElementById('LDAPHost').disabled = !value;
	document.getElementById('LDAPPort').disabled = !value;
	document.getElementById('LDAPBaseDN').disabled = !value;
	document.getElementById('LDAPUsernameAttribute').disabled = !value;
	document.getElementById('LDAPSearchScope').disabled = !value;
	document.getElementById('LDAPFilter').disabled = !value;
	document.getElementById('LDAPUrl').disabled = !value;
	document.getElementById('LDAPProtocolVersion').disabled = !value;
	document.getElementById('LDAPBindByUser').disabled = !value;
	document.getElementById('LDAPBindUsername').disabled = !value;
	document.getElementById('LDAPBindPassword').disabled = !value;
	document.getElementById('LDAPReferrals').disabled = !value;
	document.getElementById('LDAPFallback').disabled = !value;
}

function buildLDAPUrlFromDb() {
	//get values from database
	var scheme = '<?php echo $resultset['LDAPScheme']; ?>';
	var host = '<?php echo $resultset['LDAPHost']; ?>';
	var port = '<?php echo $resultset['LDAPPort']; ?>';
	var baseDN = '<?php echo $resultset['LDAPBaseDN']; ?>';
	var usernameAttribute = '<?php echo $resultset['LDAPUsernameAttribute']; ?>';
	var searchScope = '<?php echo $resultset['LDAPSearchScope']; ?>';
	var filter = '<?php echo $resultset['LDAPFilter']; ?>';

	buildLDAPUrl(scheme, host, port, baseDN, usernameAttribute, searchScope, filter);
}

function buildLDAPUrlFromForm() {
	buildLDAPUrl(
		document.getElementById('LDAPScheme').value,
		document.getElementById('LDAPHost').value,
		document.getElementById('LDAPPort').value,
		document.getElementById('LDAPBaseDN').value,
		document.getElementById('LDAPUsernameAttribute').value,
		document.getElementById('LDAPSearchScope').value,
		document.getElementById('LDAPFilter').value);
}

function buildLDAPUrl(scheme, host, port, baseDN, usernameAttribute, searchScope, filter) {
	//fill out defaults for those which are empty
	if (scheme == '')
		scheme = 'ldaps';
	if (host == '')
		host = 'localhost';
	if (port == '')
		port = 389;
	if (baseDN == '')
		baseDN = 'dc=yourOrganisation, dc=com, ou=yourOrganisationalUnit';
	if (usernameAttribute == '')
		usernameAttribute = 'uid';
	if (searchScope == '')
		searchScope = 'base';

	//combine into one string
	var url = scheme + '://' + host + ':' + port + '/' + baseDN + '?' + usernameAttribute + '?'
		+ searchScope;

	if (filter != '')
		url += '?' + filter;

	//set in the form
	document.getElementById('LDAPUrl').value = url;
}

function fillOutLDAPFieldsFromUrl() {

	//get the url from the form
	var url = document.getElementById('LDAPUrl').value;

	if (url.indexOf('ldaps') == 0)
		document.getElementById('LDAPScheme').selectedIndex = 1;
	else
		document.getElementById('LDAPScheme').selectedIndex = 0;

	//find the host
	var pos1 = url.indexOf('://') + 2;
	if (pos1 == -1)
		return false;
	var pos2 = url.indexOf(':', pos1+1);
	if (pos2 == -1)
		return;
	document.getElementById('LDAPHost').value = url.substring(pos1+1, pos2);

	//find the port
	var pos3 = url.indexOf('/', pos2+1);
	if (pos3 == -1)
		return false;
	document.getElementById('LDAPPort').value = url.substring(pos2+1, pos3);

	//find the base dn
	var pos4 = url.indexOf('?', pos3+1);
	if (pos4 == -1)
		return false;
	document.getElementById('LDAPBaseDN').value = url.substring(pos3+1, pos4);

	//find the username attribute
	var pos5 = url.indexOf('?', pos4+1);
	if (pos5 == -1)
		return false;
	document.getElementById('LDAPUsernameAttribute').value = url.substring(pos4+1, pos5);

	//find the search scope
	var pos6 = url.indexOf('?', pos5+1);
	if (pos6 == -1)
		pos6 = url.length;
	var searchScope = url.substring(pos5+1, pos6);
	if (searchScope == 'one')
		document.getElementById('LDAPSearchScope').selectedIndex = 1;
	else if (searchScope == 'sub')
		document.getElementById('LDAPSearchScope').selectedIndex = 2;
	else
		document.getElementById('LDAPSearchScope').selectedIndex = 0;
	if (pos6 == -1)
		return true;

	//the filter
	document.getElementById('LDAPFilter').value = url.substring(pos6+1, url.length);
	return true;
}

function onSubmit() {
	if (document.configurationForm.LDAPEntryMethod.value != 'normal') {
		if (!fillOutLDAPFieldsFromUrl()) {
			alert('There was an error parsing the LDAP Url. Please correct it and try again.');
			return;
		}
	}

	if (document.getElementById('useLDAPCheck').checked)
		document.getElementById('useLDAP').value = 1;
	else
		document.getElementById('useLDAP').value = 0;

	if (document.getElementById('LDAPBindByUsercheck').checked)
		document.getElementById('LDAPBindByUser').value = 1;
	else
		document.getElementById('LDAPBindByUser').value = 0;

	if (document.getElementById('LDAPReferralsCheck').checked)
		document.getElementById('LDAPReferrals').value = 1;
	else
		document.getElementById('LDAPReferrals').value = 0;

	if (document.getElementById('LDAPFallbackCheck').checked)
		document.getElementById('LDAPFallback').value = 1;
	else
		document.getElementById('LDAPFallback').value = 0;

	//re-enable the fields just before submitting because otherwise they are not send in mozilla
	enableLDAP(true);

	//submit the form
	document.configurationForm.submit();
}

</script>
<?php 
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CONFIG_PARMS')." | ".gbl::getContextUser()."</title>");
ob_start();

PageElements::setTheme('newcss');
ob_end_clean();

?>
<body  onload="enableLDAP(<?php echo $resultset["useLDAP"]?>);">
<div id="inputArea">
<form action="<?php echo Config::getRelativeRoot(); ?>/config_action" name="configurationForm" method="post">
<input type="hidden" name="action" value="edit" />

<h1><?php echo JText::_('CONFIG_PARMS') ?></h1>

<table class="noborder">
	<tr>
		<td class="configdesc" colspan="2">
		<?php echo JText::_('CONFIG_INTRO') ?>
		</td>
		<td align="right">
			<input type="button" value="<?php echo JText::_('SAVE_CHANGES') ?>" name="submitButton" id="submitButton" onclick="onSubmit();" />
		</td>
	</tr>
	</table>
<br />
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">
		<!-- LDAP configurationForm -->
		<tr>
			<td class="configtype">
				<b><?php echo JText::_('LDAP'); ?></b>:
			</td>
			<td align="left" width="100%">
				<input type="checkbox" name="useLDAPCheck" id="useLDAPCheck" onclick="enableLDAP(this.checked);"
					 <?php if ($resultset['useLDAP'] == 1) echo "checked=\"checked\""; ?> /><?php echo JText::_('USE_LDAP'); ?>
				<input type="hidden" name="useLDAP" id="useLDAP" />
			</td>
		</tr>
		<tr>
			<td align="left" class="label" nowrap width="90">&nbsp;</td>
			<td class="configdesc">
				<legend><?php echo JText::_('LDAP_DETAILS'); ?></legend>
					<table width="100%" >
						<tr>
							<td>
								<b>&nbsp;Data entry style:</b>
								</td><td>
								<select id="LDAPEntryMethod" name="LDAPEntryMethod" onChange="onChangeLDAPEntryMethod();">
									<option value="normal" selected="selected">Normal</option>
									<option value="advanced">RFC 2255 URL</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<span class="label"><?php echo JText::_('LDAP_SCHEME'); ?>:</span>
								</td><td>
								<select id="LDAPScheme" name="LDAPScheme">
								<option value="ldap" <?php if ($resultset["LDAPScheme"] == "ldap") echo "selected=\"selected\"";?>>LDAP</option>
								<option value="ldaps" <?php if ($resultset["LDAPScheme"] == "ldaps") echo "selected=\"selected\"";?>>LDAPS</option>
								</select>
								(LDAP=Non SSL, LDAPS=Use SSL)
							</td>
						</tr>
						<tr>
							<td width="50%">
								<span class="label"><?php echo JText::_('LDAP_HOST'); ?>:</span>
								</td><td>
								<input id="LDAPHost" name="LDAPHost" type="text" value="<?php echo $resultset['LDAPHost']; ?>" style="width:100%;" />
							</td>
							</tr>
							<tr>
							<td width="50%">
								<span class="label"><?php echo JText::_('LDAP_PORT'); ?>:</span>
								</td><td>
								<input id="LDAPPort" name="LDAPPort" type="text" size="10" maxlength="10" value="<?php echo $resultset['LDAPPort']; ?>" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_SEARCH_BASE'); ?>:</span>
							</td>
							<td width="100%">
								<input id="LDAPBaseDN" type="text" name="LDAPBaseDN" value="<?php echo $resultset["LDAPBaseDN"]; ?>" style="width:100%;" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_USERNAME_ATTRIBUTE'); ?>:</span>
							</td>
							<td width="100%">
								<input id="LDAPUsernameAttribute" name="LDAPUsernameAttribute" type="text" value="<?php echo $resultset["LDAPUsernameAttribute"]; ?>" size="30"/>
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_SEARCH_SCOPE'); ?>:</span>
							</td>
							<td width="100%">
								<select id="LDAPSearchScope" name="LDAPSearchScope">
									<option value="base" <?php if ($resultset["LDAPSearchScope"] == "base") echo "selected=\"selected\""; ?>>Base DN search only (LDAPRead)</option>
									<option value="one" <?php if ($resultset["LDAPSearchScope"] == "one") echo "selected=\"selected\""; ?>>One level search (LDAPList)</option>
									<option value="sub" <?php if ($resultset["LDAPSearchScope"] == "sub") echo "selected=\"selected\""; ?>>Full sub-tree search (LDAPSearch)</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('FILTER'); ?>:</span>
							</td>
							<td width="100%">
								<input id="LDAPFilter" type="text" name="LDAPFilter" value="<?php echo $resultset["LDAPFilter"]; ?>" style="width:100%;" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_PROTOCOL_VERSION'); ?>:</span>
							</td>
							<td width="100%">
								<select id="LDAPProtocolVersion" name="LDAPProtocolVersion">
									<option value="3" <?php if ($resultset["LDAPProtocolVersion"] == "3") echo "selected=\"selected\""; ?>>3</option>
									<option value="2" <?php if ($resultset["LDAPProtocolVersion"] == "2") echo "selected=\"selected\""; ?>>2</option>
									<option value="1" <?php if ($resultset["LDAPProtocolVersion"] == "1") echo "selected=\"selected\""; ?>>1</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_USE_REFERRALS'); ?>:</span>
							</td>
							<td width="100%">
								<input type="checkbox" name="LDAPReferralsCheck" id="LDAPReferralsCheck" <?php if ( $resultset['LDAPReferrals'] == 1 ) echo "checked=\"checked\""; ?> />
								<input type="hidden" name="LDAPReferrals" id="LDAPReferrals" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_FALLBACK'); ?>:</span>
							</td>
							<td width="100%">
								<input type="checkbox" name="LDAPFallbackCheck" id="LDAPFallbackCheck" <?php if ( $resultset['LDAPFallback'] == 1 ) echo "checked=\"checked\""; ?> />
								<input type="hidden" name="LDAPFallback" id="LDAPFallback" />
							</td>
						</tr>
						<tr>
							<td colspan="2" nowrap>
								<span class="label_grey" nowrap><i><?php echo JText::_('LDAP_REQUIRED_ACTIVEDIRECTORY'); ?>:</i></span>
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_USE_AUTHENTICATION'); ?>:</span>
							</td>
							<td width="100%">
								<input type="checkbox" name="LDAPBindByUsercheck" id="LDAPBindByUsercheck" <?php if ($resultset['LDAPBindByUser'] == 1) echo "checked=\"checked\""; ?> />
								<input type="hidden" name="LDAPBindByUser" id="LDAPBindByUser" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_USERNAME'); ?>:</span>
							</td>
							<td width="50%">
								<input id="LDAPBindUsername" type="text" name="LDAPBindUsername" value="<?php echo $resultset["LDAPBindUsername"]; ?>" style="width:100%;" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap><?php echo JText::_('LDAP_PASSWORD'); ?>:</span>
							</td>
							<td width="50%">
								<input id="LDAPBindPassword" type="password" name="LDAPBindPassword" value="<?php echo $resultset["LDAPBindPassword"]; ?>" style="width:100%;" AUTOCOMPLETE="OFF" />
							</td>
						</tr>
						<tr>
							<td nowrap>
								<span class="label" nowrap>RFC 2255 URL:</span>
							</td>
							<td width="100%">
								<input id="LDAPUrl" name="LDAPUrl" type="text" value="" style="width:100%;" />
							</td>
						</tr>

				</table>
			</table>
		<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

		<!-- ACL-->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('ACL'); ?></b>:
				</td>
				<td class="configdesc">
					<legend><?php echo JText::_('ACL_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="aclReset" value="off" valign="absmiddle" /><?php echo JText::_('RESET'); ?>
				</td>
				<td>
				<table>			

				<?php
         
    		 //TODO FInd a way to replace the nobr tags whilst keeping the menu icons next to the names
    		      	        
        ?>
					<tr><td><?php echo JText::_('STOPWATCH'); ?>:</td><td><?php Common::acl_select_droplist("aclStopwatch", $resultset["aclStopwatch"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('DAILY_TIMESHEET'); ?>:</td><td><?php Common::acl_select_droplist("aclDaily", $resultset["aclDaily"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('WEEKLY_TIMESHEET'); ?>:</td><td><?php Common::acl_select_droplist("aclWeekly", $resultset["aclWeekly"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('MONTHLY_TIMESHEET'); ?>:</td><td><?php Common::acl_select_droplist("aclMonthly", $resultset["aclMonthly"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('SIMPLE_WEEKLY_TIMESHEET'); ?>:</td><td><?php Common::acl_select_droplist("aclSimple", $resultset["aclSimple"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('CLIENTS'); ?>:</td><td><?php Common::acl_select_droplist("aclClients", $resultset["aclClients"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('PROJECTS'); ?>:</td><td><?php Common::acl_select_droplist("aclProjects", $resultset["aclProjects"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('TASKS'); ?>:</td><td><?php Common::acl_select_droplist("aclTasks", $resultset["aclTasks"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('REPORTS'); ?>:</td><td><?php Common::acl_select_droplist("aclReports", $resultset["aclReports"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('RATES'); ?>:</td><td><?php Common::acl_select_droplist("aclRates", $resultset["aclRates"]); ?>&nbsp;</td></tr>
					<tr><td><?php echo JText::_('ABSENCES'); ?>:</td><td><?php Common::acl_select_droplist("aclAbsences", $resultset["aclAbsences"]); ?>&nbsp;</td></tr>
					<tr><td>Expenses:</td><td><?php Common::acl_select_droplist("aclExpenses", $resultset["aclExpenses"]); ?>&nbsp;</td></tr>
					<tr><td>Expense Categories:</td><td><?php Common::acl_select_droplist("aclECategories", $resultset["aclECategories"]); ?>&nbsp;</td></tr>
					<tr><td>Time Approval:</td><td><?php Common::acl_select_droplist("aclTApproval", $resultset["aclTApproval"]); ?>&nbsp;</td></tr>
		</table>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

		<!-- simple timesheet layout -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('SIMPLE_LAYOUT'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('SIMPLE_LAYOUT_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="aclReset" value="off" valign="absmiddle" onclick="document.configurationForm.simpleTimesheetLayout.selectedIndex = 0; document.configurationForm.simpleTimesheetLayout.disabled=(this.checked);"  /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<select name="simpleTimesheetLayout" id="simpleTimesheetLayout">
						<option value="small work description field" <?php if ($resultset["simpleTimesheetLayout"] == 'small work description field') echo "selected=\"selected\"";?>>small work description field</option>
						<option value="big work description field" <?php if ($resultset["simpleTimesheetLayout"] == 'big work description field') echo "selected=\"selected\"";?>>big work description field</option>
						<option value="no work description field" <?php if ($resultset["simpleTimesheetLayout"] == 'no work description field') echo "selected=\"selected\"";?>>no work description field</option>
					</select>
				</td>
			</tr>

	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

		<!-- start page -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('START_PAGE'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('START_PAGE_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="aclReset" value="off" valign="absmiddle" onclick="document.configurationForm.simpleTimesheetLayout.selectedIndex = 0; document.configurationForm.simpleTimesheetLayout.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<select name="startPage" id="startPage">
						<option value="stopwatch" <?php if ($resultset["startPage"] == 'stopwatch') echo "selected=\"selected\"";?>>Stopwatch</option>
						<option value="daily" <?php     if ($resultset["startPage"] == 'daily')     echo "selected=\"selected\"";?>>Daily Timesheet</option>
						<option value="weekly" <?php    if ($resultset["startPage"] == 'weekly')    echo "selected=\"selected\"";?>>Weekly Timesheet</option>
						<option value="monthly" <?php   if ($resultset["startPage"] == 'monthly')   echo "selected=\"selected\"";?>>Monthly View</option>
						<option value="simple" <?php    if ($resultset["startPage"] == 'simple')    echo "selected=\"selected\"";?>>Simple Timesheet</option>
					</select>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

		<!-- locale -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('LOCALE'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('LOCALE_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="localeReset" value="off" valign="absmiddle" onclick="document.configurationForm.locale.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<input type="text" name="locale" size="75" maxlength="254" value="<?php echo htmlentities(trim(stripslashes($resultset["locale"]))); ?>" style="width: 100%;" />
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

		<!-- timezone -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('TIME_ZONE'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('TIME_ZONE_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="timezoneReset" value="off" onclick="document.configurationForm.timezone.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<input type="text" name="timezone" size="75" maxlength="254" value="<?php echo htmlentities(trim(stripslashes($resultset["timezone"]))); ?>" style="width: 100%;" />
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- timeformat -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('TIME_FORMAT'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('TIME_FORMAT_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="timeformatReset" value="off" onclick="document.configurationForm.timeformat.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<select name="timeformat" style="width: 100%;">
						<?php if ($resultset["timeformat"] == "12") { ?>
							<option value="12" selected="selected">12 hour format</option>
							<option value="24">24 hour format</option>
						<?php } else { ?>
							<option value="12">12 hour format</option>
							<option value="24" selected="selected">24 hour format</option>
						<?php } ?>
					</select>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- weekstartday -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('WEEK_START'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('WEEK_START_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="weekStartDayReset" value="off" onclick="document.configurationForm.weekstartday.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<select name="weekstartday" style="width: 100%;">
						<?php
							//get the current time
							$dowDate = time();

							//make it sunday
							$dowDate = strtotime(date("d M Y H:i:s",$dowDate) . " -" . date("w",$dowDate) . " days");
							//TODO fix up selection to put day into internationalisation language
							//for each day of the week
							for ($i=0; $i<7; $i++) {
								$dowString = strftime("%A", $dowDate);
								print "<option value=\"$i\"";
								if ($resultset["weekstartday"] == $i)
									print " selected=\"selected\"";
								print ">$dowString</option>";
								//increment the day
								$dowDate = strtotime(date("d M Y H:i:s",$dowDate) . " +1 days");
							}
						?>
					</select>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- Items per page in Projects-->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('PROJECTS_ITEMS'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('PROJECTS_ITEMS_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="projectItemsPerPageReset" value="off" onclick="document.configurationForm.projectItemsPerPage.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<input type="text" name="projectItemsPerPage" size="75" maxlength="254" value="<?php echo htmlentities(trim(stripslashes($resultset["project_items_per_page"]))); ?>" style="width: 100%;" />
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- Items per page in Tasks-->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('TASKS_ITEMS'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('TASKS_ITEMS_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="taskItemsPerPageReset" value="off" onclick="document.configurationForm.taskItemsPerPage.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<input type="text" name="taskItemsPerPage" size="75" maxlength="254" value="<?php echo htmlentities(trim(stripslashes($resultset["task_items_per_page"]))); ?>" style="width: 100%;" />
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">


			<!-- headerhtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('HEADER_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('HEADER_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="headerReset" value="off" onclick="document.configurationForm.headerhtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="headerhtml" style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["headerhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- bodyhtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('BODY_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('BODY_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="bodyReset" value="off" onclick="document.configurationForm.bodyhtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="bodyhtml"  style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["bodyhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- bannerhtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('BANNER_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('BANNER_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="bannerReset" value="off" onclick="document.configurationForm.bannerhtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="bannerhtml" style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["bannerhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- footerhtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('FOOTER_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('FOOTER_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="footerReset" value="off" onclick="document.configurationForm.footerhtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="footerhtml" style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["footerhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">

			<!-- errorhtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('ERROR_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('ERROR_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="errorReset" value="off" onclick="document.configurationForm.errorhtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="errorhtml" style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["errorhtml"]))); ?></textarea>
				</td>
			</tr>

				</table>
				<table width="100%" border="0" cellspacing="0" cellpadding="5" class="section_body">


			<!-- tablehtml -->
			<tr>
				<td class="configtype">
					<b><?php echo JText::_('TABLE_HTML'); ?></b>:
				</td>
				<td class="configdesc">
					</<legend><?php echo JText::_('TABLE_HTML_INTRO'); ?></legend>
				</td>
			</tr>
			<tr>
				<td align="left" class="label" width="90">
					<input type="checkbox" name="tableReset" value="off" onclick="document.configurationForm.tablehtml.disabled=(this.checked);" /><?php echo JText::_('RESET'); ?>
				</td>
				<td align="left" width="100%">
					<textarea rows="5" cols="73" name="tablehtml" style="width: 100%;"><?php echo htmlentities(trim(stripslashes($resultset["tablehtml"]))); ?></textarea>
				</td>
			</tr>



			</td>
		</tr>
	</table>

		</td>
	</tr>
</table>

</form>
</div>