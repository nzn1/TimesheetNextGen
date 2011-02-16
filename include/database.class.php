<?
/**
 * *****************************************************************************
 * Name:                    database.class.php
 * Recommended Location:    /include
 * Last Updated:            July 2010
 * Author:                  Mark Wrightson
 * Contact:                 mark@voltnet.co.uk
 *
 * Description:
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 *
 * Copyright:
 * This script may not be used by any other person without the express
 * permission of the author.
 ******************************************************************************/


/**
 * HOW TO IMPORT A DATABASE USING MYSQL CLI.
 *
 * mysql -u root -p -f  mark3290_uybb < sql.sql
 *               		
 * NOTE: If the .sql file contains multiple databases it doesn't matter
 * that you have specified a db already.  The initial specification of an
 * existing db is necessary however!
 */
class MySQLDB
{

	/**
	 * The MySQL database connection
	 */
	private $connection;
	
	private $isConnected = false;
	/**
	 * Class constructor
	 */
	function __construct(){
		if(!function_exists('mysql_connect')){
			ErrorHandler::fatalError("The MySQL module hasn't been loaded correctly");
		}		
		
	}

	
	const ERROR_CONNECT = 100;
	const ERROR_SELECT_DB = 101;
	    
	/**
     *
     * On Windows Vista or above, an entry in the Windows/System32/drivers/etc/hosts 
     * file causes mysql_connect() connections to "localhost" to timeout and never connect. 
     * This happens on php 5.3 and above since it now uses mysql native driver which has changed 
     * it connection behavior compared to libmysql.dll in previous versions.  
     * It is not a PHP bug, but definitely a configuration issue for users 
     * on new windows systems.
     *
     * To get around this, you must remove the entry like this:
     * ::1             localhost
     * and make sure you still have:
     * 127.0.0.1       localhost      
     *
     * Also, you could change the code to connect to the ip instead, but that is inconvenient if you have many web sites.
     * This issue occurs on Windows Vista, Windows 7 and Windows Server 2008.                    
     */
	public function connect(){
       
    /* Make connection to database */
		//try{


			$this->connection = @mysql_connect(Config::getDbServer(),Config::getDbUser(), Config::getDbPass());
			if (!$this->connection) throw new Exception('MySQL Connection Database Error: ' . mysql_error(),self::ERROR_CONNECT);
	/*	
	}
		catch (Exception $e){
			if(true == Config::getInstaller()){
				echo "<div class=\"errorbox\">".$e."</div>";
				return;
			}
			else{
				$this->dbError($e);
				exit();
			}
		}
		*/

		//try{
			$result = mysql_select_db(Config::getDbName(), $this->connection);
			if (!$result) throw new Exception('MySQL Connection Database Error: ' . mysql_error(),self::ERROR_SELECT_DB);
		/*}
		catch (Exception $e){
			if(true == Config::getInstaller()){
				echo "<div class=\"errorbox\">".$e."</div>";
				return;
			}
			else{
				$this->dbError($e);
				exit();
			}
		}*/
		$this->isConnected = true;


	}

	/**
	 * Performs the given query on the database and
	 * returns the result, which may be false, true or a
	 * resource identifier.
	 *
	 * @param $q - sql query
	 */
	function query($q){
		if(!$this->isConnected){
			trigger_error('Database Not Connected');
			return false;
		}
		return mysql_query($q, $this->connection);
	}

	/**
	 *
	 * Retrieve the Response code data from the
	 * response database table
	 * @param $id - reponse uid
	 */
	function getResponse($id){
		if($id == ''){
			$response = "Oops! - an unknown Response Code has been Requested.";
			return $response;
		}
		if(isset($_GET['track'])&& $_GET['track']==0) return;
			
		$q = "SELECT response FROM `".tbl::getResponse()."` WHERE `id` = $id LIMIT 0 , 1";
		if (debug::getSqlStatement()==1)echo "<pre>".$q."</pre>";
		$result = Site::getDatabase()->query($q, $this->connection);
		$num_rows = mysql_numrows($result);
		if(!$result || ($num_rows <= 0)){
			if(debug::getSqlError())Debug::ppr(mysql_error(),'sqlError');
			$response = "Oops! - an unknown Response Code has been Requested.";
			return $response;
		}

		$response  = nl2br(mysql_result($result,0,"response"));
		return $response;
	}


	const TYPE_OBJECT = 1;
	const TYPE_ARRAY = 2;
	
	const SQL_ERROR = 0;
	const SQL_EMPTY = -1;
	
	/**
	 * The sql function
	 *
	 * @param $q - sql query string
	 * @param $showInfo - boolean
	 * @param $type - either MySQLDB::TYPE_OBJECT or MySQLDB::TYPE_ARRAY
	 */

	public function sql($q,$showInfo=true,$type=self::TYPE_OBJECT,$neverShowErrors=false){
		if($q==''){
			trigger_error("The SQL Statement is blank",E_USER_WARNING);
			return self::SQL_ERROR;
		}
		$trace = debug_backtrace();		
		if (isset($trace[1]) && isset($trace[0])){
			$t = array(
		        'file'=>$trace[0]['file'],
		        'line'=>$trace[0]['line'],
				'function'=>$trace[1]['function'], 
				'class'=>$trace[1]['class']      
				);
		}
		if(debug::getSqlStatement()==1)Debug::ppr($q,'SQL',$t);
		$result = $this->query($q);
		/* Error occurred, return given name by default */
		if($result == false){
			if(!$neverShowErrors && debug::getSqlError())Debug::ppr(mysql_error(),'sqlError');
			if($showInfo)echo "Error displaying info";
			return self::SQL_ERROR;
		}
		$num_rows = mysql_numrows($result);
		//echo "numrows: ".$num_rows;
		if($num_rows == 0){
			if($showInfo)echo "Nothing to Display";
			return self::SQL_EMPTY;
		}
		if($type==self::TYPE_ARRAY){
			while($obj = mysql_fetch_array($result))$data[] = $obj;
		}
		else if($type==self::TYPE_OBJECT){
			while($obj = mysql_fetch_object($result))$data[] = $obj;
		}
		return $data;
	}

	/**
	 *
	 * A database Error has occured. Generate an error page
	 * @param $e - Exception
	 */
	private function dbError($e){
		ErrorHandler::fatalError("An error has occured.<br />".$e->getMessage(),'Database Error','Database Error');
	}
	/**
	 *
	 * get the resource idedntifier for the sql database connection
	 */
	public function getConnection(){
		return $this->connection;
	}

}
?>

