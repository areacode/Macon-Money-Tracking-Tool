<?php
require_once("config.inc");
require_once("date_ranger.php");

$result_zip = sqlQuery("select * from zipcode", $mySql);
$result_event = sqlQuery("select id, name from events", $mySql);
$result_disttype = sqlQuery("select * from handout_situation", $mySql);
$result_rdmptype = sqlQuery("select * from redeemed_situation", $mySql);
$zip = $rdmp_ids = $zip2_clause = $rdmp_where_clause_suffix = $dist_where_clause_suffix = null;
$day_from = 1;
$day_to = @date("d");
$month_from = $month_to = @date("m");
$year_from = $year_to = @date("Y");
$disttypes = $rdmptypes = $filters = $redemption_event_ids = $distribution_event_ids = $redemption_type_ids = $distribution_type_ids = $result_rdmp = $result_dist = array();
foreach($result_disttype as $disttype)
	$disttypes[$disttype['situation']] = $disttype['id']; 
foreach($result_rdmptype as $rdmptype)
	$rdmptypes[$rdmptype['situation']] = $rdmptype['id']; 
if(isset($_POST['submit']) && isset($_POST['filters'])) {
	$filters = $_POST['filters'];
	$zip = $_POST['zip'];
	$distribution_type_ids = isset($_POST['distribution_type_ids']) ? $_POST['distribution_type_ids'] : $distribution_type_ids;
	$redemption_type_ids = isset($_POST['redemption_type_ids']) ? $_POST['redemption_type_ids'] : $redemption_type_ids;
	$distribution_event_ids = isset($_POST['distribution_event_ids']) ? $_POST['distribution_event_ids'] : $distribution_event_ids;
	$redemption_event_ids = isset($_POST['redemption_event_ids']) ? $_POST['redemption_event_ids'] : $redemption_event_ids;
	$day_from = $_POST['date_from'] ? $_POST['date_from'] : 1;
	$day_to = $_POST['date_to'] ? $_POST['date_to'] : @date("d");
	$month_from = $_POST['month_from'] ? $_POST['month_from'] : @date("m");
	$month_to = $_POST['month_to'] ? $_POST['month_to'] : @date("m");
	$year_from = $_POST['year_from'] ? $_POST['year_from'] : @date("Y");
	$year_to = $_POST['year_to'] ? $_POST['year_to'] : @date("Y");
	
	if(in_array("date", $filters) && $date_from > $date_to)
		$errors[] = "The end date cannot precede the start date.";
	if(in_array("distribution_types", $filters)) {
		if(count($distribution_type_ids) == 0)
			$errors[] = "You must select at least one distribution type if you are using the distribution type filter.";
		else if(in_array($disttypes['event'], $distribution_type_ids) && count($distribution_event_ids) == 0)
			$errors[] = "You must select at least one event if the event distribution type is selected.";
		else if(!in_array($disttypes['event'], $distribution_type_ids) && count($distribution_event_ids) > 0)
			$errors[] = "You cannot select distribution events without selecting the event distribution type.";
	}
	if(in_array("redemption_types", $filters)) {
		if(count($redemption_type_ids) == 0)
			$errors[] = "You must select at least one redemption type if you are using the redemption type filter.";
		else if(in_array($rdmptypes['event'], $redemption_type_ids) && count($redemption_event_ids) == 0)
			$errors[] = "You must select at least one event if the event redemption type is selected.";
		else if(!in_array($rdmptypes['event'], $redemption_type_ids) && count($redemption_event_ids) > 0)
			$errors[] = "You cannot select redemption events without selecting the event redemption type.";
	}
	if(count($errors) == 0) {
		$qry_distributed = "
			select distinct bt.serial_number, date_distributed, date_redeemed 
			from bond_tracking as bt, bonds as b
			where b.serial_number = bt.serial_number
			and date_distributed is not null %s
		";
		if(in_array("zip", $filters) && $zip) {
			$zip_result = sqlQuery("select zip from zipcode where id = $zip LIMIT 1", $mySql);
			$dist_where_clause_suffix .= " AND bt.zip_code " . ($zip == -1 ? " IS NULL " : " = '{$zip_result[0]['zip']}'");
		}
		if(in_array("date", $filters))
			$dist_where_clause_suffix .= " AND bt.date_distributed >= '$date_from 00:00:00' AND bt.date_distributed <= '$date_to 23:59:59' ";
		if(in_array("distribution_types", $filters)) {
			$dist_where_clause_suffix .= " AND (";
			foreach($distribution_type_ids as $id)
				$dist_where_clause_suffix .= $disttypes['event']==$id ?
					" (bt.handout_situation LIKE 'event' AND bt.handout_event_id IN (" . implode(",", $distribution_event_ids) . ")) OR " :
					" bt.handout_situation LIKE '" . array_search($id, $disttypes) . "' OR "; 
			$dist_where_clause_suffix = substr($dist_where_clause_suffix, 0, -3) . ") "; //trim "OR "
		}	
		if(in_array("redemption_types", $filters)) {
			$dist_where_clause_suffix .= " AND (";
			foreach($redemption_type_ids as $id)
				$dist_where_clause_suffix .= $rdmptypes['event']==$id ?
					" (bt.redeemed_situation LIKE 'event' AND bt.redeemed_event_id IN (" . implode(",", $redemption_event_ids) . ")) OR " :
					" bt.redeemed_situation LIKE '" . array_search($id, $rdmptypes) . "' OR "; 
			$dist_where_clause_suffix = substr($dist_where_clause_suffix, 0, -3) . ")"; //trim "OR "
		}	
		$result_dist = sqlQuery(sprintf($qry_distributed, $dist_where_clause_suffix) . " order by bt.serial_number asc ", $mySql);
		$unredeemed = $redeemed = $avg_duration = 0;
		$durations = array();
		foreach($result_dist as $dist) {
			if(empty($dist['date_redeemed']))
				$unredeemed++;
			else {
				$redeemed++;
				$durations[] = @strtotime($dist['date_redeemed']) - @strtotime($dist['date_distributed']); 
			}
		}
		if(count($durations) > 0)
			$avg_duration = floor((array_sum($durations) / count($durations)) / 86400);
	}
}
?>
<html>
<head><?php include("includes/css.inc")?></head>
<body>
<?php include("includes/header.inc")?>
<?php include("includes/reports_nav.inc")?>
<?php displayErrors($errors); ?><h2>Bond Redemption Rates</h2>
<form method="post" name="f" action="reports_rates.php">
<table>
<tr align="center"><td colspan="3">Select Filters</td></tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="date" <?php if (empty($_REQUEST['submit']) || in_array('date', $filters)) echo " checked"?> /></td>
	<td>Date</td>
	<td>
		Date From:<br />
		<?php $ddlFrom->genYearDDL("year_from", @date("Y")-2010, @date("Y"), $year_from)?>/<?php $ddlFrom->genDayDDL("date_from", $day_from)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $month_from)?><br />
		Date To:<br /> 
		<?php $ddlFrom->genYearDDL("year_to", @date("Y")-2010, @date("Y"), $year_to)?>/<?php $ddlFrom->genDayDDL("date_to", $day_to)?>/<?php $ddlFrom->genMonthDDL("month_to", "short", $month_to)?>
	</td>
