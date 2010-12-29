<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

// Authenticate
if(!class_exists('Site')){
	die('remove .php from the url to access this page');
}
if (!Site::getAuthenticationManager()->isLoggedIn() || !Site::getAuthenticationManager()->hasAccess('aclSimple')) {
	if(!class_exists('Site')){
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . get_acl_level('aclSimple'));	
	}
	else{
		Header("Location: login.php?redirect=".$_SERVER['REQUEST_URI']."&clearanceRequired=" . Common::get_acl_level('aclSimple'));
	}
	
	exit;
}
//load local vars from superglobals
$client_id = $_REQUEST['client_id'];

$contextUser = strtolower($_SESSION['contextUser']);

?>
<HTML>
<head>
<title>Client Info</title>

<?php
$CLIENT_TABLE = tbl::getClientTable();
	$query = "SELECT organisation, description, address1, address2,".
				"city, country, postal_code, contact_first_name, contact_last_name,".
				"username, contact_email, phone_number, fax_number, gsm_number, ".
				"http_url ".
			"FROM $CLIENT_TABLE ".
			"WHERE $CLIENT_TABLE.client_id=$client_id";



	list($qh, $num) = dbQuery($query);
	if ($num > 0) {

		$data = dbResult($qh);
?>
	<center>
		
			<font SIZE=+1><b><?php echo $data['organisation']; ?></b></font>
		
		<table border="1" width="100%">
		
		<tr>
			<td align="right">Description:</td>
			<td COLSPAN=3><i><?php echo $data['description'] ?></i>
		</td></tr>
		<tr>
			<td align="right">Address1:</td>
			<td COLSPAN=2 WIDTH=80%> <?php echo $data['address1']; ?>
		</td></tr>
		<tr>
			<td align="right">Address2:</td>
			<td COLSPAN=2><?php echo  $data['address2']; ?>
		</td></tr>
		<tr>
			<td align="right">ZIP, City:</td>
			<td COLSPAN=2> <?php echo $data['postal_code']; echo  $data['city']; ?>
		</td></tr>
		<tr>
			<td align="right">Country:</td>
			<td COLSPAN=2><?php echo  $data['country']; ?>
		</td></tr>
		<tr>
			<td align="right">Contact:</td>
			<td COLSPAN=2><?php echo  $data['contact_first_name']; echo $data['contact_last_name']; ?>
		</td></tr>
		<tr>
			<td align="right">Phone:</td>
			<td COLSPAN=2><?php echo  $data['phone_number']; ?>
		</td></tr>
		<tr>
			<td align="right">Fax::</td>
			<td COLSPAN=2><?php echo  $data['fax_number']; ?>
		</td></tr>
		<tr>
			<td align="right">GSM:</td>
			<td COLSPAN=2><?php echo  $data['gsm_number']; ?>
		</td></tr>
<?php 	} else {
		print "None.";
	}
?>

	</table></center>
