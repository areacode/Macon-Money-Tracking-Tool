<?php
require_once("config.inc");

$sorter = new Sorter(
	array(
		'id' => array('order_by_field' => 'brs.id', 'direction' => 'desc', 'display' => 'ID'),
		'staff' => array('order_by_field' => 's.first_name', 'direction' => 'asc', 'display' => 'Staff'), 
		'business' => array('order_by_field' => 'b.business_name', 'direction' => 'asc', 'display' => 'Business'),
		'employee' => array('order_by_field' => 'e.first_name', 'direction' => 'asc', 'display' => 'Employee'),
		'date' => array('order_by_field' => 'brs.create_date', 'direction' => 'desc', 'display' => 'Created At'), 
		'default' => array('order_by_field' => 'brs.create_date desc, s.first_name asc, b.business_name', 'direction' => 'asc', 'display' => '')
	)
);

$qry_bus = "select brs.id, bt.serial_number, bt.id as btid, e.first_name as e_first_name, e.last_name as e_last_name,
	s.first_name as s_first_name, s.last_name as s_last_name, brs.create_date, b.business_name
	from bill_redemption_session as brs
	inner join staff as s on brs.staff_id = s.id
	inner join employee as e on brs.employee_id = e.id
	inner join business as b on e.business_id = b.id
	left join bill_tracking as bt on brs.id = bt.session_id
	order by {$sorter->getOrderBy()} {$sorter->getDirection()}";
$result_bus = sqlQuery($qry_bus, $mySql);
$serial_number = null;
if(isset($_POST['submit']) && isset($_POST['serial_number']) && strlen(trim($_POST['serial_number']))) {
	$serial_number = $_POST['serial_number'];
	if($result = sqlQuery("select id, session_id from bill_tracking where serial_number LIKE '$serial_number'", $mySql))
		header("Location: bill_tracking_history.php?btid={$result[0]['id']}&sid={$result[0]['session_id']}");
	else 
		$errors[] = "The serial number $serial_number did not match any bills.";
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Manage Bills</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php if(isset($_GET['action']))
	echo "<center><font color='red'>You have successfully edited the Bill Redemption Session (ID : " . $_GET['session_id'] . ")</font></center>"; ?>
<a href="bill_redemption_session_add.php">Create a new bill redemption session.</a><p />
<H2>Bill History Search</H2>
<?php displayErrors($errors); ?>
<form name="f" method="post" action="manage_bills.php">
	<font size="3">Enter Serial #:</font> 
	<input type="text" maxlength="45" size="45" name="serial_number" value="<?php echo $serial_number?>" />
	<input type="submit" name="submit" value="Submit" />
</form>
<p />
<H2>All bill redemption sessions</H2>
<table>
	<tr align="center"><?php echo $sorter->getSortableHeaderCells()?><td>Bills Involved</td></tr>
	<?php for($i=0; $i<count($result_bus); $i++) { ?>
		<?php if($i==0 || $result_bus[$i]['id'] != $result_bus[$i-1]['id']) { ?>
			<tr>
			<td><?php echo $result_bus[$i]['id']?><font size="1"> (<a href="bill_redemption_session_edit.php?session_id=<?php echo $result_bus[$i]['id']?>">Edit</a> | <a href="bill_redemption_session_receipt.php?session_id=<?php echo $result_bus[$i]['id']?>"> Receipt</a>)</font></td>
			<td><?php echo $result_bus[$i]['s_first_name'] . " " . $result_bus[$i]['s_last_name']?> </td>
			<td><?php echo $result_bus[$i]['business_name']?></td>
			<td><?php echo $result_bus[$i]['e_first_name'] . " " . $result_bus[$i]['e_last_name']?> </td>
			<td><?php echo $result_bus[$i]['create_date']?></td>
			<td>
		<?php } ?>
		<a href="bill_tracking_history.php?btid=<?php echo $result_bus[$i]['btid']?>&sid=<?php $result_bus[$i]['id']?>"><?php echo $result_bus[$i]['serial_number']?></a><br />
		<?php if($i+1 == count($result_bus)) { ?> 
			</td>
			</tr>
		<?php } else if ($result_bus[$i]['id'] != $result_bus[$i+1]['id']) { ?>
			</td>
		<?php } ?>
	<?php } ?>
</table>
</body>
</html>