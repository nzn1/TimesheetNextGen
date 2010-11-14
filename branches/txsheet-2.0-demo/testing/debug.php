
<?php 
ppr(Config::getRelativeRoot(),'relative root');
ppr(Config::getAbsoluteRoot(),'absolute root');
ppr(Config::getDocumentRoot(),'document root');
ppr($_SERVER['REQUEST_URI'],'request_uri');
?>