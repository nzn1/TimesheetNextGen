<?php
class WeeklyPage{

  public $structuredArray;
  public $previousTaskId;
  public $currentTaskId;
  public function __construct($startStr,$endStr,$startDate){
    $this->startStr = $startStr;
    $this->endStr = $endStr;
    $this->startDate = $startDate;
  }
  
  
  /**
   * Get the required data and sort it into a structured array that 
   * makes the display logic simple
   *
   */        
  public function getSortData(){
  
  	//debug
	//$startDateStr = strftime("%D", $this->startDate);
	//$endDateStr = strftime("%D", $endDate);
	//echo "<p>WEEK start: $startDateStr WEEK end: $endDateStr</p>";

	require("include/tsx/taskinfo.class.php");

	// Get the Weekly data.
	$order_by_str = "".tbl::getClientTable().".organisation, ".tbl::getProjectTable().".title, ".tbl::getTaskTable().".name";
	list($num3, $qh3) = Common::get_time_records($this->startStr, $this->endStr, gbl::getContextUser(), gbl::getProjId(), gbl::getClientId(), $order_by_str);

	//echo "<p>Query: $query </p>";
	//echo "<p>there were $num3 results</p>";


	//we're going to put the data into an array of
	//different (unique) TASKS
	//which has an array of DAYS (7) which has
	//and array of size 4:
	// -index 0 is task entries array for tasks which started on a previous day and finish on a following day
	// -index 1 is task entries array for tasks which started on a previous day and finish today
	// -index 2 is task entreis array for tasks which started and finished today
	// -index 3 is task entries array for tasks which started today and finish on a following day

	$this->structuredArray = array();
	$this->previousTaskId = -1;
	$this->currentTaskId = -1;

	//iterate through results
	for ($i=0; $i<$num3; $i++) {
		//get the record for this task entry
		$data = dbResult($qh3,$i);
		
		//LogFile::write("\nweekly.php qh3 tuple: ". var_export($data, true)."\n");
		//There are several potential problems with the date/time data comming from the database
		//because this application hasn't taken care to cast the time data into a consistent TZ.
		//See: http://jokke.dk/blog/2007/07/timezones_in_mysql_and_php & read comments
		//So, we handle it as best we can for now...
		Common::fixStartEndDuration($data);

		//get the current task properties
		$this->currentTaskId = $data["task_id"];
		$currentTaskStartDate = $data["start_stamp"];
		$currentTaskEndDate = $data["end_stamp"];
		$currentTaskName = $data["taskName"];
		$currentProjectTitle = $data["projectTitle"];
		$currentProjectId = $data["proj_id"];
		$currentWorkDescription =  $data['log_message'];
		$currentClientName = $data["clientName"];
		$currentClientId = $data["client_id"];

		//find the current task id in the array
		$taskCount = count($this->structuredArray);
		unset($matchedPair);
		for ($j=0; $j<$taskCount; $j++) {
			//does its value1 (the task id) match?
			if ($this->structuredArray[$j]->value1 == $this->currentTaskId) {
				//store the pair we matched with
				$matchedPair = &$this->structuredArray[$j];

				//debug
				//echo "<p> found existing matched pair so adding to that one </p>";

				//break since it matched
				break;
			}
		}

		//was it not matched
		if (!isset($matchedPair)) {

			//debug
			//echo "<p> creating a new matched pair for this task </p>";

			//create a new days array
			$daysArray = array();

			//put an array in each day (this internal array will be of size 4)
			for ($j=0; $j<7; $j++) {
				//create a task event types array
				$taskEventTypes = array();

				//add 4 arrays to it
				for ($k=0; $k<4; $k++)
					$taskEventTypes[] = array();

				//add the task event types array to the days array for this day
				$daysArray[] = $taskEventTypes;
			}

			//create a new pair
			$matchedPair = new TaskInfo($this->currentTaskId,
											$daysArray,
											$currentProjectId,
											$currentProjectTitle,
											$currentTaskName,
											$currentClientName,
											$currentClientId,
											$currentWorkDescription);
      LogFile::write("\nweekly.php new matched pair: ". var_export($matchedPair, true)."\n");
			//add the matched pair to the structured array
			$this->structuredArray[] = $matchedPair;

			//make matchedPair be a reference to where we copied it to
			$matchedPair = &$this->structuredArray[count($this->structuredArray)-1];

			//echo "<p> added matched pair with task '$matchedPair->taskName'</p>";
		}

		//iterate through the days array
		$currentDate = $this->startDate;
		for ($k=0; $k<7; $k++) {
			$tomorrowDate = strtotime(date("d M Y H:i:s",$currentDate) . " +1 days");

			//work out some booleans
			$startsToday = (($currentTaskStartDate >= $currentDate ) && ( $currentTaskStartDate < $tomorrowDate ));
			$endsToday =   (($currentTaskEndDate > $currentDate) && ($currentTaskEndDate <= $tomorrowDate));
			$startsBeforeToday = ($currentTaskStartDate < $currentDate);
			$endsAfterToday = ($currentTaskEndDate > $tomorrowDate);

			if ($startsBeforeToday && $endsAfterToday){
				$matchedPair->value2[$k][0][] = $data;
			}
			else if ($startsBeforeToday && $endsToday){
				$matchedPair->value2[$k][1][] = $data;
			}
			else if ($startsToday && $endsToday){
				$matchedPair->value2[$k][2][] = $data;
			}
			else if ($startsToday && $endsAfterToday){
				$matchedPair->value2[$k][3][] = $data;
			}

			$currentDate = $tomorrowDate;
		}
	}
  
  
  }

