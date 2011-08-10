<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclSimple'))return;

//load local vars from request/post/get
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
$cloneFrom = isset($_REQUEST['cloneFrom']) ? $_REQUEST['cloneFrom'] : "";
$cloneTo = isset($_REQUEST['cloneTo']) ? $_REQUEST['cloneTo'] : "";
$projary = isset($_REQUEST['proj']) ? $_REQUEST['proj'] : array();

if($cloneFrom=="0") $cloneFrom='';
if($cloneTo=="0") $cloneTo='';

//$cfs & $cts used when printing text, when one or the other may not have been selected yet
if($cloneFrom=='') 
	$cfs="??"; 
else 
	$cfs = $cloneFrom; 
if($cloneTo=='') 
	$cts="??"; 
else 
	$cts = $cloneTo; 

if($action!='performCopy') {

} else {
	Site::getCommandMenu()->add(new TextCommand(JText::_('BACK'), true, "$_SERVER[PHP_SELF]?cloneFrom=$cloneFrom&amp;cloneTo=$cloneTo"));
}

//LogFile::->write("status = \"$status\"  isActive=\"".$_REQUEST["isActive"]."\"\n");

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('COPY_HDG')." | ".gbl::getContextUser()."</title>");
ob_start();
?>
	<script type="text/javascript">
	<!--
	//gets a DOM object by it's name
	function getObjectByName(sName){				
		if(document.getElementsByName){
			oElements = document.getElementsByName(sName);

			if(oElements.length > 0)
				return oElements[0];
			else
				return null;
		}
		else if(document.all)
			return document.all[sName][0];
		else if(document.layers)
			return document.layers[sName][0];
		else
			return null;
	}
		//run init function on window load
		window.onload = init;

		//apply auto-submit behaviour when changing date values
		function init() {
			var clone_from  = getObjectByName('cloneFrom');
			var clone_to    = getObjectByName('cloneTo');

			if(clone_from == null) return;
			clone_from.onchange  = function (){document.userForm.submit();};
			clone_to.onchange    = verify_can_copy;

			verify_can_copy();
		}

		function verify_can_copy() {
				var clone_from   = getObjectByName('cloneFrom');
				var clone_to     = getObjectByName('cloneTo');
				var clone_button = getObjectByName('cloneUser');

				if (clone_from.value == "0" || clone_to.value == "0" || clone_from.value == clone_to.value)
					clone_button.disabled=true;
				else
					clone_button.disabled=false;
		}


		function onClone() {
			//validation
			var clone_from  = getObjectByName('cloneFrom');
			var clone_to    = getObjectByName('cloneTo');
			if (clone_from.value == "0")
				alert("You must select a user FROM which to copy the assignments");
			else {
				if (clone_to.value == "0")
					alert("You must select a user to copy the assignments TO");
				else {
					var action = getObjectByName('action');
					action.value="performCopy";
					document.userForm.submit();
				}
			}
		}
	//-->
	</script>
<?php 
PageElements::setHead(PageElements::getHead().ob_get_contents());
PageElements::setTheme('newcss');
ob_end_clean();
print "</div>";

//print "Action: $action<br />";
//print_r($projary);

