<?php
require_once("config.inc");
define("DEFAULT_STAFF_ID", 4);

$events = $staff = $sits = array();
$result_event = sqlQuery("select * from events", $mySql);
foreach($result_event as $event)
	$events["{$event['id']}"] = $event['name'];
$events[''] = "No event selected";
$result_staff = sqlQuery("select *, concat(first_name,' ',last_name) as name from staff", $mySql);
foreach($result_staff as $staff_member)
	$staff["{$staff_member['id']}"] = $staff_member['name'];	
$staff[''] = "No staff selected";
$result_zip = sqlQuery("select * from zipcode", $mySql);
$result_rdmsit = sqlQuery("select * from redeemed_situation", $mySql);

$doBondsMatch = $doBondsSerialNumbersMatch = -1;  
$result1 = $result2 = null;
$qry1 = $qry2 = "";
$problem = $b1_redeemed = $b2_redeemed = false;
$p1_zip = isset($_POST['p1_zip']) ? $_POST['p1_zip'] : null;
$p2_zip = isset($_POST['p2_zip']) ? $_POST['p2_zip'] : null;
if (isset($_POST["serial_number1"]))
	$result1 = sqlQuery("select * from track_view where serial_number = '" . mysql_real_escape_string($_POST["serial_number1"]) . "';", $mySql);
if (isset($_POST["serial_number2"]))
	$result2 = sqlQuery("select * from track_view where serial_number = '" . mysql_real_escape_string($_POST["serial_number2"]) . "';", $mySql);
if ($result1 && !empty($result1[0]["prize_serial_number"]))
	$b1_redeemed = $problem = true;
if($result2 && !empty($result2[0]["prize_serial_number"]))
	$b2_redeemed = $problem = true;				
