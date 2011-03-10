<?php

if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclWeekly'))return;

//load local vars from superglobals
$year = $_REQUEST["year"];
$month = $_REQUEST["month"];
$day = $_REQUEST["day"];
$destination = $_REQUEST["destination"];
$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;


$currentDate = mktime(0, 0, 0, $month, $day, $year);

//get todays values

$todayDate = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
//check that the client id is valid
//if ($client_id == 0 || empty($client_id))
//	$client_id = getFirstClient();

//check that project id is valid
if ($proj_id == 0)
	$task_id = 0;

//include date input classes
include "include/tsx/form_input.inc";


ob_start();
include("client_proj_task_javascript.php");
?>
<title>Update timesheet for <?php echo gbl::getContextUser(); ?></title>
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

?>
</head>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" id="outer_table">
	  <tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" nowrap class="outer_table_heading" nowrap>
							Clock On / Off
						</td>
						<td align="right" nowrap class="outer_table_heading">
							<?php echo strftime("%A %B %d, %Y", mktime(0,0,0,$month, $day, $year)); ?>
						</td>
					</tr>
				</table>

<!-- include the timesheet face up until the next start section -->
<?php 
	$fromPopup = "true";
	include("clockOnOff_core_new.inc"); 
?>


		</td>
	  </tr>
	</table>

</body>
</html>
<?php
// vim:ai:ts=4:sw=4
?>
