<?php
require_once("config.inc");

$qry = "select id, sequence_id, serial_number, symbol_1, symbol_2, symbol_3, is_test_data
		FROM bonds; ";
$result = sqlQuery($qry, $mySql);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>All Bonds</title>
</head>
<body>
<table border="1" >
<tr>
<td>sequence_id</td> <td>serial_number</td> <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td>
</tr>
<?php for ($i = 0; $i < count($result); $i++){ ?>
<tr>
	<td><?php echo $result[$i]["sequence_id"]?></td>  <td><?php echo $result[$i]["serial_number"]?></td>  <td><?php echo $result[$i]["symbol_1"]?></td>  
	<td><?php echo $result[$i]["symbol_2"]?></td>  <td><?php echo $result[$i]["symbol_3"]?></td>  		
</tr>	
<?php } ?>
</table>
</body>
</html>