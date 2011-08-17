<?php
//if(Auth::ACCESS_GRANTED != $this->requestPageAuth('level'))return;
echo 'If you can see this page, then the Allow Override Directive may not be set correctly in your apache configuration.<br />';

echo 'If the htaccess file is being read, a server 500 error will occur, thus confirming the htaccess files are being read.<br />';

echo 'In this case, your htaccess file was not read as you are reading this message.';


?>