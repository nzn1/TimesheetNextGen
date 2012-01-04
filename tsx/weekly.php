<h1><?php echo JText::_('WEEKLY_TIMESHEET'); ?></h1>
<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclWeekly'))return;
PageElements::setTheme('newcss');

if (isEmpty(gbl::getLoggedInUser()))
	errorPage(JText::_('WHO_IS_LOGGED_IN'));

//check that project id is valid
if (gbl::getProjId() == 0)
	gbl::setTaskId(0);

// Check project assignment.
if (gbl::getProjId() != 0 && gbl::getClientId() != 0) { // id 0 means 'All Projects'

	//make sure project id is valid for client. If not then choose another.
	if (!Common::isValidProjectForClient(gbl::getProjId(), gbl::getClientId())) {
		gbl::setProjId(Common::getValidProjectForClient(gbl::getClientId()));
	}
}
else{
	gbl::setTaskId(0);
}

	$mode = "weekly";
//get the context date
$startDayOfWeek = Common::getWeekStartDay();  //needed by NavCalendar
$contextTimeStamp = mktime(0, 0, 0,gbl::getMonth(), gbl::getDay(), gbl::getYear());
$contextDate = getdate($contextTimeStamp);

list($startDate,$endDate) = Common::getWeeklyStartEndDates($contextTimeStamp);

$startStr = date("Y-m-d H:i:s",$startDate);
$endStr = date("Y-m-d H:i:s",$endDate);

$nextWeekDate = strtotime(date("d M Y H:i:s",$contextTimeStamp) . " +1 week");
$prevWeekDate = strtotime(date("d M Y H:i:s",$contextTimeStamp) . " -1 week");
PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('WEEKLY_TIMESHEET')." | ".gbl::getContextUser()."</title>");
ob_start();

include('tsx/client_proj_task_javascript.class.php');
$js = new ClientProjTaskJavascript();
$js->printJavascript();
?>
<script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/datetimepicker_css.js"></script>
<script type="text/javascript">

function CallBack_WithNewDateSelected(strDate) 
{
	document.subtimes.submit();
}
</script>
<?php
PageElements::setHead(PageElements::getHead().ob_get_contents());
ob_end_clean();
PageElements::setBodyOnLoad('doOnLoad();');

//WARNING - IF POPUP IS SET THEN doOnLoad() won't be run

if (isset($popup)){
	$str = "window.open(\"".Config::getRelativeRoot()."/clock_popup?proj_id=".gbl::getProjId()."&amp;task_id=".gbl::getTaskId()."\",\"Popup\",\"location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=205\");";
	PageElements::setBodyOnLoad($str);
}	
	
	require_once("include/tsx/navcal/navcal.class.php");
	$nav = new NavCal();
	$nav->navCalClockOnOff($contextTimeStamp,false);
?>

<form action="<?php echo Rewrite::getShortUri(); ?>" method="post" name="subtimes">
<input type="hidden" name="month" value="<?php echo gbl::getMonth(); ?>" />
<input type="hidden" name="year" value="<?php echo gbl::getYear(); ?>" />
<input type="hidden" name="day" value="<?php echo gbl::getDay(); ?>" />
<input type="hidden" name="task_id" value="<?php echo gbl::getTaskId(); ?>" />


<!-- date selection table -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr>
		<td  align="center" class="outer_table_heading">
			<?php
				$sdStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$startDate));
				//just need to go back 1 second most of the time, but DST 
				//could mess things up, so go back 6 hours...
				$edStr = utf8_encode(strftime(JText::_('DFMT_MONTH_DAY_YEAR'),$endDate - 6*60*60));
				echo JText::_('CURRENT_WEEK').': <span style="color:#00066F;">'.$sdStr.' - '.$edStr.'</span>';
			?>
		</td>
		<td>&nbsp;</td>
		<td  align="center">
  		<?php Common::printDateSelector($mode, $startDate, $prevWeekDate, $nextWeekDate); ?>
		</td>
		<td  align="right"><?php echo JText::_('FILTER')?>:</td>

		<td  align="left">
		<?php 
      echo ucfirst(JText::_('CLIENT')).':</td><td width="25%" align="left">';
      Common::client_select_list(gbl::getClientId(), gbl::getContextUser(), false, false, true, false, "submit();"); ?>
		</td>
		<td  align="left">
      <?php 
      echo ucfirst(JText::_('PROJECT')).':</td><td width="25%" align="left">';
      Common::project_select_list(gbl::getClientId(), false, gbl::getProjId(), gbl::getContextUser(), false, true, "submit();");
      ?>
    </td>
	</tr>
