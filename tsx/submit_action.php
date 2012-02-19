<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate


if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclTsubmission'))return;

if (isset($_REQUEST['Submit'])) { // if submission of times
	
	$action = $_REQUEST["Submit"];
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
	else 
	{
		// a date, client or user redirection
		// get new date for the url

		Header("Location: ".Config::getRelativeRoot()."/submit?uid=".gbl::getUid()."&orderby=".$_REQUEST["orderby"]."&client_id=".gbl::getClientId()."&mode=".gbl::getMode()."&year=".gbl::getYear()."&month=".gbl::getMonth()."&day=".gbl::getDay());
		exit;
	}
	// we're done so redirect to the submission page
	Header("Location: ".Config::getRelativeRoot()."/submit?uid=".gbl::getUid()."&orderby=".$_REQUEST["orderby"]."&client_id=".gbl::getClientId()."&mode=".gbl::getMode()."&year=".gbl::getYear()."&month=".gbl::getMonth()."&day=".gbl::getDay());
	exit;

?>




