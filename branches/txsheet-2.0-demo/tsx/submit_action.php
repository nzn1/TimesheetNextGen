<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate


if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

// Config::getRelativeRoot()."submit.php?uid=peter&amp;orderby=project&amp;client_id=0&amp;mode=monthly&amp;year=2010&amp;month=8&amp;day=1"
	
if (isset($_REQUEST['submit'])) { // if submission of times
	
		$action = $_REQUEST["submit"];
	
	//if ($action == "Submit") {
		$name = $_REQUEST["name"];
	//}
	
		if (isset($action)) {
	
		if (isset($_REQUEST['sub'])) {
			//var_dump ($_REQUEST['sub']);
			$transids = "";
			foreach ($_REQUEST['sub'] as $transId) {
				if($transids == "")
					$transids = $transId;
				else 
					$transids = $transids . ", " . $transId;
			}
			list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Submitted\"" .
					" WHERE trans_num IN ( $transids )");
			
			}
		}
	}
	// we're done so redirect to the submission page

	$path = Config::getRelativeRoot()."/submit?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay();
	gotoLocation($path);
	exit;

?>




