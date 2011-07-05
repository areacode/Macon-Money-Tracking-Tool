<?php
require_once("config.inc");

$sorter = new Sorter(
	array(
		'id' => array('order_by_field' => 'brs.id', 'direction' => 'desc', 'display' => 'ID'),
		'serial_number' => array('order_by_field' => 'c.serial_number', 'direction' => 'asc', 'display' => '#'), 
		'business' => array('order_by_field' => 'b.business_name', 'direction' => 'asc', 'display' => 'Business'),
		'date' => array('order_by_field' => 'c.at', 'direction' => 'desc', 'display' => 'Created At'), 
		'default' => array('order_by_field' => 'b.business_name asc, c.at ', 'direction' => 'desc', 'display' => '')
	)
);
$qry_check = "select distinct c.*, b.id as business_id, b.business_name
	from checks as c
	inner join bill_tracking as bt on c.id = bt.check_id
	inner join bill_redemption_session as brs on bt.session_id = brs.id
	inner join employee as e on e.id = brs.employee_id
	inner join business as b ON b.id = e.business_id
	order by {$sorter->getOrderBy()} {$sorter->getDirection()}";
$result = sqlQuery($qry_check, $mySql);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Manage Checks</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php if(isset($_GET['action']))
	echo "<center><font color='red'>You have successfully entered check # " . base64_decode($_GET['serial_number']) . "</font></center>"; ?>
<a href="check_add.php">Enter a new check.</a><p />
<H2>All checks</H2>
<table>
	<tr align="center"><?php echo $sorter->getSortableHeaderCells()?><td>Check Amount</td></tr>
	<?php for($i=0; $i<count($result); $i++) { ?>
		<tr align="center">
			<td><?php echo $result[$i]['id']?> <font size="1">(<a href="check_view.php?check_id=<?php echo $result[$i]['id']?>">View</a>)</font></td>
			<td><?php echo $result[$i]['serial_number']?> </td>
			<td><?php echo $result[$i]['business_name']?></td>
			<td><?php echo $result[$i]['at']?></td>
			<td>
				<?php if($amount = sqlQuery("SELECT count(*) as total from bill_tracking where check_id = {$result[$i]['id']}", $mySql))
					echo "\${$amount[0]['total']}0";?>
			</td>
		</tr>
	<?php } ?>
</table>
</body>
</html>