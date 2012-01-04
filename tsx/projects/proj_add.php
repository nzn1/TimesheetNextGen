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
	<h2><?php echo JText::_('ADD_NEW_PROJECT'); ?></h2>

	<div><label><?php echo JText::_('PROJECT_TITLE'); ?>:</label>
		<input type="text" name="title" size="42" maxlength="200" /></div>
	
	<div><label><?php echo JText::_('CLIENT'); ?>:</label>
		<?php Common::client_select_list(gbl::getClientId(), 0, false, false, false, true, "", false); ?></div>
	
	<div><label><?php echo JText::_('DESCRIPTION'); ?>:</label>
		<textarea name="description" rows="4" cols="40"></textarea></div>
	
	<div><label><?php echo JText::_('START_DATE'); ?>:</label>
		<?php Common::day_button("start_day",0,0); Common::month_button("start_month", $start_month); Common::year_button("start_year", $start_year); ?></div>
	</div>
	
	<div><label><?php echo JText::_('DUE_DATE'); ?>:</label>
		<?php Common::day_button("end_day",0,0); Common::month_button("end_month", $end_month); Common::year_button("end_year", $end_year); ?></div>
	</div>
	
	<div><label><?php echo JText::_('STATUS'); ?>:</label>
		<?php Common::proj_status_list("proj_status", "Started"); ?></div>
	</div>
	
	<div><label><?php echo JText::_('URL'); ?>:</label>
		<input type="text" name="url" size="42" /></div>
	</div>
	
	<div><label><?php echo JText::_('PROJECT_MEMBERS'); ?>:</label>
		<?php Common::multi_user_select_list("assigned[]"); ?></div>
	</div>
	
	<div><label><?php echo JText::_('PROJECT_LEADER'); ?>:</label>
		<?php Common::single_user_select_list("project_leader"); ?></div>
	</div>
	
	<input type="submit" name="add" value="<?php echo JText::_('ADD_NEW_PROJECT'); ?>" />
	</div>
</form>