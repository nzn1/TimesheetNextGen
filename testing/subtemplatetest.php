<?php 
echo "<hr />";
PageElements::setHead("<title>".Config::getMainTitle()." - Sub Template Test</title>");
		require_once(Config::getDocumentRoot()."/include/templateparser/templateparser.class.php");

		$tp = new templateParser();
		$tp->getPageElements()->setTemplate(config::getDocumentRoot()."/themes/sub1/template.php");
		
		$tp->getPageElements()->add('debugInfoTop');
		$tp->getPageElements()->add('content');
		
		$tp->getPageElements()->add('tag1');
		
		$tp->getPageElements()->getTagByName('tag1')->setOutput('substituted tag1');
			
		// parse template file
		$tp->parseTemplate();

		// display generated page
		echo $tp->display();
		?>