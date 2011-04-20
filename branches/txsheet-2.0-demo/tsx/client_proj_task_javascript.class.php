<?php
if(!class_exists('Site'))die(JText::_('RESTRICTED_ACCESS'));
class ClientProjTaskJavascript{

	private $userClientList = array();
	private $userProjectList = array();
	
	private $jsonClientProjectsHash;
	private $jsonProjectTasksHash;

	public function __construct(){
	
    if(gbl::getContextUser() == ''){
	     ErrorHandler::fatalError("context user hasn't been set properly.");
    }

		$this->getUserClientsList();

		$this->getClientsQuery();
		
		$this->getProjectsQuery();
		
		$this->getTasksQuery();
	}

	private function getUserClientsList(){
		//Get list of clients and projects the contextUser is assigned to
		$getUsersClientList = "SELECT ".tbl::getProjectTable().".proj_id, "
		.tbl::getProjectTable().".client_id FROM "
		.tbl::getAssignmentsTable().", "
		.tbl::getProjectTable()." WHERE "
		.tbl::getAssignmentsTable().".username='".gbl::getContextUser()."' AND "
		.tbl::getProjectTable().".proj_id=".tbl::getAssignmentsTable().".proj_id AND "
		.tbl::getProjectTable().".proj_status!='Suspended' AND "
		.tbl::getProjectTable().".proj_status!='Complete' "
		."ORDER BY ".tbl::getProjectTable().".title";

		list($qh5, $num5) = dbQuery($getUsersClientList);

		//LogFile::write("found $num5 clients for gbl::getContextUser()\n");

		for ($i=0; $i<$num5; $i++) {
			$data = dbResult($qh5, $i);
			//create arrays of the client and project id's
			$this->userClientList[$data["client_id"]]=$data["client_id"];
			$this->userProjectList[$data["proj_id"]]=$data["proj_id"];			
		}
	}

	private function getClientsQuery(){
		//get all of the clients and put them into a hastable
		$getClientsQuery = "SELECT ".tbl::getClientTable().".client_id, "
		.tbl::getClientTable().".organisation FROM " 
		.tbl::getClientTable()." ORDER BY ".tbl::getClientTable().".organisation";

		list($qh2, $numClients) = dbQuery($getClientsQuery);
		//iterate through results
		for ($i=0; $i<$numClients; $i++) {
			//get the current record
			$data = dbResult($qh2, $i);

			
			#if(($data["client_id"]==1) && ($numClients>1) && sizeof($this->userClientList)>1) continue;
			
			if(array_key_exists($data["client_id"],$this->userClientList)) {
				
				$this->jsonClientProjectsHash[$data["client_id"]] = array();
				$this->jsonClientProjectsHash[$data["client_id"]]['name'] = addslashes($data["organisation"]);
				$this->jsonClientProjectsHash[$data["client_id"]]['projects'] = array();
			}
		}
	}

	private function getProjectsQuery(){
		//get all of the projects and put them into the hashtable
		$getProjectsQuery = "SELECT ".tbl::getProjectTable().".proj_id, "
			.tbl::getProjectTable().".client_id, "
			.tbl::getProjectTable().".title, "
			.tbl::getProjectTable().".proj_status FROM "
			.tbl::getProjectTable().", ".tbl::getAssignmentsTable()
			." WHERE "
			.tbl::getProjectTable().".proj_id=".tbl::getAssignmentsTable().".proj_id AND "
			.tbl::getAssignmentsTable().".username='".gbl::getContextUser()."' AND "
			.tbl::getProjectTable().".proj_status!='Suspended' AND "
			.tbl::getProjectTable().".proj_status!='Complete' "
			."ORDER BY ".tbl::getProjectTable().".title";

		list($qh3, $num3) = dbQuery($getProjectsQuery);
		//iterate through results
		for ($i=0; $i<$num3; $i++) {
			//get the current record
			$data = dbResult($qh3, $i);

			if(!array_key_exists($data["client_id"],$this->userClientList)) continue;
			if(!array_key_exists($data["proj_id"],$this->userProjectList))  continue;
			#if(($data["client_id"]==1) && ($data["proj_id"]==1) && ($num3>1)) continue;

			//add the project id to the array for this particular client
			$this->jsonClientProjectsHash[$data["client_id"]]['projects'][$data['proj_id']] = $data['proj_id'];
			
			$this->userProjectList[$data["proj_id"]]=$data["proj_id"];

			//add the project properties to the projectTasksHash			
			$this->jsonProjectTasksHash[$data['proj_id']] = array();
			$this->jsonProjectTasksHash[$data['proj_id']]['name'] = addslashes($data["title"]);
			$this->jsonProjectTasksHash[$data['proj_id']]['tasks'] = array();
		}
		
//		if($this->jsonClientProjectsHash != ''){
		foreach($this->jsonClientProjectsHash as $i=>$data){
			$this->jsonClientProjectsHash[$i]['projectCount'] = sizeof($data['projects']);
			
//		}
		}
	}

	private function getTasksQuery(){
		//get all of the tasks and put them into the hashtable
		$getTasksQuery = "SELECT ".tbl::getTaskTable().".proj_id, ".tbl::getTaskTable().".task_id, ".tbl::getTaskTable().".name FROM ".
			"".tbl::getTaskTable().", ".tbl::getTaskAssignmentsTable()." WHERE ".
			"".tbl::getTaskTable().".task_id = ".tbl::getTaskAssignmentsTable().".task_id AND ".
			"".tbl::getTaskAssignmentsTable().".username='".gbl::getContextUser()."' ".
			"ORDER BY ".tbl::getTaskTable().".name";

		list($qh4, $num4) = dbQuery($getTasksQuery);
		//iterate through results
		for ($i=0; $i<$num4; $i++) {
			//get the current record
			$data = dbResult($qh4, $i);

			if(!array_key_exists($data["proj_id"],$this->userProjectList)) continue;
			
			if(array_key_exists($data['proj_id'],$this->jsonProjectTasksHash)){
				$this->jsonProjectTasksHash[$data['proj_id']]['tasks'][$data['task_id']] = addslashes($data["name"]);
			}
		}
	}
	
	public function printJSONObjects(){
		echo "\nvar jsonClientProjectsHash = '".json_encode($this->jsonClientProjectsHash)."';";
		echo "\nvar jsonProjectTasksHash = '".json_encode($this->jsonProjectTasksHash)."';";
		echo"\n\n";
	}
	
	
	public function printJavascript(){
	   ?>
    <script type="text/javascript" src="<?php echo Config::getRelativeRoot();?>/js/client_proj_task_javascript.js"></script>

    <script type="text/javascript">
    	//set initial values
    	<?php
    	echo "var initialClientId = ".gbl::getClientId().";\n";
    	echo "var initialProjectId = ".gbl::getProjId().";\n";
    	echo "var initialTaskId = ".gbl::getTaskId().";\n";
      ?>
    	//define the hash table
    	var projectTasksHash;
    	var clientProjectsHash;
      <?php	
    	 $this->printJSONObjects();
      ?>  
    	//parse the JSON Objects generated by the PHP script
    	parseJSON();
      //console.log(jsonClientProjectsHash);
  </script>
  <?php
  }
}
