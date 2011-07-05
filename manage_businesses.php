<?php
require_once("config.inc");

$sorter = new Sorter(
	array(
		'id' => array('order_by_field' => 'id', 'direction' => 'desc', 'display' => 'ID'),
		'business' => array('order_by_field' => 'business_name', 'direction' => 'asc', 'display' => 'Name'),
		'address' => array('order_by_field' => 'address', 'direction' => 'asc', 'display' => 'Address'), 
		'zip' => array('order_by_field' => 'zip', 'direction' => 'asc', 'display' => 'Zip'),
		'email' => array('order_by_field' => 'email', 'direction' => 'asc', 'display' => 'Email'), 
		'phone' => array('order_by_field' => 'phone', 'direction' => 'asc', 'display' => 'Phone'), 
		'tin' => array('order_by_field' => 'tin', 'direction' => 'asc', 'display' => 'TIN'), 
		'date' => array('order_by_field' => 'contract_date', 'direction' => 'desc', 'display' => 'Contract Date'), 
		'default' => array('order_by_field' => 'business_name', 'direction' => 'asc', 'display' => '')
	)
);

$qry_bus = "select * from business order by {$sorter->getOrderBy()} {$sorter->getDirection()}";
$result_bus = sqlQuery($qry_bus, $mySql);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Businesses</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php if(isset($_GET['action']))
	echo "<center><font color='red'>The business has been successfully " . ($_GET['action']=="edit" ? "edited." : "created.") . " (ID: {$_GET['id']})</font></center>";
?>
<p><a href="business.php">Create a business</a></p>
<h2>All Businesses</h2>
<table>
	<tr align="center"><?php echo $sorter->getSortableHeaderCells()?></tr>
	<?php for ($i = 0; $i < count($result_bus); $i++) { ?>
		<tr align="center">
			<td><?php echo $result_bus[$i]["id"]?> <font size="1">(<a href="business.php?bid=<?php echo $result_bus[$i]["id"];?>">edit</a> | <a href="employee.php?bid=<?php echo $result_bus[$i]["id"];?>">add employee</a>)</font></td>   <td><?php echo $result_bus[$i]["business_name"]?></td>  	
			<td><?php echo $result_bus[$i]["address"]?></td>  <td><?php echo $result_bus[$i]["zip"]?></td>
			<td><?php echo $result_bus[$i]["email"]?></td> <td><?php echo $result_bus[$i]["phone"]?></td>
			<td><?php echo $result_bus[$i]["tin"]?></td> <td><?php echo substr($result_bus[$i]["contract_date"], 0, 10)?></td> 
		</tr>	
	<?php } ?>
</table>
</body>
</html>