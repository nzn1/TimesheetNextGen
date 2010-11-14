<?php
PageElements::setHead("<title>".Config::getMainTitle()." - Blank</title>");

?>

<h1>Hello!</h1>

<p>Welcome to the Object Oriented Timesheet Demo.</p>
<p>If you are seeing this page then odds are everything is configured correctly.</p>

<h3>URL's</h3>
<p>Your url probably currently looks like one of these:<br />
localhost<br />
localhost/mytxsheet/<br />
localhost/mytxsheet/index<br />

<?php 
echo "Your actual url:".$_SERVER['REQUEST_URI']."<br />";
?>
</p>
<h3>Just to Check we are working Correctly...</h3>
<p>Just to make sure everything is setup these pages should also be accessible:</p>

	<ul>
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/terms">Terms &amp; Conditions</a></li> 
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/privacy">Privacy</a></li> 
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/sitemap">Site Map</a></li>
		<li><a href="<?php echo Config::getRelativeRoot(); ?>/contact">Contact</a></li>
	</ul>

<h3>Lets get Cracking</h3>

<p>At the moment any page you view with a black header and footer no matter what the url is, being redirected to index.php</p>
<p>The htaccess rule defines this behaviour.</p>

<p>Using the menu at the top of this page if you visit /monthly.php you see the old version of that page.  If you visit /monthly you will see the new version.</p>
<p>The difference is that apache can resolve /timesheet/monthly.php to a physical file and therefore runs that file.   
Whereas it cannot resolve timesheet/monthly itself so it goes through index.php which finds the file and does some clever stuff.</p>

<h3>What is the point of this?</h3>
<p>Although it doesn't just yet, it will reduce the amount of code in each page such as monthly.php<br />
It will also enable users to add their own informational pages such as the examples above extremely easily.<br />
This change allows a restructuring of the code to use more Object Orientated methods that promote code reuse.<br />
It will also make the future updating of the site easier to accomplish</p>

<h3>Updating the site to this method whilst not breaking anything</h3>
<p>I have written this update in such a way that allows a new version and also the old version to co-exist whilst each page is slowly changed over to the new system<br />
If you visit <a href="<?php echo Config::getRelativeRoot();?>/monthly">Timesheet Monthly (new)</a> (if logged in) you will find that any of the links on that page e.g. the daily link
will take you to the original version of daily without the surrounding black header and footer</p>

<h3>What next?</h3>
<p>The next step is to move the timesheet menu and the timesheet header into the template</p>
<p>Then remove the remaining html errors on the monthly page</p>

<h3>Have a look:</h3>
<p>Have a look at the source code for this page in your browser (right click, view source), then have a look at the php file for this particular page (it is in content/index.php)<br />
See how clean and tidy it is compared to the browser source code.  This is becuase the template adds a level of abstraction which makes coding a new page easier and means there is less to worry about.<br />
It also means that If you want to change the general layout all the pages are built from one template allowing changes to be propagated quickly and easily</p>

<h3>Other useful features to know about</h3>
<p>The template for any given page can be changed from the default (i.e. for stopwatch.php) to a different one with oneline of code in the stopwatch.php file<br />
Debug output functionality for printing out session variables, sql errors, sql statements is available aswell.  Take a look at debug.class.php for the various options that are currently in there<br />
Note that many won't do anything as they were used on an old site, however enabling the get, post, and session variable outputs would be a worthwhile test.</p>

<h3>Finally - Feedback</h3>

<p>I would really appreciate your opinions on the proposed changes either on the forum or directly to mark_|at|_rwrightson_|dot|_f9_|d0t|_co_|d0t|_uk</p>