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
		//$date1 = $_POST["date1"];
		//$newdate = explode("-", $date1);
		Header("Location: ".Config::getRelativeRoot()."/submit?uid=".gbl::getUid()."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay());
		exit;
	}
	// we're done so redirect to the submission page
	Header("Location: ".Config::getRelativeRoot()."/submit?uid=".gbl::getUid()."&amp;orderby=".$_REQUEST["orderby"]."&amp;client_id=".gbl::getClientId()."&amp;mode=".gbl::getMode()."&amp;year=".gbl::getYear()."&amp;month=".gbl::getMonth()."&amp;day=".gbl::getDay());
	exit;

?>




