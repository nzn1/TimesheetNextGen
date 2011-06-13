
	//function to populate existing rows with project and task names and select the right one in each
	function populateExistingSelects() {
		//get the number of existing rows
		var existingRows = parseInt(document.getElementById('existingRows').value);
		//alert('There are ' + existingRows + ' existing rows');

		//iterate to plus one to do the additional row
		for (var i=0; i<=existingRows; i++) {
			//alert('existing row ' + i);

			//get the client, project and task id for this row
			var clientId = document.getElementById('client_row' + i).value;
			var projectId = document.getElementById('project_row' + i).value;
			var taskId = document.getElementById('task_row' + i).value;

			//alert('clientID is' + clientId + "\nprojectID is" + projectId + "\ntaskID is" + taskId);

			//alert('projectTaskHash is ' + projectTasksHash);

			//get the selects
			var clientSelect = document.getElementById('clientSelect_row' + i);
			var projectSelect = document.getElementById('projectSelect_row' + i);
			var taskSelect = document.getElementById('taskSelect_row' + i);

			//add None to the selects
			clientSelect.options[clientSelect.options.length] = new Option('None', '-1');
			projectSelect.options[projectSelect.options.length] = new Option('None', '-1');
			taskSelect.options[taskSelect.options.length] = new Option('None', '-1');

			//add the clients
			//var clientId = -1;
			for (var key in projectTasksHash) {
				//alert('looking at key ' + key);
				if (projectTasksHash[key]['clientId'] != clientId) {
					//projectSelect.options[projectSelect.options.length] = new Option('[' + projectTasksHash[key]['clientName'] + ']', -1);
					clientSelect.options[clientSelect.options.length] = new Option(projectTasksHash[key]['clientName'], projectTasksHash[key]['clientId']);
					clientId = projectTasksHash[key]['clientId'];
				}

				//alert('added client ' + key);

				if (key == projectId && projectTasksHash[key]['clientId'] == clientId) {
					populateProjectSelect(i, clientId, key);
					clientSelect.options[clientSelect.options.length-1].selected = true;
				}
			}

			if (projectId != -1) {
				//add the tasks
				var thisProjectTasks = projectTasksHash[projectId]['tasks'];
				for (taskKey in thisProjectTasks) {
					taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

					if (taskKey == taskId)
						taskSelect.options[taskSelect.options.length-1].selected = true;
				}
			}
		}
		recalculateAll();
	}

	function populateTaskSelect(row, projectId, selectedTaskId) {
		//get the task select for this row
		var taskSelect = document.getElementById('taskSelect_row' + row);

		//add the tasks
		var thisProjectTasks = projectTasksHash[projectId]['tasks'];
		for (var taskKey in thisProjectTasks) {
			taskSelect.options[taskSelect.options.length] = new Option(thisProjectTasks[taskKey], taskKey);

			//alert('added task ' + taskKey);

			if (taskKey == selectedTaskId)
				taskSelect.options[taskSelect.options.length-1].selected = true;
		}
	}

	function populateProjectSelect(row, clientId, selectedProjectId) {
		//get the project select for this row
		var projectSelect = document.getElementById('projectSelect_row' + row);

		//add the projects
		for (key in projectTasksHash) {
			if (projectTasksHash[key]['clientId'] == clientId) {
				projectSelect.options[projectSelect.options.length] = new Option(projectTasksHash[key]['name'], key);

				//alert('added project ' + key);

				if (key == selectedProjectId) {
					projectSelect.options[projectSelect.options.length-1].selected = true;
				}
			}
		}
	}

	function clearTaskSelect(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		for (var i=1; i<taskSelect.options.length; i++) {
			taskSelect.options[i] = null;
		}

		//set the length back to 1
		taskSelect.options.length = 1;

		//select the 'None' option
		taskSelect.options[0].selected = true;

		onChangeTaskSelectRow(row);
	}

	function clearProjectSelect(row) {
		projectSelect = document.getElementById('projectSelect_row' + row);
		for (i=1; i<projectSelect.options.length; i++) {
			projectSelect.options[i] = null;
		}

		projectSelect.options.length = 1;
		projectSelect.options[0].selected = true;
	}

	function clearWorkDescriptionField(row) {
		descField = document.getElementById("description_row" + row);
		descField.value = "";
	}

	function rowFromIdStr(idStr) {
		var pos1 = idStr.indexOf("row") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function colFromIdStr(idStr) {
		var pos1 = idStr.indexOf("col") + 3;
		var pos2 = idStr.indexOf('_', pos1);
		if (pos2 == -1)
			pos2 = idStr.length;
		return parseInt(idStr.substring(pos1, pos2));
	}

	function onChangeProjectSelect(idStr) {
		row = rowFromIdStr(idStr);
		clearTaskSelect(row);

		//get the project id
		var projectSelect = document.getElementById('projectSelect_row' + row);
		var projectId = projectSelect.options[projectSelect.selectedIndex].value;

		if (projectId != -1)
			//populate the select with tasks for this project
			populateTaskSelect(row, projectId);

		setDirty();
	}

	function onChangeClientSelect(idStr) {
		row = rowFromIdStr(idStr);
		clearProjectSelect(row);
		clearTaskSelect(row);

		var clientSelect = document.getElementById('clientSelect_row' + row);
		var clientId = clientSelect.options[clientSelect.selectedIndex].value;

		if (clientId != -1) {
			populateProjectSelect(row, clientId);
		}
	}

	function onChangeTaskSelect(idStr) {
		var rowNum = rowFromIdStr(idStr);
		//alert('octs called for row ' + rowNum);
		onChangeTaskSelectRow(rowNum);
	}

	function onChangeTaskSelectRow(row) {
		taskSelect = document.getElementById('taskSelect_row' + row);
		//alert('octsr called for row ' + row);
		if (taskSelect.options[0].selected == true) {
			//alert('disabling row ' + row);
			//disable fields
			for (var i=1; i<=7; i++) {
				document.getElementById('hours_row' + row + '_col' + i).disabled = true;
				document.getElementById('mins_row' + row + '_col' + i).disabled = true;
			}
		} else {
			//get the total number of rows
			var totalRows = parseInt(document.getElementById('totalRows').value);
			//alert('change task droplist on row ' + row + ', totalRows=' + totalRows);

			//enable fields
			//alert('enabling row ' + row);
			for (var i=1; i<=7; i++) {
				document.getElementById('hours_row' + row + '_col' + i).disabled = false;
				document.getElementById('mins_row' + row + '_col' + i).disabled = false;
			}

			if (row == (totalRows-1)) {
				//get the row to copy
				var tempNode = document.getElementById('row' + row);

				//clone the row
				var newNode = tempNode.cloneNode(true);

				//setup the pattern to match
				var rowRegex = new RegExp("row(\\d+)");

				//iterate through with dom and replace all name and id attributes with regexp
				replaceIdAndNameAttributes(newNode, rowRegex, totalRows);

				//increment totalRows by one
				//alert('totalRows was ' + document.getElementById('totalRows').value);
				document.getElementById('totalRows').value = parseInt(document.getElementById('totalRows').value) + 1;
				//alert('totalRows is now ' + document.getElementById('totalRows').value);

				//get the totals node
				var totalsNode = document.getElementById('totalsRow');

				//insert the new node before the totals node
				totalsNode.parentNode.insertBefore(newNode, totalsNode);


				//clear the task select
				clearTaskSelect(totalRows);

				// clear the work description field
				clearWorkDescriptionField(totalRows);

				clearProjectSelect(totalRows);

				/* //select default project
				var oldProjectSelect = document.getElementById('projectSelect_row' + row);
				var newProjectSelect = document.getElementById('projectSelect_row' + (row+1));
				newProjectSelect.options[oldProjectSelect.selectedIndex].selected = true;

				//repopulate task
				var projectId = newProjectSelect.options[newProjectSelect.selectedIndex].value;
				populateTaskSelect(row+1, projectId); */

			}

		}
		setDirty();
	}

	function onChangeWorkDescription(idStr) {
		setDirty();
	}

	//clear row and make it invisible
	function onDeleteRow(idStr) {
		var row = rowFromIdStr(idStr);
		var tr = document.getElementById('row' + row)

		// clear the task select
		clearTaskSelect(row);

		// clear the work description field
		clearWorkDescriptionField(row);

		// clear hours and minutes
		for (var i=1; i<=7; i++) {
			document.getElementById("hours_row" + row + "_col" + i).value = "0";
			document.getElementById("mins_row" + row + "_col" + i).value = "0";
			recalculateCol(i,idStr);
		}

		tr.style.display = "none";
	}

	function replaceIdAndNameAttributes(node, rowRegex, rowNumber) {
		while (node != null) {
			if (node.getAttribute != null && node.getAttribute("id") != null)
				node.setAttribute("id", node.getAttribute("id").replace(rowRegex, "row" + rowNumber));
			if (node.getAttribute != null && node.getAttribute("name") != null)
				node.setAttribute("name", node.getAttribute("name").replace(rowRegex, "row" + rowNumber));

			// call this function recursively for children
			// did not to work recursely with if statement like it was:
			// if (node.firstChild != null && node.firstChild.tagName != null)
			if (node.firstChild != null)
				replaceIdAndNameAttributes(node.firstChild, rowRegex, rowNumber);

			//do the same for the next sibling
			node = node.nextSibling;
		}
	}

	function recalculateRowCol(idStr) {
		recalculateRow(rowFromIdStr(idStr));
		recalculateCol(colFromIdStr(idStr),idStr);
		setDirty();
	}

	function recalculateRow(row) {
		var totalMins = 0;
		//alert('recalculateRow ' + row);
		for (i=1; i<=7; i++) {
			minsinday = parseInt(document.getElementById("minsinday_" + i).value);
			//var hrsinday = minsinday/60;
			hours = parseInt(document.getElementById("hours_row" + row + "_col" + i).value);
			mins = parseInt(document.getElementById("mins_row" + row + "_col" + i).value);
			if (isNaN(hours)) {
				hours = 0;
			}
			if (isNaN(mins)) {
				mins = 0;
			}

			var minutes = hours * 60 + mins;

			if (minutes > minsinday) {
				alert("<?php echo JText::sprintf('TOO_MUCH_TIME_FOR_DAY', minsinday/60) ?>");
				document.getElementById("hours_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("mins_row" + row + "_col" + i).value="";  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				document.getElementById("mins_row" + row + "_col" + i).select();  //=true;
				document.getElementById("hours_row" + row + "_col" + i).select();  //=true;
				return false;
			}
			totalMins += minutes;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the total cell
		var totalCell = document.getElementById("subtotal_row" + row);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function recalculateCol(col,idStr) {
		//alert('recalculateCol ' + col +' idStr:' + idStr);

		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);
		var minsinday = parseInt(document.getElementById("minsinday_" + col).value);

		var totalMins = 0;
		var row="";
		for (var i=0; i<totalRows; i++) {
			hours = parseInt(document.getElementById("hours_row" + i + "_col" + col).value);
			mins = parseInt(document.getElementById("mins_row" + i + "_col" + col).value);
			if (isNaN(hours)) {
				hours = 0;
			}
			if (isNaN(mins)) {
				mins = 0;
			}

			var minutes = hours * 60 + mins;

			totalMins += minutes;
		}

		if (totalMins > minsinday) {
			alert("<?php echo JText::sprintf('TOO_MUCH_TIME_FOR_COL', minsinday/60) ?>");
			row=rowFromIdStr(idStr);
			document.getElementById("hours_row" + row + "_col" + col).value="";  //=true;
			document.getElementById("mins_row" + row + "_col" + col).value="";  //=true;
			document.getElementById("hours_row" + row + "_col" + col).select();  //=true;
			document.getElementById("mins_row" + row + "_col" + col).select();  //=true;
			document.getElementById("hours_row" + row + "_col" + col).select();  //=true;
			return false;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the total cell
		var totalCell = document.getElementById("subtotal_col" + col);
		totalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';

		recalculateGrandTotal();
	}

	function recalculateGrandTotal() {
		//alert('recalculateGrandTotal');
		var totalMins = 0;
		for (var i=1; i<=7; i++) {
			var currentInnerHTML = document.getElementById("subtotal_col" + i).innerHTML;
			//get the hours
			hPos = currentInnerHTML.indexOf('h');
			hours = parseInt(currentInnerHTML.substring(0, hPos));
			if (!isNaN(hours))
				totalMins += hours * 60;

			//get the minutes
			mPos = currentInnerHTML.indexOf('m');
			mins = parseInt(currentInnerHTML.substring(hPos+7, mPos));
			if (!isNaN(mins))
				totalMins += mins;
		}

		hours = Math.floor(totalMins / 60);
		mins = totalMins - (hours * 60);

		//get the grand total cell
		var grandTotalCell = document.getElementById("grand_total");
		grandTotalCell.innerHTML = '' + hours + 'h&nbsp;' + mins + 'm';
	}

	function recalculateAll() {
		//alert('recalculateAll');

		var totalRows = parseInt(document.getElementById('totalRows').value);
		var rowTotals = {}; 
		var colTotals = {};
		var grandTotal = 0;

		for (i=0; i<=7; i++) {
			colTotals[i] = 0;
		}

		for (j=0; j<totalRows; j++) {
			rowTotals[j] = 0;
		}

		for (i=1; i<=7; i++) {
			for (j=0; j<totalRows; j++) {
				hours = parseInt(document.getElementById("hours_row" + j + "_col" + i).value);
				mins = parseInt(document.getElementById("mins_row" + j + "_col" + i).value);

				if (isNaN(hours)) {
					hours = 0;
				}

				if (isNaN(mins)) {
					mins = 0;
				}

				minutes = hours * 60 + mins;

				colTotals[i] += minutes;
				rowTotals[j] += minutes;
				grandTotal   += minutes;

				//alert('i=' + i + ' j=' + j + ' minutes=' + minutes + ' ct[i]=' + colTotals[i] + ' rt[j]=' + rowTotals[j]);
			}
		}
		for (i=1; i<=7; i++) {
			hours = Math.floor(colTotals[i] / 60);
			mins = colTotals[i] - (hours * 60);

			totalCell = document.getElementById("subtotal_col" + i);
			totalCell.innerHTML = '' + hours + "<?php echo JText::_('HR')?>" + '&nbsp;' + mins + "<?php echo JText::_('MN')?>";
		}

		for (j=0; j<totalRows; j++) {
			hours = Math.floor(rowTotals[j] / 60);
			mins = rowTotals[j] - (hours * 60);

			totalCell = document.getElementById("subtotal_row" + j);
			totalCell.innerHTML = '' + hours + "<?php echo JText::_('HR')?>" + '&nbsp;' + mins + "<?php echo JText::_('MN')?>";
		}

		hours = Math.floor(grandTotal / 60);
		mins = grandTotal - (hours * 60);

		totalCell = document.getElementById("grand_total");
		totalCell.innerHTML = '' + hours + "<?php echo JText::_('HR')?>" + '&nbsp;' + mins + "<?php echo JText::_('MN')?>";
	}

	function setDirty() {
		document.getElementById("saveButton").disabled = false;
	}

	function validate() {
		//get the total number of rows
		var totalRows = parseInt(document.getElementById('totalRows').value);

		//iterate through rows
		for (var i=0; i<totalRows; i++) {
			//iterate through cols
			for (var j=1; j<=7; j++) {
				hours = parseInt(document.getElementById("hours_row" + i + "_col" + j).value);
				mins = parseInt(document.getElementById("mins_row" + i + "_col" + j).value);
				if (isNaN(hours)) {
					hours = 0;
				}
				if (isNaN(mins)) {
					mins = 0;
				}

				var minsinday = parseInt(document.getElementById("minsinday_" + j).value);

				var minutes = hours * 60 + mins;

				if (minutes > minsinday) {
					alert("<?php echo JText::sprintf('TOO_MUCH_TIME_FOR_DAY', minsinday/60) ?>");
					document.getElementById("hours_row" + i + "_col" + j).value="";  //=true;
					document.getElementById("mins_row" + i + "_col" + j).value="";  //=true;
					document.getElementById("hours_row" + i + "_col" + j).select();  //=true;
					document.getElementById("mins_row" + i + "_col" + j).select();  //=true;
					document.getElementById("hours_row" + i + "_col" + j).select();  //=true;
					return false;
				}
			}
		}

		document.simpleForm.submit();
	}
	
	function CallBack_WithNewDateSelected(strDate) {
		validate();
	}