if($result1 && $result2) {
	$doBondsMatch = $doBondsSerialNumbersMatch = 0;
	if ($result1[0]["symbol_1"]  == $result2[0]["symbol_1"] && $result1[0]["symbol_2"]  == $result2[0]["symbol_2"] && $result1[0]["symbol_3"]  == $result2[0]["symbol_3"])
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
<script>
	function validate_form(thisform) {
		with(thisform) {
			var error = '';
			if(redeemed_situation.value == '') {
				error = 'You must select a redemption type';
			} else if(redeemed_situation[redeemed_situation.selectedIndex].innerHTML!='event' && (redeemed_event_id.value != '' || redeemed_event_other.value != '')) {
				error = 'You cannot specify redemption event information (i.e. ID, notes) unless the event redemption type is selected';
			} else if(redeemed_situation[redeemed_situation.selectedIndex].innerHTML=='event' && redeemed_event_id.value == '' && redeemed_event_other.value == '') {
				error = 'You must include event information';
			} else if (staff_id_redeemed.value == '' && staff_other_redeemed.value == '' ) {
				error = 'You must include staff information';
			} else if (document.f.p1_tos && !document.f.p1_tos.checked) {
				error = 'You must check the TOS checkbox for the first bond.';				
			} else if (document.f.p2_tos && !document.f.p2_tos.checked) {
				error = 'You must check the TOS checkbox for the second bond.';				
			}
			if (error == '') {
				document.f.sub.disabled = true;
				document.f.action='redeem.php'; 
				document.f.submit(); 			
			} else {
				document.getElementById('missing').innerHTML= "<font color=red size='+1'>" + error + "</font>";	
			}
		}
	}
</script>
</head>
<body>
<?php include("includes/header.inc")?>
<H2>Bond Redemption</H2>
<P />
<P><a href="bond_redemption.php">Redeem different bonds</a></P>
<form method="post" name="f" >
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<?php if($doBondsMatch == 0) { ?>	
<P />
<h2><font color=blue>Problem: Bond symbols do not match.  This pair is invalid.</font></h2>	
<?php $problem = true; 
	  } 
	  if  (  $doBondsSerialNumbersMatch == 1  ) { ?>
	  <P /><h2><font color=blue>Problem: These two bonds have the same serial number.   This pair is invalid.</font></h2>	
	<?php $problem = true;
	  } ?>
<table>
<tr><td valign="top">
<!-- BEGIN BOND 1 -->
<H3>Bond 1 Info </H3>  
<?php if (!(isset($_POST["serial_number1"]))){ 
	$problem = true; ?>
	<P />
	Please enter a serial_number  <input type="text" name="serial_number1" ><BR />
	<input type="submit" value="submit" onclick="document.f.submit();  this.disabled = true;">	
<?php } else if (!$result1)  {
	$problem = true; ?>
	<P />
	<font color="red">invalid serial number: <?php echo $_POST["serial_number1"]?> </font> <BR />
	Please try again  <input type="text" name="serial_number1" value="<?php echo $_POST["serial_number1"]?>"><BR />
	<input type="submit"  value="try again" onclick="document.f.submit();  this.disabled = true;">	
<?php }  else { ?>
	<P />
	<input type="text" name="serial_number1" value="<?php echo $_POST["serial_number1"]?>"><BR />
	<input type="submit"  value="re-submit" onclick="document.f.submit();  this.disabled = true;"><P />
<?php if ($b1_redeemed) { ?>
<P /><h2><font color=red>Problem: Bond already redeemed.</font></h2>
<?php } ?>
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
<?php if  ( !empty($result1[0]["prize_serial_number"]) ) { ?>
<P />
<table>
<tr><td colspan=2><font color="red"> This bond has been redeemed.</font></td></tr> 
<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result1[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result1[0]["prize_serial_number"]?>  </td></tr>
</table>
<?php } ?>
<p />
<?php if  ( empty($result1[0]["tracking_id"]) ) { ?>
	<font color=blue>Warning: This Bond has not yet been distributed.</font><P />
	You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page.
<?php } elseif ($result1[0]["tos"] != 1) {
	if(!$result1[0]["handout_situation"]=="direct mail") $problem = true; 
	$p1_tos = isset($_REQUEST['p1_tos']) && $_REQUEST['p1_tos']=="true";?>
	<?php if(!$p1_tos) { ?>
		<font color=blue>Problem: Terms of Service has yet to be signed. </font><p />
	<?php } ?>
	<?php if($result1[0]["handout_situation"]=="direct mail") { ?> 
		TOS Completed <input type="checkbox" name="p1_tos" value="true" <?php echo $p1_tos ? "checked" : ""; ?>><p />
	<?php } ?>
	Go to the <a href="bond_tracking.php?serial_number=<?php echo $result1[0]["serial_number"]?>">bond tracking</a> page. <p />
<?php }  else { ?>
<table>
<tr><td> TOS Completed   </td> <td> <?php echo $result1[0]["tos"]?>  </td></tr>
<tr><td> macon money event id  </td> <td>  <?php echo $events["{$result1[0]['handout_event_id']}"]?>   </td></tr>
<tr><td> handout event other  </td> <td>  <?php echo $result1[0]["handout_event_other"]?> </td></tr>
<tr><td> distribution type</td> <td>  <?php echo $result1[0]["handout_situation"]?>  </td></tr>
<tr><td> date distributed  </td> <td>  <?php echo $result1[0]["date_distributed"]?>  </td></tr>
<tr><td> redemption type  </td> <td>  <?php echo $result1[0]["redeemed_situation"]?>  </td></tr>
<tr><td> redeemed event id  </td> <td> <?php echo $events["{$result1[0]['redeemed_event_id']}"]?>  </td></tr>
<tr><td> redeemed event other  </td> <td>  <?php echo $result1[0]["redeemed_event_other"]?>  </td></tr>
<tr><td> date redeemed  </td> <td>  <?php echo $result1[0]["date_redeemed"]?> </td></tr>
<tr><td> first name  </td> <td>  <?php echo $result1[0]["first_name"]?> </td></tr>
<tr><td> last name  </td> <td>  <?php echo $result1[0]["last_name"]?> </td></tr>
<tr><td> zipcode  </td> <td>  <?php echo $result1[0]["zip_code"]?> </td></tr>
<tr><td> address  </td> <td>  <?php echo $result1[0]["address"]?>  </td></tr>
<tr><td> distribution staff id  </td> <td> <?php echo $staff[$result1[0]['staff_id_handout']]?>   </td></tr>
<tr><td> staff other handout  </td> <td> <?php echo $result1[0]["staff_other_handout"]?>  </td></tr>
<tr><td> redeeming staff id </td> <td> <?php echo $staff["{$result1[0]['staff_id_redeemed']}"]?> </td></tr>
<tr><td> staff other redeemed  </td> <td> <?php echo $result1[0]["staff_other_redeemed"]?> </td></tr>
<tr><td> notes </td> <td><?php echo $result1[0]["note"]?></td></tr>
<tr><td> tracking test  </td> <td> <?php echo $result1[0]["tracking_test"]?> </td></tr>
</table>
<input type="hidden" name="serial_number1_confirm" value="<?php echo $result1[0]["serial_number"]?>">
<?php } } ?>
	<P />
	<table>
		<tr><td> redeemer 1 first name  </td> <td>  <input type="text" name="p1_first" value="<?php echo isset($_POST["p1_first"]) ? $_POST["p1_first"] : ''?>"> </td> <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_first.value = document.f.p1_first.value"> </td> </tr>
		<tr><td> redeemer 1 last name  </td> <td>  <input type="text" name="p1_last" value="<?php echo isset($_POST["p1_last"]) ? $_POST["p1_last"] : ''?>"> </td> <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_last.value = document.f.p1_last.value"> </td>   </tr>
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
		<tr><td> redeemer 1 address </td> <td>  <input type="text" name="p1_add" value="<?php echo isset($_POST["p1_add"]) ? $_POST["p1_add"] : ''?>"> </td>  <td align="right"> <input type="button" value="copy ->" onclick="document.f.p2_add.value = document.f.p1_add.value"> </td>  </tr>
	</table>
<P />
</td>
<!-- END BOND 1 -->

<!-- BEGIN BOND 2 -->
<td  valign="top">
<H3>Bond 2 Info </H3>  
<?php if  (!(isset($_POST["serial_number2"]))  ){  
	$problem = true;
	?>
	<P />
	Please enter a serial_number  <input type="text" name="serial_number2" ><BR />
	<input type="submit" value="submit" onclick="document.f.submit();  this.disabled = true;" />	

<?php } else if (!$result2)  {  
	$problem = true;
	?>
	<P />
	<font color="red">invalid serial number: <?php echo $_POST["serial_number2"]?> </font> <BR />
	Please try again  <input type="text" name="serial_number2" value="<?php echo $_POST["serial_number2"]?>"><BR/>
	<input type="submit"  value="try again" onclick="document.f.submit();  this.disabled = true;">
<?php }  else { ?>
	<P />
	<input type="text" name="serial_number2" value="<?php echo $_POST["serial_number2"]?>"><BR />
	<input type="submit"  value="re-submit" onclick="document.f.submit();  this.disabled = true;"><P />
<?php if ($b2_redeemed) { ?>
<P><h3><font color=red>Problem: Bond already redeemed.</font></h3></P>
<?php } ?>
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
<?php if  ( !empty($result2[0]["prize_serial_number"]) ){  ?>
<P />
<table> 
<tr><td colspan=2> <font color=red> This bond has been redeemed. </td></tr> 
<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result2[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result2[0]["prize_serial_number"]?>  </td></tr>
</table>
<?php } ?>
<P />
<?php if  ( empty($result2[0]["tracking_id"]) ) {  ?>
<P />
<font color=blue>Warning: This Bond has not yet been distributed.</font><P />
		You may distribute it now on the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page.
<?php } elseif ($result2[0]["tos"] != 1) {
	$p2_tos = isset($_REQUEST['p2_tos']) && $_REQUEST['p2_tos']=="true";
	if(!$result2[0]["handout_situation"]=="direct mail") $problem = true; ?>
	<?php if(!$p2_tos) { ?>
		<font color=blue>Problem: Terms of Service has yet to be signed. </font><p />
	<?php } ?>
	<?php if($result2[0]["handout_situation"]=="direct mail") { ?> 
		TOS Completed <input type="checkbox" name="p2_tos" value="true" <?php echo $p2_tos ? "checked" : ""; ?>><p />
	<?php } ?>
	Go to the <a href="bond_tracking.php?serial_number=<?php echo $result2[0]["serial_number"]?>">bond tracking</a> page. <p />
<?php }  else { ?>
<table>
<tr><td> TOS Completed   </td> <td> <?php echo $result2[0]["tos"]?>  </td></tr>
<tr><td> handout event id  </td> <td>  <?php echo $events["{$result2[0]['handout_event_id']}"]?>   </td></tr>
<tr><td> handout event other  </td> <td>  <?php echo $result2[0]["handout_event_other"]?> </td></tr>
<tr><td> distribution type</td> <td>  <?php echo $result2[0]["handout_situation"]?>  </td></tr>
<tr><td> date distributed  </td> <td>  <?php echo $result2[0]["date_distributed"]?>  </td></tr>
<tr><td> redemption type </td> <td>  <?php echo $result2[0]["redeemed_situation"]?>  </td></tr>
<tr><td> redeemed event id  </td> <td> <?php echo $events["{$result2[0]['redeemed_event_id']}"]?>  </td></tr>
<tr><td> redeemed event other  </td> <td>  <?php echo $result2[0]["redeemed_event_other"]?>  </td></tr>
<tr><td> date redeemed </td> <td>  <?php echo $result2[0]["date_redeemed"]?> </td></tr>
<tr><td> first name </td> <td>  <?php echo $result2[0]["first_name"]?> </td></tr>
<tr><td> last name </td> <td>  <?php echo $result2[0]["last_name"]?> </td></tr>
<tr><td> zipcode </td> <td>  <?php echo $result2[0]["zip_code"]?> </td></tr>
<tr><td> address </td> <td>  <?php echo $result2[0]["address"]?>  </td></tr>
<tr><td> distribution staff id </td> <td> <?php echo $staff["{$result2[0]['staff_id_handout']}"]?>   </td></tr>
<tr><td> staff other handout </td> <td> <?php echo $result2[0]["staff_other_handout"]?>  </td></tr>
<tr><td> distribution staff id </td> <td> <?php echo $staff["{$result2[0]['staff_id_redeemed']}"]?> </td></tr>
<tr><td> staff other redeemed  </td> <td> <?php echo $result2[0]["staff_other_redeemed"]?> </td></tr>
<tr><td> notes  </td> <td><?php echo $result2[0]["note"]?></td></tr>
<tr><td> tracking test  </td> <td> <?php echo $result2[0]["tracking_test"]?> </td></tr>
</table>
<?php } } ?>
<P />
<table>
	<tr><td> redeemer 2 first name  </td> <td>  <input type="text" name="p2_first" value="<?php echo isset($_POST["p2_first"]) ? $_POST["p2_first"] : ''?>"> </td></tr>
	<tr><td> redeemer 2 last name  </td> <td>  <input type="text" name="p2_last" value="<?php echo isset($_POST["p2_last"]) ? $_POST["p2_last"] : ''?>"> </td></tr>
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
	<tr><td> redeemer 2 address </td> <td>  <input type="text" name="p2_add" value="<?php echo isset($_POST["p2_add"]) ? $_POST["p2_add"] : ''?>"> </td></tr>
</table>	
<P />
</td>
</tr>
<!-- end bond 2 -->
 


</table>
<P />
<table>
<tr><td> redemption type</td> <td>  <?php echo getOptionList($result_rdmsit,'redeemed_situation','select a redemption type','situation',isset($_POST["redeemed_situation"]) ? $_POST['redeemed_situation'] : "") ?>  </td></tr>
<tr><td> redeemed event ID </td> <td>  <?php echo getOptionList($result_event,'redeemed_event_id','select an event','name',isset($_POST["redeemed_event_id"]) ? $_POST['redeemed_event_id'] : "") ?>  </td></tr>
<tr><td> redeemed event notes  </td> <td>  <input type="text" name="redeemed_event_other" maxlength="255" size="47" value="<?php echo isset($result[0]["redeemed_event_other"]) ? $result[0]["redeemed_event_other"] : ""?>"> </td></tr>
<tr><td> redeeming staff ID</td> <td> <?php echo getOptionList($result_staff,'staff_id_redeemed','select a staff member','name',isset($_POST["staff_id_redeemed"]) ? $_POST['staff_id_redeemed'] : DEFAULT_STAFF_ID) ?>  </td></tr>
<tr><td> redeeming staff notes  </td> <td>  <input type="text" name="staff_other_redeemed" maxlength="255" size="47" value="<?php echo isset($result[0]["staff_other_redeemed"]) ? $result[0]["staff_other_redeemed"] : ""?>"> </td></tr>
</table>
<span id="missing" ></span>
<P />
<?php 	if ($problem == false)  { ?>
	<?php if  ( empty($result1[0]["tracking_id"])  ||  empty($result2[0]["tracking_id"])   ){ ?>
		<font color=blue>Warning: one or both of these bonds has not been set as distributed in the tracking system.</font>
		<P> </P>  
		<?php } ?>
		<input type='hidden' name='serial_number1_confirm' value='<?php echo $result1[0]["serial_number"]?>' >
		<input type='hidden' name='serial_number2_confirm' value='<?php echo $result2[0]["serial_number"]?>' >
		<input type="hidden" name="redeem" value="true">
		<B>Redeem These Bonds</B>  <input name="sub" type="button" value="Redeem" onclick="validate_form(document.f); ">
<?php } ?>	
</form>
<P><a href="bond_redemption.php">redeem different bonds</a></P>
<P><a href="bond_tracking.php">track a bond</a></P>
</body>
</html>
<?php 
function getOptionList($result, $input_name, $first_option, $name_col, $selected) {
	$dropo = "<select id='" . $input_name . "' name='" . $input_name . "' >"; 
	if(isset($first_option))
		$dropo .= "<option value=''>" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++){
		$id = $result[$i]["id"];
		$name = $result[$i][$name_col];
		$dropo .= "<option value='" . ($input_name == "redeemed_situation" ? base64_encode($name) : $id) . "' ";
		if($selected == ($input_name == "redeemed_situation" ? base64_encode($name) : $id))
			$dropo .=  " selected ";
		$dropo .= "    >" . $result[$i][$name_col] . "</option>"; 
	}
	return "$dropo</select>";
}
?>

