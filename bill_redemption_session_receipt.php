<?php
require_once("config.inc");

$session_id = $_REQUEST['session_id'];
$qry_sess = "select s.id as staff_id, s.first_name as staff_fname, s.last_name as staff_lname,
		b.business_name, e.first_name as employee_fname, e.last_name as employee_lname
		from bill_redemption_session as brs
		inner join staff as s on s.id = brs.staff_id
		inner join employee as e on e.id = brs.employee_id
		inner join business as b on b.id = e.business_id
		where brs.id = $session_id";
$result_sess = sqlQuery($qry_sess, $mySql);

$qry_rdmp = "select id, serial_number from bill_tracking where session_id = $session_id ";
$result_rdmp = sqlQuery($qry_rdmp, $mySql);
$qry_hist = "select * from bill_tracking_record where bill_tracking_id = ";
$events = array();
foreach(array_keys($statuses) as $status)
	$events[$status] = array();
foreach($result_rdmp as $rdmp) {
	$qry = $qry_hist . $rdmp['id'] . " order by at desc";
	$result_hist = sqlQuery($qry, $mySql);
	for($i=0; $i<count($result_hist); $i++) {
		if($i==0) $hist = $result_hist[$i];
		if($i+1 == count($result_hist) || $result_hist[$i+1]['status'] < 2) 
			break;
		else {
			$hist['at'] = $result_hist[$i]['at'];
			$hist['status'] = $result_hist[$i]['status'];
		}
	}
	$rdmp['at'] = $hist['at'];
	$rdmp['note'] = $hist['note'];
	$events[$hist['status']][] = $rdmp;
}
?>
<html>
<head>
<title>Bill Redemption Session Receipt</title>
<?php include("includes/css.inc");?>
</head>
<body>
<h1><center>Bill Redemption Session Receipt</center></h2>
<table>
<tr><td colspan="2">Staff Id: <?php echo $result_sess[0]['staff_id']?> (<?php echo $result_sess[0]['staff_fname'] . " " . $result_sess[0]['staff_lname']?>)</td></tr>
<tr><td colspan="2">Business Name: <?php echo $result_sess[0]['business_name']?></tr>
<tr><td colspan="2">Business Representative: <?php echo $result_sess[0]['employee_fname'] . " " . $result_sess[0]['employee_lname']?></tr>
</table>
<p />
<?php  
for($i=count($statuses)-1; $i>=0; $i--) { ?>
<table width="800">
	<tr><td colspan=4 align="center"><b><u><?php echo ucwords($statuses[$i])?></b></u></td></tr>
	<tr align="center"><td width="170">Serial #</td><td width="180">Date Redeemed</td><td width="450">Latest Note</td></tr>
	<?php if(isset($events[$i])) { ?>
		<?php foreach ($events[$i] as $rdmp) { ?>
			<tr align="center">
				<td width="170"><?php echo $rdmp['serial_number']; ?></td>
				<td width="180"><?php echo $rdmp['at']; ?></td>
				<td width="450"><?php echo $rdmp['note']; ?></td>
			</tr>
		<?php } ?>
	<?php } ?>
	<?php if($i > REJECTED && $i < REDEEMED) {
			$pendings = count($events[PENDING]) > 0 ? count($events[PENDING]) : "";
			$redeems = count($events[REDEEMED]) + count($events[REDEEMED_BUT_QUESTIONABLE]);
			if($redeems == 0) $redeems = "";?>
			<tr align="center">
				<td colspan="4">
					<b>Total <?php echo $i==PENDING ? "Pending: $$pendings" : "Redeemed: $$redeems"?>0</b></td></tr>
	<?php } ?>
</table>
<?php if ($i!=REDEEMED) echo "<p />";?>
<?php } ?>
</body>
</html>