<?php
require_once("config.inc");

$result_biz = null;
$qry_check = "select distinct c.*, bt.serial_number as bill, b.business_name, b.id as business_id
	from checks as c
	inner join bill_tracking as bt on c.id = bt.check_id
	inner join bill_redemption_session as brs on bt.session_id = brs.id
	inner join employee as e on e.id = brs.employee_id
	inner join business as b ON b.id = e.business_id
	where c.id = " . $_GET['check_id'];
if($result = sqlQuery($qry_check, $mySql))
	$result_biz = sqlQuery("select b.*, concat(first_name,' ',last_name) as employee_name from business as b inner join employee as e on e.business_id = b.id where b.id = {$result[0]['business_id']} order by is_active desc limit 1 ", $mySql); 
	?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>View Check</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<a href="check_add.php">Enter a new check.</a><p />
<table>
	<tr><td>Check #</td><td><b><?php echo $result[0]['serial_number']?></b></td>
	<tr>
		<td>Business: </td>
		<td><?php 
				foreach($result_biz[0] as $key => $value) {
					foreach(explode("_", $key) as $word) 
						echo " " . ucfirst($word);
					echo ": $value<br />";
				} ?>
		</td>
	</tr>
	<tr><td>Created at: </td><td><?php echo $result[0]['at']?></td></tr>
	<tr><td>Total Amount: </td><td>$<?php echo count($result)?>0</td></tr>
	<tr>
		<td>Bills Paid</td>
		<td>
			<?php for($i=0; $i<count($result); $i++) { ?>
				<?php echo $result[$i]['bill']?><br />
			<?php } ?>		
		</td>
	</tr>
</table>