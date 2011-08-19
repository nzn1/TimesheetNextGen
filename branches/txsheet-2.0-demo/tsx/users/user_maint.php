<?php

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('USERS')."</title>");
ob_start();	
PageElements::setTheme('newcss');
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

	function editUser(uid, firstName, lastName, employee_type, supervisor, username, emailAddress, password, isAdministrator, isManager, isActive) {
		document.userForm.uid.value = uid;
		document.userForm.first_name.value = firstName;
		document.userForm.last_name.value = lastName;
		document.userForm.employee_type.value = employee_type;
		document.userForm.username.value = username;
		document.userForm.supervisor.value = supervisor;
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

	function clearFields() {
		var cloneTo = document.userForm.username.value;
		var location = "user_clone";
		if(cloneTo.length > 0)
			location+="?cloneTo=" + cloneTo;
		window.location.href=location;
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
		document.userForm.isActive.value =
			document.userForm.checkActive.checked;
	}

</script>
<?php 
	PageElements::setHead(ob_get_contents());
	ob_end_clean(); 
?>
<h1><?php echo JText::_('USERS'); ?></h1>
<form action="user_action" name="userForm" method="post">
<input type="hidden" name="action" value="" />
<input type="hidden" name="uid" value="" />

<table>
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
			<a href="javascript:goClone()"><?php print JText::_('COPY_TASKS'); ?></a>
		</td>
	</tr>
</table>
<table>
<thead>
	<tr>
		<th><?php echo JText::_('FIRST_NAME'); ?></th>
		<th><?php echo JText::_('LAST_NAME'); ?></th>
		<th align="center"><?php echo JText::_('ACTIVE'); ?></th>
		<th><?php echo JText::_('ROLE'); ?></th>
		<th><?php echo JText::_('EMPLOYEE_TYPE'); ?></th>
		<th><?php echo JText::_('SUPERVISOR'); ?></th>
		<th><?php echo JText::_('SUPERVISOR'); ?></th>
		<th><?php echo JText::_('LOGIN_NAME'); ?></th>
		<th><i><?php echo JText::_('ACTIONS'); ?></i></th>
	</tr>
	</thead>
	<tbody>
<?php

// get a list of supervisors to populate a drop-down list
$svruids = array();
$svrunames = array();
list($qsvrs,$numsvr) = dbQuery("SELECT uid, username FROM ".tbl::getuserTable()." WHERE level>='5' ORDER BY username");
while($svrdata = dbResult($qsvrs)) {
	$svruids[] = $svrdata['uid'];
	$svrunames[] = $svrdata['username'];
}

// create an array of the possible employee types - must match the enum of employee_type in the user table
$emptypearray = array('Contractor', 'Employee');

list($qh,$num) = dbQuery("SELECT * FROM ".tbl::getuserTable()." WHERE username!='guest' ORDER BY status desc, last_name, first_name");

while ($data = dbResult($qh)) {
	$uid = $data['uid'];
	// now get the name of the supervisor
	list($qs, $num) = dbQuery("SELECT b.username as supervisor FROM ".tbl::getuserTable()." a, ".tbl::getuserTable().
		" b WHERE a.uid = $uid and a.supervisor=b.uid");
	$svr = dbResult($qs);
	
	$firstNameField = empty($data["first_name"]) ? "&nbsp;": $data["first_name"];
	$lastNameField = empty($data["last_name"]) ? "&nbsp;": $data["last_name"];
	$usernameField = empty($data["username"]) ? "&nbsp;": $data["username"];
	$isActive = ($data["status"]=='ACTIVE');
	$emailAddressField = empty($data["email_address"]) ? "&nbsp;": $data["email_address"];
	$isAdministrator = ($data["level"] >= 10);
	$isManager = ($data["level"] >= 5);
	$employee_type = empty($data["employee_type"]) ? "&nbsp;": $data["employee_type"];
	
	$supervisor = empty($svr["supervisor"]) ? "None": $svr["supervisor"];
			
	print "<tr>\n";
	print "<td class=\"calendar_cell_middle\">$firstNameField</td>";
	print "<td class=\"calendar_cell_middle\">$lastNameField</td>";
	if ($isActive)
		print "<td align=\"center\" class=\"calendar_cell_middle\"><img src=\"../images/green-check-mark.gif\" height=\"12\" border=\"0\" alt=\"\" /></td>";
	else
		print "<td align=\"center\" class=\"calendar_cell_middle\"><img src=\"../images/red-x.gif\" height=\"12\" border=\"0\" alt=\"\" /></td>";

	if ($isAdministrator)
		print "<td style=\"color:red; \" class=\"calendar_cell_middle\"><span class=\"calendar_total_value_weekly\">".JText::_('ADMIN')."</td>";
	else if ($isManager)
		print "<td style=\"color:blue; \" class=\"calendar_cell_middle\">".JText::_('MANAGER')."</td>";
	else
		print "<td class=\"calendar_cell_middle\">".JText::_('BASIC')."</td>";
	print "<td class=\"calendar_cell_middle\">$employee_type</td>";
	print "<td class=\"calendar_cell_middle\">$supervisor</td>";
	print "<td class=\"calendar_cell_middle\">$usernameField</td>";
	print "<td class=\"calendar_cell_middle\">$emailAddressField</td>";
	print "<td class=\"calendar_cell_disabled_right\">";
	print "	<a href=\"javascript:deleteUser('$data[uid]', '$data[username]')\">".JText::_('DELETE')."</a>,&nbsp;\n";
	print "	<a href=\"javascript:editUser('$data[uid]', '$data[first_name]', '$data[last_name]', '$data[employee_type]', '$data[supervisor]', '$data[username]', '$data[email_address]', '$data[password]', '$isAdministrator', '$isManager', '$isActive')\">".JText::_('EDIT')."</a>\n";
	print "</td>\n";
	print "</tr>\n";
}
?>
		</td>
	</tr>
</table>
<br /><br />
<table>

	<tr>
		<td align="left" class="outer_table_heading">
			<a name="AddEdit">	<?php echo JText::_('ADD_UPDATE_USER'); ?>:</a>
		</td>
		<td align="right" >
			&nbsp;
		</td>
	</tr>
</table>
<table>
	<thead>
	<tr>

		<th><?php echo JText::_('FIRST_NAME'); ?></th>
		<th><?php echo JText::_('LAST_NAME'); ?></th>
		<th><?php echo JText::_('EMPLOYEE_TYPE'); ?></th>
		<th><?php echo JText::_('SUPERVISOR'); ?></th>
		<th><?php echo JText::_('USERNAME'); ?></th>
		<th><?php echo JText::_('EMAIL_ADDRESS'); ?></th>
		<th><?php echo JText::_('PASSWORD'); ?></th>

	</tr>

	</thead>
	<tbody>
		<tr>
			<td><input name="first_name"></th>
		<td><input name="last_name"></td>
		<td><?php Common::emp_button("employee_type", $emptypearray, $employee_type)?></td>
		<td><?php Common::svr_button("supervisor", $svruids, $svrunames, "none")?></td>
		<td><input name="username" /></td>
		<td><input name="email_address" /></td>
		<td><input type="password" size="20" name="password"  AUTOCOMPLETE="OFF" /></td>
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

		<td align="center">
			<input type="button" name="addupdate" value="Add/Update Employee/Contractor" onclick="javascript:addUser()" class="bottom_panel_button" />
		</td>
		<td align="center">
			<input type="button" name="clearfield" value="Clear Fields" onclick="this.form.reset()" class="bottom_panel_button" />
		</td>
	</tr>
	</tbody>
</table>

</form>