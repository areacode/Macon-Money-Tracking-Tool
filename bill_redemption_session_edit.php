<?php
require_once("config.inc");

$session_id = isset($_REQUEST['session_id']) ? $_REQUEST['session_id'] : "";
$status = $note = $staff_id = $employee_id = $business_id = null;

if (isset($_REQUEST["submit"])) {
	$note = $_POST['note'];
	$staff_id = $_POST['staff_id'];
	$business_id = $_POST['business_id'];
	$employee_id = $_POST['employee_id'];
	if(empty($_POST['confirm']))
		$errors[] = "You must check the checkbox to confirm the submission.";
	else { 
		$session_id = updateSession($mySql);
		header("Location: manage_bills.php?action=edit&session_id=$session_id");
	}
} else {
	$result_edit = sqlQuery("select * from bill_redemption_session where id = $session_id", $mySql);
	foreach($result_edit[0] as $key => $value)
		if($key != "id")
			eval('$' . $key . '="' . $value . '";');
	$result_edit = sqlQuery("select business_id from employee where id = $employee_id", $mySql);
	$business_id = $result_edit[0]['business_id'];
}	

$qry_bus = "select * from business as b where id=$employee_id OR exists 
	(select * from employee as e where b.id = e.business_id AND e.is_active = 1)";
$result_bus = sqlQuery($qry_bus, $mySql);

$bids = array();
foreach($result_bus as $business)
	$bids[] = $business['id'];
$result_emps = sqlQuery("select * from employee where business_id IN (" . implode(",", $bids) . ") ORDER BY business_id ASC", $mySql);

$qry_rdmp  = "select bt.id, serial_number, status, maxat from bill_tracking as bt
	inner join bill_tracking_record as btr 
	on bt.id = btr.bill_tracking_id
	inner join (select bill_tracking_id, MAX(at) as maxat FROM bill_tracking_record GROUP BY bill_tracking_id) as btr2
	on (btr.at = btr2.maxat AND btr.bill_tracking_id = btr2.bill_tracking_id) 
	where bt.session_id = $session_id 
	order by status asc, btr.id desc ";
$result_rdmp = sqlQuery($qry_rdmp, $mySql);

$qry_staff = "select *, concat(first_name,' ',last_name) as `name` from staff where is_active = 1" . (isset($_POST["staff_id"]) ? " AND id = {$_POST['staff_id']}" : "");
$result_staff = sqlQuery($qry_staff, $mySql);  	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Bill Redemption</title>
<?php include("includes/css.inc"); ?>
<script language="Javascript">
function SelectSubCat(){
	removeAllOptions(document.f.employee_id);
	<?php for($i=0; $i<count($result_emps); $i++) {
		if($i==0 || $result_emps[$i]['business_id'] != $result_emps[$i-1]['business_id']) {
			if($i!=0) echo "\t";
			echo "if(document.f.business_id.value == " . $result_emps[$i]['business_id'] . ") {\n";
		}
		echo "\t\taddOption(document.f.employee_id,'" . $result_emps[$i]['id'] . "','" . $result_emps[$i]['first_name'] . " " . $result_emps[$i]['last_name'] . "');\n";
		if($i+1 == count($result_emps) || $result_emps[$i]['business_id'] != $result_emps[$i+1]['business_id']) 
			echo "\t}\n";
	}
	?>
}
function removeAllOptions(selectbox) {
	var i;
	for(i=selectbox.options.length-1;i>=0;i--) {
		selectbox.remove(i);
	}
}
function addOption(selectbox, value, text) {
	var optn = document.createElement("OPTION");
	optn.text = text;
	optn.value = value;
	selectbox.options.add(optn);
}
</script>
</head>
<body>
<?php include("includes/header.inc"); ?>
<?php if(isset($_GET['btid'])) { $errors[] = "The bill information has been saved successfully (ID: {$_GET['btid']})"; } ?>
<?php displayErrors($errors); ?>
<a href="bill_redemption_session_add.php">Start a new bill redemption session</a> |
<a href="bill_redemption_session_receipt.php?session_id=<?php echo $session_id?>">View receipt</a><P />
<h2>Edit Bill Redemption Session.</h2><P />
<i>Note: If a business does not appear in the list, it contains no active employees.</i><P />
<form method="post" name="f" >
	<table>
	<tr>
		<td>business</td>
		<td>
			<SELECT NAME="business_id" id="business_id" onChange="SelectSubCat();" >
				<?php foreach($result_bus as $business) { ?>
					<option value='<?php echo $business['id']?>' <?php echo $business['id']==$business_id ? " selected" : ""?>><?php echo $business['business_name']?></option>
				<?php } ?>
			</SELECT>
		</td>
	</tr>
	<tr>
		<td>employee of business</td>
		<td>
			<SELECT NAME="employee_id" id="employee_id" >
				<option value="">select an employee</option>
				<?php foreach($result_emps as $employee) { 
					if($employee['business_id'] == $business_id) { ?>
						<option value='<?php echo $employee['id']?>' <?php echo $employee['id']==$employee_id ? " selected" : ""?>><?php echo $employee['first_name'] . " " . $employee['last_name']?></option>
					<?php } ?>
				<?php } ?>
			</SELECT>		
		</td>  
	</tr>
	<tr><td>note</td><td width="300"><textarea name="note" cols="32" rows="4"><?php echo $note?></textarea></td></tr>
	<tr><td>redemption staff</td><td> <?php echo getOptionList($result_staff,'staff_id','name',$staff_id)?></td></tr>
	</table>
	<p /> 
	<input type="checkbox" name="confirm" value="1">Check box to confirm. 
	<BR />
	<input type="submit" name="submit" value="Submit">
</form>
<a href="bill_tracking.php?session_id=<?php echo $session_id?>">Redeem a new bill</a><p />
<h2>Bill Redemptions associated with this session</h2><p />
<table>
	<tr><td>ID</td><td>Serial Number</td><td>Status</td><td>Last Modified</td></tr>
	<?php foreach($result_rdmp as $redemption) { ?>
	<tr>
		<td><?php echo $redemption['id']?> (<a href="bill_tracking.php?btid=<?php echo $redemption['id']?>&session_id=<?php echo $session_id?>">Edit</a> | <a href="bill_tracking_history.php?btid=<?php echo $redemption['id']?>">History</a>)</td>
		<td><?php echo $redemption['serial_number']?></td>
		<td><?php echo $statuses[$redemption['status']]?></td>
		<td><?php echo $redemption['maxat']?></td>
	</tr>
	<?php } ?>
</table>
</body>
</html>
<?php 
function getOptionList($result, $input_name, $name_col, $selected) {
	$dropo = "<select name='" . $input_name . "' >"; 	
	for ($i = 0; $i < count($result); $i++)
		$dropo .= "<option value='" . $result[$i]["id"] . "'" . ($result[$i]["id"]==$selected ? " selected" : "") . ">" . $result[$i][$name_col] . "</option>"; 
	return $dropo . "</select>";
}

function updateSession($mySql) {
	$qry = "update bill_redemption_session
		set employee_id = {$_POST['employee_id']},
		note = '" . mysql_real_escape_string($_POST['note']) . "', 	
		staff_id = {$_POST['staff_id']}
		where id = {$_REQUEST['session_id']};";
	return sqlInsert($qry, $mySql);
}
?>
