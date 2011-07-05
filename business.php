<?php
require_once("config.inc");
require_once("date_ranger.php");

$result_new = $business_name = $address = $zip = $email = $phone = $tin = null;
$id = -1;
$day = @date("d"); $month = @date("m"); $year = @date("Y");
$bid = isset($_REQUEST['bid']) ? $_REQUEST['bid'] : null;
if (isset($_REQUEST["submit"]) && isTagMatch($_TAG_PREFIX) && count($errors) == 0) {
	if($bid > 0) {
		update($mySql, $date_from, $bid);
		header("Location: manage_businesses.php?action=edit&id=$bid");
	} else {
		create($mySql, $date_from);
		if($id = mysql_insert_id($mySql))
			header("Location: manage_businesses.php?action=create&id=$id");
	}
} else if (isset($_GET['bid']) && $bid > 0  && $result_edit = sqlQuery("SELECT * from business where id = $bid", $mySql)) {
	foreach($result_edit[0] as $key => $value)
		if($key != "id")
			eval('$' . $key . '="' . $value . '";');
	$year = substr($contract_date, 0, 4);
	$month = substr($contract_date, 5, 2);
	$day = substr($contract_date, 8, 2);
}
$qry_new = "select * from business where id = $id;";
if ($id > -1)
	$result_new = sqlQuery($qry_new, $mySql);	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Businesses</title>
<?php include("includes/css.inc"); ?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		var str = strip_non_numeric(tin.value);
		if(str.length != 9) {
			alert("TIN must contain 9 digits.");
			return false;
		} else {
			var temp = parseInt(str);
			if(!(temp > 0)) {
				alert("Invalid TIN number");
				return false;
			} else if (business_name.value == '') {
	  	  		alert("You must enter a business name.");
		  		return false;
	  		} else if (address.value == '') {
	  	  		alert("You must enter an address.");
		  		return false;
		  	} else if (zip.value == '') {
	  	  		alert("You must enter a zip code.");
		  		return false;
		  	} else if (phone.value == '') {
	  	  		alert("You must enter a phone number.");
		  		return false;
	  		} else if (!document.getElementById("confirm_checkbox").checked) {
	  	  		alert("You must confirm the submission by clicking the checkbox.");
		  		return false;
	  		}
		}
  	}
}
function strip_non_numeric(str) {
	str += '';
	var rgx = /^\d|\.|-$/;
	var out = '';
	for(var i = 0; i < str.length; i++) {
		if(rgx.test(str.charAt(i))) {
			if(!(( str.charAt(i) == '.' && out.indexOf( '.' ) != -1 ) || (str.charAt(i) == '-' && out.length != 0))) {
        		out += str.charAt(i);
      		}
    	}
  	}
  	return out;
}
</script>
</head>
<body>
<?php include("includes/header.inc")?>
<?php displayErrors($errors); ?>
<?php if($bid > 0) { ?><a href="employee.php?bid=<?php echo $bid;?>">Add an Employee</a> <?php } ?>
<h2><?php echo isset($bid) ? "Edit a " : "Create a new "; ?> business. </h2><P />
<form method="post"  name="f">
	<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
	<table>
		<tr><td>business_name</td> <td> <input type="text" name="business_name" value="<?php echo $business_name?>"> </td>  </tr>
		<tr><td>address</td> <td> <input type="text" name="address" value="<?php echo $address?>"></td></tr>
		<tr><td>zip</td> <td> <input type="text" name="zip" value="<?php echo $zip?>"> </td></tr>
		<tr><td>email</td> <td> <input type="text" name="email" value="<?php echo $email?>"> </td></tr>
		<tr><td>phone</td> <td> <input type="text" name="phone" value="<?php echo $phone?>"> </td></tr>
		<tr><td>tin</td> <td> <input type="text" name="tin" maxlength="11" value="<?php echo $tin?>"> </td></tr>
		<tr><td>contract date</td> <td> <?php $ddlFrom->genYearDDL("year_from", 2, @date("Y")+1, $year)?>/<?php $ddlFrom->genDayDDL("date_from", $day)?>/<?php $ddlFrom->genMonthDDL("month_from", "short", $month)?> </td>  </tr>
	</table>
	<p /> 
 	<input type="checkbox" id="confirm_checkbox" name="confirm" value="1" />Check box to confirm.<BR />
	<input type="submit" value="Submit" name="submit" onclick="return validate_form(document.f);" />
</form>
<P />
<?php if($id == -2) { ?>
		<P><font color="red"><H3>Please be sure to fill in all fields ("email" is optional ).</font> </H3></P>
<?php }?>
<?php if($result_new) { ?>
	<P> New Business created: </P>
	<table>
		<tr>
			<td>business_id</td> <td>business_name</td> <td>address</td> <td>zip</td> <td>email</td> <td>phone</td><td>tin</td> <td>contract_date</td> 
		</tr>
		<tr>
			<td><?php echo $result_new[0]["id"]?></td>   <td><?php echo $result_new[0]["business_name"]?></td>  
			<td><?php echo $result_new[0]["address"]?></td>  <td><?php echo $result_new[0]["zip"]?></td>
			<td><?php echo $result_new[0]["email"]?></td> <td><?php echo $result_new[0]["phone"]?></td>
			<td><?php echo $result_new[0]["tin"]?></td> <td><?php echo $result_new[0]["contract_date"]?></td> 
		</tr>
	</table>		

<?php } ?>
<P /><P />
</body>
</html>
<?php 
function getQueryBody($date_from) {
	$qry = " set business_name = '" . mysql_real_escape_string($_POST["business_name"]) . "'"; 	
	$qry .= ", address = '" . mysql_real_escape_string($_POST["address"]) . "'";
	$qry .= ", zip = '" . mysql_real_escape_string($_POST["zip"]) . "'";
	$qry .= ", tin = '" . mysql_real_escape_string($_POST["tin"]) . "'"; 
	$qry .= ", email = '" . mysql_real_escape_string($_POST["email"]) . "'"; 
	$qry .= ", phone = '" . mysql_real_escape_string($_POST["phone"]) . "'"; 		
	$qry .= ", contract_date = '$date_from 00:00:00' " ;
	return $qry;
}
function create($mySql, $date_from ) {
	$qry = "insert into business " . getQueryBody($date_from) . " ; ";		
	sqlInsert($qry, $mySql);
}
function update($mySql, $date_from, $bid) {
	$qry = "update business " . getQueryBody($date_from) . " where id = $bid; ";		
	sqlUpdate($qry, $mySql);
}
?>
