<?php
	/* @var $tp TemplateParser */
	$tp->getPageElements()->addFile('content',Rewrite::getContent());
	
	//$tp->getPageElements()->addFile('menu','themes/'.PageElements::getTheme().'/menu.php');
	$tp->getPageElements()->addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner.inc');
	$tp->getPageElements()->addFile('tsx_footer','themes/'.PageElements::getTheme().'/footer.inc');
	$tp->getPageElements()->setError404('404.php');
  
	$tp->getPageElements()->addElement(new FunctionTag('relativeRoot','relRoot',FunctionTag::TYPE_FUNC));
?>