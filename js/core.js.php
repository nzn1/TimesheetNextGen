/**
 *
 * This is the core javascript code required to run the website
 * This code will be listed at the top of every template file
 * to ensure that it is run first. 
 *
 */    

/**
 * a method of including a javascript file within a js script
 * @param jsFile
 */
function includeJavaScript(jsFile) {
	document.write('<script type="text/javascript" src="' + jsFile
			+ '"></script>');
}

cfg = new Object();
cfg.relativeRoot = '<?php echo Config::getRelativeRoot();?>';
cfg.requestUri = '<?php echo urlencode($_SERVER['REQUEST_URI']);?>';


includeJavaScript(cfg.relativeRoot+"/js/lang.js");


<?php
exit();
?>

