<?php
if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_PROJECT')."</title>");
PageElements::setTheme('txsheet2');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclProjects'))return;

if (isEmpty(gbl::getLoggedInUser()))
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
	<h1><?php echo JText::_('EDIT_PROJECT').": <i>".stripslashes($data["title"]); ?> </i></h1>

	<div><label><?php echo JText::_('PROJECT_TITLE'); ?>:</label>
		<input type="text" name="title" size="42" value="<?php echo stripslashes($data["title"]); ?>" maxlength="200" /></div>

	<div><label>Client:</label>
		<?php Common::client_select_list($data["client_id"], 0, false, false, false, true, "", false); ?></div>

	<div><label>Description:</label>
		<textarea name="description" rows="4" cols="40"><?php $data["description"] = stripslashes($data["description"]); echo $data["description"]; ?></textarea></div>

	<div><label><?php echo JText::_('START_DATE'); ?>:</label>
		<?php Common::day_button("start_day",$data["start_stamp"],0); Common::month_button("start_month",$start_month); Common::year_button("start_year",$start_year); ?></div>

	<div><label><?php echo JText::_('DUE_DATE'); ?>:</label>
		<?php Common::day_button("end_day",$data["end_stamp"],0); Common::month_button("end_month",$end_month); Common::year_button("end_year",$end_year); ?></div>

	<div><label><?php echo JText::_('STATUS'); ?>:</label>
		<?php Common::proj_status_list("proj_status", $data["proj_status"]); ?></div>

	<div><label>URL:</label>
		<input type="text" name="url" size="42" value="<?php echo $data["http_link"]; ?>" /></div>

	<div><label><?php echo JText::_('PROJECT_MEMBERS'); ?>:</label>
		<?php Common::multi_user_select_list("assigned[]",$selected_array); ?></div>
	
	<div><label><?php echo JText::_('PROJECT_LEADER'); ?>:</label>
		<?php Common::single_user_select_list("project_leader", $data["proj_leader"]); ?></div>
			<input type="submit" value="<?php echo JText::_('SUBMIT_CHANGES'); ?>" />
</div>
</form>
