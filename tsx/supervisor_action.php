<?php
die('NOT CONVERTED TO OO YET');
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

require("class.AuthenticationManager.php");
require("class.CommandMenu.php");
	
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTasks'))return;

// Config::getRelativeRoot()."submit.php?uid=peter&amp;orderby=project&amp;client_id=0&amp;mode=monthly&amp;year=2010&amp;month=8&amp;day=1"
//load local vars from request/post/get
$action = $_REQUEST["Modify"];

	$name = $_REQUEST["name"];
	$orderby = $_REQUEST["orderby"];
	$client_id = $_REQUEST["client_id"];
	$mode = $_REQUEST["mode"];
	$year = $_REQUEST["year"];
	$month = $_REQUEST["month"];
	$day = $_REQUEST["day"];
	$uid = $_REQUEST["uid"];

	if (isset($_REQUEST['Modify'])) {
		//LogFile::->write("action = \" $action\" request modify = \"" . $_REQUEST['Modify'] . "\"" .  "\"\n");		
		
	if (isset($_REQUEST['approve'])) {
		//var_dump ($_REQUEST['sub']);
		$transids = "";
		foreach ($_REQUEST['approve'] as $transId) {
			if($transids == "")
				$transids = $transId;
			else 
				$transids = $transids . ", " . $transId;
		}
		list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Approved\"" .
				" WHERE trans_num IN ( $transids )");
		//LogFile::->write("update query transids = \" $transids\" qh = \"$qh\"  num=\"".$num. "\"\n");		
		}
	
	if (isset($_REQUEST['reject'])) {
		//var_dump ($_REQUEST['sub']);
		$transids = "";
		foreach ($_REQUEST['reject'] as $transId) {
			if($transids == "")
				$transids = $transId;
			else 
				$transids = $transids . ", " . $transId;
		}
		list($qh, $num) = dbQuery("UPDATE ".tbl::getTimesTable()." SET status = \"Open\"" .
				" WHERE trans_num IN ( $transids )");
		//LogFile::->write("update query transids = \" $transids\" qh = \"$qh\"  num=\"".$num. "\"\n");		
	}
}
	
	// we're done so redirect to the submission page
	$path = Config::getRelativeRoot()."/supervisor?uid=".$_REQUEST["uid"]."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay();
	gotoLocation($path);

?>




