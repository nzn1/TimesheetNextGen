<?php

if(!class_exists('Site'))die('Restricted Access');
class DailyClass{
	public function __construct(){}
	
public function make_daily_link($ymdStr, $projId, $string) {
	echo "<a href=\"".Config::getRelativeRoot()."/daily?" .  $ymdStr .  "&amp;proj_id=$projId\"><i>" . 
		$string .  "</i></a>"; 
}

public function open_cell_middle_td() {

	echo "<td class=\"alignmiddle\">";

}
	
}