<?php
require_once("config.inc");

$dupe = null;
$status = isset($_POST['status']) ? $_POST['status'] : REDEEMED;
$session_id = $_REQUEST['session_id'];
$serial_number = isset($_POST['serial_number']) ? trim($_POST['serial_number']) : null;
$note = isset($_POST['note']) ? $_POST['note'] : null;
$result_dup = array();
$btid = isset($_REQUEST['btid']) ? $_REQUEST['btid'] : null;
if (isset($_POST["submit"]) && isTagMatch($_TAG_PREFIX) && isset($_POST['serial_number'])
	&& isset($_POST["status"]) && $_POST['status'] > -1 && $_POST['session_id'] > 0) {
		$result_exist = sqlQuery("SELECT count(*) as total from bills where serial_number LIKE '$serial_number'", $mySql);
		$result_dupe = sqlQuery("SELECT distinct bt.id, bt.session_id from bill_tracking as bt 
			inner join bill_tracking_record as btr ON bt.id = btr.bill_tracking_id 
			where serial_number = '$serial_number' " . ($btid > 0 ? " AND bt.id != $btid" : ""), $mySql);
		if($result_exist[0]['total'] == 0 && $statuses[$_POST['status']] != "Rejected")
			$errors[] = "The serial number entered was not found in our database.  This can only be entered with a status of rejected.";
		elseif(count($result_dupe) > 1 || (count($result_dupe)==1 && (!($btid > 0))))
			$errors[] = "The serial number is already used by another Bill Redemption <a href=\"bill_tracking.php?btid=" . $result_dupe[0]['id'] . "&session_id=" . $result_dupe[0]['session_id'] . "\">ID: " . $result_dupe[0]['id'] . "</a> from <a href=\"bill_redemption_session_edit.php?session_id=" . $result_dupe[0]['session_id'] . "\">Bill Redemption Session " . $result_dupe[0]['session_id'] . "</a>";
		elseif($btid > 0) {
			$result_maxat = sqlQuery("select maxat
				from bill_tracking as bt 
				inner join bill_tracking_record as btr on bt.id = btr.bill_tracking_id
				inner join (select bill_tracking_id, max(at) as maxat 
				from bill_tracking_record GROUP BY bill_tracking_id) as btr2
				on (btr.at = btr2.maxat AND btr.bill_tracking_id = btr2.bill_tracking_id) 
				where bt.id = $btid", $mySql);
			$status_check = sqlQuery("select status from bill_tracking_record 
				where bill_tracking_id = $btid and at = '{$result_maxat[0]['maxat']}'", $mySql); 
			if ($status_check[0]["status"] > PENDING && $_POST['status'] <= PENDING) {
				$errors[] = "You cannot unredeem a redeemed bill.  Please contact an administrator to resolve this issue.";
			} else {
				update($mySql, $btid);
				header("Location: bill_redemption_session_edit.php?action=create&session_id=$session_id&btid=$btid");
			}
		} else {
			if ($btid = create($mySql))
				header("Location: bill_redemption_session_edit.php?action=edit&session_id=$session_id&btid=$btid");
		}
} elseif (isset($_GET['btid']) && $btid > 0 
	&& $result_edit = sqlQuery("SELECT bt.*, btr.note, btr.status from bill_tracking as bt inner join bill_tracking_record as btr on bt.id = btr.bill_tracking_id where bt.id = $btid order by at desc, id desc limit 1", $mySql)) {
		foreach($result_edit[0] as $key => $value)
			if($key != "id")
				eval('$' . $key . '="' . $value . '";');
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Bill Redemption</title>
<?php include ("includes/css.inc")?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (status.value == -1) {
  	  		alert("You must select a status.");
	  		return false;
	  	} else if (serial_number.value == '') {
 	  		alert("You must enter a serial number.");
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
<?php if(isset($_GET['session_id']) && isset($_GET['action']))
	$errors[] = "The bill tracking session has been successfully created (ID: {$_GET['session_id']}).<p />You can now redeem a bill.<p />";?>
<?php displayErrors($errors); ?>
<a href="bill_redemption_session_edit.php?session_id=<?php echo $session_id?>">Edit this Bill Redemption Session</a>
<h2><?php echo isset($rid) ? "Edit a bill redemption." : "Redeem a bill."; ?></h2>
<form method="post"  name="f" action="bill_tracking.php">
	<?php if (isset($_REQUEST['btid'])) { ?>
		<input type="hidden" name="btid" value="<?php echo $btid; ?>" />
	<?php } ?>
	<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
	<input type="hidden" name="session_id" value="<?php echo $_REQUEST['session_id']?>" />
	<table>
		<tr><td>Serial Number</td><td><input type="text" name="serial_number" maxlength="45" value="<?php echo $serial_number?>"></td></tr>
		<tr><td>Status</td><td><?php echo getOptionList($statuses,'status','select a status','status', $status) ?></td></tr>
		<tr><td>Note</td><td><textarea name="note"><?php echo $note;?></textarea></td></tr>
	</table>
	<p /> 
	<input id="confirm_checkbox" type="checkbox" name="confirm" value="1" checked>Check box to confirm. 
	<BR />
	<input type="submit" name="submit" value="Submit" onclick="return validate_form(document.f);">
</form>
</body>
</html>
<?php
function getOptionList($list, $input_name, $first_option, $name_col, $status = null) {
	$dropo = "<select name='" . $input_name . "' >"; 	
	if (!empty($first_option))
		$dropo .= "<option value='-1'>" . $first_option . "</option>";	
	for ($i = 0; $i < count($list); $i++)
		$dropo .= "<option value='$i'" . ($i==$status ? ' selected' : '') . ">{$list[$i]}</option>"; 
	return $dropo . "</select>";
}
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
function create($mySql) {
	$qry = "insert into bill_tracking set serial_number = '{$_POST['serial_number']}', session_id = '{$_REQUEST['session_id']}'";
	sqlInsert($qry, $mySql);
	$btid = mysql_insert_id($mySql);
	createBillTrackingRecord($mySql, $btid);
	return $btid;
}
function update($mySql, $btid) {
	$qry = "update bill_tracking set serial_number = '{$_POST['serial_number']}', session_id = '{$_REQUEST['session_id']}' where id = $btid; ";	
	sqlUpdate($qry, $mySql);
	createBillTrackingRecord($mySql, $btid);
}
function createBillTrackingRecord($mySql, $btid) {
	$qry = "insert into bill_tracking_record set bill_tracking_id = $btid, status = {$_POST['status']}, at = now(), note = '" . mysql_real_escape_string($_POST['note']) . "'";
	sqlQuery($qry, $mySql);
}
?>