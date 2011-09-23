<?php

if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclWeekly'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('CLOCK')." | ".gbl::getContextUser()."</title>");

PageElements::setTemplate('popup_template.php');

//include date input classes
include "include/tsx/form_input.inc";

ob_start();
include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();
?>
<script type="text/javascript">

function resizePopupWindow() {
	//now resize the window
	var outerTable = document.getElementById('outer_table');
	var innerWidth = window.innerWidth;
	var outerWidth = window.outerWidth;
	if (innerWidth == null || outerWidth == null) {
		innerWidth = document.body.offsetWidth;
		outerWidth = innerWidth + 28;
	}
	var innerHeight = window.innerHeight;
	var outerHeight = window.outerHeight;
	if (innerHeight == null || outerHeight == null) {
		innerHeight = document.body.offsetHeight;
		outerHeight = innerHeight + 30;
	}

	var newWidth = outerTable.offsetWidth + outerWidth - innerWidth;
	var newHeight = outerTable.offsetHeight + outerHeight - innerHeight;
	window.resizeTo(newWidth, newHeight);
}

</script>

<?php 
PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad("doOnLoad();");

?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="outer_table">
	  <tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left"  class="outer_table_heading" >
							<?php echo JText::_('CLOCK_ON_OFF')?>
						</td>
						<td align="right"  class="outer_table_heading">
							<?php echo strftime(JText::_('DFMT_WKDY_MONTH_DAY_YEAR'), mktime(0,0,0,gbl::getMonth(), gbl::getDay(), gbl::getYear())); ?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the next start section -->
<?php 
        require("include/tsx/clocking.class.php");
        $clock = new Clocking();
        $clock->createClockOnOff(null,true,false);        
	      //$fromPopup = "true";
	      //require(dirname(__FILE__)."/../include/tsx/clockOnOff_core_new.inc"); 
?>


		</td>
	  </tr>
	</table>

</body>
</html>
<?php
// vim:ai:ts=4:sw=4
?>