</table><!-- end date selection table -->

<div>&nbsp;</div>
 <!--  data table -->
<div id="weekly"> 
	<table class="weekTable">
		<tr class="table_head">
			<th width="22%">
<?php			echo ucfirst(JText::_('CLIENT'))." / ".ucfirst(JText::_('PROJECT'))." / ".ucfirst(JText::_('Task'));?>
			</th>
			<th class="align_center">&nbsp;</th>
			<?php
			//print the days of the week
			$currentDate = $startDate;
			for ($i=0; $i<7; $i++) {
				$currentDayStr = strftime("%A %d", $currentDate);
				echo "<th class=\"inner_table_column_heading\" align=\"center\" width=\"10%\">$currentDayStr</th>\n";
				$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days"); // increment date to next day
			}
			?>
			<th class="align_center">&nbsp;</th>
			<th class="inner_table_column_heading" align="center"><?php echo ucfirst(JText::_('TOTAL'));?></th>
		</tr>

<?php

  require(Config::getDocumentRoot().'/tsx/weekly.class.php');
  
  $wp = new WeeklyPage($startStr,$endStr,$startDate);
  
  $wp->getSortData();

	//by now we should have our results structured in such a way that it's easy to output it

	//set vars
	$previousProjectId = -1;
	$allTasksDayTotals = array(0,0,0,0,0,0,0); //totals for each day

