<?php
if(!class_exists('Site'))die('Restricted Access');
// Authenticate

if(Auth::ACCESS_GRANTED != $this->requestPageAuth('aclECategories'))return;

//make sure the selected project is valid for this client
$client_id = gbl::getClientId();
if ($client_id != 0) {
	if (!Common::isValidProjectForClient($proj_id, $client_id))
		$proj_id = Common::getValidProjectForClient($client_id);
}

if (isset($_REQUEST['page']) && $_REQUEST['page'] != 0) { $page  = $_REQUEST['page']; } else { $page=1; };
$results_per_page = Common::getTaskItemsPerPage();
$start_from = ($page-1) * $results_per_page;

$query_categories = "SELECT cat_id, description FROM ". tbl::getExpenseCategoryTable() ;

function writePageLinks($page, $results_per_page, $num_task_page)
{
	//echo "num_task_page: ".$num_task_page.", results_per_page: ".$results_per_page.", page: ".$page;
	if (($num_task_page/$results_per_page) == (int)($num_task_page/$results_per_page))
		$numberOfPages = ($num_task_page/$results_per_page);
	else
		$numberOfPages = 1+(int)($num_task_page/$results_per_page);
	//echo $numberOfPages." =< 1 or ".$num_task_page." equals 0";
	if($numberOfPages > 1 && $num_task_page != 0)
	{
		//echo '<td width="16em" align="right">';
		if($page > 1)
			echo '<a href="javascript:change_page(\''.($page-1).'\')">Previous Page</a>';
		else
			echo 'Previous Page';
		echo ' / ';
		//echo '</td><td width="19em>"';
		if ($numberOfPages > $page)
		 	echo '<a href="javascript:change_page('.($page+1).')">Next Page</a>';
		else
			echo 'Next Page';
		echo ' ( <b>';
		echo $page." of ";
		echo $numberOfPages;
		echo '</b> )';
		//echo '</td>';
	}
}

PageElements::setHead("<title>".Config::getMainTitle()." | ".JText::_('EDIT_CATEGORIES')."</title>");
PageElements::setTheme('txsheet2');
?>

<h1> <?php echo (JText::_('EDIT_CATEGORIES')) ?> </h1>

<form name="editCategories" action="<?php echo Rewrite::getShortUri(); ?>" style="margin-bottom: 0px;">
<input type="hidden" name="page" />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		
		<td>
				<a href="cat_add"><?php echo (JText::_('ADD_NEW_CATEGORY')) ?></a>
		</td>
	</tr>
</table>
<div id ="simple">
<table class="simpleTable">
	<thead class="table_head">
		<tr>
			<th><?php echo JText::_('CATEGORY') ?></th>
			<th><?php echo JText::_('ACTIONS') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
list($qh, $num) = dbQuery($query_categories);
	//were there any results
	if ($num == 0) {
			print "	<tr>\n";
			print "		<td align=\"center\">\n";
			print "			<i><br />No Expense Categories have yet been defined<br /><br /></i>\n";
			print "		</td>\n";
			print "	</tr>\n";

	}
	else {
		//iterate through categories
		for ($j=0; $j<$num; $j++) {
			$data_cat = dbResult($qh);
			//start the row
			if(($j % 2) ==1)
				print "<tr class=\"diff\">\n";
			else
				print "<tr>\n";
?>
			<td class="calendar_cell_middle">
				<input type="hidden" name="catId" value="<?php echo $data_cat['cat_id']; ?>">
				<?php echo stripslashes($data_cat["description"]); ?>
			</td>		

			<td align="right" class="calendar_cell_middle">
				<a href="cat_edit?cat_id=<?php echo $data_cat["cat_id"]; ?>"><?php echo (JText::_('EDIT')) ?></a>,
				<a href="javascript:delete_task(<?php echo $data_cat["cat_id"]; ?>);"><?php echo (JText::_('DELETE')) ?></a>
			</td>
		</tr>

<?php
		}
	}
?>
			
</table>
</div>

</form>