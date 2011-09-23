<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

PageElements::setTemplate('popup_template.php');
//load local vars from request/post/get
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;
$destination = $_REQUEST["destination"];

//check that the client id is valid
//if ($client_id == 0)
//	$client_id = getFirstClient();

//check that project id is valid
if ($proj_id == 0)
	$task_id = 0;

ob_start();
require(Config::getDocumentRoot().'/tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();
?>

<script type="text/javascript">
//<![CDATA[

function doClockonoff(clockon) {
	document.theForm.clockonoff.value = clockon;
	validate();
}

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
//]]>
</script>
<?php

$head = ob_get_contents();
ppr($head);
PageElements::setHead($head);
ob_end_clean();                                             
PageElements::setBodyOnLoad("doOnLoad();");
PageElements::appendHead("<title>".Config::getMainTitle()." | ".JText::_('STOPWATCH')." | ".gbl::getContextUser()."</title>");
?>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="outer_table">
		<tr>
			<td width="100%" class="face_padding_cell">
<?php
	//$stopwatch = 1;
	//$fromPopup = "true";
	//include(dirname(__FILE__)."/../include/tsx/clockOnOff_core_new.inc");
	
	require("include/tsx/clocking.class.php");
  $clock = new Clocking();
  $clock->createClockOnOff(null,true,false,true);
        
?>
			</td>
		</tr>
	</table>
