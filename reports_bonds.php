<?php
require_once("config.inc");
require_once("date_ranger.php");

$result_zip = sqlQuery("select * from zipcode", $mySql);
$result_staff = sqlQuery("select id, first_name, last_name from staff", $mySql);
$result_event = sqlQuery("select id, name from events", $mySql);
$result_disttype = sqlQuery("select * from handout_situation", $mySql);
$result_rdmptype = sqlQuery("select * from redeemed_situation", $mySql);
$zip1 = $zip2 = $is_redeemed = $is_distributed = $staff_id = $symbol1 = $symbol2 = $symbol3 = $rdmp_ids = $zip2_clause = $rdmp_where_clause_suffix = $dist_where_clause_suffix = null;
$day_from = 1;
$day_to = @date("d");
$month_from = $month_to = @date("m");
$year_from = $year_to = @date("Y");
$disttypes = $rdmptypes = $filters = $redemption_event_ids = $distribution_event_ids = $redemption_type_ids = $distribution_type_ids = $redemption_type_ids_dist_only = $distribution_type_ids_rdmp_only = $result_rdmp = $result_dist = array();
foreach($result_disttype as $disttype)
	$disttypes[$disttype['situation']] = $disttype['id']; 
foreach($result_rdmptype as $rdmptype)
	$rdmptypes[$rdmptype['situation']] = $rdmptype['id']; 
