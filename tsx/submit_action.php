<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate


if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

// Config::getRelativeRoot()."submit.php?uid=peter&amp;orderby=project&amp;client_id=0&amp;mode=monthly&amp;year=2010&amp;month=8&amp;day=1"
//load local vars from superglobals
		$uid = $_REQUEST["uid"];
		$mode = gbl::getMode();
		$proj_id = gbl::getProjId();
		$client_id = gbl::getClientId();
		$year = gbl::getYear();
		$month = gbl::getMonth();
		$day = gbl::getDay();
		$orderby = $_REQUEST["orderby"];
		
if (isset($_REQUEST['submit'])) { // if submission of times
	
		$action = $_REQUEST["submit"];
	
	//if ($action == "Submit") {
		$name = $_REQUEST["name"];

	
			
		$TIMES_TABLE = tbl::getTimesTable();
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
			list($qh, $num) = dbQuery("UPDATE $TIMES_TABLE SET status = \"Submitted\"" .
					" WHERE trans_num IN ( $transids )");
			
			}
		}
	}
	// we're done so redirect to the submission page

	$path = Config::getRelativeRoot()."/submit?uid=$uid&amp;orderby=$orderby&amp;client_id=$client_id&amp;mode=$mode&amp;year=$year&amp;month=$month&amp;day=$day";
	gotoLocation($path);
	exit;

?>




