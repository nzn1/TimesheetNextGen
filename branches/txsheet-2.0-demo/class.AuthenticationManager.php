<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

/**
 * Added 11 Dec 2008
 * Installation script. As this file is called by every other file,
 * it makes sense to check to see if this app has been installed here.
 */	
$cpath = dirname(__FILE__);
if(
	!file_exists($cpath.DIRECTORY_SEPARATOR.'database_credentials.inc') ||
	!file_exists($cpath.DIRECTORY_SEPARATOR.'table_names.inc') 
	) {
	// this app has not been installed yet, redirect to the installation pages
	header("Location: ./install/");
	exit;
} else if(file_exists($cpath.DIRECTORY_SEPARATOR.'install')) {
	// the install directory has not been deleted, redirect to "delete install dir" pages
	// NOTE: this may also mean that the user has upgraded. The "delete install dir"
	// pages also check the version number to see if the user is running the latest
	// version, if not takes user to the "upgrade" pages
	header("Location: ./install/");
	exit;	
}

if( file_exists( $cpath . "/siteclosed")) {
	$siteclosed=1;
} else {
	$siteclosed=0;
}
	
require("table_names.inc");
if(class_exists('Site')){
	require("common.class.php");
	$common = new Common();
}
else{
	require("common.inc");
}
require("enum.php");

//define constants for error code
enum(
	"AUTH_NONE", //no attempt has been made to authenticate yet
	"AUTH_SUCCESS", //authentication succeeded
	"AUTH_FAILED_INCORRECT_PASSWORD", //incorrect password
	"AUTH_FAILED_NO_USERNAME_PASSED", //error no username was passed
	"AUTH_FAILED_EMPTY_PASSWORD", //error empty password not allowed
	"AUTH_LOGOUT", //user logged out
	"AUTH_FAILED_LDAP_LOGIN", //failed login via LDAP, check ldapErrorCode
	"AUTH_FAILED_NO_LDAP_MODULE", //no ldap module detected
	"AUTH_FAILED_INACTIVE" //error account is marked as inactive
);

//define constants for ldap error code
enum (
	"LDAP_AUTH_NONE", //no LDAP authentication has been attempted
	"LDAP_CONNECTION_FAILED", //connection failed
	"LDAP_MULTIPLE_ENTRIES_RETURNED", //multiple entries were returned
	"LDAP_SERVER_ERROR", //server error, check server error code
	"LDAP_USER_NOT_FOUND" //user not found
);

//define clearance levels
define("CLEARANCE_ADMINISTRATOR", 10);
define("CLEARANCE_MANAGER", 5);
define("CLEARANCE_BASIC", 0);

/**
*	Manages and provides authentication services
*/
class AuthenticationManager {

	/**
	*	The error code
	*/
	var $errorCode = AUTH_NONE;

	/**
	* The error text
	*/
	var $errorText = "Authentication has not yet been attempted";

	/**
	*	The error code to check if errorCode=AUTH_FAILED_LDAP_LOGIN
	*/
	var $ldapErrorCode;

	/**
	*	The error text which matches the ldapErrorCode
	*/
	var $ldapErrorText;

	/**
	* The error code returned from the LDAP server
	*/
	var $ldapServerErrorCode;

	/**
	* The error description returned from the LDAP server
	*/
	var $ldapServerErrorText;

