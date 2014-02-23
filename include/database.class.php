<?
/**
 * Description:
 * The Database class is meant to simplify the task of accessing
 * information from the website's database.
 ******************************************************************************/

class Database {

	/**
	 * The MySQL database connection
	 */
	private $connection;

	private $isConnected = false;

	/**
	 *
	 * The result of the last database query.
	 * This could be true, false or a resource identifier
	 * @var unknown_type
	 */
	private $result;

	/**
	 *
	 * The error message of the last database query.
	 * @var unknown_type
	 */
	private $error;

	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	private $numRows;

	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	private $affectRows;

	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	private $insertId;

	const ERROR_CONNECT = 100;
	const ERROR_SELECT_DB = 101;

	/**
	 * Class constructor
	 */
	public function __construct(){
		$this->result = NULL;
	}

	public function connect($server, $user, $pass, $base){

		/* Make connection to database */
		if(!function_exists('mysql_connect')){
			ErrorHandler::fatalError("The MySQL module hasn't been loaded correctly");
		}
		/**
		 * @todo Fix: mysql_connect() [function.mysql-connect]: Headers and client library minor version mismatch. Headers:60000 Library:50151
		 */
		$this->connection = @mysql_connect($server, $user, $pass);
		if (!$this->connection){
			$this->error = mysql_error();
			throw new Exception('MySQL Database Error: ' . $this->error,self::ERROR_CONNECT);
		}

		$result = mysql_select_db($base, $this->connection);
		if (!$result){
			$this->error = mysql_error();
			throw new Exception('MySQL Database Error: ' . $this->error,self::ERROR_SELECT_DB);
		}
		$this->isConnected = true;
	}

	/**
	 * Performs the given query on the database and
	 * returns the result, which may be false, true or a
	 * resource identifier.
	 *
	 * @param $q - sql query
	 */
	public function query($q){
		if(!$this->isConnected){
			//trigger_error('Database Not Connected');
			return false;
		}
		$this->result = mysql_query($q, $this->connection);
		return $this->result;
	}

    /** Convenient method for mysql_fetch_object().
      * @param $result The ressource returned by query(). If NULL, the last result returned by query() will be used.
      * @return An object representing a data row.
      */
    function fetchNextObject($result = NULL)
    {
      if ($result == NULL)
        $result = $this->result;

      if ($result == NULL || mysql_num_rows($result) < 1)
        return NULL;
      else
        return mysql_fetch_object($result);
    }

	/**
	 * Execute the passed query on the database and determine
	 * if insert_id or affected_rows or numrows has to be called.
	 *
	 * @param SQL String
	 *
	 * @return boolean - true for successful query. false for unsuccessful.
	 */
	public function newQuery($q){

		if(!$this->isConnected){
			//trigger_error('Database Not Connected');
			return false;
		}
		$this->result = mysql_query($q, $this->connection);
		$this->error = mysql_error($this->connection);
		$query = strtolower($q);
		if (empty($this->error)){
			if (strpos($query, 'insert') !== false){
				$this->insertId = mysql_insert_id($this->connection);
				$this->numRows = 0;
			} elseif (strpos($query, 'delete') !== false || strpos($query, 'replace') !== false || strpos($query, 'update') !== false){
				$this->affectedRows = mysql_affected_rows($this->connection);
				$this->numRows = 0;
			} else {
				$this->numRows = $this->getNumRows();
				$this->affectedRows = 0;
			}

			if (!empty($this->insertId)) {
				return $this->insertId;
			}
			return true;
		} else {
		 //die($this->error."\nWhile executing query: \n{$query}");
		 return false;
		}
	}

	/**
	 *
	 * Retrieve the Response code data from the
	 * response database table
	 * @param $id - reponse uid
	 */
	public function getResponse($id){
		if($id == ''){
			$response = "Oops! - an unknown Response Code has been Requested.";
			return $response;
		}

		$q = "SELECT response FROM `".tbl::getResponse()."` WHERE `id` = $id LIMIT 0 , 1";

		$data = $this->db->sql($q,false, Database::TYPE_OBJECT);

		if($data == Database::SQL_EMPTY || $data == Database::SQL_ERROR){
			$response = "Oops! - an unknown Response Code has been Requested.";
			return $response;
		}
		return $data[0]->response;
	}


	const TYPE_OBJECT = 1;
	const TYPE_ARRAY = 2;
	const TYPE_ASSOC = 3;

	const SQL_ERROR = 0;
	const SQL_EMPTY = -1;

	/**
	 * The sql function
	 *
	 * @param $q - sql query string
	 * @param $showInfo - boolean
	 * @param $type - either Database::TYPE_OBJECT or Database::TYPE_ARRAY
	 */

	public function sql($q,$showInfo=true,$type=self::TYPE_OBJECT,$neverShowErrors=false){
		if(!$this->isConnected){
			//trigger_error('Database Not Connected');
			return self::SQL_ERROR;
		}

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

		if(debug::getSqlStatement()==1)
			Debug::ppr($q,'SQL',$t);

		$result = $this->query($q);

		/* Error occurred, return given name by default */
		if($result == false){
			if(!$neverShowErrors && debug::getSqlError())
				Debug::ppr(mysql_error(),'sqlError');
			if($showInfo)
				echo "Error displaying info";
			return self::SQL_ERROR;
		}

		$num_rows = mysql_numrows($result);
		//echo "numrows: ".$num_rows;

		if($num_rows == 0){
			if($showInfo)
				echo "Nothing to Display";
			return self::SQL_EMPTY;
		}

		if($type==self::TYPE_ARRAY){
			while($obj = mysql_fetch_array($result))$data[] = $obj;
		} else if($type==self::TYPE_OBJECT){
			while($obj = mysql_fetch_object($result))$data[] = $obj;
		} else if($type==self::TYPE_ASSOC){
			while($obj = mysql_fetch_assoc($result))$data[] = $obj;
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

	public function getError(){
		return $this->error;
	}

	public function getNumRows(){
		return $this->numRows;
	}

	public function getAffectedRows(){
		return $this->affectRows;
	}

	public function isConnected(){
		return $this->isConnected;
	}
}
?>
