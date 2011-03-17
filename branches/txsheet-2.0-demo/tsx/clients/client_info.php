<?php
if(!class_exists('Site'))die('Restricted Access');

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/popups.inc');
PageElements::addFile('tsx_footer','themes/'.PageElements::getTheme().'/popups.inc');

//load local vars from superglobals
$client_id = $_REQUEST['client_id'];
?>
<HTML>
<head>
<title>TSX v<?php echo Config::getVersion()." - ".JText::_('CLIENT_INFO')?></title>
</head>

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
		
		<?php if(trim($data['http_url']) !=='') echo "<a href=".trim($data['http_url']).">"; ?>
		<font SIZE=+1><b><?php echo $data['organisation']; ?></b></font>
		<?php if(trim($data['http_url']) !=='') echo "</a>"; ?>
		
	<table border="1" width="100%">
		
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('DESCRIPTION'))?>:</td>
			<td COLSPAN=5><i><?php echo $data['description'] ?></i>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('ADDRESS1'))?>:</td>
			<td COLSPAN=5 WIDTH=80%> <?php echo $data['address1']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('ADDRESS2'))?>:</td>
			<td COLSPAN=5><?php echo  $data['address2']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('CITY'))?>:</td>
			<td> <?php echo $data['city']; ?>
			<td align="right"><?php echo ucfirst(JText::_('POSTAL_CODE'))?>:</td>
			<td> <?php echo $data['postal_code']; ?>
			<td align="right"><?php echo ucfirst(JText::_('COUNTRY'))?>:</td>
			<td><?php echo  $data['country']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('CONTACT'))?>:</td>
			<td COLSPAN=5><?php echo $data['contact_first_name']." ".$data['contact_last_name']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('EMAIL'))?>:</td>
			<td COLSPAN=5><?php echo  $data['contact_email']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('PHONE'))?>:</td>
			<td COLSPAN=5><?php echo  $data['phone_number']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('FAX'))?>:</td>
			<td COLSPAN=5><?php echo  $data['fax_number']; ?>
		</td></tr>
		<tr>
			<td align="right"><?php echo ucfirst(JText::_('MOBILE'))?>:</td>
			<td COLSPAN=5><?php echo  $data['gsm_number']; ?>
		</td></tr>
<?php 	} else {
		print JText::_('NO_CLIENT_SELECTED');
	}
?>

	</table></center
