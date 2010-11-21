<?php

class gbl{

	private static $realToday;
	private static $year;
	private static $month;
	private static $day;
	private static $mode;
	private static $proj_id;
	private static $task_id;
	private static $client_id;
	private static $post;
	
	
	
	public static function initialize(){
		//get todays values
		self::$realToday = getdate(time());
		
		//load local vars from superglobals
		self::$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: self::$realToday["year"];
		self::$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: self::$realToday["mon"];
		self::$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: self::$realToday["mday"];
		self::$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"]: 0;
		self::$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
		self::$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
		self::$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;
		
		
	}
	
	
	public static function getRealToday(){
		return self::$realToday;
	}
	public static function getYear(){
		return self::$year;
	}	
	public static function getMonth(){
		return self::$month;
	}
	public static function getDay(){
		return self::$day;
	}
	public static function getProjId(){
		return self::$proj_id;
	}
	public static function getTaskId(){
		return self::$task_id;
	}
	public static function getMode(){
		return self::$mode;
	}
	public static function setTaskId($id){
		self::$task_id = $id;
	}
	public static function getClientId(){
		return self::$client_id;
	}
}
?>
