<?php
require_once("config.inc");

$qry_emp = "select s.* from staff s order by first_name; ";
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
	echo "<center><font color='red'>The staff member has been successfully " . ($_GET['action']=="edit" ? "edited." : "created.") . " (ID: " . $_GET['id'] . ")</font></center>";
?>
<p><a href="staff.php">Create a staff member</a></p>
<P> </P>
<h2>All Staff</h2>
<P> </P>
<table border="1" >
<tr>
<td>Staff ID</td> <td>First Name</td> <td>Last Name</td>  <td>Date Created</td> <td>Is Active?</td>
</tr>
<?php
for ($i = 0; $i < count($result_emp); $i++){
?>
<tr>
	<td><?php echo $result_emp[$i]["id"]?><font size="1"> (<a href="staff.php?sid=<?php echo $result_emp[$i]["id"];?>">edit</a>)</font></td>   <td><?php echo $result_emp[$i]["first_name"]?></td>  	
	<td><?php echo $result_emp[$i]["last_name"]?></td>  <td><?php echo $result_emp[$i]["date_created"]?></td>  <td><?php echo $result_emp[$i]["is_active"]?></td> 
</tr>
		
<?php
}
?>

</table>
</body>
</html>