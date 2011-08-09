<?php

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('USERS')."</title>");

?>

<script type="text/javascript">

	function deleteUser(uid, username) {
		//get confirmation
		if (confirm("<?php echo JText::_('JS_CONFIRM_DELETE_USER')?>")) {
			document.userForm.action.value = "delete";
			document.userForm.uid.value = uid;
			document.userForm.username.value = username;
			document.userForm.submit();
		}
	}

	function editUser(uid, firstName, lastName, username, emailAddress, password, isAdministrator, isManager, isActive) {
		document.userForm.uid.value = uid;
		document.userForm.first_name.value = firstName;
		document.userForm.last_name.value = lastName;
		document.userForm.username.value = username;
		document.userForm.email_address.value = emailAddress;
		document.userForm.password.value = password;
		document.userForm.checkAdmin.checked = isAdministrator;
		document.userForm.isAdministrator.value = document.userForm.checkAdmin.checked=='1' ? true : false;
		document.userForm.checkManager.checked = isManager;
		document.userForm.isManager.value = document.userForm.checkManager.checked=='1' ? true : false;
		document.userForm.checkActive.checked = isActive;
		document.userForm.isActive.value = document.userForm.checkActive.checked=='1' ? true : false;
		onCheckClearance();
		document.location.href = "#AddEdit";
	}

	function addUser() {
		//validation
		if (document.userForm.username.value == "")
			alert("<?php echo JText::_('JS_ALERT_GIVE_USERNAME')?>");
		else if (document.userForm.password.value == "")
			alert("<?php echo JText::_('JS_ALERT_GIVE_PASSWORD')?>");
		else {
			document.userForm.action.value = "addupdate";
			document.userForm.submit();
		}
	}

	function goClone() {
		var cloneTo = document.userForm.username.value;
		var location = "user_clone";
		if(cloneTo.length > 0)
			location+="?cloneTo=" + cloneTo;
		window.location.href=location;
	}

	function onCheckClearance() {
		document.userForm.isAdministrator.value =
			document.userForm.checkAdmin.checked;
		document.userForm.isManager.value =
			document.userForm.checkManager.checked;
	}

	function onCheckActive() {
		document.userForm.isActive.value =
			document.userForm.checkActive.checked;
	}

</script>
</head>
<h1><?php echo JText::_('USERS'); ?></h1>
<form action="user_action" name="userForm" method="post">
<input type="hidden" name="action" value="" />
<input type="hidden" name="uid" value="" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<!--  td width="100%" class="face_padding_cell" -->
		<td align="left" class="outer_table_heading">
			<?php echo JText::_('EMPLOYEES_CONTRACTORS_LIST'); ?>
		</td>
		<td align="right" >
			&nbsp;
		</td>
	</tr>
	<tr>
		<!--  td width="100%" class="face_padding_cell" -->
		<td align="left" class="outer_table_heading">
			&nbsp;
		</td>
		<td align="right" >
			<a href="javascript:goClone()">Copy Projects/Tasks between users</a>
		</td>
	</tr>
		<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
				<!--  table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_body" -->
	<tr class="inner_table_head">
		<td class="inner_table_column_heading"><?php echo JText::_('FIRST_NAME'); ?></td>
		<td class="inner_table_column_heading"><?php echo JText::_('LAST_NAME'); ?></td>
		<td align="center" class="inner_table_column_heading"><?php echo JText::_('ACTIVE'); ?></td>
		<td class="inner_table_column_heading"><?php echo JText::_('ROLE'); ?></td>
		<td class="inner_table_column_heading"><?php echo JText::_('USERNAME'); ?></td>
		<td class="inner_table_column_heading"><?php echo JText::_('EMAIL_ADDRESS'); ?></td>
		<td class="inner_table_column_heading"><i><?php echo JText::_('ACTIONS'); ?></i></td>
	</tr>
<?php

list($qh,$num) = dbQuery("SELECT * FROM ".tbl::getuserTable()." WHERE username!='guest' ORDER BY status desc, last_name, first_name");