	/* authentication function: this is called by
	*   each page to ensure that there is an authenticated user
	*/
	function login($username, $password) {
		require("table_names.inc");
		require("database_credentials.inc");
		global $siteclosed;

		//start/continue the session
		session_start();

		//set initial error codes
		$this->errorCode = AUTH_NONE;
		$this->errorText = "No attempt has been made to authenticate yet";
		$this->ldapErrorCode = LDAP_AUTH_NONE;
		$this->ldapErrorText = "No attempt has been made to authenticate via LDAP yet";
		$this->ldapServerErrorCode = 0;
		$this->ldapServerErrorText = "[]";

		//a username must be passed
		if (empty($username)) {
			$this->logout();
			$this->errorCode = AUTH_FAILED_NO_USERNAME_PASSED;
			$this->errorText = "You must enter a username";
			return false;
		}

		//a password must be passed
		if (empty($password)) {
			$this->logout();
			$this->errorCode = AUTH_FAILED_EMPTY_PASSWORD;
			$this->errorText = "You must enter a password";
			return false;
		}

		//connect to the database
		$dbh = dbConnect();

		//check whether we are using ldap
		if ($this->usingLDAP()) {
			//check their credentials with LDAP
			if ( !$this->ldapAuth($username, $password) ) {
				if ($this->usingFallback()) {
					if(!$this->dbAuth($username, $password)){
						return false;
					}
				} else {
					return false;
				}
			}
		} else {
			if(!$this->dbAuth($username, $password)){
				return false;
			}
		}

		//get the access level
		list($qh,$num) = dbQuery("SELECT level ".
									"FROM $USER_TABLE WHERE username='$username'");
		$data = dbResult($qh);

		if($siteclosed && ($data["level"] < CLEARANCE_ADMINISTRATOR))
			return false;

		//Fix session ID vulnerability: if someone installs this system locally, and creates the same username as an 
		//administrator/manager/ etc, making the session_id a hash from the username, password, and the access level 
		//should ensure the real production system won't allow them in without logging in, unless all that data is 
		//exactly the same.

		session_unset();
		session_destroy();

		$session_id=md5($username.$password.uniqid());
		//if we want to use a non-random string (for testing)
		//$session_id=md5($username.$password.$data["level"]);
		session_id($session_id);
		session_start();

		//set session variables
		$_SESSION["loggedInUser"] = $username;
		$_SESSION["accessLevel"] = $data["level"];
		$_SESSION["contextUser"] = $username;

		dbquery("UPDATE $USER_TABLE SET session='$session_id' WHERE username='$username'");

		$this->errorCode = AUTH_SUCCESS;
		$this->errorText = "Authentication succeeded";

		return true;
	}

	/* 
	* Login using ldap database	
	*/
	function ldapAuth($username, $password){
		// check that the module is availble
		$ldapMaxLinks = ini_get( "ldap.max_links" );
		if ( empty( $ldapMaxLinks ) ){
			$this->errorCode = AUTH_FAILED_NO_LDAP_MODULE;
			$this->errorText = "Could not access LDAP module - is it installed?";
			return false;
		}
		// check their credentials with LDAP
		if ( !$this->ldapLogin( $username, $password ) ){
			if($this->errorCode == AUTH_NONE ) { //error code hasn't been set to some other error
				$this->errorCode = AUTH_FAILED_LDAP_LOGIN;
				$this->errorText = "Authentication via LDAP failed";
			}
			return false;
		}
		return true;
	}
	
	/* 
	* Login using local database 
	*/
	function dbAuth($username, $password){
		require( "table_names.inc" );
		require( "database_credentials.inc" );
		// query the user table for authentication details
		list( $qh, $num ) = dbQuery( "SELECT password AS passwd1, $DATABASE_PASSWORD_FUNCTION('$password') AS passwd2, status " . "FROM $USER_TABLE WHERE username='$username'" );
		$data = dbResult( $qh );
		// is the password correct?
		if ( $num == 0 || $data["passwd1"] != $data["passwd2"] ){
			$this->errorCode =  AUTH_FAILED_INCORRECT_PASSWORD;
			if((isset($this->errorText))){
				$this->errorText = $this->errorText . " or Incorrect username or password";
			} else {
				$this->errorText = "Incorrect username or password";
			}
			return false;
		}

		if ( $data['status'] == 'INACTIVE') {
			$this->errorCode = AUTH_FAILED_INACTIVE; 
			$this->errorText = "That account is marked INACTIVE";
			return false;
		}

		return true;
	}

	/**
	* Logs out the currenlty logged in user
	*/
	function logout() {
		require("table_names.inc");
		require("database_credentials.inc");

		//start/continue the session
		session_start();

		if($this->isLoggedIn()) {
			$username=$_SESSION['loggedInUser'];
			dbquery("UPDATE $USER_TABLE SET session='logged out' WHERE username='$username'");
		}

		//unset all the variables
		session_unset();

		//destroy the session
		session_destroy();

		$this->errorCode = AUTH_LOGOUT;
		$this->errorText = "The user was logged out";
		return;
	}

