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

//load client id from superglobals
$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id']: 1;

$startDate = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());
$start_day = 1;
$start_month = date("n", $startDate);
$start_year = date("Y", $startDate);;
$end_day = 31;
$end_month = date("n", $startDate);;
$end_year = date("Y", $startDate);;

?>
<html>
<head>
<title>Add New Project</title>

<form action="<?php echo Config::getRelativeRoot(); ?>/projects/proj_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">
<table width="600" align="center" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="outer_table_heading">
		<td>	<h1>Add New Project</h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right">Project Title:</td>
		<td><input type="text" name="title" size="42" style="width: 100%;" maxlength="200" /></td>
	</tr>
	<tr>
		<td align="right">Client:</td>
		<td><?php Common::client_select_list($client_id, 0, false, false, false, true, "", false); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top">Description:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%;"></textarea></td>
	</tr>
	<tr>
		<td align="right">Start Date:</td>
		<td><?php Common::day_button("start_day",0,0); Common::month_button("start_month", $start_month); Common::year_button("start_year", $start_year); ?></td>
	</tr>
	<tr>
		<td align="right">Deadline:</td>
		<td><?php Common::day_button("end_day",0,0); Common::month_button("end_month", $end_month); Common::year_button("end_year", $end_year); ?></td>
	</tr>
	<tr>
		<td align="right">Status:</td>
		<td><?php Common::proj_status_list("proj_status", "Started"); ?></td>
	</tr>
	<tr>
		<td align="right">URL:</td>
		<td><input type="text" name="url" size="42" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Assignments:</td>
		<td><?php Common::multi_user_select_list("assigned[]"); ?></td>
	</tr>
	<tr>
		<td align="right">Project Leader:</td>
		<td><?php Common::single_user_select_list("project_leader"); ?></td>
	</tr>
	<tr>
			<!-- table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" name="add" value="Add New Project" />
		</td>
	</tr>
</table>
</div>
</form>