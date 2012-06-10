<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  
  <link rel="stylesheet" href="<?php echo PageElements::getRelThemePath();?>/styles/popup.css" type="text/css" />
  
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/core.js" ></script>

{head}
<!--This is an IE hack that allows absolute positioning inside a relative div-->
<!--[if IE]>
  <style type="text/css">
    .layout {
    	height: 0;
    	he\ight: auto;
    	zoom: 1;
    }
  </style>
<![endif]-->

<style type="text/css">
  html{ overflow-y:Scroll;}
</style>
</head>

<body {onload}>
  {debugInfoTop}
  {templateParserDebug}
  <div align="center">
    <div id="wrap">       
    <div class="popup_content">
      
      {response}
      {content}
    </div><!--end content-->  
    

      
    </div>
    <!--end wrap-->
  </div>
  <!--end center-->
<?
//tags in index.php instead
/*
</body>
</html>
*/
?>