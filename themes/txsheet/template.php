<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="language" content="en" />
<meta name="robots" content="index, follow" />
<meta name="audience" content="all" />
<meta name="distribution" content="global" />
<meta name="revisit-after" content="5 days" />
<meta name="description" content="Timesheet Next Gen" />
<link rel="stylesheet" href="<?php echo Config::getRelativeRoot();?>/css/timesheet.css" type="text/css" />
<link rel="stylesheet" href="<?php echo Config::getRelativeRoot();?>/themes/txsheet/styles/main.css" type="text/css" />
<link rel="shortcut icon" href="<?php echo Config::getRelativeRoot();?>/favicon.ico" />

{head}
<style type="text/css">
html {
	overflow-y: Scroll;
}
</style>

<!--This is an IE hack that allows absolute positioning inside a relative div-->
<!--[if IE 6]>


    <style type="text/css">
      .layout {
      	height: 0;
      	he\ight: auto;
      	zoom: 1;
      }
    </style>
  <![endif]-->
<!--this javascript is to fix an IE 6 PNG alpha transparency renderring bug-->
<!--[if lt IE 7.]>
    <script defer type="text/javascript" src="/uybb/javascript/pngfix.js"></script>
  <![endif]-->
<!--[if lt IE 8.]>
  <style type="text/css">
    #nav-container ul li {
    width:150px;
    font-size:0.7em; 
    margin:0;
    padding:0;
  }
  </style>
  <![endif]-->

</head>
<body{onload}>
<!-- this is the old body tag from body.inc  -->
<!-- <body style="width:100%; height:100%;"link="#004E8A" vlink="#171A42"> -->
{debugInfoTop}
<div id="wrap" style="">
<div class="headerwrap" style="background:#222;">
	<h1 style="color:#fff;">TimesheetNG 1.5.x OO Demo</h1>
<!--	<p style="color:#fff;">The surrounding template can be customised easily in the themes directory!</p>-->
	{tsx_banner}
</div>
<!--end headerwrap--> {response}

            <div class="content">
              
              
              {content}
              
            </div><!--end content-->  


<div class="clearall"></div>


<div id="footer">
{tsx_footer}
</div>
<!--end footer--> {debugInfoBottom}</div>
<!--end wrap-->


<?php /*</body></html> are in index.php & are the last two lines parsed in the webapp*/?>