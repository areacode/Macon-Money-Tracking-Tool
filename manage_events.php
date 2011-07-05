<?php
require_once("config.inc");

$sorter = new Sorter(
	array(
		'id' => array('order_by_field' => 'id', 'direction' => 'desc', 'display' => 'ID'),
		'name' => array('order_by_field' => 'name', 'direction' => 'asc', 'display' => 'Name'),
		'description' => array('order_by_field' => 'description', 'direction' => 'asc', 'display' => 'Description'),
		'start' => array('order_by_field' => 'start_date', 'direction' => 'desc', 'display' => 'Start Date'), 
		'end' => array('order_by_field' => 'end_date', 'direction' => 'desc', 'display' => 'End Date'), 
		'default' => array('order_by_field' => 'start_date desc, name ', 'direction' => 'asc', 'display' => '')
	)
);
$qry_event = "select * from events order by {$sorter->getOrderBy()} {$sorter->getDirection()} ";
$result_event = sqlQuery($qry_event, $mySql);
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
	echo "<center><font color='red'>The event has been successfully " . ($_GET['action']=="edit" ? "edited." : "created.") . " (ID: " . $_GET['id'] . ")</font></center>";
?><p />
<a href="event.php">Create an event</a><P />
<h2>All events</h2><P />
<table border="1" >
<tr align="center"><?php echo $sorter->getSortableHeaderCells()?></tr>
<?php for($i = 0; $i < count($result_event); $i++) { ?>
	<tr align="center">
		<td width="70"><?php echo $result_event[$i]["id"]?> <font size="1">(<a href="event.php?eid=<?php echo $result_event[$i]["id"];?>">edit</a>)</font></td>   <td><?php echo $result_event[$i]["name"]?></td>  
		<td><?php echo $result_event[$i]["description"]?></td>  <td width="160"><?php echo substr($result_event[$i]["start_date"], 0, 10)?></td>  <td width="160"><?php echo substr($result_event[$i]["end_date"], 0, 10)?></td> 	
	</tr>	
<?php } ?>
</table>
</body>
</html>
