<?php
if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open')) return;

PageElements::addFile('tsx_banner','themes/'.PageElements::getTheme().'/banner-no-menu.inc');

echo JText::_('MAX_POST_SIZE_TOO_SMALL');

// vim:ai:ts=4:sw=4
?>
