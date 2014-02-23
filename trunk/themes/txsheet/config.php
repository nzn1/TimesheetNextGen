<?php

	self::addFile('content',Rewrite::getContent());
	
	self::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner.inc');
	self::addFile('tsx_footer','themes/'.PageElements::getTheme().'/footer.inc');
	self::setError404('404.php');
  
	self::addElement(new FunctionTag('relativeRoot','relRoot',FunctionTag::TYPE_FUNC));
?>