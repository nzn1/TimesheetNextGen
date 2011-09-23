<?php

class gbl{

	private static $todayDateTime;
	private static $todayDate;
	private static $year;
	private static $month;
	private static $day;
	private static $mode;
	private static $proj_id;
	private static $task_id;
	private static $client_id;
	private static $uid;
	private static $post;
	private static $contextUser;
	private static $loggedInUser;	
	private static $breakRatio = 0;
	private static $siteClosed = false;
	
	public static function initialize(){
		//get todays values
		self::$todayDateTime = getdate(time());
		self::$todayDate = getdate(mktime(0, 0, 0,self::$todayDateTime['mon'],self::$todayDateTime['mday'], self::$todayDateTime['year']));
		
		//load local vars from request/post/get
		self::$year = isset($_REQUEST["year"]) ? $_REQUEST["year"]: self::$todayDate["year"];
		self::$month = isset($_REQUEST["month"]) ? $_REQUEST["month"]: self::$todayDate["mon"];
		self::$day = isset($_REQUEST["day"]) ? $_REQUEST["day"]: self::$todayDate["mday"];
		self::$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"]: 0;
		self::$proj_id = isset($_REQUEST["proj_id"]) ? $_REQUEST["proj_id"]: 0;
		self::$task_id = isset($_REQUEST["task_id"]) ? $_REQUEST["task_id"]: 0;
		self::$client_id = isset($_REQUEST["client_id"]) ? $_REQUEST["client_id"]: 0;		
		self::$uid = isset($_REQUEST["uid"]) ? $_REQUEST["uid"]: "";	
		
	if(isset($_REQUEST['date1'])){
//if the date1 variable exists, which comes from the java date picker

		/* @TODO - add validation to this!!!*/
		$d = explode('-',$_REQUEST['date1']);

		gbl::setYear(intval($d[2])); // not sure what the purpose of this is
		self::$year = intval($d[2]);
		self::$month = intval($d[1]);
		self::$day = intval($d[0]);

	}    
		
		if (isset($_SESSION['contextUser']))
			self::$contextUser = strtolower($_SESSION['contextUser']);
		if (isset($_SESSION['loggedInUser']))
		self::$loggedInUser = strtolower($_SESSION['loggedInUser']);
		
		//check that project id is valid
    if (self::$proj_id == 0)self::$task_id = 0;
    
    self::$post="year=".self::$year."&amp;month=".self::$month."&amp;day=".self::$day."&amp;proj_id=".self::$proj_id."&amp;task_id=".self::$task_id."&amp;client_id=".self::$client_id;
		
	}
		
	/**
	 * getNow returns a date object for NOW
	 */   	
	public static function getNow(){
		return self::$todayDateTime;
	}
	/**
	 * Returns a date object for today at 00:00:00 hours
	 */   	
	public static function getTodayDate(){
		return self::$todayDate;
	}
	public static function getTodayDateStamp(){
		return self::$todayDate[0];
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
	public static function setDay($i){
		self::$day = $i;
	}
	public static function getProjId(){
		return self::$proj_id;
	}
	public static function setProjId($id){
    self::$proj_id = $id;
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
	public static function setClientId($id){
    self::$client_id = $id;
  }
	public static function getUId(){
		return self::$uid;
	}
	public static function getPost(){
    	return self::$post;
  	}
  	public static function setPost($s){
    	self::$post = $s;
  	}
	public static function getContextUser(){
		return self::$contextUser;
	}
	public static function setContextUser($s){
		self::$contextUser = $s;
	}
	public static function getLoggedInUser(){
		return self::$loggedInUser;
	}
	public static function getBreakRatio(){
    return self::$breakRatio;
  	}
  	public static function setBreakRatio($s){
    	self::$breakRatio = $s;
  	}
	public static function getSiteClosed(){
    	return self::$siteClosed;
  	}
  	public static function setSiteClosed($s){
    	self::$siteClosed = $s;
  }
  
}
?>
