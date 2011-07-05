<?php
require_once("config.inc");

$sorter = new Sorter(
	array(
		'id' => array('order_by_field' => 'e.id', 'direction' => 'desc', 'display' => 'Employee ID'),
		'business' => array('order_by_field' => 'b.business_name', 'direction' => 'asc', 'display' => 'Business'),
		'first_name' => array('order_by_field' => 'first_name', 'direction' => 'asc', 'display' => 'First Name'), 
		'last_name' => array('order_by_field' => 'last_name', 'direction' => 'asc', 'display' => 'Last Name'),
		'date' => array('order_by_field' => 'date_created', 'direction' => 'desc', 'display' => 'Created At'), 
		'active' => array('order_by_field' => 'is_active', 'direction' => 'desc', 'display' => 'Is Active?'), 
		'default' => array('order_by_field' => 'b.business_name', 'direction' => 'asc', 'display' => '')
	)
);

$qry_emp = "select e.*, b.business_name, b.id as bid from employee e inner join business b on b.id = e.business_id order by {$sorter->getOrderBy()} {$sorter->getDirection()}";
$result_emp = sqlQuery($qry_emp, $mySql);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Staff</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php if(isset($_GET['action']))
	echo "<center><font color='red'>The employee has been successfully " . ($_GET['action']=="edit" ? "edited." : "created.") . " (ID: " . $_GET['id'] . ")</font></center>";
?><p />
<a href="employee.php">Create an employee</a>
<h2>all employees</h2><P />
<table border="1" >
<tr align="center"><?php echo $sorter->getSortableHeaderCells(); ?></tr>
<?php for ($i = 0; $i < count($result_emp); $i++){ ?>
<tr align="center">
	<td><?php echo $result_emp[$i]["id"]?> <font size="1">(<a href="employee.php?eid=<?php echo $result_emp[$i]["id"];?>">edit</a>)</font></td>  <td><a href="business.php?bid=<?php echo $result_emp[$i]["bid"]; ?>"><?php echo $result_emp[$i]["business_name"]?></a><font size="1"> (<a href="employee.php?bid=<?php echo $result_emp[$i]["bid"]?>">add employee</a>)</font></td></a></td>  <td><?php echo $result_emp[$i]["first_name"]?></td>  
	<td><?php echo $result_emp[$i]["last_name"]?></td>  <td><?php echo $result_emp[$i]["date_created"]?></td>  <td><?php echo $result_emp[$i]["is_active"]?></td> 
</tr>
<?php } ?>
</table>

</body>
</html>