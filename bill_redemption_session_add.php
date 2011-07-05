<?php
require_once("config.inc");

$problem = false;
$session_id = $qry_emp = $result_emp = $qry_bus = $result_bus = $qry_staff = $result_staff = null;

if (isset($_REQUEST["create"])
	&& isset($_POST["employee_id"]) && $_POST["employee_id"] > 0
	&& isset($_POST["staff_id"]) && $_POST["staff_id"] > 0
	&& empty($_POST["session_id"]) && count($errors) == 0) {
		$sessionId = createSession($mySql);
		header("Location: bill_tracking.php?action=create&session_id=$sessionId");
}	
$qry_bus = "select * from business as b where exists 
	(select * from employee as e where b.id = e.business_id AND e.is_active = 1 "
	. (isset($_POST["business_id"]) ? "and b.id = {$_POST['business_id']})" : ")");
	$result_bus = sqlQuery($qry_bus, $mySql);

if (empty($_POST["employee_id"]) && !empty($_POST["business_id"])) {
	$qry_emp = "select *, concat(first_name,' ',last_name) as name from employee where business_id = {$_POST['business_id']} ; ";
	$result_emp = sqlQuery($qry_emp, $mySql);
} elseif (!empty($_POST["employee_id"])) {
	$qry_emp = "select *, concat(first_name,' ',last_name) as name from employee where id = {$_POST['employee_id']}; ";
	$result_emp = sqlQuery($qry_emp, $mySql);
}
$qry_staff = "select *, concat(first_name,' ',last_name) as `name` from staff where is_active = 1" . (isset($_POST["staff_id"]) ? " AND id = {$_POST['staff_id']}" : "");
$result_staff = sqlQuery($qry_staff, $mySql); 		
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Bill Redemption</title>
<?php include("includes/css.inc"); ?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		<?php if (empty($_POST)) { ?>
		if (business_id.value == '') {
  	  		alert("You must select a business.");
	  		return false;
	  	} else if (staff_id.value == '') {
  	  		alert("You must select a staff member.");
	  		return false;
	  	}
<?php } else { ?>
		if (employee_id.value == '') {
  	  		alert("You must select an employee.");
	  		return false;
	  	} else if (!document.getElementById("confirm_checkbox").checked) {
  	  		alert("You must confirm the submission by clicking the checkbox.");
	  		return false;
	  	}
<?php } ?>
  	}
	return true;
}
</script>
</head>
<body>
<?php include("includes/header.inc"); ?>
<?php displayErrors($errors); ?>
<?php if(!empty($_POST)) { ?><a href="bill_redemption_session_add.php">Restart Bill Redemption Session</a><p /><?php } ?>
<h2>Create a new Bill Redemption Session.</h2><P />
<?php if (empty($_POST)) { ?><i>Note: If a business does not appear in the list, it contains no active employees.</i><P /><?php } ?>
<form method="post" name="f" action="bill_redemption_session_add.php">
<?php if(isset($_POST['staff_id'])) { ?><input type="hidden" name="staff_id" value="<?php echo $_POST['staff_id']?>"><?php } ?>
<table>
	<tr><td>business</td><td><?php echo empty($_POST["business_id"]) ? getOptionList($result_bus,'business_id','select a business','business_name') : $result_bus[0]["business_name"];?></td></tr>
	<?php if(empty($_POST["employee_id"]) &&  !empty($_POST["business_id"])) { ?>
		<?php if (empty($result_emp)) {  $problem = true; ?>
			<tr><td colspan=2 ><Font color=red><?php echo $result_bus[0]["business_name"]?> has no employees.</Font>  <a href="https://www.macontool.playareacode.com/employee.php">Create an Employee</a>  </td></tr>
		<?php } else { ?>
			<tr><td>employee of business</td> <td> <?php echo getOptionList($result_emp,'employee_id','select an employee','name') ?> </td>  </tr>
			<tr><td>note</td> <td width="300"><textarea name="note" cols="32" rows="4"></textarea></td>  </tr>
		<?php } ?>
	<?php } ?>
	<tr><td> redemption staff (you) </td> <td> <?php echo isset($_POST['staff_id']) ? $result_staff[0]["name"] : getOptionList($result_staff,'staff_id','select a staff member','name')?> </td></tr>
</table>
<p> 
<?php if( $problem == false) { ?>
	<?php if(!empty($_POST["business_id"])) { ?>
	 	<input id="confirm_checkbox" type="checkbox" name="confirm" value="1" > Check box to confirm. 
	 	<BR />
		<input type="submit" name="create" value="Create" onclick="return validate_form(document.f);" >
	<?php } else { ?>
		<input type="submit" name="proceed" value="Proceed to next step" onclick="return validate_form(document.f);" >
	<?php } ?>
<?php } ?>
</form>
</body>
</html>
<?php 
function getOptionList($result, $input_name, $first_option, $name_col) {
	$dropo = "<select name='" . $input_name . "' >"; 	
	if (!empty($first_option))
		$dropo .= "<option value='' selected >" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++)
		$dropo .= "<option value='" . $result[$i]["id"] . "' >" . $result[$i][$name_col] . "</option>"; 
	return $dropo . "</select>";
}

function createSession($mySql) {
	$qry = "insert into bill_redemption_session
		set employee_id = {$_POST['employee_id']},
		note = '" . mysql_real_escape_string($_POST['note']) . "', 	
		staff_id = {$_POST['staff_id']};";
	sqlInsert($qry, $mySql);
	return mysql_insert_id();
}
?>