while ($data = dbResult($qh)) {
	$firstNameField = empty($data["first_name"]) ? "&nbsp;": $data["first_name"];
	$lastNameField = empty($data["last_name"]) ? "&nbsp;": $data["last_name"];
	$usernameField = empty($data["username"]) ? "&nbsp;": $data["username"];
	$isActive = ($data["status"]=='ACTIVE');
	$emailAddressField = empty($data["email_address"]) ? "&nbsp;": $data["email_address"];
	$isAdministrator = ($data["level"] >= 10);
	$isManager = ($data["level"] >= 5);

	print "<tr>\n";
	print "<td class=\"calendar_cell_middle\">$firstNameField</td>";
	print "<td class=\"calendar_cell_middle\">$lastNameField</td>";
	if ($isActive)
		print "<td align=\"center\" class=\"calendar_cell_middle\"><img src=\"../images/green-check-mark.gif\" height=\"12\" border=\"0\" alt=\"\" /></td>";
	else
		print "<td align=\"center\" class=\"calendar_cell_middle\"><img src=\"../images/red-x.gif\" height=\"12\" border=\"0\" alt=\"\" /></td>";

	if ($isAdministrator)
		print "<td class=\"calendar_cell_middle\"><span class=\"calendar_total_value_weekly\">".JText::_('ADMIN')."</td>";
	else if ($isManager)
		print "<td style=\"color:blue; font-weight:bold;\" class=\"calendar_cell_middle\">".JText::_('MANAGER')."</td>";
	else
		print "<td class=\"calendar_cell_middle\">Basic</td>";

	print "<td class=\"calendar_cell_middle\">$usernameField</td>";
	print "<td class=\"calendar_cell_middle\">$emailAddressField</td>";
	print "<td class=\"calendar_cell_disabled_right\">";
	print "	<a href=\"javascript:deleteUser('$data[uid]', '$data[username]')\">".JText::_('DELETE')."</a>,&nbsp;\n";
	print "	<a href=\"javascript:editUser('$data[uid]', '$data[first_name]', '$data[last_name]', '$data[username]', '$data[email_address]', '$data[password]', '$isAdministrator', '$isManager', '$isActive')\">".JText::_('EDIT')."</a>\n";
	print "</td>\n";
	print "</tr>\n";
}
?>
		</td>
	</tr>
</table>
<br /><br />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<!--  table width="100%" border="0" cellspacing="0" cellpadding="0" -->
	<tr>
		<td align="left" class="outer_table_heading">
			<a name="AddEdit">	<?php echo JText::_('ADD_UPDATE_USER'); ?>:</a>
		</td>
		<td align="right" >
			&nbsp;
		</td>
	</tr>
	<tr>
		<td align="left" class="outer_table_heading">
			&nbsp;
		</td>
		<td align="right" >
			<a href="javascript:goClone()">Copy Projects/Tasks between users</a>
		</td>
	</tr>

	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
		<!-- table width="100%" border="0" class="table_body" -->
	<tr>
		<td><?php echo JText::_('FIRST_NAME'); ?>:<br /><input size="20" name="first_name" style="width: 100%;"></td>
		<td><?php echo JText::_('LAST_NAME'); ?>:<br /><input size="20" name="last_name" style="width: 100%;"></td>
		<td><?php echo JText::_('USERNAME'); ?>:<br /><input size="15" name="username" style="width: 100%;" /></td>
		<td><?php echo JText::_('EMAIL_ADDRESS'); ?>:<br /><input size="35" name="email_address" style="width: 100%;" /></td>
		<td><?php echo JText::_('PASSWORD'); ?>:<br /><input type="password" size="20" name="password" style="width: 100%;" AUTOCOMPLETE="OFF" /></td>
	</tr>
	<tr>
		<td colspan="2" align="left">
			<input type="checkbox" name="checkAdmin" id="checkAdmin" value="" onclick="onCheckClearance();" /><?php echo JText::_('IS_ADMINISTRATOR'); ?>
			<input type="hidden" name="isAdministrator" id="isAdministrator" value="false" />
		</td>
		<td colspan="2" align="left">
			<input type="checkbox" name="checkManager" id="checkManager" value="" onclick="onCheckClearance();" /><?php echo JText::_('IS_PROJ_MANAGER'); ?>
			<input type="hidden" name="isManager" id="isManager" value="false" />
		</td>
		<td align="left">
			<input type="checkbox" name="checkActive" id="checkActive" value="" onclick="onCheckActive();" /><?php echo JText::_('IS_ACTIVE'); ?>
			<input type="hidden" name="isActive" id="isActive" value="false" />
		</td>
	</tr>
				<!--  table width="100%" border="0" class="table_bottom_panel" -->
	<tr>
		<td align="center">
			<input type="button" name="addupdate" value="Add/Update Employee/Contractor" onclick="javascript:addUser()" class="bottom_panel_button" />
		</td>
	</tr>
</table>

</form>