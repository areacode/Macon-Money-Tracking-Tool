<?php
require_once("config.inc");

$doBondsMatch = $doBondsSerialNumbersMatch = $doSerialsMatchConfirms = -1;
$b1_redeemed = $b2_redeemed = $problem = false;
$qry1 = $qry2 = "";
$result1 = $result2 = $p1_zip_name = $p2_zip_name = null;
$zips = array();
$qry_proc = "original";
$result_zip = sqlQuery("select * from zipcode", $mySql);
$serial_number1 = isset($_POST['serial_number1']) ? trim($_POST['serial_number1']) : null;
$serial_number1_confirm = isset($_POST['serial_number1_confirm']) ? trim($_POST['serial_number1_confirm']) : null;
$serial_number2 = isset($_POST['serial_number2']) ? trim($_POST['serial_number2']) : null;
$serial_number2_confirm = isset($_POST['serial_number2_confirm']) ? trim($_POST['serial_number2_confirm']) : null;
$p1_zip = isset($_POST['p1_zip']) ? $_POST['p1_zip'] : "0";
$p2_zip = isset($_POST['p2_zip']) ? $_POST['p2_zip'] : "0";
$p1_tos = isset($_POST['p1_tos']);
$p2_tos = isset($_POST['p2_tos']);

if (isset($serial_number1) && isset($serial_number1_confirm)) {
	if (strtoupper($serial_number1) != strtoupper($serial_number1_confirm)) {
		$doSerialsMatchConfirms = 1;
		$problem = true;
	} else {
		$qry1 = "select * from track_view where serial_number = '" . mysql_real_escape_string(strtoupper($serial_number1)) . "';";
		$result1 = sqlQuery($qry1, $mySql);
	}
} else
	$problem = true;		

if (isset($serial_number2) && $serial_number2_confirm) {
	if (strtoupper($serial_number2) != strtoupper($serial_number2_confirm)) {
		$doSerialsMatchConfirms = 1;
		$problem = true;
	} else {
		$qry2 = "select * from track_view where serial_number = '" . mysql_real_escape_string(strtoupper($serial_number2)) . "';";
		$result2 = sqlQuery($qry2, $mySql);
	}	
} else
	$problem = true;

if($result1 && $result2) {
	$doBondsMatch = 0;
	$doBondsSerialNumbersMatch = 0;
	if(($result1[0]["symbol_1"]  == $result2[0]["symbol_1"]) && ($result1[0]["symbol_2"]  == $result2[0]["symbol_2"]) && ($result1[0]["symbol_3"] == $result2[0]["symbol_3"]))
		$doBondsMatch = 1;	
	if($result1[0]["serial_number"]  == $result2[0]["serial_number"])
		$doBondsSerialNumbersMatch = 1;
	if(!empty($result1[0]["prize_serial_number"]))
		$b1_redeemed = $problem = true;
	if(!empty($result2[0]["prize_serial_number"]))
		$b2_redeemed = $problem = true;
}
if($doBondsMatch == 0 || $doBondsSerialNumbersMatch == 1 || !$result1 || !$result2)
	$problem = true;
if (!$problem) {
	$qry_proc = "test - no prob";
	if ($_POST["redeem"] != null && isset($_POST["redeem"]) && isTagMatch($_TAG_PREFIX )  ){ 
		if (checkAll()) {
			$qry_proc = "test - redeem ";
			$bond1 = mysql_real_escape_string(strtoupper($serial_number1));
			$bond2 = mysql_real_escape_string(strtoupper($_POST["serial_number2"]));
			if($p1_tos) mysql_query("update bond_tracking set tos = 1 where serial_number = '$bond1'", $mySql);
			if($p2_tos) mysql_query("update bond_tracking set tos = 1 where serial_number = '$bond2'", $mySql);
			
			$evId   = mysql_real_escape_string($_POST["redeemed_event_id"]);
			$evOth  = mysql_real_escape_string($_POST["redeemed_event_other"]);
			$rdmSit  = mysql_real_escape_string(base64_decode($_POST["redeemed_situation"]));
			$stId   = mysql_real_escape_string($_POST["staff_id_redeemed"]);
			$stOth  = mysql_real_escape_string($_POST["staff_other_redeemed"]);
	
			$p1_first   = mysql_real_escape_string($_POST["p1_first"]);
			$p1_last  = mysql_real_escape_string($_POST["p1_last"]);
			$p1_add   = mysql_real_escape_string($_POST["p1_add"]);
			
			$p2_first   = mysql_real_escape_string($_POST["p2_first"]);
			$p2_last  = mysql_real_escape_string($_POST["p2_last"]);
			$p2_add   = mysql_real_escape_string($_POST["p2_add"]);
			foreach($result_zip as $zip) { //convert id to db format
				$p1_zip_name = $zip['id']==$p1_zip ? $zip['zip'] : $p1_zip_name;
				$p2_zip_name = $zip['id']==$p2_zip ? $zip['zip'] : $p2_zip_name;
			}		
			$qry_proc = "call redeem('$bond1', '$bond2', " . (empty($evId) ? "NULL" : $evId) . ",'$evOth','$rdmSit',$stId, '$stOth', '$p1_first', '$p1_last', '$p1_add', " . (!$p1_zip_name ? "NULL" : "'$p1_zip_name'") . ", '$p2_first', '$p2_last', '$p2_add',  " . (!$p2_zip_name ? "NULL" : "'$p2_zip_name'") . ")";
			mysql_query($qry_proc, $mySql) or die(mysql_error());
		} else
			$problem = true;
		$result1 = sqlQuery($qry1, $mySql);
		$result2 = sqlQuery($qry2, $mySql);		
	}
} else
	$qry_proc = "problem with qry_proc";
		
