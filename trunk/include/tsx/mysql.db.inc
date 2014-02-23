<?php
{
if(!class_exists('Site'))die('Restricted Access');
	//To port to another database, simply keep this interface and duplicate the functionality.
	//Should have plug in compatibility.

	
	//FUNCTION NO LONGER REQUIRED AS CONNECTION IS CONTROLLED BY database.class.php
	function dbConnect() {
		if(class_exists('Site')){
			trigger_error('The function dbConnect is deprecated.  The OO version of this site is already connected to the DB',E_USER_WARNING);
			//ppr(getShortDebugTrace(2));
			return false;
		}
	}

	function MakeInsertString ( $data ) {
		// Takes an associative array of $data and makes a string of fields and values like
		// (field1, field2, field3) VALUES ('value1', 'value2', 'value3')
		while ( list( $key, $val ) = each( $data ) ) {
			if ($key && $val) {
				$string["fields"] .= "$key, ";
				$string["values"] .= "'" . addslashes($val) . "', ";
			}
		}
		// Remove the last comma...
		$string["fields"]=preg_replace("/, $/","",$string["fields"]);
		$string["values"]=preg_replace("/, $/","",$string["values"]);

		// Return the completed string.
		return " ( $string[fields] ) VALUES $string[values] ";
	}

	function MakeUpdateString ($data) {
		// Similar to above, but instead makes a string of
		while ( list( $key, $val ) = each( $data ) ) {
			if ($data[$key]) {
				$strings .= $key . " = '" . addslashes($val) . "', ";
			}
		}
		$strings=preg_replace("/, $/", " ", $strings);
		return $strings;
	}

	/**
	 * 
	 * @TODO Move this function to the database class
	 * @param unknown_type $statement
	 */
	function dbQuery ($statement) {
		$result = Site::Db()->query($statement) or die("<pre>\n\nCan't perform query: " . mysql_error() . " \n\n$statement\n\n</pre>");
		$num_rows = dbNumRows($result);
		return array($result, $num_rows);
	}

	/**
	 * 
	 * @TODO Move this function to the database class
	 * @param unknown_type $statement
	 */
	function tryDbQuery ($statement) {
		//$result=@mysql_query($statement) or die("<pre>\n\nCan't perform query: " . mysql_error() . " \n\n$statement\n\n</pre>");
		$result=mysql_query($statement);
		$num_rows = dbNumRows($result);
		return array($result, $num_rows);
	}

	/**
	 * 
	 * @TODO Move this function to the database class
	 * @param unknown_type $statement
	 */
	function dbNumRows ($result) {
		return @mysql_numrows($result);
	}

	/**
	 * 
	 * @TODO Move this function to the database class
	 * @param unknown_type $statement
	 */
	function dbResult ($result,$i=-1) {
		if ($i >= 0) {
			@mysql_data_seek($result,$i);
		}
		return mysql_fetch_array($result);
	}

	/**
	 * 
	 * @TODO Move this function to the database class
	 * @param unknown_type $statement
	 */
	function dbLastID() {
		return mysql_insert_id();
	}

}?>
