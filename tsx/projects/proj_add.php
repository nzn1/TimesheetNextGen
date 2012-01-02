<?php

if(!class_exists('Site'))die('Restricted Access');
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('ADD_NEW_PROJECT')."</title>");
PageElements::setTheme('newcss');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclProjects'))return;

if (isEmpty(gbl::getLoggedInUser()))
	errorPage("Could not determine the logged in user");

//define the command menu
Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "javascript:history.back()"));

//load client id from REQUEST variables
$startDate = mktime(0,0,0, gbl::getMonth(), 1, gbl::getYear());
$start_day = 1;
$start_month = date("n", $startDate);
$start_year = date("Y", $startDate);;
$end_day = 31;
$end_month = date("n", $startDate);;
$end_year = date("Y", $startDate);;

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/projects/proj_action" method="post">
<input type="hidden" name="action" value="add" />
<div id="inputArea">

<table class="noborder">
	<tbody class="nobground">
	<tr>
		<td class="outer_table_heading">
		<td>	<h1><?php echo JText::_('ADD_NEW_PROJECT'); ?></h1>
		</td>
	</tr>
	<!--  table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table" -->
	<tr>
				<!--  table width="100%" border="0" cellpadding="1" cellspacing="2" class="table_body" -->
		<td align="right"><?php echo JText::_('PROJECT_TITLE'); ?>:</td>
		<td><input type="text" name="title" size="42" maxlength="200" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('CLIENT'); ?>:</td>
		<td><?php Common::client_select_list(gbl::getClientId(), 0, false, false, false, true, "", false); ?></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('DESCRIPTION'); ?>:</td>
		<td><textarea name="description" rows="4" cols="40" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('START_DATE'); ?>:</td>
		<td><?php Common::day_button("start_day",0,0); Common::month_button("start_month", $start_month); Common::year_button("start_year", $start_year); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('DUE_DATE'); ?>:</td>
		<td><?php Common::day_button("end_day",0,0); Common::month_button("end_month", $end_month); Common::year_button("end_year", $end_year); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('STATUS'); ?>:</td>
		<td><?php Common::proj_status_list("proj_status", "Started"); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('URL'); ?>:</td>
		<td><input type="text" name="url" size="42" /></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo JText::_('PROJECT_MEMBERS'); ?>:</td>
		<td><?php Common::multi_user_select_list("assigned[]"); ?></td>
	</tr>
	<tr>
		<td align="right"><?php echo JText::_('PROJECT_LEADER'); ?>:</td>
		<td><?php Common::single_user_select_list("project_leader"); ?></td>
	</tr>
	<tr>
			<!-- table width="100%" border="0" class="table_bottom_panel" -->
		<td align="center">
			<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_PROJECT'); ?>" />
		</td>
	</tr>
</table>
</div>
</form>