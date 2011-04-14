<?php

 require("class.Pair.php");

	class TaskInfo extends Pair {
		var $clientId;
		var $projectId;
		var $projectTitle;
		var $taskName;
		var $workDescription;

		function TaskInfo($value1, $value2, $projectId, $projectTitle, $taskName, $workDescription) {
			parent::Pair($value1, $value2);
			$this->projectId = $projectId;
			$this->projectTitle = $projectTitle;
			$this->taskName = $taskName;
			$this->workDescription = $workDescription;
		}
	}


?>