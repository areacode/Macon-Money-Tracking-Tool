<?php
require_once("config.inc");

$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : null;
$first_name = $last_name = $is_active = null;
$business_id = isset($_REQUEST['bid']) ? $_REQUEST['bid'] : null;

if (isset($_REQUEST["submit"]) && isTagMatch($_TAG_PREFIX)){
	if($eid > 0) {
		update($mySql, $eid);
		header("Location: manage_employees.php?action=edit&id=$eid");			
	} else {
		create($mySql);
		if($id = mysql_insert_id($mySql))
			header("Location: manage_employees.php?action=create&id=$id");
	}
} else if (isset($_GET['eid']) && $eid > 0  && $result_edit = sqlQuery("SELECT * from employee where id = $eid", $mySql)) {
	foreach($result_edit[0] as $key => $value)
		if($key != "id")
			eval('$' . $key . '="' . $value . '";');
}	
$qry_bus = "select * from business order by business_name asc; ";
$result_bus = sqlQuery($qry_bus, $mySql);		
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Employees</title>
<?php include ("includes/css.inc")?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (first_name.value == '') {
  	  		alert("You must enter a first name.");
	  		return false;
	  	} else if (last_name.value == '') {
  	  		alert("You must enter a last name.");
	  		return false;
	  	} else if (bid.value == '') {
	  		alert("You must select a business.");
	  		return false;
	  	} else if (!document.getElementById("confirm_checkbox").checked) {
  	  		alert("You must confirm the submission by clicking the checkbox.");
	  		return false;
	  	}
  	}
	return true;
}
</script>
</head>
<body>
<?php include("includes/header.inc")?>
<h2><?php echo $eid > 0 ? "Edit" : "Create"; ?> an employee.</h2>
<form method="post" name="f" action="employee.php">
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<?php if($eid > 0) { ?>
	<input type="hidden" name="eid" value="<?php echo $eid;?>">
<?php } ?>
<table>
<tr><td>first name</td><td><input type="text" name="first_name" value="<?php echo $first_name?>"></td></tr>
<tr><td>last name</td><td><input type="text" name="last_name" value="<?php echo $last_name?>"> </td></tr>
<tr><td>business</td><td><?php echo getOptionList($result_bus,'bid','select a business','business_name', $business_id) ?></td></tr>
<?php if ($eid > 0) { ?>
	<tr><td>is active?</td> <td> <input type="checkbox" name="is_active" value="yes" <?php echo $is_active == "1" ? " checked" : "";?>></td></tr>
<?php } ?>
</table><p /> 
<input id="confirm_checkbox" type="checkbox" name="confirm" value="1" />Check box to confirm<BR />
<input type="submit" value="Submit" name="submit" onclick="return validate_form(document.f);" />
</form>
</body>
</html>
<?php 
function getOptionList($result, $input_name, $first_option, $name_col, $bid = null) {
	$dropo = "<select name='" . $input_name . "' >"; 
	if (!empty($first_option))
		$dropo .= "<option value=''>" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++)
		$dropo .= "<option value='" . $result[$i]["id"] . "' " . ($bid == $result[$i]['id'] ? " selected" : "") . ">" . $result[$i][$name_col] . "</option>"; 
	$dropo .= "</select>";
	return $dropo;
}

function getQueryBody() {
	$qry = " set last_name = '" . mysql_real_escape_string($_POST["last_name"]) . "'"; 	
	$qry .= ", business_id = " . mysql_real_escape_string($_POST["bid"]) ; 		
	$qry .= ", first_name = '" . mysql_real_escape_string($_POST["first_name"]) . "'"; 		
	return $qry;
}

function update($mySql, $eid) {
	$qry = "update employee " . getQueryBody() . ", is_active = " . ($_POST['is_active']=="yes" ? "1": "0") . " where id = $eid; "; 
	sqlUpdate($qry, $mySql);
	return $qry;
}


function create($mySql) {
	$qry = "insert into employee " . getQueryBody() . ";";
	sqlInsert($qry, $mySql);
	return $qry;
}
?>
