<?php
if(!class_exists('Site'))die('Restricted Access');
class MonthlyClass{
	public function __construct(){}

	public function print_totals($Minutes, $type="", $pyear, $pmonth, $pday) {

		/**
		 * Bug fix by robsearles 26 Jan 2008
		 * Strange bug I noticed whilst fixing bug below. If a month starts
		 * on a Monday, there is an extra total and link before the month
		 * starts. Simply check to see if we are on the first day of the
		 * month, if so, don't do anything.
		 */
		if($pday == 1) { return false; }
		/**
		 * Bug fix by robsearles 26 Jan 2008
		 * Fix the "weekly total" link. Both the last and first
		 * weeks' links now link to the correct week
		 */

		$curDate = mktime(0,0,0,$pmonth, $pday, $pyear);

		$pdayOfWeek = date("w", $curDate);
		// if the start day is a monday, want to view the week before
		if($pdayOfWeek == 1) { $pdayOfWeek = 7;}
		// other wise want to view this week (for use for last week of month)
		else $pdayOfWeek--;
		// Bug fix, if month ends on Saturday, dow==0, so, dow-- => -1
		// and then Date math below returns -1 (ie, Dec 31, 1969)
		if($pdayOfWeek<0)
			$pdayOfWeek=6;

		$curDate = strtotime(date("d M Y H:i:s",$curDate) . " -$pdayOfWeek days");
		$dateValues = getdate($curDate);
		$ymdStr = "&amp;year=".$dateValues["year"] . "&amp;month=".$dateValues["mon"] . "&amp;day=".$dateValues["mday"];

		// Called from monthly.php to print out a line summing the hours worked in the past
		// week.  index.phtml must set all global variables.

		print "</tr><tr>\n";
		if (gbl::getBreakRatio() > 0) {
			print "<td align=\"left\" colspan=\"3\">";
			$break_sec =  floor(gbl::getBreakRatio()*$seconds);
			$seconds -= $break_sec;
			print "<font size=\"-1\">Break time: <font color=\"red\">". formatSeconds($break_sec);
			print "</font></font></td><td align=\"right\" colspan=\"4\">";
		} else
			print "<td align=\"right\" colspan=\"7\" class=\"calendar_totals_line_$type\">";

		if ($type=="monthly")
			print JText::_('MONTHLY')." ".JText::_('TOTAL').": ";
		else
			print "<a href=\"".Config::getRelativeRoot()."/weekly?client_id=".gbl::getClientId()."&amp;proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."$ymdStr\">". JText::_('WEEKLY')." ".JText::_('TOTAL').": </a>";

		print "<span class=\"calendar_total_value_$type\">". Common::formatMinutes($Minutes) ."</span></td>\n";
	}
}