  /**
   * Iterate through the Day Array and calculate the start and end times, 
   * and sum the total hours for that day   
   *
   *
   */           
  public function iterateCurrentDayArray($currentDayArray,$currentDate, $tomorrowDate){
  
      //get the timeformat
      $CfgTimeFormat = Common::getTimeFormat();
  			//declare todays vars
			$todaysTotal = 0;
			$formattedStartTime = '';
			$formattedEndTime = '';

			//create a flag for empty cell
			$emptyCell = true;

			//iterate through the current day array
			for ($j=0; $j<4; $j++) {
				$currentTaskEntriesArray = $currentDayArray[$j];

				//echo "C" . count($currentTaskEntriesArray) . " ";

				//iterate through the task entries
				foreach ($currentTaskEntriesArray as $currentTaskEntry) {
					//is the cell empty?
					if ($emptyCell){
						//the cell is not empty since we found a task entry
						$emptyCell = false;
					}
					else{
						//print a break for the next entry
						echo "<br />";
					}

					//format printable times
					if ($CfgTimeFormat == "12") {
						$formattedStartTime = date("g:iA",$currentTaskEntry["start_stamp"]);
						$formattedEndTime = date("g:iA",$currentTaskEntry["end_stamp"]);
					} else {
						$formattedStartTime = date("G:i",$currentTaskEntry["start_stamp"]);
						$formattedEndTime = date("G:i",$currentTaskEntry["end_stamp"]);
					}

					//Simple math will be wrong during Daylight savings time changes
					switch($j) {
					case 0: //tasks which started on a previous day and finish on a following day
						echo "...-...";
						$todaysTotal += Common::get_duration($currentDate, $tomorrowDate);
						break;
					case 1: //tasks which started on a previous day and finish today
						echo "...-" . $formattedEndTime;
						$todaysTotal += Common::get_duration($currentDate, $currentTaskEntry["end_stamp"]);
						break;
					case 2: //tasks which started and finished today
						echo $formattedStartTime . "-" . $formattedEndTime;
						$todaysTotal += $currentTaskEntry["duration"];
						break;
					case 3: //tasks which started today and finish on a following day
						echo $formattedStartTime . "-...";
						$todaysTotal += Common::get_duration($currentTaskEntry["start_stamp"],$tomorrowDate);
						break;
					default:
						echo "error";
					}
				}
			}
			$arr = array();
			$arr['formattedStartTime'] = $formattedStartTime;
      $arr['formattedEndTime'] = $formattedEndTime;
      $arr['todaysTotal'] = $todaysTotal;
      $arr['emptyCell'] = $emptyCell; 
			return $arr;
  
  }
}


?>