</tr>
<tr>
	<td><input type="checkbox" id="zip_checkbox" name="filters[]" value="zip" <?php if (in_array('zip', $filters)) echo " checked"?> /></td>
	<td>Zip</td>
	<td>
		<?php 
			$temp = "zip";
			getZipList($result_zip, $$temp, "Distributed Bond");
		?>
	</td>
</tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="distribution_types" <?php if (in_array('distribution_types', $filters)) echo " checked"?> /></td>
	<td>Distribution Types</td>
	<td> 
		<table><tr>
		<td>
		<font size="1">Choose distribution type(s):</font><br />
		<select multiple size="<?php echo min(count($result_disttype), 4)?>" name="distribution_type_ids[]">
			<?php foreach($result_disttype as $disttype) { ?>
				<option value="<?php echo $disttype['id']?>" <?php echo in_array($disttype['id'], $distribution_type_ids) ? " selected" : ""?>><?php echo $disttype['situation']; ?></option>
			<?php } ?>
		</select>
		</td>
		<td>
		<font size="1">Choose events (if event type is selected):</font><br />
		<select multiple name="distribution_event_ids[]">
			<?php foreach($result_event as $event) { ?>
				<option value="<?php echo $event['id']?>" <?php echo in_array($event['id'], $distribution_event_ids) ? " selected" : ""?>><?php echo $event['name']; ?></option>
			<?php } ?>
		</select>
		</td>
		</tr>
		<tr><td colspan="2" align="center"><font size="1">Ctrl-click to unselect</font></td></tr>
		</table>
	</td>
</tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="redemption_types" <?php if (in_array('redemption_types', $filters)) echo " checked"?> /></td>
	<td>Redemption Types</td>
	<td>
		<table><tr>
		<td>
		<font size="1">Choose redemption type(s):</font><br />
		<select multiple size="<?php echo min(count($result_rdmptype), 4)?>" name="redemption_type_ids[]">
			<?php foreach($result_rdmptype as $rdmptype) { ?>
				<option value="<?php echo $rdmptype['id']?>" <?php echo in_array($rdmptype['id'], $redemption_type_ids) ? " selected" : ""?>><?php echo $rdmptype['situation']; ?></option>
			<?php } ?>
		</select>		
		</td>
		<td>
		<font size="1">Choose events (if event type is selected):</font><br />
		<select multiple name="redemption_event_ids[]">
			<?php foreach($result_event as $event) { ?>
				<option value="<?php echo $event['id']?>" <?php echo in_array($event['id'], $redemption_event_ids) ? " selected" : ""?>><?php echo $event['name']; ?></option>
			<?php } ?>
		</select>
		</td>
		</tr>
		<tr><td colspan="2" align="center"><font size="1">Ctrl-click to unselect</font></td></tr>	
		</table>
	</td>
</tr>
<tr align="center"><td colspan="3"><input type="submit" name="submit" value="Submit" /></td></tr>
</table>
</form>
<?php if(isset($_POST['submit']) && count($errors) == 0) { ?>
	<table width="540">
		<tr><td>Bonds Distributed:</td><td><?php echo $redeemed + $unredeemed?></td></tr>
		<tr><td>Bonds Unredeemed:</td><td><?php echo $unredeemed?></td></tr>
		<tr><td>Bonds Redeemed:</td><td><?php echo $redeemed?></td></tr>
		<tr><td>Redemption Rate:</td><td><?php echo floor($redeemed / ($redeemed + $unredeemed) * 100)?>%</td></tr>
		<tr><td>Average Time Until Redemption</td><td><?php echo $avg_duration == 0 ? "< 1" : $avg_duration?> day<?php if ($avg_duration > 1) echo "s"?></td></tr>	
	</table>
<?php } ?>
</body></html>