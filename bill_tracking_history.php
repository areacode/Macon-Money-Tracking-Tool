<?php
require_once("config.inc");

$btid = $_REQUEST['btid'];

$qry = "select session_id, serial_number, note, status, at
		from bill_tracking as bt inner join bill_tracking_record as btr
		on bt.id = btr.bill_tracking_id
		where bt.id = $btid
		order by at desc";
$history = sqlQuery($qry, $mySql);
?>
<html>
<head>
<title>Bill Redemption History</title>
<?php include("includes/css.inc");?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php if($_REQUEST['sid']) {?>
	<a href="bill_redemption_session_edit.php?session_id=<?php echo $_REQUEST['sid']?>">View / Edit Associated Bill Redemption Session #<?php echo $_REQUEST['sid']?></a><p />
<?php } ?>
<?php 
	$serial_number = $history[0]['serial_number'];
	$session_id = $history[0]['session_id'];
?>
<font size="3">History of Serial Number <?php echo $serial_number ?></font><br />
(Redemption ID: <?php echo $btid; ?> from Redemption Session #<?php echo $session_id?>)<p />
<table width="800">
<tr align="center"><td width="175">Date</td><td width="100">Status</td><td width="525">Note</td></tr>
<?php for($i=0; $i<count($history); $i++) { ?>
<tr align="center">
	<td><?php echo $history[$i]['at']; ?></td>
	<td><?php echo $statuses[$history[$i]['status']]; ?></td>
	<td><?php echo $history[$i]['note']; ?></td>
</tr>
<?php } ?>
</table>
</body>
</html>