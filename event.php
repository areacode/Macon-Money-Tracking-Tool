<?php
require_once("config.inc");
require_once("date_ranger.php");

$eid = isset($_REQUEST['eid']) ? $_REQUEST['eid'] : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$description = isset($_POST['name']) ? $_POST['description'] : null;
$start_day = $end_day = @date("d"); $start_month = $end_month = @date("m"); $start_year = $end_year = @date("Y");
if (isset($_REQUEST["submit"]) && isTagMatch($_TAG_PREFIX )) {
	if($date_to < $date_from)
		$errors[] = "The end date cannot precede the start date.";
	if(strlen(trim($_POST['name'])) == 0)
		$errors[] = "The event name cannot be blank.";
	if(count($errors) == 0) {
		if($eid > 0) {
			update($mySql, $date_from, $date_to, $eid);
			header("Location: manage_events.php?action=edit&id=$eid");
		} else {
			create($mySql, $date_from, $date_to);
			if($id = mysql_insert_id($mySql))
				header("Location: manage_events.php?action=create&id=$id");
		}
	}
} else if (isset($_GET['eid']) && $eid > 0  && $result_edit = sqlQuery("SELECT * from events where id = $eid", $mySql)) {
	foreach($result_edit[0] as $key => $value)
		if($key != "id")
			eval('$' . $key . '="' . $value . '";');
	$start_year = substr($start_date, 0, 4);
	$start_month = substr($start_date, 5, 2);
	$start_day = substr($start_date, 8, 2);
	$end_year = substr($end_date, 0, 4);
	$end_month = substr($end_date, 5, 2);
	$end_day = substr($end_date, 8, 2);
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Events</title>
<?php include("includes/css.inc")?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (name.value == '') {
  	  		alert("You must enter an event name.");
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
<?php displayErrors($errors); ?>
<h2><?php echo isset($eid) ? "Edit" : "Create"; ?> an event. </h2><P />
<form method="post"  name="f" >
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<table>
<tr><td>name</td><td><input type="text" name="name" value="<?php echo $name?>"></td></tr>
<tr><td>description</td><td><textarea name="description"><?php echo $description;?></textarea></td></tr>
<tr><td>start date</td><td><?php $ddlFrom->genYearDDL("year_from", 2, @date("Y")+1, $start_year)?>/<?php $ddlFrom->genDayDDL("date_from", $start_day)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $start_month)?> </td>  </tr>
<tr><td>end date</td><td><?php $ddlFrom->genYearDDL("year_to", 2, @date("Y")+1, $end_year)?>/<?php $ddlFrom->genDayDDL("date_to", $end_day)?>/<?php $ddlFrom->genMonthDDL("month_to", "short", $end_month)?> </td>  </tr>
</table>
<p> 
 	<input type="checkbox" id="confirm_checkbox" name="confirm" value="1" > Check box to confirm. 
 	<BR />
	<input type="submit" value="Submit" name="submit" onclick="return validate_form(document.f);">
</p>
</form>
<P /><P />
</body>
</html>
<?php 
function getOptionList($result, $input_name, $first_option, $name_col) {
	
	$dropo = "<select name='" . $input_name . "' >"; 
	
	if (!empty($first_option)) {
		$dropo .= "<option value='-1' selected >" . $first_option . "</option>";
	}
	
	for ($i = 0; $i < count($result); $i++){
		$dropo .= "<option value='" . $result[$i]["id"] . "' >" . $result[$i][$name_col] . "</option>"; 
	}
	$dropo .= "</select>";
	return $dropo;
}
function getQueryBody($date_from, $date_to) {
	$qry = " set name = '" . mysql_real_escape_string($_POST["name"]) . "'"; 	
	$qry .= ", description = '" . mysql_real_escape_string($_POST["description"]) . "'"; 		
	$qry .= ", start_date = '$date_from 00:00:00' " ;
	$qry .= ", end_date = '$date_to 00:00:00'";
	return $qry;
}
function create($mySql, $date_from, $date_to ) {
	$qry = "insert into events " . getQueryBody($date_from, $date_to) . " ; ";		
	sqlInsert($qry, $mySql);
}
function update($mySql, $date_from, $date_to, $eid) {
	$qry = "update events " . getQueryBody($date_from, $date_to) . " where id = $eid; ";		
	sqlUpdate($qry, $mySql);
}
?>
