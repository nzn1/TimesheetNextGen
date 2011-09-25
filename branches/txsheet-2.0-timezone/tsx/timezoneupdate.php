<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from request/post/get
$uid = gbl::getContextUser();

//define the command menu

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('SET_TIMEZONES')."</title>");
PageElements::setTheme('newcss');

?>

<form action="<?php echo Config::getRelativeRoot(); ?>/timezone_action" method="post">
<input type="hidden" name="action" value="settimezone" />

<div id="inputArea">
<?php 
// get name of project and client name using project id

	$queryString = "SELECT first_name, last_name, timezone ".
				" FROM ".tbl::getUserTzTable(). 
				" WHERE username= '$uid'";
	list($qh, $num) = dbQuery($queryString);
	$data = dbResult($qh);
	LogFile::write(" Get title organisation  Query = \"$queryString\" and rows returned is \"$num\"\n");
	
?>
<table class="noborder">
	<tbody class="nobground">
	<tr>
		<td class="outer_table_heading">
			<h2><?php echo JText::_('SET_TIMEZONES'); ?></h2>
		</td>
	</tr>
	<tr>
		<td class="outer_table_heading">
			<h3><?php echo JText::_('SET_TIMEZONE_EXPLAIN'); ?></h3>
		</td>
	</tr>
	<tr>
		<td><?php echo (JText::_('USER')) ?>:</td>
		<td><span><?php echo $data['first_name']. " ".$data['last_name'] ?></span></td>
	</tr>
	<tr>
		<td><?php echo (JText::_('TZONE')) ?>:
		<span><?php
			if ($data['timezone'] != "") echo $data['timezone'];
			else echo JText::_('NO_TZONE');
			?></span></td> 
	</tr>
	<tr>
		<td><?php echo (JText::_('CONF_TZONE')) ?>
		<span><?php
			list($qhq, $numq) = dbQuery("SELECT timezone FROM ".tbl::getConfigTable()." WHERE config_set_id = '1'");
			$configdata = dbResult($qhq);	
			if ($configdata['timezone'] != "") echo $data['timezone'];
			else echo JText::_('NO_TZONE');
			?></span></td> 
	</tr>
	<tr>
		<td><?php echo (JText::_('SYSTEM_TZONE')) ?>
		<span><?php
			$systz = date_default_timezone_get();
			if ($systz != "") echo $systz;
			else echo JText::_('NO_TZONE');
			?></span></td> 
	</tr>
	<tr>
		<td><?php echo (JText::_('USED_TZONE')) ?>
		<span><?php
			if ($data['timezone'] != "") {
				echo JText::_('USER_TZONE');
				$timezonetobeused = $data['timezone']; 
			}
			else if ($configdata['timezone'] != "") {
				echo JText::_('CONF_TZONE');
				$timezonetobeused = $configdata['timezone'];
			}
			else if ($systz != "") {
				echo JText::_('SYSTEM_TZONE');
				$timezonetobeused = $systz;
			}
			else {
				echo JText::_('NO_TZONE');
				$timezonetobeused = "null";
			}
			?></span></td> 
	</tr>
	<tr>
		<td >
		<input type="submit" value="<?php echo (JText::_('CHANGE_TZ')) ?>" />
		<input type="hidden" name="timezone" value=" <?php echo $timezonetobeused ?>" />
		</td>
	</tr>

</table>
</div>
</form>