if(isset($_POST['submit']) && isset($_POST['filters'])) {
	$filters = $_POST['filters'];
	$zip1 = $_POST['zip1'];
	$zip2 = $_POST['zip2'];	
	$symbol1 = $_POST['symbol1'];
	$symbol2 = $_POST['symbol2'];
	$symbol3 = $_POST['symbol3'];
	$staff_id = $_POST['staff_id'];
	$is_redeemed = isset($_POST['is_redeemed']) ? $_POST['is_redeemed'] : $is_redeemed;
	$is_distributed = isset($_POST['is_distributed']) ? $_POST['is_distributed'] : $is_distributed;
	$distribution_type_ids = isset($_POST['distribution_type_ids']) ? $_POST['distribution_type_ids'] : $distribution_type_ids;
	$distribution_type_ids_rdmp_only = isset($_POST['distribution_type_ids_rdmp_only']) ? $_POST['distribution_type_ids_rdmp_only'] : $distribution_type_ids_rdmp_only;
	$redemption_type_ids = isset($_POST['redemption_type_ids']) ? $_POST['redemption_type_ids'] : $redemption_type_ids;
	$redemption_type_ids_dist_only = isset($_POST['redemption_type_ids_dist_only']) ? $_POST['redemption_type_ids_dist_only'] : $redemption_type_ids_dist_only;
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
		else if($is_redeemed && count($redemption_type_ids_dist_only) == 0)
			$errors[] = "You must select a redemption type(s) if searching for redeemed distributed bonds .";
	}
	if(in_array("redemption_types", $filters)) {
		if(count($redemption_type_ids) == 0)
			$errors[] = "You must select at least one redemption type if you are using the redemption type filter.";
		else if(in_array($rdmptypes['event'], $redemption_type_ids) && count($redemption_event_ids) == 0)
			$errors[] = "You must select at least one event if the event redemption type is selected.";
		else if(!in_array($rdmptypes['event'], $redemption_type_ids) && count($redemption_event_ids) > 0)
			$errors[] = "You cannot select redemption events without selecting the event redemption type.";
		else if($is_distributed && count($distribution_type_ids_rdmp_only) == 0)
			$errors[] = "You must select a distribution type(s) if searching for distributed redeemed bonds .";
	}
	if(count($errors) == 0) {
		if(in_array("zip", $filters) && $zip1) {
			$zip1_result = sqlQuery("select zip from zipcode where id = $zip1 LIMIT 1", $mySql);
			$dist_where_clause_suffix .= " AND bt.zip_code " . ($zip1 == -1 ? " IS NULL " : " = '{$zip1_result[0]['zip']}'");
			$qry_redeemed_zip = "
				select distinct bt.serial_number
				from bond_tracking as bt
				inner join bonds as b on b.serial_number = bt.serial_number
				inner join prizes as p on bt.serial_number = p.bond_1_serial_number
				inner join bond_tracking as bt2 on p.bond_2_serial_number = bt2.serial_number
				where (%s AND %s)
				UNION
				select distinct bt.serial_number 
				from bond_tracking as bt
				inner join bonds as b on b.serial_number = bt.serial_number
				inner join prizes as p on bt.serial_number = p.bond_2_serial_number
				inner join bond_tracking as bt2 on p.bond_1_serial_number = bt2.serial_number
				where (%s AND %s) ";
			$rdmp_suffix1 = " bt.redeemer_zip " . ($zip1 == -1 ? " is null ": " like '{$zip1_result[0]['zip']}' ");
			if($zip2) {
				$qry_redeemed_zip .= " UNION " . $qry_redeemed_zip;
				$zip2_result = sqlQuery("select zip from zipcode where id = $zip2 LIMIT 1", $mySql);
				$rdmp_suffix2 = " bt2.redeemer_zip " . ($zip2 == -1 ? " is null ": " like '{$zip2_result[0]['zip']}' ");
				$rdmp_suffix3 = " bt.redeemer_zip " . ($zip2 == -1 ? " is null ": " like '{$zip2_result[0]['zip']}' ");
				$rdmp_suffix4 = " bt2.redeemer_zip " . ($zip1 == -1 ? " is null ": " like '{$zip1_result[0]['zip']}' ");
				$query = sprintf($qry_redeemed_zip, $rdmp_suffix1, $rdmp_suffix2, $rdmp_suffix1, $rdmp_suffix2, $rdmp_suffix3, $rdmp_suffix4, $rdmp_suffix3, $rdmp_suffix4);
			} else
				$query = sprintf($qry_redeemed_zip, $rdmp_suffix1, "TRUE", $rdmp_suffix1, "TRUE");
			$temp_result = sqlQuery($query, $mySql);
			$temp = "'";
			foreach($temp_result as $temp_rdmp)
				$temp .= $temp_rdmp['serial_number'] . "','";
			$rdmp_where_clause_suffix = " AND " . ($temp!="'" ? " bt.serial_number IN (" . substr($temp, 0, -2) . ")" : " false ");
		}
		$qry_distributed = "
			select distinct bt.serial_number 
			from bond_tracking as bt, bonds as b
			where b.serial_number = bt.serial_number
			and date_distributed is not null %s
		";
		$qry_redeemed = "
			select distinct bt.serial_number 
			from bond_tracking as bt, bonds as b, prizes as p
			where b.serial_number = bt.serial_number
			AND (bt.serial_number = p.bond_1_serial_number OR bt.serial_number = p.bond_2_serial_number) %s
		";
		if(in_array("date", $filters)) {
			$rdmp_where_clause_suffix .= " AND bt.date_redeemed >= '$date_from 00:00:00' AND bt.date_redeemed <= '$date_to 23:59:59' ";
			$dist_where_clause_suffix .= " AND bt.date_distributed >= '$date_from 00:00:00' AND bt.date_distributed <= '$date_to 23:59:59' ";
		}
		if(in_array("staff", $filters)) {
			$rdmp_where_clause_suffix .= " AND bt.staff_id_redeemed " . ($staff_id ? " = $staff_id " : " is null ");
			$dist_where_clause_suffix .= " AND bt.staff_id_handout " . ($staff_id ? " = $staff_id " : " is null ");
		}
		if(in_array("distribution_types", $filters)) {
			$dist_where_clause_suffix .= " AND (";
			foreach($distribution_type_ids as $id)
				$dist_where_clause_suffix .= $disttypes['event']==$id ?
					" (bt.handout_situation LIKE 'event' AND bt.handout_event_id IN (" . implode(",", $distribution_event_ids) . ")) OR " :
					" bt.handout_situation LIKE '" . array_search($id, $disttypes) . "' OR "; 
			$dist_where_clause_suffix = substr($dist_where_clause_suffix, 0, -3) . ") "; //trim "OR "
			if($is_redeemed) {
				$dist_where_clause_suffix .= ' AND bt.is_redeemed = 1 AND (';
				foreach($redemption_type_ids_dist_only as $id) 
					$dist_where_clause_suffix .= " bt.redeemed_situation LIKE '" . array_search($id, $rdmptypes) . "' OR ";
				$dist_where_clause_suffix = substr($dist_where_clause_suffix, 0, -3) . ") "; //trim "OR "
			}
		}	
		if(in_array("redemption_types", $filters)) {
			$rdmp_where_clause_suffix .= " AND (";
			foreach($redemption_type_ids as $id)
				$rdmp_where_clause_suffix .= $rdmptypes['event']==$id ?
					" (bt.redeemed_situation LIKE 'event' AND bt.redeemed_event_id IN (" . implode(",", $redemption_event_ids) . ")) OR " :
					" bt.redeemed_situation LIKE '" . array_search($id, $rdmptypes) . "' OR "; 
			$rdmp_where_clause_suffix = substr($rdmp_where_clause_suffix, 0, -3) . ")"; //trim "OR "
			if($is_distributed) {
				$rdmp_where_clause_suffix .= ' AND bt.date_distributed IS NOT NULL AND (';
				foreach($distribution_type_ids_rdmp_only as $id) 
					$rdmp_where_clause_suffix .= " bt.handout_situation LIKE '" . array_search($id, $disttypes) . "' OR ";
				$rdmp_where_clause_suffix = substr($rdmp_where_clause_suffix, 0, -3) . ") "; //trim "OR "
			}
		}	
		if(in_array("symbol", $filters)) {
			if($symbol1) {
				$rdmp_where_clause_suffix .= " AND b.symbol_1 = '" . base64_decode($symbol1) . "' ";
				$dist_where_clause_suffix .= " AND b.symbol_1 = '" . base64_decode($symbol1) . "' ";
			}
			if($symbol2) {
				$rdmp_where_clause_suffix .= " AND b.symbol_2 = '" . base64_decode($symbol2) . "' ";
				$dist_where_clause_suffix .= " AND b.symbol_2 = '" . base64_decode($symbol2) . "' ";
			}
			if($symbol3) {
				$rdmp_where_clause_suffix .= " AND b.symbol_3 = '" . base64_decode($symbol3) . "' ";
				$dist_where_clause_suffix .= " AND b.symbol_3 = '" . base64_decode($symbol3) . "' ";
			}
		}
		$result_dist = sqlQuery(sprintf($qry_distributed, $dist_where_clause_suffix) . " order by bt.serial_number asc ", $mySql);
		$result_rdmp = sqlQuery(sprintf($qry_redeemed, $rdmp_where_clause_suffix) . " order by bt.serial_number asc ", $mySql);
	}
}
?>
<html>
<head>
<?php include("includes/css.inc")?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (document.getElementById("zip_checkbox").checked && zip1.value == 0 && zip2.value != 0) {
  	  		alert("You must select something besides 'any zip' for Bond #1.");
	  		return false;
	  	} else if (document.getElementById("zip_checkbox").checked && zip1.value == 0 && zip2.value != 0) {
  	  		alert("You must select something besides 'any zip' for Bond #1.");
	  		return false;
	  	}
  	}
	return true;
}
</script>
</head>
<body>
<?php include("includes/header.inc")?>
<?php include("includes/reports_nav.inc")?>
<?php displayErrors($errors); ?><h2>Bond Reporting</h2>
<form method="post" name="f" action="reports_bonds.php">
<table>
<tr align="center"><td colspan="3">Select Filters</td></tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="date" <?php if (empty($_REQUEST['submit']) || in_array('date', $filters)) echo " checked"?> /></td>
	<td>Date</td>
	<td>
	Date From:<br />
	<?php $ddlFrom->genYearDDL("year_from", @date("Y")-2010, @date("Y"), $year_from)?>/<?php $ddlFrom->genDayDDL("date_from", $day_from)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $month_from)?>
	<br />
	Date To:<br /> 
	<?php $ddlFrom->genYearDDL("year_to", @date("Y")-2010, @date("Y"), $year_to)?>/<?php $ddlFrom->genDayDDL("date_to", $day_to)?>/<?php $ddlFrom->genMonthDDL("month_to", "short", $month_to)?></td>
