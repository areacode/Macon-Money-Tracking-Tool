<?php
require_once("config.inc");

$problem = false;
$result1 = $result2 = $p1_zip_name = $p2_zip_name = null;
$qry1 = $qry2 = "";
$doBondsMatch = $doBondsSerialNumbersMatch = -1;  
$result_zip = sqlQuery("select * from zipcode", $mySql);
$qry_proc = "original";
if ($_POST["serial_number1"] != null && isset($_POST["serial_number1"])){
	$qry1 = "select * from track_view where serial_number = '" . mysql_real_escape_string(strtoupper($_POST["serial_number1"])) . "';";
	$result1 = sqlQuery($qry1, $mySql);
} else {
	$problem = true;
	$qry_proc = "a";
}
	
if ($_POST["serial_number2"] != null && isset($_POST["serial_number2"])){
	$qry2 = "select * from track_view where serial_number = '" . mysql_real_escape_string(strtoupper($_POST["serial_number2"])) . "';";
	$result2 = sqlQuery($qry2, $mySql);
} else {
	$problem = true;
	$qry_proc = "b";
}

$p1_zip = isset($_POST['p1_zip']) ? $_POST['p1_zip'] : null;
$p2_zip = isset($_POST['p2_zip']) ? $_POST['p2_zip'] : null;

if (!$result1 || !$result2 )  {
	$problem = true;
	$qry_proc = "c";
}

if (!$problem) {
	$qry_proc = "test - no prob";
	if ( $_POST["redeem_edit"] != null && isset($_POST["redeem_edit"]) ) { 
		$qry_proc = "test - redeem_edit ";
		 if ( isTagMatch($_TAG_PREFIX ) ){ 
			$qry_proc = "test - redeem ";
			$bond1 = mysql_real_escape_string(strtoupper($_POST["serial_number1"]));
			$bond2 = mysql_real_escape_string(strtoupper($_POST["serial_number2"]));
			
			$p1_first   = mysql_real_escape_string($_POST["p1_first"]);
			$p1_last  = mysql_real_escape_string($_POST["p1_last"]);
			$p1_add   = mysql_real_escape_string($_POST["p1_add"]);
			$p1_zip = mysql_real_escape_string($_POST["p1_zip"]);
			
			$p2_first   = mysql_real_escape_string($_POST["p2_first"]);
			$p2_last  = mysql_real_escape_string($_POST["p2_last"]);
			$p2_add   = mysql_real_escape_string($_POST["p2_add"]);
			$p2_zip = mysql_real_escape_string($_POST["p2_zip"]);
			foreach($result_zip as $zip) { //convert id to db format
				$p1_zip_name = $zip['id']==$p1_zip ? $zip['zip'] : $p1_zip_name;
				$p2_zip_name = $zip['id']==$p2_zip ? $zip['zip'] : $p2_zip_name;
			}	
						
			update($mySql,$bond1,$p1_first,$p1_last,$p1_add,$p1_zip_name);
			update($mySql,$bond2,$p2_first,$p2_last,$p2_add,$p2_zip_name);
			$errors[] = "The bond info was updated successfully.";
		}
	} else {
		$problem = true;
		$qry_proc = "test - big problem";
	}
	$result1 = sqlQuery($qry1, $mySql);
	$result2 = sqlQuery($qry2, $mySql);		
}


if (!$result1 || !$result2 )  {
	$qry_proc = "test - d";
	$problem = true;
} else if (($result1) && ($result2) ) {
	$doBondsMatch = 0;
	$doBondsSerialNumbersMatch = 0;
	if ( ($result1[0]["symbol_1"]  == $result2[0]["symbol_1"]) && ($result1[0]["symbol_2"]  == $result2[0]["symbol_2"]) && ($result1[0]["symbol_3"]  == $result2[0]["symbol_3"]))
		$doBondsMatch = 1;
	if ($result1[0]["serial_number"]  == $result2[0]["serial_number"])
		$doBondsSerialNumbersMatch = 1;	
}
?>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Macon Money - Bond Redemption</title>
<?php include("includes/css.inc")?>
</head>
<body>
<?php include("includes/header.inc")?>
<?php displayErrors($errors); ?>
<H2>Bond Redemption <?php echo $qry_proc ?></H2>
<a href="bond_redemption.php">redeem different bonds</a>
<form method="post"  name="f"  >
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<?php if  (  $doBondsMatch == 0  ){  ?>
<P><h2><font color=blue>Problem: Bond symbols do not match.  This pair is invalid.</font></h2></P>	
<?php
	$problem = true;
	} ?>
<?php if  (  $doBondsSerialNumbersMatch == 1  ){  ?>
<P><h2><font color=blue>Problem: These two bonds have the same serial number.   This pair is invalid.</font></h2></P>	
<?php 
	$problem = true;
	} ?>
