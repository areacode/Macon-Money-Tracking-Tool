<?php
require_once("config.inc");
require_once("date_ranger.php");

$day_from = isset($_POST['date_from']) ? $_POST['date_from'] : 1;
$day_to = isset($_POST['date_to']) ? $_POST['date_to'] : @date("d");
$month_from = isset($_POST['month_from']) ? $_POST['month_from'] : @date("m");
$month_to = isset($_POST['month_to']) ? $_POST['month_to'] : @date("m");
$year_from = isset($_POST['year_from']) ? $_POST['year_from'] : @date("Y");
$year_to = isset($_POST['end_year']) ? $_POST['end_year'] : @date("Y");

if(isset($_REQUEST['submit'])) {
	if ($date_to < $date_from)
		$errors[] = "The end date cannot precede the start date.";
	if(count($errors) == 0) {
		$result_dist = sqlQuery("
			select p.redeemer_zip, sum(truncate(p.amount_per_person / 10, 0)) as total from (
				select redeemer_zip, amount_per_person from prizes as p 
				inner join bond_tracking on p.bond_1_serial_number = bond_tracking.serial_number
				where p.date_redeemed >= '$date_from 00:00:00' AND p.date_redeemed <= '$date_to 23:59:59'
				union all
				select redeemer_zip, amount_per_person from prizes as p 
				inner join bond_tracking on p.bond_2_serial_number = bond_tracking.serial_number
				where p.date_redeemed >= '$date_from 00:00:00' AND p.date_redeemed <= '$date_to 23:59:59'
			) as p group by p.redeemer_zip",$mySql);
		$result_btr = sqlQuery("select b.business_name, bt.id, btr.status, btr.at from business as b
			inner join employee as e on b.id = e.business_id
			inner join bill_redemption_session as brs on brs.employee_id = e.id
			inner join bill_tracking as bt on brs.id = bt.session_id
			inner join bill_tracking_record as btr on bt.id = btr.bill_tracking_id 
			order by business_name asc, bt.id asc, btr.at desc", $mySql);
		$history = $result_rej = $result_pend = $result_rdmp = array();
		
		foreach($result_btr as $btr) {
			$biz = $btr['business_name'];
			$btid = $btr['id'];
			$at = $btr['at'];
			if($btr['status'] <= PENDING && isset($history[$biz]) && isset($history[$biz]["$btid"]))
				continue;
			elseif(isset($history[$biz]) && isset($history[$biz]["$btid"])) {
				unset($history[$biz]["$btid"]);
					if(isset($history[$biz]) && count($history[$biz]) == 0) unset($history[$biz]);
			}
			if(@strtotime($at) >= @strtotime("$date_from 00:00:00") && @strtotime($at) <= @strtotime("$date_to 23:59:59")) {
				if(!isset($history[$biz]))
					$history[$biz] = array();
				$history[$biz]["$btid"] = $btr['status'];		
			}
		}
		foreach($history as $biz => $btids)
			foreach($btids as $btid => $status)
				switch($status) {
					case REJECTED: $result_rej = addToHistory($result_rej, $biz, $btid); break;
					case PENDING: $result_pend = addToHistory($result_pend, $biz, $btid); break;
					default: $result_rdmp = addToHistory($result_rdmp, $biz, $btid); break;
				}
	}
}

function addToHistory($history, $biz, $btid) {
	if(!isset($history[$biz])) $history[$biz] = array();
	if(!isset($history[$biz]["$btid"])) $history[$biz]["$btid"] = array();
	$history[$biz]["$btid"] = true;
	return $history;
}
?>
<html>
<head>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php include("includes/reports_nav.inc")?>
<?php displayErrors($errors); ?>
<h2>Bill Reporting</h2>
<form method="post" name="f">
<table>
<tr>
	<td>From:</td>
	<td><?php $ddlFrom->genYearDDL("year_from", @date("Y")-2010, @date("Y"), $year_from)?>/<?php $ddlFrom->genDayDDL("date_from", $day_from)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $month_from)?></td>
</tr>
<tr>
	<td>To:</td> 
	<td><?php $ddlFrom->genYearDDL("year_to", @date("Y")-2010, @date("Y"), $year_to)?>/<?php $ddlFrom->genDayDDL("date_to", $day_to)?>/<?php $ddlFrom->genMonthDDL("month_to", "short", $month_to)?></td>
</tr>
<tr align="center"><td colspan="2"><input type="submit" name="submit" value="Submit" /></td></tr>
</table>
</form>
<?php if(isset($_POST['submit']) && count($errors)==0) { ?> 
<table width="800"><tr valign="top"><td width="200">
<table>
	<tr align="center"><td colspan="2"><b>Bills Distributed<b></b></td></tr>
	<tr align="center"><td>Zip Code</td><td>Quantity</td></tr>
<?php 
	$grand_total = 0;
	foreach($result_dist as $distribution) {
		$grand_total += $distribution['total']; ?>
		<tr align="center">
			<td width="130">
				<?php echo empty($distribution['redeemer_zip']) ? "<i>Unknown</i>" : $distribution['redeemer_zip'];?>
			</td>
			<td width="70"><?php echo $distribution['total']; ?></td>
		</tr>
<?php } ?>
<tr align="center"><td colspan="2">Total: <?php echo $grand_total?></td></tr>
</table>
</td>
<td><?php displayTable("Pending", $result_pend)?></td>
<td><?php displayTable("Redeemed", $result_rdmp)?></td>
<td><?php displayTable("Rejected", $result_rej)?></td>
</tr></table>
<?php } ?> 
</body></html>
<?php 
function displayTable($text, $history) { ?>
	<table>
		<tr align="center"><td colspan="2"><b>Bills <?php echo $text?><b></b></td></tr>
		<tr align="center"><td>Business Name</td><td>Quantity</td></tr>
	<?php 
		$grand_total = 0;
		foreach($history as $business_name => $bill_tracking_ids) {
			$grand_total += count($history[$business_name]); ?>
			<tr align="center">
				<td width="130"><?php echo $business_name;?></td>
				<td width="70"><?php echo count($history[$business_name]); ?></td>
			</tr>
	  <?php } ?>
		<tr align="center"><td colspan="2">Total: <?php echo $grand_total?></td></tr>
	</table>
<?php 
} 
?>