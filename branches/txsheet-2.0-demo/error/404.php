<?php
PageElements::setPageAuth('content');

PageElements::setHead("<title>".Config::getMainTitle()."</title>");

header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
?>

<div class="col50">
<div class="pad5">
<h1><strong>Error 404</strong></h1>
<p><img style="width: 50px; vertical-align: middle;"
	src="<?php echo Config::getRelativeRoot();?>/images/info.png" alt="" />&nbsp;&nbsp;We
are sorry but the requested URL is no longer available</p>
<p><strong>Most likely causes:</strong></p>
<ul>
	<li>There might be a typing error in the address.</li>
	<li>If you clicked on a link, it may be out of date.</li>
</ul>


<p><strong>What you can try:</strong></p>
<ul>
	<li>Please retype the address</li>
	<li>Use the above menus to find the page you were looking for</li>
	<li><a href="<?php echo Config::getRelativeRoot();?>/">Click here to
	visit the home page</a></li>
</ul>
<small> This error (HTTP 404 Not Found) means that your browser was able
to connect to the website, but the page you wanted was not found. It's
possible that the webpage is temporarily unavailable. Alternatively, the
website might have changed or removed the webpage. </small></div>
<!-- Close pad5 --></div>
<!--end col50-->
<div class="col50 right"><img class="big-img"
	src="<?php echo Config::getRelativeRoot();?>/images/notes.jpg"
	alt="" /></div>
<!--end col50 right-->