<table>
<tr><td valign="top">
<!-- begin bond 1 -->
<H3>Bond 1 Info </H3>
<?php if  (  $_POST["serial_number1"] == null || !isset($_POST["serial_number1"])  ){ 
	$problem = true;
	?>
	
<P>No serial number entered.</P>	
<?php } else if (!$result1)  {
	$problem = true;
	?>
	<P><font color="red">invalid serial number: <?php echo $_POST["serial_number1"]?> </font> </P>	
<?php }  else { ?>
<P />
<table>
<tr>
<td>serial_number</td> <td>sequence_id</td>  <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td>
</tr>
<tr>
	<td><?php echo $result1[0]["serial_number"]?></td>  <td><?php echo $result1[0]["sequence_id"]?></td>    
		<td><?php echo $result1[0]["symbol_1"]?></td>  
		<td><?php echo $result1[0]["symbol_2"]?></td>  <td><?php echo $result1[0]["symbol_3"]?></td>  
</tr>
</table>
	<input type="hidden" name="serial_number1" value="<?php echo $result1[0]["serial_number"]?>">
<?php if  ( !empty($result1[0]["prize_serial_number"]) ){  ?>
<P />
<table >
<tr><td colspan=2> <font color=red> This bond has been redeemed. </td></tr> 
<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result1[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result1[0]["prize_serial_number"]?>  </td></tr>
</table>
<?php } ?>
<p />
<?php if  ( empty($result1[0]["tracking_id"]) ){  
	?>
<P />
<font color=blue>Warning: This Bond has not yet been distributed.</font><P />
You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page.

<?php } elseif ($result1[0]["tos"] != 1) { 
	
	?>
<p>
	<font color=blue>Reminder: Terms of Service has yet to be signed. </font>

</p>
<p>
	Go to the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page.
</p> 
<p>

</p>
<?php } else { ?>

<table>
<tr><td> prize amount   </td> <td> <?php echo $result1[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result1[0]["prize_serial_number"]?>  </td></tr>
</table>

<?php } } ?>


	<P> </P>

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
	

<P> </P>

</td>
<!-- end bond 1 -->

<!-- begin bond 2 -->
<td  valign="top">
<H3>Bond 2 Info </H3>  

<?php if  (  $_POST["serial_number2"] == null || !isset($_POST["serial_number2"])  ){  
	$problem = true;
	?>
	
<P>
	No serial number entered.
</P>	

<?php } else if (!$result2)  {  
	$problem = true;
	?>
	<P>
		<font color="red">invalid serial number: <?php echo $_POST["serial_number2"]?> </font> 
	</P>	
<?php }  else { ?>



<table>
<tr>
<td>serial_number</td> <td>sequence_id</td>  <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td>
</tr>
<tr>
	<td><?php echo $result2[0]["serial_number"]?></td>  <td><?php echo $result2[0]["sequence_id"]?></td>    
		<td><?php echo $result2[0]["symbol_1"]?></td>  
		<td><?php echo $result2[0]["symbol_2"]?></td>  <td><?php echo $result2[0]["symbol_3"]?></td>  
</tr>
</table>
	<input type="hidden" name="serial_number2" value="<?php echo $result2[0]["serial_number"]?>">
<?php if  ( !empty($result2[0]["prize_serial_number"]) ){  ?>
<P />
<table >
<tr><td colspan=2> <font color=red> This bond has been redeemed. </td></tr> 
<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result2[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result2[0]["prize_serial_number"]?>  </td></tr>
</table>
<?php } ?>

<P>
<?php if  ( empty($result2[0]["tracking_id"]) ){  

	?>
	
<P>
	<font color=blue>Warning: This Bond has not yet been distributed.</font>
	<P>
		You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page.
	</P>
</P>	

<?php }  elseif ($result2[0]["tos"] != 1) { 
	
	?>
<p><font color=blue>Reminder: Terms of Service has yet to be signed. </font></p>
<p>
	Go to the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page.
</p> 
<p />
<?php } else { ?>


<table>
<tr><td> prize amount   </td> <td> <?php echo $result2[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result2[0]["prize_serial_number"]?>  </td></tr>

</td>

<?php } } ?>


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

<P> </P>




<!-- end bond 2 -->
</table>
</table>
<?php if  ($problem) { ?>
	<font color=red>There was a problem with update.</font>
<?php } else {   ?>
		<input type="hidden" name="redeem_edit" value="1">
		<P> </P>	
		<B>Enter or Update Bond Redeemer Info</B>  <input name="sub" type="button" value="Enter / Update" onclick="document.f.submit(); this.disabled = true;"); ">
<?php } ?>
<P><a href="bond_tracking.php">track a bond</a></P>
</form>
</body>
</html>
<?php 
	function update($mySql, $bond_ser, $first, $last, $addr, $zip  ) {
		$qry = "update bond_tracking set serial_number = serial_number ";
		
		$qry .= ", redeemer_first_name = '$first'"; 		
		$qry .= ", redeemer_last_name = '$last'"; 
		$qry .= ", redeemer_zip = " . (!$zip ? "NULL" : "'$zip'"); 
		$qry .= ", redeemer_address = '$addr'"; 		
		$qry .= " where serial_number = '$bond_ser' ;";
		sqlUpdate($qry, $mySql);
	}
?>

