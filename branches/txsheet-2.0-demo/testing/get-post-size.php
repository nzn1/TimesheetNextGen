<?php
if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;

echo "The max post size is ".Common::get_post_max_size()." bytes<br />\n";

if(Common::get_post_max_size() < 32768) { 
	echo "The max_post_size, set in the php.ini file, is set to less than 32 Kilobytes. The Timesheet Next Generation system can not function properly with this small a post size. It is required this be increased to at least 32 K, and recommended this be increased to 64 K. Please have a system administrator locate the appropriate php.ini file on your system and make this required change.";
}

if(Common::get_post_max_size() < 65536) { 
	echo "The max_post_size, set in the php.ini file, is set to less than 64 Kilobytes. The minimum allowed size for Timesheet Next Generation is no less than 32K, and it is recommended this be increased to at least 64 K. Please have a system administrator locate the appropriate php.ini file on your system and make this recommended change.";
}

//echo "Test 32M ".Common::parse_size('32M')." bytes<br />\n";
//echo "Test 32Meg ".Common::parse_size('32Meg')." bytes<br />\n";
//echo "Test 32Kb ".Common::parse_size('32Kb')." bytes<br />\n";
//echo "Test 32 G ".Common::parse_size('32 G')." bytes<br />\n";
//echo "Test 32 Mybpez ".Common::parse_size('32 Mybpez')." bytes<br />\n";

?>
