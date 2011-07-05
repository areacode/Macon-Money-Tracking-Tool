<?php
require_once("config.inc");
require_once("date_ranger.php");

$result_zip = sqlQuery("select distinct zip from business order by zip asc", $mySql);
$result_biz = sqlQuery("select id, business_name from business order by business_name asc", $mySql);
$zip = $qry_rdmp_suffix = $qry_chk_suffix = $date_filter = $total = null;
$filters = $biz_ids = $bizs = $checks = $rdmps = array();
$day_from = 1;
$day_to = @date("d");
$month_from = $month_to = @date("m");
$year_from = $year_to = @date("Y");

if(isset($_POST['submit']) && isset($_POST['filters'])) {
	$filters = $_POST['filters'];
	$zip = $_POST['zip'];
	$biz_ids = isset($_POST['biz_ids']) ? $_POST['biz_ids'] : $biz_ids;
	$day_from = $_POST['date_from'] ? $_POST['date_from'] : 1;
	$day_to = $_POST['date_to'] ? $_POST['date_to'] : @date("d");
	$month_from = $_POST['month_from'] ? $_POST['month_from'] : @date("m");
	$month_to = $_POST['month_to'] ? $_POST['month_to'] : @date("m");
	$year_from = $_POST['year_from'] ? $_POST['year_from'] : @date("Y");
	$year_to = $_POST['year_to'] ? $_POST['year_to'] : @date("Y");
	if(in_array("date", $filters) && $date_to < $date_from) 
		$errors[] = "The end date cannot precede the start date.";
	if(count($errors) == 0) {
		$shared_clause = " from business as b
			inner join employee as e on b.id = e.business_id
			inner join bill_redemption_session as brs on brs.employee_id = e.id
			inner join bill_tracking as bt on brs.id = bt.session_id ";
		$qry_rdmp = "select b.business_name, btr.at, bt.id
			$shared_clause
			inner join bill_tracking_record as btr on bt.id = btr.bill_tracking_id 
			inner join (select * FROM bill_tracking_record where status > " . PENDING . ") as btr2
			on btr.id = btr2.id ";
		$qry_chk = "select distinct c.id as chkid, c.serial_number, c.at, b.business_name, bt.id $shared_clause 
			inner join checks as c on c.id = bt.check_id ";
		if(in_array("zip", $filters) && strlen($zip) > 0) {
			$clause = " AND b.zip = '$zip' ";
			$qry_rdmp_suffix .= $clause;
			$qry_chk_suffix .= $clause;
		}
		if(in_array("businesses", $filters) && count($biz_ids) > 0) {
			$clause = " AND b.id IN (" . implode(",", $biz_ids) . ") ";
			$qry_rdmp_suffix .= $clause;
			$qry_chk_suffix .= $clause;
		}
		$qry_rdmp_suffix .= " order by b.business_name asc, btr.id, btr.at desc";

		$result_rdmp = sqlQuery("$qry_rdmp $qry_rdmp_suffix", $mySql);
		$rdmps = array();
		for($i=0; $i<count($result_rdmp); $i++) {
			$temp = $result_rdmp[$i];
			$at = $temp['at'];
			$name = $temp['business_name'];
			$btid = $temp['id'];
			if(!isset($rdmps[$name]))
				$rdmps[$name] = array();
			if(!isset($rdmps[$name]["$btid"]))
				$rdmps[$name]["$btid"] = array();
			$rdmps[$name]["$btid"]['at'] = $at;
		}
		if(in_array("date", $filters)) {
			$date_filter = " AND (c.at >= '$date_from 00:00:00' AND c.at <= '$date_to 23:59:59') ";
			foreach($rdmps as $name => $btids) {
				foreach($btids as $key => $btid)
					if(isset($date_filter) && (@strtotime($btid['at']) < @strtotime("$date_from 00:00:00") || @strtotime($btid['at']) > @strtotime("$date_to 23:59:59")))
						unset($rdmps[$name]["$key"]);
				if(count($rdmps[$name]) == 0) unset($rdmps[$name]);
			}	
		}		
		$qry_chk_suffix .= " $date_filter order by business_name asc, chkid asc";
		$result_bt = sqlQuery("$qry_chk $qry_chk_suffix", $mySql);
		for($i=0; $i<count($result_bt); $i++) {
			$temp = $result_bt[$i];
			$name = $temp['business_name'];
			$chkid = $temp['chkid'];
			$at = $temp['at'];
			$cserial = $temp['serial_number'];		
			if(!isset($bizs[$name]))
				$bizs[$name] = array();
			if(!isset($bizs[$name]["$chkid"]))
				$bizs[$name]["$chkid"] = array();
			if(!isset($bizs[$name]["$chkid"]['total']))
				$bizs[$name]["$chkid"]['total'] = 0;
			$bizs[$name]["$chkid"]['total']++;
			$bizs[$name]["$chkid"]['at'] = $at;
			$bizs[$name]["$chkid"]['serial_number'] = $cserial;
		}
	}
}
?>
<html>
<head>
<?php include("includes/css.inc")?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (document.getElementById("zip_checkbox").checked && document.getElementById("biz_checkbox").checked) {
  	  		alert("You cannot select both the businesses filter and the zip code filter.");
	  		return false;
	  	}
		if (document.getElementById("biz_checkbox").checked && document.getElementById("biz_listbox").selectedIndex==-1) {
  	  		alert("You must select a business if you are filtering by business.");
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
<?php displayErrors($errors); ?><h2>Business Reporting</h2>
<form method="post" name="f" action="reports_businesses.php">
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
		<select name="zip">
			<?php foreach($result_zip as $z) { ?>
				<option value="<?php echo $z['zip']?>" <?php echo $z['zip']==$zip ? " selected" : ""?>><?php echo $z['zip']?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td><input type="checkbox" id="biz_checkbox" name="filters[]" value="businesses" <?php if (in_array('businesses', $filters)) echo " checked"?> /></td>
	<td>Businesses</td>
	<td> 
		<select multiple name="biz_ids[]" id="biz_listbox">
			<?php foreach($result_biz as $biz) { ?>
				<option value="<?php echo $biz['id']?>" <?php echo in_array($biz['id'], $biz_ids) ? " selected" : ""?>><?php echo $biz['business_name']; ?></option>
			<?php } ?>
		</select>
		<br /><font size="1">Ctrl-click to unselect</font>
	</td>
</tr>
<tr align="center"><td colspan="3"><input type="submit" name="submit" value="Submit" onClick="return validate_form(document.f);" /></td></tr>
</table>
</form>
<?php if(isset($_POST['submit']) && count($errors)==0) { ?>
<table width="800"><tr>
<td valign="top">
<table width="400">
	<tr align="center"><td width="100" colspan="2"><b>Total Businesses with Redemptions: <?php echo count($rdmps)?></b></td></tr>
	<tr align="center"><td>Business</td><td>Total Redemptions</td></tr>
	<?php foreach($rdmps as $key => $value) { ?>
		<tr align="center"> 
			<td><?php echo $key ?></td>
			<td>$<?php echo count($value)?>0</td>
		</tr>
	<?php } ?>
</table>
</td>
<td valign="top">
<table width="400">
	<tr align="center"><td colspan="2"><b>Total Businesses with Checks Paid: <?php echo count($bizs)?></b></td></tr>
	<tr align="center"><td width="150">Business</td><td>Check</td></tr>
	<?php foreach($bizs as $name => $checks) { 
		$total = 0; ?>
		<tr align="center">
			<td><?php echo $name?></td>
			<td><ul><?php foreach($checks as $chkid => $check) { ?>
				<li><a href="check_view.php?check_id=<?php echo $chkid?>"><?php echo $check['serial_number']?></a>
				for $<?php echo $check['total']?>0 on <?php echo substr($check['at'], 0, 10)?></li> 
				<?php $total += $check['total']; 
					} ?>
			</ul>Total: $<?php echo $total?>0</td>
		</tr>
	<?php } ?>
</table>
</td>
</tr></table>
<?php } ?>
</body></html>