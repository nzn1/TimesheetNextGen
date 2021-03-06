TimesheetNextGen

Timesheet NG is a free Open Source online time tracking application. Focusing
on ease of use, Timesheet NG allows multiple employees and contractors to
track and log their time spent on multiple projects.

Please see INSTALL.txt for installation instructions.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.5.1 notes:

Added ability to administratively close the site by creating a file named 'siteclosed' in the main context of the site (for instance, where simple.php is located), to reopen, simply delete the file.  Allows administrators to loggin, or continue to be logged in to allow for testing or making adjustments.

Added a favicon, for this to show up, you'll need to edit the headerhtml to include this line: <link rel="shortcut icon" href="images/favicon.ico">, resetting this item to it's default after upgrading will put that line in the config for you, but any modifications you've made to that item will disappear.

Also fixed a "bug" in the bannerhtml configuration item.  If you've not made significant changes to that config item, it is recommended you reset the bannerhtml item to it's default after upgrading.

Several other minor fixes and tweaks.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.5.0 notes:

Semi major database changes have been made with regards to how time data is stored and should be retrieved.  Essentially, if you have added any reports, someone will need to at least look at rewritting those reports.  The original reports will probably appear to work just fine, but they have the potential to report incorrect amounts of time during Daylight Savings Time changes.

The original 'calendar' view has been renamed to 'monthly'.

Previous and Next links have nearly all been removed in favor of the new calendar type navigation.

Context dates should follow the user between user input forms and reports.  Context will be lost when navigating to administrative forms.

Mixed usage of the daily forms and the simple form has the potential to both add time and delete time from a user's entries when time that was originally entered with a daily form is then edited with the simple form when the daily entries span the end of a week.  For instance if a user works from 10pm on Sunday until 6am on Monday, and that single entry is made with the daily form, then the simple form is used to edit the week ending Sunday, the time from Midnight Monday to 6am will be deleted from the user's entries.  If, instead, the week beginning Monday is edited, the 6 hours on Monday will be added to Monday's time EVERY TIME the simple form is saved.  It is a bug, but not one that is easily fixed.

Export to Excel is working, for me, in exactly two reports: report_grid_client_user.php and report_user_summ.php.  It's not hard to replicate that functionality if you want to use it in other forms. If you fix it for the other reports, please submit your work/patches to the project.

The installation and upgrade scripts have been enhanced a little bit, and the web based installation script has been greatly enhanced.

Administrators are now able to change their "contextUser" and make entries or edit entries on behalf of users.

User status field has been changed so users can now become "inactive", instead of just deleting the user and all their time enties.  Inactive users will only show up for managers, and then they will show up in red.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.4.1 notes:

This readme was not kept up-to-date during this iteration.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.3.1 notes:

This readme was not kept up-to-date during this iteration.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>

TimesheetNextGen was derived from Timesheet.php at this point.

Here is the original Readme.txt from the original project:

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
PHP Timesheet Program
(c) 1998,1999 Peter D. Kovacs
(c) 2002 Dominic J. Gamble, Stratlink.

This application is distributed under the terms of the GNU Public License.
See COPYING for more information.
         
	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.2.1 notes:

If you're upgrading from an earlier version please make sure you have no projects with client id '0' and you MUST have a client with id '1' called "No Client". Otherwise there will be javascript errors and none of the droplists will populate.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.1.8 notes:

In some versions of IE, the project select list is squashed to zero size and cannot be seen. This version fixes that.

	 <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.1.7 notes:

The application has been reskinned with css classes throughout. You can now write your own CSS class to customise the look and feel to you company. Specify your css stylesheet link in 'headerhtml' in the configuration section.

Login has also been changed to use sessions. This gets around problems of logging out and logging in as administrator, etc.

	 <<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
Version 1.1.1 notes:

LOGOUT - There were problems with the logout system, so I have reverted back to Peters original code.

TIMEZONES - On international shared servers, the local time for the server may not be the local time for you, so I have added the ability to set your timezone on the configuration page. You can also set your locale.

INSTALL - The install script has been re-written to allow you to install Timesheet.php to an existing database instead of requiring a new database to be set up. On some shared servers, you may not have access to create new databases.

This release contains other small changes and bugfixes.
	 

         <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
	 
Version 1.1 notes:
	 
CONFIGURATION OPTIONS
I've added a configuration page in the admin section. This allows you to
set a number of fields which all modify how each page is shown. This
includes 

-adding stuff to go into the header (ie meta tags)
-adding fields to body tags
-adding a banner at the top of the page so you can display your company
logo and text etc.
-adding fields to table tags (ie so you can set a background color for
all tables displayed within the application).
-adding a footer at the bottom of the page (that sits just before the
timesheet.php footer)

