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
	private static $contextUser;
	
	
	
	public static function initialize(){
		//get todays values
		self::$realToday = getdate(time());
		
		//load local vars from superglobals
		self::$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: self::$realToday["year"];
		self::$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: self::$realToday["mon"];
		self::$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: self::$realToday["mday"];

	if(isset($_GET['date1'])){
//if the date variable exists

		/* @TODO - add validation to this!!!*/
$d = explode('-',$_GET['date1']);

	gbl::setYear($d[2]);
	switch($d[1]){
		case 'Jan':
			self::$month = 1;
			break;
		case 'Feb':
			self::$month = 2;
			break;
		case 'Mar':
			self::$month = 3;
			break;
		case 'Apr':
			self::$month = 4;
			break;
		case 'May':
			self::$month = 5;
			break;
		case 'Jun':
			self::$month = 6;
			break;
		case 'Jul':
			self::$month = 7;
			break;
		case 'Aug':
			self::$month = 8;
			break;
		case 'Sep':
			self::$month = 9;
			break;
		case 'Oct':
			self::$month = 10;
			break;
		case 'Nov':
			self::$month = 11;
			break;
		case 'Dec':
			self::$month = 12;
			break;
		default:
			break;
			
	}

	
}
		
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
	public static function setYear($i){
		self::$year = $i;
	}	
	public static function getMonth(){
		return self::$month;
	}
	public static function setMonth($i){
		self::$month = $i;
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
	public static function getContextUser(){
		return self::$contextUser;
	}
	public static function setContextUser($s){
		self::$contextUser = $s;
	}
}
?>
