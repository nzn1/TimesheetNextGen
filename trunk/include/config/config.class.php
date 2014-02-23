<?php

require('include/tables.class.php');
require('config.factory.class.php');

/**
 *
 * The configuration of the website
 *
 */
class Config extends ConfigFactory {
	private $data = array();
	private $db;

	function __construct($connection) {
	    $this->db = $connection;
		$this->db->query("SELECT * FROM ".tbl::getConfigTable());
		while($configdata = $this->db->fetchNextObject()) {
			$this->data[$configdata->name] = $configdata->value;
		}
	}

	function get($value) {
		if(array_key_exists($value, $this->data))
			return $this->data[$value];
	}

	function set($field, $value) {
		$this->data[$field] = $value;
		$this->db->execute("UPDATE ".tbl::getConfigTable()." SET value='$value' WHERE name='$field'");
	}
}
?>
