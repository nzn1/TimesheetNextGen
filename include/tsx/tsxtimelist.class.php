<?php

require_once('tsxtime.class.php');
class TsxTimeList{

  private $times;

  public function __construct(){
  
  }

  public function search($startStr, $endStr){
    list($num, $qh) = Common::get_time_records($startStr, $endStr, gbl::getContextUser(), gbl::getProjId(), gbl::getClientId());
    //ppr($num);
    //ppr($qh);
    
    while($obj = mysql_fetch_object($qh))$this->times[] = new TsxTime($obj);
  
    //ppr($data);
  }

  public function getTimes(){
    return $this->times;
  }
  public function getTime($i){
    return $this->times[$i];
  }
  public function getLength(){
    return count($this->times);
  }
  

}

?>