	/**
	*	returns true if the user is logged in
	*/
	function isLoggedIn() {
		global $siteclosed;
		require( "table_names.inc" );

		//start/continue the session
		@session_start();

		$session_id=session_id();
		if(empty($_SESSION['loggedInUser'])) return false;
		$username=$_SESSION['loggedInUser'];

		list( $qh, $num ) = dbQuery( "SELECT session FROM $USER_TABLE WHERE username='$username'" );
		if($num != 1) return false;
		$data = dbResult( $qh );
		if($data['session'] != $session_id) return false;

		if($siteclosed && ($_SESSION['accessLevel'] < CLEARANCE_ADMINISTRATOR))
			return false;

		return !empty($_SESSION['accessLevel']) && !empty($_SESSION['loggedInUser']) && !empty($_SESSION['contextUser']);
	}

	/**
	* returns true if the user has clearance to the specified level
	*/
	function hasClearance($accessLevel) {
		//start/continue the session
		@session_start();

		return (isset($_SESSION['accessLevel']) && $_SESSION['accessLevel'] >= $accessLevel);
	}

	/**
	* returns true if the user has access to the specified page
	*/
	function hasAccess($page) {

		if(class_exists('Site')) $acl = Common::get_acl_level($page);	
		else $acl = get_acl_level($page);
			
		switch ($acl) {
		case 'None':
			$level = 100; //This level is unobtainable
			break;
		case 'Basic':
			$level = CLEARANCE_BASIC;
			break;
		case 'Mgr':
			$level = CLEARANCE_MANAGER;
			break;
		default:
			$level = CLEARANCE_ADMINISTRATOR;
			break;
		}
		return ($this->hasClearance($level));
	}

	/* This function returns true if the system is configured to use
	*	LDAP for authentication
	*/
	function usingLDAP() {
		require("table_names.inc");
		list($qh, $num) = dbQuery("SELECT useLDAP FROM $CONFIG_TABLE WHERE config_set_id='1'");
		$data = dbResult($qh);
		return $data['useLDAP'] == 1;
	}

	/* 
	* This function returns true if the system is configured to fallback to local authentication should LDAP authentication fail
	* DB Field should perhaps be renamed to LOCALFallback
	*/
	function usingFallback(){
		require( "table_names.inc" );
		list( $qh, $num ) = dbQuery( "SELECT BIN(LDAPFallback) FROM $CONFIG_TABLE WHERE config_set_id='1'" );
		$data = dbResult( $qh );
		return $data['BIN(LDAPFallback)'] == 1;
	}

	function ldapLogin($username, $password) {
		require("table_names.inc");
		require("database_credentials.inc");

		//require("debuglog.php");
		//$debug = new logfile();

		//get the connection settings from the database
		list($qh, $num) = dbQuery("SELECT LDAPScheme, LDAPHost, LDAPPort, LDAPBaseDN, " .
						"LDAPUsernameAttribute, LDAPSearchScope, LDAPFilter, LDAPProtocolVersion, " .
						"LDAPBindByUser, LDAPBindUsername, LDAPBindPassword, LDAPReferrals " .
						"FROM $CONFIG_TABLE WHERE config_set_id='1'");
		$data = dbResult($qh);

		//build up connection string
		$connectionString = $data['LDAPScheme'] . "://" . $data['LDAPHost'] . ":" . $data['LDAPPort'];

		//$debug->write("connectionString = $connectionString\n");

		//connect to server
		//echo "connecting to server: $connectionString <p>";
		if (!($connection = @ldap_connect($connectionString))) {
			$this->ldapErrorCode = LDAP_CONNECTION_FAILED;
			$this->ldapErrorText = "Failed to connect to ldap server at $connectionString";
			return false;
		}

		//attempt to set the protocol version to use
		@ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, $data["LDAPProtocolVersion"]);

