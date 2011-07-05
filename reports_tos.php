<?php
require_once("config.inc");

$fname = isset($_POST['fname']) ? $_POST['fname'] : null;
$lname = isset($_POST['lname']) ? $_POST['lname'] : null;

if (isset($_REQUEST["submit"])) {
		$qry = "select id, serial_number, tos, first_name, 
			last_name, redeemer_first_name, redeemer_last_name 
			from bond_tracking where true ";
		if($fname)
			$qry .= " and (lower(first_name) like '%" . strtolower($fname) . "%' OR lower(redeemer_first_name) like '%" . strtolower($fname) . "%') ";
		if($lname)
			$qry .= " and (lower(last_name) like '%" . strtolower($lname) . "%' OR lower(redeemer_last_name) like '%" . strtolower($lname) . "%') ";
		$result = sqlQuery($qry, $mySql);
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>TOS Search</title>
<?php include("includes/css.inc"); ?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (fname.value == '' && lname.value == '') {
  	  		alert("You must enter a value into either the first name or last name box.");
	  		return false;
	  	}
  	}
	return true;
}
</script>
</head>
<body>
<?php include("includes/header.inc"); ?>
<?php include("includes/reports_nav.inc")?>
<h2>TOS Search</h2>
<form method="post" name="f" action="reports_tos.php">
	First Name <input type="text" size="30" maxlength="45" name="fname" value="<?php echo $fname?>" /><br />
	Last Name <input type="text" size="30" maxlength="45" name="lname" value="<?php echo $lname?>" /><br />
	<input type="submit" name="submit" value="Submit" onClick="return validate_form(document.f);" />
</form>
<?php if(isset($_POST['submit'])) { ?>
	<?php if(count($result) > 0) { ?>
		<table>
			<tr><td>Serial #</td><td>TOS</td><td>Recipient</td><td>Redeemer</td></tr>
			<?php foreach($result as $bond) { ?>
			<tr>
				<td><a href="bond_tracking.php?linked_serial_number=<?php echo base64_encode($bond['serial_number'])?>"><?php echo $bond['serial_number']?></a></td>
				<td><?php echo $bond['tos'] == 1 ? "yes" : "no"?></td>
				<td><?php echo $bond['first_name']?> <?php echo $bond['last_name']?></td>
				<td><?php echo $bond['redeemer_first_name']?> <?php echo $bond['redeemer_last_name']?></td>
			</tr>
			<?php } ?>
		</table>
	<?php } else { ?>
		<h2>No results.</h2>
	<?php } ?>
<?php } ?>
</body>
</html>