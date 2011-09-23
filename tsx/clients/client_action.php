<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));

// Authenticate
//require("class.AuthenticationManager.php");
//require("class.CommandMenu.php");
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclClients'))return;

//load local vars from request/post/get
$action = $_REQUEST["action"];
$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;
$organisation = mysql_real_escape_string(isset($_POST["organisation"]) ? $_POST["organisation"]: "");
$description = mysql_real_escape_string(isset($_POST['description']) ? $_POST['description']: "");
$address1 = mysql_real_escape_string(isset($_POST['address1']) ? $_POST['address1']: "");
$address2 = mysql_real_escape_string(isset($_POST['address2']) ? $_POST['address2']: "");
$city = mysql_real_escape_string(isset($_POST['city']) ? $_POST['city']: "");
$country = mysql_real_escape_string(isset($_POST['country']) ? $_POST['country']: "");
$postal_code = mysql_real_escape_string(isset($_POST['postal_code']) ? $_POST['postal_code']: "");
$contact_first_name = mysql_real_escape_string(isset($_POST['contact_first_name']) ? $_POST['contact_first_name']: "");
$contact_last_name = mysql_real_escape_string(isset($_POST['contact_last_name']) ? $_POST['contact_last_name']: "");
$client_username = mysql_real_escape_string(isset($_POST['client_username']) ? $_POST['client_username']: "");
$contact_email = mysql_real_escape_string(isset($_POST['contact_email']) ? $_POST['contact_email']: "");
$phone_number = mysql_real_escape_string(isset($_POST['phone_number']) ? $_POST['phone_number']: "");
$fax_number = mysql_real_escape_string(isset($_POST['fax_number']) ? $_POST['fax_number']: "");
$gsm_number = mysql_real_escape_string(isset($_POST['gsm_number']) ? $_POST['gsm_number']: "");
$http_url = mysql_real_escape_string(isset($_POST['http_url']) ? $_POST['http_url']: "");

if ($_REQUEST['action'] == "add") {
	dbquery("INSERT INTO ".tbl::getClientTable()." VALUES ('$client_id','$organisation','$description','$address1','$city'," .
	"'L','$country','$postal_code','$contact_first_name','$contact_last_name','$client_username'," .
	"'$contact_email','$phone_number','$fax_number','$gsm_number','$http_url','$address2')");
} elseif ($action == "edit") {
	//create the query
	$query = "UPDATE ".tbl::getClientTable()." SET organisation='$organisation',".
		"description='$description',address1='$address1',city='$city',".
		"country='$country',postal_code='$postal_code',".
		"contact_first_name='$contact_first_name',".
		"contact_last_name='$contact_last_name',username='$client_username',".
		"contact_email='$contact_email',phone_number='$phone_number',".
		"fax_number='$fax_number',gsm_number='$gsm_number',".
		"http_url='$http_url',address2='$address2' ".
		"WHERE client_id=$client_id ";

	list($qh,$num) = dbquery($query);
} elseif ($action == "delete") {
	//find out if this client is in use
	list($qh,$num) = dbQuery("SELECT * FROM ".tbl::getProjectTable()." WHERE client_id='$client_id'");
	if ($num > 0)
		errorPage(JText::_('CANT_DELETE_CLIENT_WITH_PROJECTS'));
	else
		dbquery("DELETE FROM ".tbl::getClientTable()." WHERE client_id='$client_id'");
}

gotoLocation(Config::getRelativeRoot()."/clients/client_maint");

// vim:ai:ts=4:sw=4
?>
