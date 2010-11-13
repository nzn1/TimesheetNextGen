{doctype}
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="language" content="en" />
<meta name="robots" content="index, follow" />
<meta name="audience" content="all" />
<meta name="distribution" content="global" />
<meta name="revisit-after" content="5 days" />
<meta name="author" content="Mark Wrightson" />
<meta name="copyright" content="2010 Mark Wrightson, All rights reserved." />

<link rel="shortcut icon"
	href="<?php echo Config::getRelativeRoot();?>/favicon.png" />

<link rel="stylesheet"
	href="<?php echo Config::getRelativeRoot();?>/themes/txsheet/styles/main.css"
	type="text/css" />
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
{debugInfoTop}
<div id="wrap" style="">
<div class="headerwrap" style="background:#222;">
	<h1 style="color:#fff;">TimesheetNG 2.0x</h1>
	


<div id="header_right">
	<p style="color:#fff;">Usage: your table names must be defined in: table_names.inc and include/tables.class.php<br />
	Only the monthly page has been ported so far.<br />
	the url for the new version is /monthly<br />
	the url for the old version is /monthly.php<br />
	</p>
</div>

<div class="clearall"></div>
{menu}


</div>
<!--end headerwrap--> {response}

            <div class="content">
              {content}
            </div><!--end content-->  


<div class="clearall"></div>


<div id="footer">
<div class="footer_content">
<div class="col20">
	<ul>
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/terms">Terms &amp; Conditions</a></li> 
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/privacy">Privacy</a></li> 
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/sitemap">Site Map</a></li>
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/contact">Contact</a></li>
	</ul>
</div>

</div><!-- end footer content -->
<div class="clearall"></div>
<div class="footer_left">
<p>Copyright &copy; 2010 Mark Wrightson. All rights reserved</p>

</div>
<div class="footer_right">
<p><a href="http://www.voltnet.co.uk">VoltNet Web Design Services</a></p>
</div>
</div>
<!--end footer--> {debugInfoBottom}</div>
<!--end wrap-->


<?php /*</body></html> are in index.php & are the last two lines parsed in the webapp*/?>