in these fields, you can put placeholders for things that need to be
displayed:

%commandmenu% - a placeholder for the command menu.
%errormsg% - a placeholder for error messages which are dispayed on the
error page
%username% - a placeholder for the currently logged in user.

If you are confused here, just install it, log in and have a look -
you'll se what I mean. All of these values have defaults. I have done
some artwork for the default banner.

You can restore configuration defaults by clicking a reset checkbox for
that field, and pressing submit.

GENERAL FACELIFT
I've gone through the html on each and every page and re-organised
/re-structured, and prettyed up the interfaces.

This includes the popup window

ERROR PAGE
I have added an error page which is dispayed in the main window when
errors occur.

TERMINOLOGY
I have changed all references to 'check in' and 'check out' to 'clock
on' and 'clock off' because this makes more sense to me - checkins
confuse me with CVS. I hope you won't hate me for this. Maybe I should
have made it a configuration option?

POPUP WINDOW
I have put some javascript code in so you get an 'alert' confirming your
clock-on.

When you clock off, it checks that you've clocked on _before_ asking you
for the log message. If you have, then the popup window closes, and it
asks you for the log message in the main window. When you've entered
your message, the popup window re-appears again. 

The same happens for error messages coming from the popup window (they
appear in the main window).

If the main window was closed, and the only window is your popup window,
then a new main window will be created when required.

PROJECT SELECTION
Instead of just selecting from "Unassigned tasks", and your pre-defined
projects, I have added another project selection option "All Projects".
When this is selected, information from all projects will be displayed. 

WORK SPANNING MULTIPLE DAYS
You can now clock on at 10pm on Monday, and clock off at 2am on Tuesday
morning, because lets face it, most programmers work past midnight :)
You can even work for 4 days straight if you want. The logic will still
add up the right hours for the day and the week.

CALENDAR
I have changed the format of displaying tasks in the day so that it fits
better. You can now select "All Projects" and see how much work you did
on a particular day, and the total for a week.

CLOCK ONS FOR A DAY
This is the detailed view when clicking on a day in the calendar.
Once again, you can select "All Projects" here. You can delete _and_
edit a task, changing the clock on and clock off time _and_ date.

You can clock on and/or off manually here as well. I have changed the
form to be a lot more flexible.

Note that work spanning multiple days really shines here. If the task
started before today, it'll show the time _and_ the date, which will be
a link to that day. If it finished after today it'll show the time _and_
date also, the date also being a link to that day. In the totals column,
you'll get two totals. One for the task overall, and one for that task
on this day. 

There is also a daily total.

CONVERTED TO PHP4
I have changed all the filenames from .php3 to .php, and tested all of
the code on PHP4. It has not been tested on PHP3 - I couldn't get it
working with mysql for some reason. Perhaps you can test it?

FIXED UP THE INSTALL SCRIPT
The install.sh script now creates a special mysql user with just access
to the timesheet database. It also takes care of modifying the
common.inc and timesheet.sql include file by prompting you for the mysql
'timesheet' user password.
The admin password also works now. There was a problem with the script,
that made a newline character part of the password.

Generally, if you have mysql set up, and you know the superuser/root
password for it, then the database should set itself up without a hitch.

---

I seem to be having problems with logging out. I have played with this
for ages, but can't seem to get it to work properly, so I left it the
way it was. I'm not PHP guru or anything. It doesn't seem to log out,
and when you log in as administrator, you can't log in again as a normal
user unless you close your browser and start over. I'm using Mozilla
0.98.
	 
         <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>


Notes:
Timesheet.php has only been tested on the module version of PHP.  If you 
are lucky enough to get it to work with the CGI version, please let me know.

It is also known to work with early beta version of PHP4, so conversion should
not be a problem at all.

Version 1.0 finally does away with the calendar.so extension, and so the
only extensions you need compiled with PHP are the mysql functions.

         <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>

PHP Installation Notes:
Configure and compile PHP.  See the PHP Readme for more information.  To compile
PHP I did:

./configure --with-apxs=/usr/bin/apxs --with-mysql
make
make install (as root)

If you are not using Apache 1.3.x please do so.  It will make your life much easier.

         <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>

Timesheet.php Installation:

Simply unpack the archive and run the file "install.sh" it will take you
through the process of installing the files.

In order to use index.php3 as a default index file do the following:
In the file srm.conf or httpd.conf change
	DirectoryIndex index.html
to
	DirectoryIndex index.html index.php3

         <<<<<<<<<<<<<<<<<<<<<<<*>>>>>>>>>>>>>>>>>>>>>
