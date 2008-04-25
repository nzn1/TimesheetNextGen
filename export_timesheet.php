<?PHP

	session_start();
	require_once("excelwriter.inc.php");

function format_seconds($seconds) {
	$temp = $seconds;
	$hour = (int) ($temp / (60*60));

	if ($hour < 10)
		$hour = '0'. $hour;

	$temp -= (60*60)*$hour;
	$minutes = (int) ($temp / 60);

	if ($minutes < 10)
		$minutes = '0'. $minutes;

	$temp -= (60*$minutes);
	$sec = $temp;

	if ($sec < 10)
		$sec = '0'. $sec;		// Totally wierd PHP behavior.  There needs to
								// be a space after the . operator for this to work.
	return "$hour:$minutes:$sec";
}



//	require_once("../devtools/connection.php");
//	include("../devtools/function_db.php");

	$dati=$_SESSION['excel_data'];

	$nomefile="temp".rand(0,9)."_prp.xls";
	$excel=new ExcelWriter($nomefile);

	if($excel==false)	echo $excel->error;


	if ($_GET['type'] == "project") {
		$intestazione=array("User ","Task","Date","Time");

		$excel->writeLine_orange($intestazione);
		//scrittura nel file dei dati dall'array

		$excel->writeRow();

		foreach($dati as $data){
			if ($last_uid != $data['uid']) {
				$excel->writeCol(stripslashes("$data[first_name] $data[last_name]"));
				$last_uid = $data['uid'];
				if ($grand_total_time) {
					$formatted_time = format_seconds($total_time);
					$total_time = 0;
				}
			} else $excel->writeCol("");
			if ($last_task_id != $data["task_id"]) {
				$last_task_id = $data['task_id'];
				$current_task_name = stripslashes($data["name"]);
				$excel->writeCol("$current_task_name");
			} else $excel->writeCol("");

			$excel->writeCol($data['start_date']);
			$excel->writeCol($data['diff_time']);

			$excel->writeRow();
		}
		$excel->close();
	}else if ($_GET['type'] == "user"){
		$intestazione=array("Project ","Task","Date","Time");

		$excel->writeLine_orange($intestazione);
		//scrittura nel file dei dati dall'array

		$excel->writeRow();

		foreach($dati as $data){
			if ($last_proj_id != $data['proj_id']) {
				$last_proj_id = $data['proj_id'];
				if ($grand_total_time) {
					$formatted_time = format_seconds($total_time);
				}
				$current_project_title = stripslashes($data["title"]);
				$total_time = 0;
				$excel->writeCol("$current_project_title");
			}else $excel->writeCol("");

			if ($last_task_id != $data["task_id"]) {
				$last_task_id = $data['task_id'];
				$current_task_name = stripslashes($data["name"]);
				$excel->writeCol("$current_task_name");
			}else $excel->writeCol("");

			$excel->writeCol($data['start_date']);
			$excel->writeCol($data['diff_time']);

			$excel->writeRow();
		}
		$excel->close();
	} else if ($_GET['type'] == "all"){
		$intestazione=array("User Name ","Time","Project - Task");

		$excel->writeLine_orange($intestazione);
		//scrittura nel file dei dati dall'array

		$excel->writeRow();

		$dati_time_data=$_SESSION['excel_data_time_data'];

		foreach($dati as $key=>$data){
			if ($last_username != $data["username"]) {
				$last_username = $data["username"];
				$excel->writeCol("$data[first_name] $data[last_name]");
			} else $excel->writeCol("");

			$valore_time=$dati_time_data[$key]['diff'];
			$excel->writeCol("$valore_time");

			$projectTitle = stripslashes($data["title"]);
			$taskName = stripslashes($data["name"]);

			$excel->writeCol($projectTitle."-".$taskName);

			$excel->writeRow();
		}
		$excel->close();
	}
	$filee=file_get_contents($nomefile);
	echo $filee;
?>
