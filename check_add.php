<?php
require_once("config.inc");
require_once("date_ranger.php");

$day_from = isset($_POST['date_from']) ? $_POST['date_from'] : 1;
$day_to = isset($_POST['date_to']) ? $_POST['date_to'] : @date("d");
$month_from = isset($_POST['month_from']) ? $_POST['month_from'] : @date("m");
$month_to = isset($_POST['month_to']) ? $_POST['month_to'] : @date("m");
$year_from = isset($_POST['year_from']) ? $_POST['year_from'] : @date("Y");
$year_to = isset($_POST['end_year']) ? $_POST['end_year'] : @date("Y");
$business_id = isset($_POST['business_id']) ? $_POST['business_id'] : null;
$serial_number = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : null;
$bill_ids = isset($_POST['bill_ids']) ? $_POST['bill_ids'] : array();
$result_rdmp = null;
$rdmps = array();

if(!empty($_POST)) {
	if($date_to < $date_from)
		$errors[] = "The end date cannot precede the start date.";
	if(count($errors) == 0) {
		$qry = "select distinct btr.at, bt.id, bt.session_id, bt.serial_number from business as b
			inner join employee as e on b.id = e.business_id
			inner join bill_redemption_session as brs on brs.employee_id = e.id
			inner join bill_tracking as bt on brs.id = bt.session_id
			inner join bill_tracking_record as btr on bt.id = btr.bill_tracking_id 
			inner join (select * FROM bill_tracking_record where status > " . PENDING . ") as btr2
			on btr.id = btr2.id  
			where btr.status > " . PENDING . " AND bt.check_id IS NULL AND b.id = $business_id
			order by bt.serial_number asc";
		$result_rdmp = sqlQuery($qry, $mySql);
		for($i=0; $i<count($result_rdmp); $i++) {
			$temp = $result_rdmp[$i];
			$at = $temp['at'];
			$btid = $temp['id'];
			$sid = $temp['session_id'];
			$serial = $temp['serial_number'];
			if(!isset($rdmps["$btid"]))
				$rdmps["$btid"] = array();
			$rdmps["$btid"]['at'] = $at;
			$rdmps["$btid"]['session_id'] = $sid;
			$rdmps["$btid"]['serial_number'] = $serial;
		}
		foreach($rdmps as $key => $value)
			if(@strtotime($value['at']) < @strtotime("$date_from 00:00:00") || @strtotime($value['at']) > @strtotime("$date_to 23:59:59"))	
				unset($rdmps[$key]);
		if(isset($_REQUEST['add']))
			if(strlen($serial_number) > 0) {
				if($result = sqlInsert("INSERT INTO checks set serial_number = '$serial_number', at = '" . @date("Y-m-d h:i:s") . "'", $mySql)) {
					$id = mysql_insert_id($mySql);
					if($result = sqlUpdate("UPDATE bill_tracking set check_id = $id where id IN (" . implode(',',$bill_ids) . ")", $mySql))
						header("Location: manage_checks.php?action=create&serial_number=" . base64_encode($serial_number));
					else $errors[] = "There was a problem updating the database for Check ID $id.  Please contact tech support.";
				} else $errors[] = "There was a problem updating the database.  Please contact tech support.";
			} else $errors[] = "You must enter a check #.";
	}
}
?>
<html>
<head>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php displayErrors($errors); ?>
<?php if(!empty($_POST)) { ?><a href="check_add.php">Enter a new check.</a><p /><?php } ?>
<h2>Add Check</h2>
<form method="post" name="f">
<?php if((isset($_POST['lookup']) && count($errors) == 0) || (isset($_POST['add']) && count($errors) > 0)) { 
	$result_biz = sqlQuery("select b.*, concat(first_name,' ',last_name) as employee_name from business as b inner join employee as e on e.business_id = b.id where b.id = $business_id order by is_active desc limit 1 ", $mySql); ?>
	<?php if(count($rdmps) == 0) { ?>
		<center><h3>Any redeemed bills within the selected date range are already associated with checks.</h3></center>
	<?php } else { ?>
	<table>
		<tr align="center">
			<td>Business:</td>
			<td align="left">
				<input type="hidden" name="business_id" value="<?php echo $business_id?>" />
				<?php 
					foreach($result_biz[0] as $key => $value) {
						foreach(explode("_", $key) as $word) 
							echo " " . ucfirst($word);
						echo ": $value<br />";
					} ?>
			</td>
		</tr>
		<tr align="center">
			<td>Date From:</td>
			<td>
				<input type="hidden" name="date_from" value="<?php echo $day_from ?>" />
				<input type="hidden" name="month_from" value="<?php echo $month_from ?>" />
				<input type="hidden" name="year_from" value="<?php echo $year_from ?>" />
				<?php echo $date_from?>
			</td>
		</tr>
		<tr align="center">
			<td>Date To:</td>
			<td>
				<input type="hidden" name="date_to" value="<?php echo $day_to ?>" />
				<input type="hidden" name="month_to" value="<?php echo $month_to ?>" />
				<input type="hidden" name="year_to" value="<?php echo $year_to ?>" />
				<?php echo $date_to?>
			</td>
		</tr>
		<tr align="center"><td>Check Total:</td><td>$<?php echo count($rdmps) > 0 ? count($rdmps) : ""?>0</td></tr>
		<tr align="center">
			<td>Bills</td>
			<td>
				<?php foreach($rdmps as $key => $value) { ?>
					<a href="bill_redemption_session_receipt.php?session_id=<?php echo $value['session_id']?>"><?php echo $value['serial_number']?></a>
					<input type="hidden" name="bill_ids[]" value="<?php echo $key; ?>" />
					<br />
				<?php } ?>
			</td>
		</tr>
		<tr align="center"><td>Check #:</td><td><input type="text" name="serial_number" maxlength="255"/></td></tr>
		<tr align="center"><td colspan="2"><input type="submit" name="add" value="Add Check"/></td></tr>
	</table>
	<?php } ?>
<?php } else { 
	$result_biz = sqlQuery("select id, business_name from business", $mySql); ?>
<table>
<tr>
	<td>From:</td>
	<td><?php $ddlFrom->genYearDDL("year_from", @date("Y")-2010, @date("Y"), $year_from)?>/<?php $ddlFrom->genDayDDL("date_from", $day_from)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $month_from)?></td>
</tr>
<tr>
	<td>To:</td> 
	<td><?php $ddlFrom->genYearDDL("year_to", @date("Y")-2010, @date("Y"), $year_to)?>/<?php $ddlFrom->genDayDDL("date_to", $day_to)?>/<?php $ddlFrom->genMonthDDL("month_to", "short", $month_to)?></td>
</tr>
<tr>
	<td>Business: </td>
	<td>
		<select name="business_id">
			<?php foreach($result_biz as $biz) { ?>
				<option value="<?php echo $biz['id']?>" <?php echo $biz['id']==$business_id ? " selected" : ""?>><?php echo $biz['business_name']?></option>
			<?php } ?>
		</select>
	</td>
<tr align="center"><td colspan="2"><input type="submit" name="lookup" value="Lookup Redeemed Bills" /></td></tr>
</table>
<?php } ?>
</form>
</body></html>