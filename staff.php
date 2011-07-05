<?php
require_once("config.inc");

$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : null;
$first_name = $last_name = $is_active = null;

if (isset($_REQUEST["submit"]) && isTagMatch($_TAG_PREFIX)) {
	if($sid > 0) {
		update($mySql, $sid);
		header("Location: manage_staff.php?action=edit&id=$sid");
	} else {
		create($mySql);
		if($id = mysql_insert_id($mySql))
			header("Location: manage_staff.php?action=create&id=$id");
	}
} else if (isset($_GET['sid']) && $sid > 0  && $result_edit = sqlQuery("SELECT * from staff where id = $sid", $mySql)) {
	foreach($result_edit[0] as $key => $value)
		if($key != "id")
			eval('$' . $key . '="' . $value . '";');
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Staff</title>
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
<h2><?php echo $sid > 0 ? "Edit" : "Create"; ?> a staff member.</h2><P />
<form method="post" name="f" action="staff.php">
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<?php if($sid > 0) { ?>
	<input type="hidden" name="sid" value="<?php echo $sid;?>">
<?php } ?>
<table>
<tr><td>first name</td> <td> <input type="text" name="first_name" value="<?php echo $first_name?>"> </td>  </tr>
<tr><td>last name</td> <td> <input type="text" name="last_name" value="<?php echo $last_name;?>"> </td>  </tr>
<?php if ($sid > 0) { ?>
	<tr><td>is active?</td> <td> <input type="checkbox" name="is_active" value="yes" <?php echo $is_active == "1" ? " checked" : "";?>> </td>  </tr>
<?php } ?>
</table>
<p /> 
 	<input id="confirm_checkbox" type="checkbox" name="confirm" value="1" > Check box to confirm. <BR />
	<input type="submit" value="Submit" name="submit" onclick="return validate_form(document.f);" >
</form>
<P />
</body>
</html>
<?php 
function getOptionList($result, $input_name, $first_option, $name_col) {
	$dropo = "<select name='" . $input_name . "' >"; 
	if (!empty($first_option))
		$dropo .= "<option value='-1' selected >" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++)
		$dropo .= "<option value='" . $result[$i]["id"] . "' >" . $result[$i][$name_col] . "</option>"; 
	$dropo .= "</select>";
	return $dropo;
}

function getQueryBody() {
	$qry = " set last_name = '" . mysql_real_escape_string($_POST["last_name"]) . "'"; 	
	$qry .= ", first_name = '" . mysql_real_escape_string($_POST["first_name"]) . "'"; 		
	return $qry;
}

function create($mySql) {
	$qry = "insert into staff " . getQueryBody() . " ; "; 
	sqlInsert($qry, $mySql);
	return $qry;
}

function update($mySql, $sid) {
	$qry = "update staff " . getQueryBody() . ", is_active = " . ($_POST['is_active']=="yes" ? "1": "0") . " where id = $sid; "; 
	sqlUpdate($qry, $mySql);
	return $qry;
}
?>