		// bind to server by user
		if ($data['LDAPBindByUser'] == 1) {
			if (empty($data["LDAPBindUsername"])) {
				//bind using user supplied info, if there is no BindUsername in the config
				$credentials=$data['LDAPUsernameAttribute'] . "=" . $username . "," . $data['LDAPBaseDN'];

				if (!($bind = @ldap_bind($connection, $credentials, $password))) {
					$this->ldapErrorCode = LDAP_SERVER_ERROR;
					$this->ldapServerErrorCode = ldap_errno($connection);
					$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
					return false;
				}
			} else {
				//bind to server with config provided username and password
				if (!($bind = @ldap_bind($connection, $data["LDAPBindUsername"], $data["LDAPBindPassword"]))) {
					$this->ldapErrorCode = LDAP_SERVER_ERROR;
					$this->ldapServerErrorCode = ldap_errno($connection);
					$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
					return false;
				}
			}
		} else {
			//bind to server (anonymously)
			if (!($bind = @ldap_bind($connection))) {
				$this->ldapErrorCode = LDAP_SERVER_ERROR;
				$this->ldapServerErrorCode = ldap_errno($connection);
				$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
				return false;
			}
		}

		//attempt to set the protocol version to use
		@ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, $data["LDAPProtocolVersion"]);

		//build up the filter by adding the username filter
		$filter = $data['LDAPUsernameAttribute'] . "=" . $username;
		if ($data['LDAPFilter'] != "") {
			//does it start with a '(' and end with a ')' ?
			$userFilter = $data["LDAPFilter"];
			$length = strlen($userFilter);
			if ($userFilter{0} == "(" && $userFilter{$length-1} == ")")
				$userFilter = substr($userFilter, 1, $length-2);

			$filter = "(&(" . $userFilter . ")(" . $filter . "))";
		}

		// Always avoid referals if flag is not set (they are enabled by default in php ldap module)
		if ( $data["LDAPReferrals"] != 1) {
			@ldap_set_option( $connection, LDAP_OPT_REFERRALS, 0 );
		}

		if ($data["LDAPSearchScope"] == "base") {
			//search the directory returning records in the base dn
			//echo "<p>searching base dn: $data[LDAPBaseDN]        with filter: $filter <p>";
			//$debug->write("searching base ".$data['LDAPBaseDN']." for $filter\n");
			if (!($search = @ldap_read($connection, $data['LDAPBaseDN'], $filter))) {
				$this->ldapErrorCode = LDAP_SERVER_ERROR;
				$this->ldapServerErrorCode = ldap_errno($connection);
				$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
				return false;
			}
		} else if ($data["LDAPSearchScope"] == "one") {
			//search the directory returning records in the base dn
			//echo "<p>searching base dn: $data[LDAPBaseDN]        with filter: $filter <p>";
			//$debug->write("searching one ".$data['LDAPBaseDN']." for $filter\n");
			if (!($search = @ldap_list($connection, $data['LDAPBaseDN'], $filter))) {
				$this->ldapErrorCode = LDAP_SERVER_ERROR;
				$this->ldapServerErrorCode = ldap_errno($connection);
				$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
				return false;
			}
		} else { //full subtree search
			//search the directory returning records in the base dn
			//echo "<p>searching base dn: $data[LDAPBaseDN]        with filter: $filter <p>";
			//$debug->write("searching sub ".$data['LDAPBaseDN']." for $filter\n");
			if (!($search = @ldap_search($connection, $data['LDAPBaseDN'], $filter))) {
				$this->ldapErrorCode = LDAP_SERVER_ERROR;
				$this->ldapServerErrorCode = ldap_errno($connection);
				$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
				return false;
			}
		}

		//get the results
		$numberOfEntries = ldap_count_entries($connection,$search);

		if ($numberOfEntries == 0) {
			$this->ldapErrorCode = LDAP_USER_NOT_FOUND;
			$this->ldapErrorText = "The user was not found in the LDAP database";
			return false;
		}

		//there must be 1 and only 1 result
		if ($numberOfEntries > 1) {
			$this->ldapErrorCode = LDAP_MULTIPLE_ENTRIES_RETURNED;
			$this->ldapErrorText = "Multiple entries were returned for that username";
			return false;
		}

		//get the entry
		$entry = ldap_first_entry($connection, $search);

		//get the entries dn
		$entryDN = ldap_get_dn($connection, $entry);
		//print "<p>The entry was found and its DN is '" . $entryDN . "'</p>";

		//now try a bind with this dn and the password
		if (!($userBind = @ldap_bind($connection, $entryDN, $password))) {
			$this->ldapErrorCode = LDAP_SERVER_ERROR;
			$this->ldapServerErrorCode = ldap_errno($connection);
			$this->ldapServerErrorText = "LDAP: " . ldap_error($connection);
			return false;
		}

		//get the attributes for this entry
		$attributes = ldap_get_attributes($connection, $entry);

		//get some info from the first entry to update into the db
		$lastName = $attributes['sn'][0];
		if (!isset($attributes['givenName'])) {
			if (isset($attributes['cn'])) {
				$spacePos = strpos($attributes['cn'][0], " ");
				if (!($spacePos === false))
					$firstName = substr($attributes['cn'][0], 0, $spacePos);
				else
					$firstName = $attributes['cn'][0];
			} else
				$firstName = $lastName;
		} else
			$firstName = $attributes['givenName'][0];

		$emailAddress = isset($attributes['mail']) ? $attributes['mail'][0]: "";

		//does the user exist in the db?
		if (!$this->userExists($username)) {
			//create the user
			if ($this->usingFallback()) //if we're using Fallback, then we want to put the password in the database
				$pwdstr = "$DATABASE_PASSWORD_FUNCTION('$password')";
			else 
				$pwdstr = "";
			dbquery("INSERT INTO $USER_TABLE (username, level, password, first_name, last_name, " .
						"email_address, time_stamp, status) " .
						"VALUES ('$username',1,$pwdstr,'$firstName',".
						"'$lastName','$emailAddress',0,'ACTIVE')");
			dbquery("INSERT INTO $ASSIGNMENTS_TABLE VALUES (1,'$username', 1)"); // add default project.
			dbquery("INSERT INTO $TASK_ASSIGNMENTS_TABLE VALUES (1,'$username', 1)"); // add default task
		} else {
			//get the existing user details
			list($qh, $num) = dbQuery("SELECT first_name, last_name, email_address, status " .
							"FROM $USER_TABLE WHERE username='$username'");
			$existingUserDetails = dbResult($qh);

			if($existingUserDetails['status'] == 'INACTIVE') {
				$this->errorCode = AUTH_FAILED_INACTIVE; 
				$this->errorText = "That account is marked INACTIVE";
				return false;
			}
			
			//use existing ones if needs be
			if ($firstName == "")
				$firstName = $existingUserDetails['first_name'];
			if ($lastName == "")
				$lastName = $existingUserDetails['last_name'];
			if ($emailAddress == "")
				$emailAddress = $existingUserDetails['email_address'];

			//update the users details
			if ($this->usingFallback()) //if we're using Fallback, then we want to store the current password in the database
				$pwdstr = "$DATABASE_PASSWORD_FUNCTION('$password')";
			else 
				$pwdstr = "''";
			dbquery("UPDATE $USER_TABLE SET first_name='$firstName', last_name='$lastName', ".
								"email_address='$emailAddress', password=$pwdstr ".
								"WHERE username='$username'");
		}

		//login succeeded, returning true
		return true;
	}

	/**
	* returns true if there is a record under that username in the database
	*/
	function userExists($username) {
		require("table_names.inc");

		//check whether the user exists
		list($qh,$num) = dbQuery("SELECT username FROM $USER_TABLE WHERE username='$username'");

		//if there is a match
		return ($data = dbResult($qh));
	}

	/**
	* returns a string with the reason the login failed
	*/
	function getErrorMessage() {
		if ($this->errorCode != AUTH_FAILED_LDAP_LOGIN)
			return $this->errorText;

		if ($this->ldapErrorCode != LDAP_SERVER_ERROR)
			return $this->ldapErrorText;

		return $this->ldapServerErrorText;
	}
}

//create the instance so its availiable by just including this file
$authenticationManager = new AuthenticationManager();

// vim:ai:ts=4:sw=4
?>
