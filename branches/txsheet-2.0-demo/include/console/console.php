<?php 
if(!debug::getSendToConsole())return;

$s = "<script type=\"text/javascript\" src=\"".Config::getRelativeRoot()."/include/console/console.js\"></script>"
."<link rel=\"stylesheet\" href=\"".Config::getRelativeRoot()."/include/console/console.css\" type=\"text/css\" />";
PageElements::appendHead($s);

?>

<div style="margin:20px;">&nbsp;</div>
<div id="phpconsole">
	<div class="menu">
		<a href="javascript:consoleOpen()">Console Open</a>
		<a href="javascript:consoleClose()">Console Close</a>
	</div>
	<div class="data">
	<?php 

	$d = Debug::getConsoleData();
	if(empty($d)){
		Debug::log('No Debug Data','Debug');
	}
	foreach (Debug::getConsoleData() as $item){
		/* @var $item ConsoleItem */
		 
		echo "<span class=\"item\" onclick=\"javascript:highlight(this)\">"
				."<span class=\"trace\">"
					."[Line: ".$item->getTrace()->line."] "
					."[File: ".$item->getTrace()->file."] "
					."[Class: ".$item->getTrace()->class."] "
				."</span>"
				."<span class=\"group\">"
					."[Group: ".$item->getGroup()."] "
				."</span>"; 
		echo"<br />";
		if(is_array($item->getData()) || is_object($item->getData())){
			echo print_r($item->getData(),true);
		}
		else echo $item->getData();
		echo"</span>";
	}	
	?>
	</div>
</div>