if (!$result1 || !$result2 )
	$problem = true;	
?>


<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Macon Money - - Bond Redemption</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc"); ?>
<H2>Bond Redemption</H2>
<P />
<P />
<P><a href="bond_redemption.php">redeam different bonds</a></P>
<form method="post"  name="f"  action="redeem_edit.php" >
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />

<?php if($doBondsMatch == 0) {  
	$problem = true;?>
	<P /><h2><font color=blue>Problem: Bond symbols do not match.  This pair is invalid.</font></h2>	
<?php } ?>
<?php if($doBondsSerialNumbersMatch == 1) { 
	$problem = true;?>
	<P /><h2><font color=blue>Problem: These two bonds have the same serial number.   This pair is invalid.</font></h2>
<?php } ?>
<table>
<tr><td valign="top">
<!-- begin bond 1 -->
<H3>Bond 1 Info </H3>

  
<?php if(!isset($serial_number1)) { 
	$problem = true;?>
	<P>No serial number entered.</P>
<?php } else if (!$result1)  {
	$problem = true;?>
	<P><font color="red">invalid serial number: <?php echo $serial_number1?></font></P>	
<?php } else { ?>
	<P />
	<?php if ($b1_redeemed) { ?>
	<P><h3><font color=red>Problem: Bond already redeemed.</font></h3></P>
	<?php } ?>
	<table>
		<tr>
			<td>serial_number</td> <td>sequence_id</td>  <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td>
		</tr>
		<tr>
			<td><?php echo $result1[0]["serial_number"]?></td>
			<td><?php echo $result1[0]["sequence_id"]?></td>    
			<td><?php echo $result1[0]["symbol_1"]?></td>  
			<td><?php echo $result1[0]["symbol_2"]?></td>
			<td><?php echo $result1[0]["symbol_3"]?></td>  
		</tr>
	</table>
	<input type="hidden" name="serial_number1" value="<?php echo $result1[0]["serial_number"]?>">
	
	<?php if  ( !empty($result1[0]["prize_serial_number"]) ) {  ?>
		<P />
		<table>
			<tr><td colspan=2> <font color=red> This bond has been redeemed. </td></tr> 
			<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result1[0]["prize_amount"]?>  </td></tr>
			<tr><td> prize id   </td> <td> <?php echo $result1[0]["prize_serial_number"]?>  </td></tr>
		</table>
	<?php } ?>
	<p>
	<?php if(empty($result1[0]["tracking_id"])) { ?>
		<P /><font color=blue>Warning: This Bond has not yet been distributed.</font>
		<P/ >You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page.
	<?php } elseif ($result1[0]["tos"] != 1) { ?>
	<p /><font color=blue>Problem: Terms of Service has yet to be signed. </font>
	<p />Go to the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page.
	<p/ >
	<?php } else { ?>
	
	<table>
		<tr><td> prize amount   </td> <td> <?php echo $result1[0]["prize_amount"]?>  </td></tr>
		<tr><td> prize id   </td> <td> <?php echo $result1[0]["prize_serial_number"]?>  </td></tr>
	</table>
	<?php } ?>
<?php } ?>
<P />
<table>
	<tr><td> redeemer 1 first name  </td> <td>  <input type="text" name="p1_first" value="<?php echo $_POST["p1_first"]?>"> </td> <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_first.value = document.f.p1_first.value"> </td> </tr>
	<tr><td> redeemer 1 last name  </td> <td>  <input type="text" name="p1_last" value="<?php echo $_POST["p1_last"]?>"> </td> <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_last.value = document.f.p1_last.value"> </td>   </tr>
	<tr>
		<td>redeemer 1 zip code</td>
		<td>
			<select name="p1_zip">
				<option value="0">no zip selected</option>
				<?php foreach($result_zip as $zip) { ?>
					<option value="<?php echo $zip['id']?>" <?php echo $zip['id']==$p1_zip ? " selected" : ""?>><?php echo $zip['zip']?></option>
				<?php } ?>
			</select>
		</td>
		<td><input type="button" value="copy ->" onclick="document.f.p2_zip.value = document.f.p1_zip.value"></td>
	</tr>
	<tr><td> redeemer 1 address </td> <td>  <input type="text" name="p1_add" value="<?php echo $_POST["p1_add"]?>"> </td>  <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_add.value = document.f.p1_add.value"> </td>  </tr>
