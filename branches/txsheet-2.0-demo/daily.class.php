<?php
class DailyClass{
	public function __construct(){}
	
public function make_daily_link($ymdStr, $proj_id, $string) {
	echo "<a href=\"daily.php?" .  $ymdStr .  "&amp;proj_id=$proj_id\"><i>" . 
		$string .  "</i></a>"; 
}

public function open_cell_middle_td() {
	echo "<td class=\"calendar_cell_middle\" align=\"right\" nowrap>";
}
	
}