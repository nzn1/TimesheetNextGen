<?php
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;
define('JPATH_BASE', dirname(__FILE__));
// Load the loader class.
if (!class_exists('JLoader')) {
        require_once 'include/loader.php';
}

//
// library imports.
//

// Base classes.
jimport('jclasses.object');


// Factory class and methods.
jimport('jclasses.factory');

// Register class that don't follow one file per class naming conventions.
//JLoader::register('JObject', dirname(__FILE__).DS.'test-classes'.DS.'object.php');
//JLoader::register('JFactory', dirname(__FILE__).DS.'test-classes'.DS.'factory.php');
//JLoader::register('JLanguage', dirname(__FILE__).DS.'test-classes'.DS.'language.php');
//JLoader::register('JXMLElement', dirname(__FILE__).DS.'test-classes'.DS.'xmlelement.php');

JLoader::register('JText', dirname(__FILE__).DS.'include'.DS.'jclasses'.DS.'methods.php');
JLoader::register('JPath', dirname(__FILE__).DS.'include'.DS.'jclasses'.DS.'filesystem.php');
JLoader::register('JFolder', dirname(__FILE__).DS.'include'.DS.'jclasses'.DS.'filesystem.php');
JLoader::register('JFile', dirname(__FILE__).DS.'include'.DS.'jclasses'.DS.'filesystem.php');

//jimport('test-classes.language');
ppr(JLoader::getClasses());

$lang = JFactory::getLanguage();
ppr($lang);

echo JText::_('JLIB_ERROR_INFINITE_LOOP')."\n";
echo JText::sprintf('JERROR_TABLE_BIND_FAILED',"These are a couple of JText tests")."\n";
?>
