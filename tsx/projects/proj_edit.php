<?php
if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_PROJECT')."</title>");

// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

$loggedInUser = strtolower($_SESSION['loggedInUser']);

if (empty($loggedInUser))
	errorPage("Could not determine the logged in user");



//load local vars from request/post/get
$proj_id = $_REQUEST['proj_id'];

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));
Site::getCommandMenu()->add(new TextCommand("&nbsp; &nbsp; &nbsp;", false, ""));
Site::getCommandMenu()->add(new TextCommand("Copy Projects/Tasks between users", true, Config::getRelativeRoot()."/user_clone"));

list($qh, $num) = dbQuery("SELECT proj_id, " .
								"title, " .
								"client_id, " .
								"description, " .
								"unix_timestamp(start_date) AS start_stamp, ".
								"unix_timestamp(deadline) AS end_stamp, ".
								"http_link, " .
								"proj_status, " .
								"proj_leader " .
							"FROM ".tbl::getProjectTable()." " .
							"WHERE proj_id = $proj_id " .
							"ORDER BY proj_id");
$data = dbResult($qh);

$dti=getdate($data["start_stamp"]);
$start_month = $dti["mon"];
$start_year = $dti["year"];

$dti=getdate($data["end_stamp"]);
$end_month = $dti["mon"];
$end_year = $dti["year"];

list($qh, $num) = dbQuery("SELECT username FROM ".tbl::getAssignmentsTable()." WHERE proj_id = $proj_id");
$selected_array = array();
$i = 0;
while ($datanext = dbResult($qh)) {
	$selected_array[$i] = $datanext["username"];
	$i++;
}

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/projects/proj_action" method="post">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="proj_id" value="<?php echo $data["proj_id"]; ?>" />
<div id="inputArea">
<!--  table width="600" align="center" border="0" cellspacing="0" cellpadding="0" -->
<table>
	<tr>
		<td class="outer_table_heading">
			<h1><?php echo JText::_('EDIT_PROJECT').": ".stripslashes($data["title"]); ?> </h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right"><?php echo JText::_('PROJECT_TITLE'); ?>:</td>
		<td><input type="text" name="title" size="42" value="<?php echo stripslashes($data["title"]); ?>" style="width: 100%;" maxlength="200" /></td>
	</tr>
	<tr>
		<td align="right">Client:</td>
		<td><?php Common::client_select_list($data["client_id"], 0, false, false, false, true, "", false); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top">Description:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual" style="width: 100%;"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('START_DATE'); ?>:</td>
		<td><?php Common::day_button("start_day",$data["start_stamp"],0); Common::month_button("start_month",$start_month); Common::year_button("start_year",$start_year); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('DUE_DATE'); ?>:</td>
		<td><?php Common::day_button("end_day",$data["end_stamp"],0); Common::month_button("end_month",$end_month); Common::year_button("end_year",$end_year); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('STATUS'); ?>:</td>
		<td><?php Common::proj_status_list("proj_status", $data["proj_status"]); ?></td>
	</tr>
	<tr>
		<td align="right">URL:</td>
		<td><input type="text" name="url" size="42" value="<?php echo $data["http_link"]; ?>" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('PROJECT_MEMBERS'); ?>:</td>
		<td><?php Common::multi_user_select_list("assigned[]",$selected_array); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('PROJECT_LEADER'); ?>:</td>
		<td><?php Common::single_user_select_list("project_leader", $data["proj_leader"]); ?></td>
	</tr>
	<tr>
			<!--  table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" value="<?php echo JText::_('SUBMIT_CHANGES'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>