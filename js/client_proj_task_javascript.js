/**
 * This file should be used in association with client_proj_task_javascript.php
 */

	/**
	 *parse the JSON objects into javascript arrays
	 */
	function parseJSON(){	   
		clientProjectsHash = JSON.parse(jsonClientProjectsHash);
		projectTasksHash = JSON.parse(jsonProjectTasksHash);
	}

  function clockingShowHide(id){
    obj = document.getElementById(id);
    var stlSection = obj.style;
    var isCollapsed = obj.style.display.length;
    if (isCollapsed) stlSection.display = '';
    else stlSection.display = 'none';
  }
	/**
	 * @todo .....
	 */
	function enableClockOn() {
		var isChecked = document.getElementById('clock_on_check').checked;
		var isDate = true;
		if (document.getElementById('clock_on_radio_date') != null) {
			document.getElementById('clock_on_radio_date').disabled = !isChecked;
			document.getElementById('clock_on_radio_now').disabled = !isChecked;
			isDate = document.getElementById('clock_on_radio_date').checked;
		}
		document.getElementById('clock_on_time_hour').disabled = !(isChecked && isDate);
		document.getElementById('clock_on_time_min').disabled = !(isChecked && isDate);
	}

	/**
	 * @todo .....
	 */
	function enableClockOff() {
		var isChecked = document.getElementById('clock_off_check').checked;
		var isDate = true;
		if (document.getElementById('clock_off_radio_date') != null) {
			document.getElementById('clock_off_radio_date').disabled = !isChecked;
			document.getElementById('clock_off_radio_now').disabled = !isChecked;
			isDate = document.getElementById('clock_off_radio_date').checked;
		}
		document.getElementById('clock_off_time_hour').disabled = !(isChecked && isDate);
		document.getElementById('clock_off_time_min').disabled = !(isChecked && isDate);
	}

	/**
	 * @todo .....
	 */
	function validate() {
		if (document.getElementById('clock_on_check') != null) {
			if (!document.getElementById('clock_on_check').checked &&
					!document.getElementById('clock_off_check').checked) {
				alert('Please select an action by ticking a box: clock on, clock off, or both.');
				return false;
			}
		}

		/*copy the values of the selects
		var clientSelect = document.getElementById('clientSelect');
		document.getElementById('client_id').value = clientSelect.options[clientSelect.selectedIndex].value;
		var projectSelect = document.getElementById('projectSelect');
		document.getElementById('proj_id').value = projectSelect.options[projectSelect.selectedIndex].value;
		var taskSelect = document.getElementById('taskSelect');
		document.getElementById('task_id').value = taskSelect.options[taskSelect.selectedIndex].value;
		*/

		/*alert('submitting with clientId ' + document.getElementById('client_id').value +
						'  projectId ' + document.getElementById('proj_id').value +
						'  taskId ' + document.getElementById('task_id').value);*/

		document.theForm.submit();
		//return true;
	}

	/**
	 * @todo .....
	 */
	function enableProjectSelect(booleanValue) {
		var projectSelect = document.getElementById('projectSelect');
		projectSelect.disabled = !booleanValue;
		enableTaskSelect(booleanValue);
	}

	/**
	 * @todo .....
	 */
	function enableTaskSelect(booleanValue) {
		var taskSelect = document.getElementById('taskSelect');
		taskSelect.disabled = !booleanValue;
		if (document.getElementById('submitButton') != null)
			document.getElementById('submitButton').disabled = !booleanValue;
	}

	/**
	 * @todo .....
	 */
	function getInitialClient() {
		for (clientKey in clientProjectsHash) {
			if(clientKey > 0) {
				return clientKey;
			}
		}
	}

	/**
	 * @todo .....
	 */
	function getInitialProject() {
		for (key in projectTasksHash) {
			if(key > 0) {
				return key;
			}
		}
	}

	/**
	 * @todo .....
	 */
	function populateClientSelect(selectedClientId) {
		//get the client select
		var clientSelect = document.getElementById('clientSelect');

		//clientSelect.options[clientSelect.options.length] = new Option("All Clients", 0);

		//add the clients
		for (clientKey in clientProjectsHash) {
			//alert("client:" + clientKey + ", name: " + clientProjectsHash[clientKey].name);
			clientSelect.options[clientSelect.options.length] = new Option(clientProjectsHash[clientKey].name, clientKey);

			if(selectedClientId == undefined || selectedClientId == 0) {
				if(clientKey > 0) {
					selectedClientId=clientKey;
				}
			}

			if (clientKey == selectedClientId)
				clientSelect.options[clientSelect.options.length-1].selected = true;
		}
	}

	/**
	 * @todo .....
	 */
	function populateProjectSelect(clientId, selectedProjectId) {

		clearProjectSelect();

		var projectSelect = document.getElementById('projectSelect');

		//add the projects
		var empty = true;
		if (clientId == 0 || clientId == undefined) {
			//load all projects
			for (key in projectTasksHash) {
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[key]['name'], key);
				empty = false;

				if(selectedProjectId == undefined || selectedProjectId == 0) {
					if(key > 0) {
						selectedProjectId=key;
					}
				}

				if (key == selectedProjectId)
					projectSelect.options[projectSelect.options.length-1].selected = true;
			}
		} else {
			//load the projects for this client only
			var currentProjectId;

			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=1');

			for (key in clientProjectsHash[clientId]['projects']) {
				currentProjectId = clientProjectsHash[clientId]['projects'][key];
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[currentProjectId]['name'], currentProjectId);
				empty = false;

				if (currentProjectId == selectedProjectId)
					projectSelect.options[projectSelect.options.length-1].selected = true;
			}
		}

		//if there were no projects then disable the field
		if (empty) {
			projectSelect.options[projectSelect.options.length] = new Option("None assigned to you", -1);
			enableProjectSelect(false);
		} else
			enableProjectSelect(true);
	}

	/**
	 * @todo .....
	 */
	function populateTaskSelect(projectId, selectedTaskId) {

		clearTaskSelect();

		//get the task select for this row
		var taskSelect = document.getElementById('taskSelect');

		//add the tasks
		var empty = true;
		//alert('populateTaskSelect for project ' + projectId);
		if (projectId != -1) {
			var thisProjectTasks = projectTasksHash[projectId]['tasks'];
			for (taskKey in thisProjectTasks) {
				taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);
				empty = false;

				if(selectedTaskId == undefined || selectedTaskId == 0) {
					if(taskKey > 0) {
						selectedTaskId=taskKey;
					}
				}

				if (taskKey == selectedTaskId)
					taskSelect.options[taskSelect.options.length-1].selected = true;
			}
		}

		//if there were no tasks then disable the field
		if (empty) {
			taskSelect.options[taskSelect.options.length] = new Option("None assigned to you", -1);
			enableTaskSelect(false);
		}
		else
			enableTaskSelect(true);
	}

	/**
	 * @todo .....
	 */
	function clearProjectSelect() {
		projectSelect = document.getElementById('projectSelect');
		projectSelect.disabled = false;
		for (i=projectSelect.options.length-1; i>=0; i--)
			projectSelect.options[i] = null;
	}

	/**
	 * @todo .....
	 */
	function clearTaskSelect() {
		taskSelect = document.getElementById('taskSelect');
		taskSelect.disabled = false;
		for (i=taskSelect.options.length-1; i>=0; i--)
			taskSelect.options[i] = null;
	}

	/**
	 * @todo .....
	 */
	function doOnLoad() {
		//are there checkboxes
		if (document.getElementById('clock_on_check') != null) {
			enableClockOn();
			enableClockOff();
		}

		if(initialClientId == -1 || initialClientId == 0 || initialClientId == undefined)
			initialClientId=getInitialClient();
		if(initialProjectId == -1 || initialProjectId == 0 || initialProjectId == undefined)
			initialProjectId=getInitialProject();

		initialProjectId = getValidProjectIdForClient(initialClientId, initialProjectId);

		if (initialProjectId == -1)
			initialTaskId = -1;
		else
			initialTaskId = getValidTaskIdForProject(initialProjectId, initialTaskId);

		populateClientSelect(initialClientId);
		populateProjectSelect(initialClientId, initialProjectId);
		populateTaskSelect(initialProjectId, initialTaskId);

		if (window.resizePopupWindow)
			resizePopupWindow();
	}

	/**
	 * @todo .....
	 */
	function onChangeClientSelect() {
		//get the selects
		var clientSelect = document.getElementById('clientSelect');
		var projectSelect = document.getElementById('projectSelect');
		initialClientId = clientSelect.options[clientSelect.selectedIndex].value;
		initialProjectId = projectSelect.options[projectSelect.selectedIndex].value;
		initialProjectId = getValidProjectIdForClient(initialClientId, initialProjectId);

		//alert('onChangeClientSelect, about to populate projects with client: ' + initialClientId + ' project: ' + initialProjectId);

		populateProjectSelect(initialClientId, initialProjectId);
		onChangeProjectSelect();
	}

	/**
	 * @todo .....
	 */
	function onChangeProjectSelect() {
		var projectSelect = document.getElementById('projectSelect');
		var taskSelect = document.getElementById('taskSelect');
		initialProjectId = projectSelect.options[projectSelect.selectedIndex].value;
		initialTaskId = taskSelect.options[taskSelect.selectedIndex].value;
		initialTaskId = getValidTaskIdForProject(initialProjectId, initialTaskId);
		populateTaskSelect(initialProjectId, initialTaskId);

		if (window.resizePopupWindow)
			resizePopupWindow();
	}

	/**
	 * @todo .....
	 */
	function onChangeTaskSelect() {
		//do nothing for now
	}

	/**
	 * @todo .....
	 */
	function getValidProjectIdForClient(clientId, suggestedProjectId) {

		if (clientId == null || clientId == undefined || clientId == 0)
			alert('ERROR:clientId is ' + clientId + ' in getValidProjectIdForClient');

		//check that the project is valid for the client
		var currentProjectId;
		var validProjectId = false;
		if (clientId != 0) {

			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=2');

			for (key in clientProjectsHash[clientId]['projects']) {
				currentProjectId = clientProjectsHash[clientId]['projects'][key];

				if (currentProjectId == suggestedProjectId) {
					validProjectId = true;
					break;
				}
			}
		}

		if (validProjectId == false) {

			if (clientProjectsHash[clientId] == null)
				alert('ERROR:clientProjectsHash[clientId] is null, clientId=' + clientId + ' position=3');

			if (clientProjectsHash[clientId]['projectCount'] > 0) {
				for (key in clientProjectsHash[clientId]['projects']) {
					suggestedProjectId = clientProjectsHash[clientId]['projects'][key];
					break;
				}
			} else
				suggestedProjectId = -1;
		}

		return suggestedProjectId;
	}

	/**
	 * @todo .....
	 */
	function getValidTaskIdForProject(projectId, suggestedTaskId) {

		if (projectId == -1)
			return -1;

		//check that the task is valid for the project
		var currentTaskId;
		var validTaskId = false;
		var thisProjectTasks = projectTasksHash[projectId]['tasks'];
		for (key in thisProjectTasks) {
			if (key == suggestedTaskId) {
				validTaskId = true;
				break;
			}
		}

		if (validTaskId == false) {
			var replacementFound = false
			for (key in thisProjectTasks) {
				suggestedTaskId = key;
				replacementFound = true;
				break;
			}

			if (!replacementFound) {
				suggestedTaskId = -1;
			}
		}

		return suggestedTaskId;
	}