</tr>
<tr>
	<td><input type="checkbox" id="zip_checkbox" name="filters[]" value="zip" <?php if (in_array('zip', $filters)) echo " checked"?> /></td>
	<td>Zip</td>
	<td>
		<?php 
			for($i=1; $i<=2; $i++) {
				$temp = "zip$i";
				getZipList($result_zip, $$temp, "Bond ", true, $i);
			}
		?>
		<font size="1"><i>Note: Bond 2 is only considered when calculating redemptions, not distributions.</i></font>
	</td>
</tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="staff" <?php if (in_array('staff', $filters)) echo " checked"?> /></td>
	<td>Staff</td>
	<td> 
		<select name="staff_id">
			<option value="0">No staff associated</option>
			<?php foreach($result_staff as $staff) { ?>
				<option value="<?php echo $staff['id']?>" <?php echo $staff['id']==$staff_id ? " selected" : ""?>><?php echo $staff['first_name'] . " " . $staff['last_name']; ?></option>
			<?php } ?>
		</select>
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
		<tr>
			<td align="left">Redeemed bonds only?
				<input type="checkbox" name="is_redeemed" value="1" <?php echo $is_redeemed ? " checked" : ""?> />
			</td>
			<td>
				<font size="1">Choose redemption type(s):</font><br />
				<select multiple size="<?php echo min(count($result_rdmptype), 4)?>" name="redemption_type_ids_dist_only[]">
					<?php foreach($result_rdmptype as $rdmptype) { ?>
						<option value="<?php echo $rdmptype['id']?>" <?php echo in_array($rdmptype['id'], $redemption_type_ids_dist_only) ? " selected" : ""?>><?php echo $rdmptype['situation']; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
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
		<tr>
			<td align="left">Distributed bonds only?
				<input type="checkbox" name="is_distributed" value="1" <?php echo $is_distributed ? " checked" : ""?> />
			</td>
			<td>
				<font size="1">Choose distribution type(s):</font><br />
				<select multiple size="<?php echo min(count($result_disttype), 4)?>" name="distribution_type_ids_rdmp_only[]">
					<?php foreach($result_disttype as $disttype) { ?>
						<option value="<?php echo $disttype['id']?>" <?php echo in_array($disttype['id'], $distribution_type_ids_rdmp_only) ? " selected" : ""?>><?php echo $disttype['situation']; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td><input type="checkbox" name="filters[]" value="symbol" <?php if (in_array('symbol', $filters)) echo " checked"?> /></td>
	<td>Symbol</td>
	<td>
		<?php
			for($i=1; $i<=3; $i++) {
				$temp = "symbol$i";
				getSymbolList(sqlQuery("select distinct symbol_$i as name from bonds order by name asc", $mySql), $i, $$temp);
			}
		?>	
	</td>
</tr>
<tr align="center"><td colspan="3"><input type="submit" name="submit" value="Submit" onClick="return validate_form(document.f);" /></td></tr>
</table>
</form>
<?php if(isset($_POST['submit']) && count($errors) == 0) { ?>
<table width="800"><tr>
<td valign="top">
<table width="400">
	<tr><td width="100">Total Redemptions:</td><td><?php echo count($result_rdmp)?></td></tr>
	<tr>
		<td>Serials Redeemed:</td>
		<td><?php foreach($result_rdmp as $rdmp)
				echo $rdmp['serial_number'] . ($rdmp==$result_rdmp[count($result_rdmp)-1] ? "" : ", ")?>
		</td>
	</tr>
</table>
</td>
<td valign="top">
<table width="400">
	<tr><td width="100">Total Distributions:</td><td><?php echo count($result_dist)?></td></tr>
	<tr>
		<td>Serials Distributed:</td>
		<td><?php foreach($result_dist as $dist)
				echo $dist['serial_number'] . ($dist==$result_dist[count($result_dist)-1] ? "" : ", ")?>
		</td>
	</tr>
</table>
</td>
</tr></table>
<?php } ?>
</body></html>
<?php 

function getSymbolList($result, $i, $val) { ?>
		#<?php echo $i?>: <select name="symbol<?php echo $i?>">
			<option value="0">any symbol</option>
			<?php foreach($result as $symbol) { ?>
				<option value="<?php echo base64_encode($symbol['name'])?>" <?php echo base64_encode($symbol['name'])==$val ? " selected" : ""?>><?php echo $symbol['name']?></option>
			<?php } ?>
		</select>
<?php } ?>