/*	$wp->previousTaskId = -1;
	$thisTaskId = -1;
	$columnDay = -1;
	$columnStartDate = $startDate;*/

	//iterate through the structured array
	$count = count($wp->structuredArray);
	unset($matchedPair);
	for ($i=0; $i<$count; $i++) {
		$matchedPair = &$wp->structuredArray[$i];
    LogFile::write("\nweekly.php structured matched pair: ". var_export($matchedPair, true)."\n");

		if (($i % 2) == 1){
			echo "<tr class=\"diff\">\n";
		}
		else{
			echo "<tr>\n";
		}
		?>

			
		  <td  class="calendar_cell_middle" valign="top"><!--column for client name, project title, task name-->
  			<span class="client_name_small"><?php echo $matchedPair->clientName;?> / </span>
  			<span class="project_name_small"><?php echo $matchedPair->projectTitle;?> / </span>
  		  <span class="task_name_small"><?php echo $matchedPair->taskName;?></span>
		  </td>
		  <!--print the spacer column-->
		  <td class="calendar_cell_disabled_middle" width="2">&nbsp;</td>

      <?php
		//iterate through the days array
		$dayIndex = 0;
		$weeklyTotal = 0;

		$currentDate = $startDate;
		foreach ($matchedPair->value2 as $currentDayArray) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");
			
			//Put a popup link in the cell
			$contextDate = getdate($currentDate);
			$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
				"?client_id=$matchedPair->clientId".
				"&amp;proj_id=$matchedPair->projectId".
				"&amp;task_id=$matchedPair->value1".
				"&amp;year=".$contextDate["year"].
				"&amp;month=".$contextDate["mon"].
				"&amp;day=".$contextDate["mday"].
				"&amp;destination=".Rewrite::getShortUri().
				"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
			?>
      <td class="calendar_cell_middle" valign="top">
  			
  			<div style="float:right; width:12px">
          <a href="<?echo $popup_href;?>" class="action_link">
				    <img src="{relativeRoot}/images/add.gif" alt="+" width="11" height="11" border="0" />
				  </a>
        </div>
        
        <span class="task_time_small">
  			<?php
        			$arr = $wp->iterateCurrentDayArray($currentDayArray,$currentDate,$tomorrowDate);      
              $formattedStartTime = $arr['formattedStartTime'];
              $formattedEndTime = $arr['formattedEndTime'];
              $todaysTotal = $arr['todaysTotal'];
              $emptyCell = $arr['emptyCell']; 
              if ($emptyCell) echo "&nbsp;";       
        ?>  			
  			</span> 
        <?php
  			if (!$emptyCell) {
  				//print todays total
  				$todaysTotalStr = Common::formatMinutes($todaysTotal);
  				echo "<br /><span class=\"task_time_total_small\">$todaysTotalStr</span>";
  			}
        ?>			
			</td>
      <?php
			//add this days total to the weekly total
			$weeklyTotal += $todaysTotal;

			//add this days total to the all tasks total for this day
			$allTasksDayTotals[$dayIndex] += $todaysTotal;
			$dayIndex++;
			$currentDate=$tomorrowDate;
		
    }//end foreach loop
    ?>
		
  		<td class="calendar_cell_disabled_middle" width="2">&nbsp;</td><!--print the spacer column-->
  		<td class="calendar_totals_line_weekly subtotal" valign="bottom" align="right" ><!--total column-->
  		  <span class="calendar_total_value_weekly" style="text-align:right"><?php echo Common::formatMinutes($weeklyTotal);?></span></td>
    </tr>
    <?php
		//store the previous task and project ids
		$wp->previousTaskId = $wp->currentTaskId;
		$previousProjectId = $matchedPair->projectId;
	}

	//create an actions row
	echo "<tr><!--create an actions row-->\n";
	echo "<td class=\"calendar_cell_disabled_middle\" align=\"right\">".ucfirst(JText::_('ACTIONS')).":</td>\n";
	echo "<td class=\"calendar_cell_disabled_middle\" width=\"2\">&nbsp;</td>\n";
	$currentDate = $startDate;
	for ($i=0; $i<7; $i++) {
		$contextDate = getdate($currentDate);
		$ymdStr = "&amp;year=".$contextDate["year"] . "&amp;month=".$contextDate["mon"] . "&amp;day=".$contextDate["mday"];
		$popup_href = "javascript:void(0)\" onclick=\"window.open('".Config::getRelativeRoot()."/clock_popup".
											"?client_id=".gbl::getClientId()."".
											"&amp;proj_id=".gbl::getProjId()."".
											"&amp;task_id=".gbl::getTaskId()."".
											"$ymdStr".
											"&amp;destination=".Rewrite::getShortUri().
											"','Popup','location=0,directories=no,status=no,menubar=no,resizable=1,width=420,height=310')";
		echo "<td class=\"calendar_cell_disabled_middle\"><span class=\"actions\">";
		echo "<a href=\"$popup_href\" class=\"action_link\">".ucfirst(JText::_('ADD'))."</a>,";
		echo "<a href=\"{relativeRoot}/daily?$ymdStr\">".ucfirst(JText::_('EDIT'))."</a></span></td>\n";
		$currentDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");
	}
	?>
	<td class="calendar_cell_disabled_middle" width="2">&nbsp;</td>
	<td class="calendar_cell_disabled_right">&nbsp;</td>
	</tr>
	
	<tr><!--new totals row-->
  	<td class="calendar_cell_disabled_middle" align="right"><?php echo ucwords(JText::_('TOTAL')." ".JText::_('HOURS'));?>:</td>
  	<td class="calendar_cell_disabled_middle" width="2">&nbsp;</td>

    <?php 
  	//iterate through day totals for all tasks
  	$grandTotal = 0;
  	foreach ($allTasksDayTotals as $currentAllTasksDayTotal) {
  		$grandTotal += $currentAllTasksDayTotal;
  		$formattedTotal = Common::formatMinutes($currentAllTasksDayTotal);
  		?>  		
      <td class="calendar_totals_line_weekly_right align_right">
  		  <span class="calendar_total_value_weekly"><?php echo $formattedTotal;?></span>
      </td>
  		<?php
  	}  
  	?>
  	<!--print grand total-->
    <td class="calendar_cell_disabled_middle" width="2">&nbsp;</td>
  	<td class="calendar_totals_line_monthly" align="right">
  	<span class= "calendar_total_value_monthly"><?php echo Common::formatMinutes($grandTotal);?></span></td>
	</tr><!-- end totals row-->

  </table>
</div>
</form>
