<?php
class Kohana {
	
	function __construct() {
		echo "kohana<BR>";
	}
	public function __call($name, $arguments) {
        // Note: value of $name is case sensitive.
        echo "Calling object method '$name' "
             . implode(', ', $arguments). "\n";
    }
    
    public static function log($one = false, $two = false, $three = false, $four = false) {
    	echo "log: $one, $two, $three, $four<br />";
    	
    }
}