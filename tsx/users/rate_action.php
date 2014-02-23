<?php

if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Manager'))return;
//N.B. This page should be authorised to administrators only!

//load local vars from request/post/get
$action = $_REQUEST["action"];
$rate_id = mysql_real_escape_string($_REQUEST["rate_id"]);
$bill_rate = mysql_real_escape_string($_REQUEST["bill_rate"]);

//print "<p>isAdministrator='$isAdministrator'</p>";
	
if ($action == "addupdate") {
	if ($rate_id == 1) {
	    //redirect back to the rate management page
	    Header("Location: rate_maint.php");
	    exit(0);
	}
	//check whether the rate exists
	list($qh,$num) = dbQuery("select rate_id, bill_rate from ".tbl::getRateTable()." where rate_id='$rate_id'");

	//if there is a match
	if ($data = dbResult($qh)) {

		//has the bill_rate changed
		if ($data["bill_rate"] != $bill_rate) {
			//update the assignments
			dbQuery("update ".tbl::getRateTable()." set bill_rate='$bill_rate' where rate_id='$data[rate_id]'");
		}
	} else {
		// a new rate
		dbquery("insert into ".tbl::getRateTable()." (bill_rate) values ('$bill_rate')");
	}
} 

//redirect back to the rate management page
gotoLocation(Config::getRelativeRoot()."/users/rate_maint");

?>