if($action!='performCopy') {
//==========================================================================================
//we need to display the copy setup form
//==========================================================================================
?>
<form action="<?php echo Config::getRelativeRoot(); ?>/users/user_clone" name="userForm" method="post">
<input type="hidden" name="action" id="action" value="" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">


				<table width="100%" border="0">
					<tr>
						<td align="left" class="outer_table_heading">
						<?php
								print JText::_('COPY_HDG'). " &nbsp; &nbsp;" . JText::_('CFROM'); 
						
							Common::single_user_select_list('cloneFrom',$cloneFrom,'',true);
						
								print "&nbsp; &nbsp; &nbsp;" . JText::_('CTO'); 
					
							Common::single_user_select_list('cloneTo',$cloneTo,'',true);
						?>
						</td>
						<td align="right" class="outer_table_heading">
						<?php
							echo "&nbsp; &nbsp;<input type=\"button\" name=\"cloneUser\" value=\"" . JText::_('COPY_ASSG') . "\" onclick=\"javascript:onClone()\" disabled=\"disabled\" class=\"bottom_panel_button\" /> ";
						?>
						</td>
					</tr>
				</table>

	<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="outer_table">
		<tr>
			<td>
			<?php 
				print JText::_('COPY_MSG1'). $cfs. $cts ."<br>" . JText::_('COPY_MSG2'). $cts . "<br />"; 
			?>
			</td>
		</tr>
	</table>
	<table>
		<thead>
			<tr>
				<th><?php print JText::_('SELECT'); ?></</td>
				<th><?php print JText::_('PROJECT'); ?></td>
				<th><?php print JText::_('TASKS'); ?></td>
			</tr>
		</thead>
		<tbody>
<?php
	function get_task_name($task_id) {
		$sql = "SELECT name FROM ".tbl::getTaskTable()." WHERE task_id=$task_id";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['name'];
	}

	$proj_list = array();

	list($qh,$num) = dbQuery(" SELECT * FROM ".tbl::getAssignmentsTable()." WHERE username = '$cloneFrom' AND  proj_id!=1");
	while ($data = dbResult($qh)) {
		$p_id=$data['proj_id'];
		$p_name=Common::get_project_name($p_id);
		$proj_list[$p_name]=$p_id;
	}
	ksort($proj_list);

	foreach($proj_list as $p_name => $p_id) {
		print "<tr><td><input type=\"checkbox\" name=\"proj[]\" id=\"proj_$p_id\" value=\"$p_id\" CHECKED />&nbsp;</td>\n";
		//print "<tr><td><input type=\"checkbox\" name=\"proj[]\" id=\"proj_$p_id\" value=\"$p_id\"";
		//if(in_array($p_id,$projary)) echo ' CHECKED';
		//print " />&nbsp;</td>\n";

		print "<td>$p_name</td><td>";

		$sql = "SELECT * FROM  ".tbl::getTaskAssignmentsTable()." WHERE username ='$cloneFrom' AND task_id!=1 AND proj_id=$p_id";
		list($qh2,$num2) = dbQuery($sql);

		$ntasks=0;
		while ($data2 = dbResult($qh2)) {
			$t_id=$data2['task_id'];
			$t_name=get_task_name($t_id);
			if($ntasks>0)
				print ", ";
			print "$t_name";
			$ntasks++;
		}
		print "</td></tr>\n";
	}
?>
				</table>
			</td>
		</tr>
	</table>


		</td>
	</tr>
</table>

</form>
<?php
} else {  
//==========================================================================================
//we need to actually perform the copy
//==========================================================================================
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" class="face_padding_cell">

				<table width="100%" border="0">
					<tr>
						<td align="left" class="outer_table_heading">
														<?php 
								print JText::_('COPY_HDG'). $cloneFrom . $cloneTo; 
							?>
						</td>
				</tr>
				<tr>
				<td>
			<?php 
				print JText::_('COPYING_MSG1'). $cloneFrom. $cloneTo ."<br>" . JText::_('COPYING_MSG2'); 
			?>
			</td>
					</tr>
				</table>

	<table>
		<thead>
			<tr>
				<th><?php JText::_('PROJECT'); ?></td>
				<th><?php JText::_('OPERATION'); ?></td>
				<th><?php JText::_('TASKS'); ?></td>
			</tr>
			</thead>
			<tbody>
<?php
	function get_task_name($task_id) {
		$sql = "SELECT name FROM ".tbl::getTaskTable()." WHERE task_id=$task_id";
		list($my_qh, $num) = dbQuery($sql);
		$result = dbResult($my_qh);
		return $result['name'];
	}

	$proj_list = array();
	list($qh,$num) = dbQuery(" SELECT * FROM ".tbl::getAssignmentsTable()." WHERE username = '$cloneTo' AND  proj_id!=1");
	while ($data = dbResult($qh)) {
		$p_id=$data['proj_id'];
		$proj_list[]=$p_id;
	}

	foreach($projary as $p_id) {
		$task_list = array();
		$cf_task_list = array();
		$ignored=0;
		$added=0;
		$ignstr='';
		$addstr='';
		$p_name = Common::get_project_name($p_id);
		print "<tr><td>$p_name</td><td> ";
		if(in_array($p_id,$proj_list)) { //if user is already a member of the project, we must check task assignments...
			$sql = "SELECT * FROM  ".tbl::getTaskAssignmentsTable()." WHERE username ='$cloneTo' AND proj_id=$p_id";
			list($qh,$num) = dbQuery($sql);
			while ($data = dbResult($qh)) {
				$t_id=$data['task_id'];
				$task_list[]=$t_id;
			}
			print JText::_('ADDED') . "</td><td>";
		} else {
			//dbquery("INSERT INTO ".tbl::getAssignmentsTable()." VALUES ('$p_id','$cloneTo', 1)");
			print JText::_('EXISTING') . "</td><td>";
		}

		$sql = "SELECT * FROM  ".tbl::getTaskAssignmentsTable()." WHERE username ='$cloneFrom' AND proj_id=$p_id";
		list($qh,$num) = dbQuery($sql);
		while ($data = dbResult($qh)) {
			$t_id=$data['task_id'];
			$cf_task_list[]=$t_id;
		}

		foreach($cf_task_list as $t_id) {
			if(in_array($t_id,$task_list)) {
				if($ignored>0)
					$ignstr.=", ";
				$ignored++;
				$ignstr .= get_task_name($t_id);
			} else {
				//dbquery("INSERT INTO ".tbl::getTaskAssignmentsTable()." VALUES ('$t_id','$cloneTo','$p_id')");
				if($added>0)
					$addstr.=", ";
				$added++;
				$addstr .= get_task_name($t_id);
			}
		}
		print  JText::_('IGNORED') . "$ignored: $ignstr<br />";
		print  JText::_('ADDED') . "&nbsp; $added: $addstr</td>";
		print "</tr>";
	}
?>
				</table>
			</td>
		</tr>
	</table>


		</td>
	</tr>
</table>

<?php
} 
//==========================================================================================
//  End of form or perform copy if statement
//==========================================================================================

?>