</table>
<P />
</td>
<!-- end bond 1 -->
<!-- begin bond 2 -->
<td  valign="top">
<H3>Bond 2 Info </H3>  

<?php if($_POST["serial_number2"] == null || !isset($_POST["serial_number2"])) {  
	$problem = true; ?>
	
<P />No serial number entered.	

<?php } else if (!$result2)  {  
	$problem = true;?>
	<P><font color="red">invalid serial number: <?php echo $_POST["serial_number2"]?> </font> 
	</P>	
<?php } else { ?>
	<?php if ($b2_redeemed) { ?>
		<P><h3><font color=red>Problem: Bond already redeemed.</font></h3></P>
	<?php } ?>
	<table>
	<tr><td>serial_number</td> <td>sequence_id</td>  <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td></tr>
	<tr>
		<td><?php echo $result2[0]["serial_number"]?></td>  <td><?php echo $result2[0]["sequence_id"]?></td>    
			<td><?php echo $result2[0]["symbol_1"]?></td>  
			<td><?php echo $result2[0]["symbol_2"]?></td>  <td><?php echo $result2[0]["symbol_3"]?></td>  
	</tr>
	</table>
	<input type="hidden" name="serial_number2" value="<?php echo $result2[0]["serial_number"]?>">
	<?php if(!empty($result2[0]["prize_serial_number"])) { ?>
	<P />
	<table>
	<tr><td colspan=2> <font color=red> This bond has been redeemed. </td></tr> 
	<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result2[0]["prize_amount"]?>  </td></tr>
	<tr><td> prize id   </td> <td> <?php echo $result2[0]["prize_serial_number"]?>  </td></tr>
	</table>
	<?php } ?>
	<P>
	<?php if( empty($result2[0]["tracking_id"])) { ?>
	<P>
		<font color=blue>Warning: This Bond has not yet been distributed.</font>
		<P>
			You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page.
		</P>
	</P>	

	<?php } elseif ($result2[0]["tos"] != 1) { ?>
		<p><font color=blue>Problem: Terms of Service has yet to be signed.</font></p>
		<p>Go to the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page.</p> 
		<p />
	<?php } else { ?>
		<table>
		<tr><td> prize amount   </td> <td> <?php echo $result2[0]["prize_amount"]?>  </td></tr>
		<tr><td> prize id   </td> <td> <?php echo $result2[0]["prize_serial_number"]?>  </td></tr>
		</td>	
	<?php } ?>
<?php } ?>
<P />
<table>
	<tr><td> redeemer 2 first name  </td> <td>  <input type="text" name="p2_first" value="<?php echo $_POST["p2_first"]?>"> </td></tr>
	<tr><td> redeemer 2 last name  </td> <td>  <input type="text" name="p2_last" value="<?php echo $_POST["p2_last"]?>"> </td></tr>
	<tr>
		<td> redeemer 2 zip code</td>
		<td>
			<select name="p2_zip">
				<option value="0">no zip selected</option>
				<?php foreach($result_zip as $zip) { ?>
					<option value="<?php echo $zip['id']?>" <?php echo $zip['id']==$p2_zip ? " selected" : ""?>><?php echo $zip['zip']?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
	<tr><td> redeemer 2 address </td> <td>  <input type="text" name="p2_add" value="<?php echo $_POST["p2_add"]?>"> </td></tr>
</table>	
<P />
<!-- end bond 2 -->
</table>
</table>
<?php if($problem) { ?>
	<font color=red>There was a problem with this redemption attempt.</font>
	<P><a href="bond_redemption.php">try the bond redemption page again</a></P>
<?php } else {   ?>
	<input type="hidden" name="redeem_edit" value="1">
	<P />	
	<B>Enter or Update Bond Redeemer Info</B>  <input name="sub" type="button" value="Enter / Update" onclick="document.f.submit(); this.disabled = true;"); ">
<?php } ?>
<P><a href="bond_tracking.php">track a bond</a></P>
</form>
</body>
</html>
<?php 
	function checkAll() {
		$all = true;
		if (base64_decode($_POST['redeemed_situation']) == "event" && empty($_POST["redeemed_event_id"]) && empty($_POST["redeemed_event_other"]) ){ $all = false ;}
		if (empty($_POST["staff_id_redeemed"]) && empty($_POST["staff_other_redeemed"]) ){ $all = false ;}		
		return $all;
	}
?>