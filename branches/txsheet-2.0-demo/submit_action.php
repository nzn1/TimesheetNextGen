<?php
// $Header: /cvsroot/tsheet/timesheet.php/submit_action.php,v 1.7 2005/05/23 07:32:00 vexil Exp $
// Authenticate


if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	Header("Location: login?redirect=$_SERVER[PHP_SELF]&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	exit;
}

$contextUser = strtolower($_SESSION['contextUser']);

// submit.php?uid=peter&orderby=project&client_id=0&mode=monthly&year=2010&month=8&day=1
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

	$path = Config::getRelativeRoot()."/submit?uid=$uid&orderby=$orderby&client_id=$client_id&mode=$mode&year=$year&month=$month&day=$day";
	if(debug::getLocation()==1)echo "Location: <a href=\"".$path."\">".$path."</a>";
	else header("Location: ".$path);	

?>




