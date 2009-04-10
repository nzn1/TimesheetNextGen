<?php

class Controller_Calendar extends Controller_Core {
	
	public function index() {
		echo "calendar index<br />";
		echo "from i18n language file: ".TSNG::lang('Calendar.test')."<br />";
		
		try {
			$db = Database::instance();
			$result= $db->query('SELECT * FROM timesheet_clients');
	        echo 'last = '.$db->last_query().'<br />';
	        foreach($result as $row)
	        {
	            echo "row = ".$row->organisation."<br />";
	        }
		}
		catch(Exception $e) {
			echo "Exception 1:<pre>"; print_r($e); echo "</pre>";
		}	

		echo "ORM<BR>";
		try {
			$Client = Core_ORM::factory('Client_Client', 1);
echo "client = ".$Client->organisation."<BR>";
	
		$articles = Core_ORM::factory('Client_Client')->find_all();
foreach($articles as $article)
{
    echo $article->organisation;
}

			
		}
		catch(Exception $e) {
			echo "Exception 2:<pre>"; print_r($e); echo "</pre>";
		}	
	}
	
	public function add() {
		echo "add method<br />";
	}
}