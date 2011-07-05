<?php
require_once("config.inc");
require_once("date_ranger.php");

header("Content-type:text/html");
$qry = $result = $event_id = $result_event = $result_staff = $result_sit = $result_zip = $result_now = null;
$date_to = isset($date_to) ? $date_to : null;
$qry_event = "select * from events";
$qry_staff = "select *, concat(first_name,' ',last_name) as name from staff";
$qry_sit = "select * from handout_situation";
$qry_zip = "select * from zipcode";
$qry_now = "select now() as theTime";
$result_event = sqlQuery($qry_event, $mySql);
$result_rdmsit = sqlQuery("select * from redeemed_situation", $mySql);
if (isset($_REQUEST["serial_number"])) {
	if(isTagMatch($_TAG_PREFIX )) {
		if(!empty($_POST['handout_event_id']))
			foreach($result_event as $event)
				if($_POST['handout_event_id'] == $event['id'] && @strtotime($date_to) < @strtotime($event['start_date']))
					$errors[] = "The distribution date cannot precede the event start date (" . substr($event['start_date'], 0, 10) . ")";
		if(count($errors) == 0) {
			$prefix = "The bond has been successfully %s.";
			if (isset($_POST["create"])) { 
				create($mySql, $date_to);
				$errors[] = sprintf($prefix, "distributed");
			} elseif (isset($_POST["update"])) {
				update($mySql, $date_to);
				$errors[] = sprintf($prefix, "updated");
			}	
		}
	}
	$qry = "select * from track_view where serial_number = '" . mysql_real_escape_string($_REQUEST["serial_number"]) . "';";
	$result = sqlQuery($qry, $mySql);
	$result_event = sqlQuery($qry_event, $mySql);
	$result_staff = sqlQuery($qry_staff, $mySql);
	$result_sit = sqlQuery($qry_sit, $mySql);
	$result_zip = sqlQuery($qry_zip, $mySql);
	$result_now = sqlQuery($qry_now, $mySql);
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Macon Money - Bond Tracking</title>
<?php include("includes/css.inc"); ?>
<script type="text/javascript">
function validate_form(thisform) {
	with (thisform) {
		if (handout_situation.value != "event" && handout_event_id.selectedIndex > 0) {
  	  		alert("If the distribution type is not an event, you cannot select a Macon Money Event ID.  Please select 'no event selected' before submitting.");
  			changeDistributeDate.focus();
	  		return false;
	  	}
		if (handout_situation.value == "event" && handout_event_id.selectedIndex == 0 && handout_event_other.value == '') {
  	  		alert("If the distribution type is an event, you cannot leave both the Macon Money Event ID and Event Notes blank.");
  			changeDistributeDate.focus();
	  		return false;
	  	}
<?php if(empty($result[0]["tracking_id"]) || empty($result[0]["date_distributed"])) { ?> 
  		if (!document.getElementById("date_checkbox").checked) {
  	  		alert("Date is a required field.  Please check the date confirmation checkbox before submitting.");
	  		return false;
	  	}
<?php } ?>
  	}
	return true;
}
</script>
</head>
<body>
<?php include("includes/header.inc")?>
<?php displayErrors($errors); ?>
<H2>Bond Tracking</H2>
<P />
<form method="post"  name="f" >
<input type="hidden" name="<?php echo getSessionTag($_SESSION, $_TAG_PREFIX)?>" value="true" />
<?php if  (!(isset($_REQUEST["serial_number"]))){  ?>	
<P>
	Please enter a serial_number  <input type="text" name="serial_number" value="<?php echo isset($_GET['linked_serial_number']) ? base64_decode($_GET['linked_serial_number']) : ""?>">
	<BR>

	<input type="submit" value="submit" onclick="document.f.submit(); this.disabled = true;">
</P>	

<?php } else if (!$result)  {?>
	<P>
		<font color="red">invalid serial number: <?php echo $_REQUEST["serial_number"]?> </font> 
		<BR>
		Please try again  <input type="text" name="serial_number" value="<?php echo $_REQUEST["serial_number"]?>">
		<BR>
	
		<input type="submit"  value="try again" onclick="document.f.submit(); this.disabled = true;">
	</P>	
<?php }  else { ?>


<H3>Bond Info </H3>  

<table border="1" >
<tr>
<td>serial_number</td> <td>sequence_id</td>  <td>symbol_1</td> <td>symbol_2</td>  <td>symbol_3</td>
</tr>
<tr>
	<td><?php echo $result[0]["serial_number"]?></td>  <td><?php echo $result[0]["sequence_id"]?></td>    
		<td><?php echo $result[0]["symbol_1"]?></td>  
		<td><?php echo $result[0]["symbol_2"]?></td>  <td><?php echo $result[0]["symbol_3"]?></td>  
</tr>
</table>

<?php if  ( !empty($result[0]["prize_serial_number"]) ){  ?>
<P> </P>
<table >
<tr><td colspan=2> <font color=blue> This bond has been redeemed. </td></tr> 
<tr><td> prize amount   </td> <td style="background-color: #FFDDDD"> $<?php echo $result[0]["prize_amount"]?>  </td></tr>
<tr><td> prize id   </td> <td> <?php echo $result[0]["prize_serial_number"]?>  </td></tr>
</table>
<?php } ?>

<?php if(empty($result[0]["tracking_id"])){  ?>
	<P><font color=blue>This Bond has not yet been distributed.</font></P>	
<?php } ?>
<P><a href="bond_tracking.php">track different bond</a></P>
<table>
<?php if ($result[0]["tos"] == 1) { ?>
	<tr><td> TOS Completed   </td> <td>  <input type="checkbox" name="tos" value="1" checked > </td></tr> 
<?php } else { ?>
	<tr><td> TOS Completed   </td> <td>  <input type="checkbox" name="tos" value="1"  > </td></tr>
<?php } ?>
<tr><td> Macon Money Event ID <BR> <font size="-1">(if distributed at event)</font>  </td> <td> <?php echo getOptionList($result_event,'handout_event_id','no event selected','name',$result[0]["handout_event_id"] ) ?> </td></tr>
<tr><td> Event notes  <BR> <font size="-1">(if there is no Event ID)</font>   </td> <td>  <input type="text" name="handout_event_other" value="<?php echo $result[0]["handout_event_other"]?>"> </td></tr>
<tr><td> Distribution Type  </td> <td> <?php echo getOptionListLiteral($result_sit,'handout_situation','select a situation','situation',$result[0]["handout_situation"] ) ?></td></tr>


<tr>
	<td>Distribution Date: </td> 
	<td>  
	<font size="-1">
	<?php if(!empty($result[0]["date_distributed"])) { ?>
		<?php echo substr($result[0]["date_distributed"],5,2)?> / <?php echo substr($result[0]["date_distributed"],8,2)?> / <?php echo substr($result[0]["date_distributed"],0,4)?>
	<?php } else  { ?>
		no date entered
	<?php } ?>
	<P> </P>
	 	<input type="checkbox" id="date_checkbox" name="changeDistributeDate" value="1" > <font color=blue>Check box to confirm new date entry.</font>
	 <BR>
	 <?php $ddlTo->genMonthDDL("month_to")?> / <?php $ddlTo->genDayDDL("date_to")?> / <?php $ddlTo->genYearDDL("year_to")?>
	</font>
	</td>
</tr> 	

<tr><td colspan=2> </td></tr>


<tr><td> Bond Recipient First Name </td> <td>  <input type="text" name="first_name" value="<?php echo $result[0]["first_name"]?>"> </td></tr>
<tr><td> Bond Recipient Last Name  </td> <td>  <input type="text" name="last_name" value="<?php echo $result[0]["last_name"]?>"> </td></tr>
<tr><td> Bond Recipient Zipcode   </td> <td> <?php echo getOptionListLiteral($result_zip,'zip_code','select a zipcode','zip',$result[0]["zip_code"] ) ?> </td></tr>
<tr><td> Bond Recipient Address   </td> <td>  <input type="text" name="address" value="<?php echo $result[0]["address"]?>"> </td></tr>

<tr><td colspan=2> </td></tr>


<tr><td> Distribution Staff ID  </td> <td> <?php echo getOptionList($result_staff,'staff_id_handout','select a staff member','name',$result[0]["staff_id_handout"] ) ?> </td></tr>
<tr><td> Distribution Staff Notes <BR> <font size="-1">(If Staff member does not have ID)</font>  </td> <td>  <input type="text" name="staff_other_handout" value="<?php echo $result[0]["staff_other_handout"]?>"> </td></tr>

<?php if  ( !empty($result[0]["tracking_id"]) && $result[0]["is_redeemed"] == 1 ){  ?>


	<tr><td colspan="2"> </td></tr>
	<tr><td> Redemption Type</td> <td>  <?php echo getOptionList($result_rdmsit,'redeemed_situation','select a redemption type','situation',base64_encode($result[0]['redeemed_situation'])) ?>  </td></tr>
	<tr><td> Redeemed Event ID  </td> <td>  <?php echo getOptionList($result_event,'redeemed_event_id','select an event','name',$result[0]["redeemed_event_id"] ) ?>  </td></tr>
	<tr><td> Redeemed Event Notes  </td> <td>  <input type="text" name="redeemed_event_other" value="<?php echo $result[0]["redeemed_event_other"]?>"> </td></tr>
	<tr><td> Redeeming Staff ID </td> <td> <?php echo getOptionList($result_staff,'staff_id_redeemed','select a staff member','name',$result[0]["staff_id_redeemed"] ) ?>  </td></tr>
	<tr><td> Redeeming Staff Notes <BR> <font size="-1">(If Staff member does not have ID) </td> <td>  <input type="text" name="staff_other_redeemed" value="<?php echo $result[0]["staff_other_redeemed"]?>"> </td></tr>
<tr>
	<td> date_redeemed:  </td> 
	<td>  
		<font size="-1">
	<?php if(!empty($result[0]["date_redeemed"])) { ?>
		<?php echo substr($result[0]["date_redeemed"],5,2)?> / <?php echo substr($result[0]["date_redeemed"],8,2)?> / <?php echo substr($result[0]["date_redeemed"],0,4)?>
	<?php } else  { ?>
		no date entered
	<?php } ?>
	<P>
	 	<input type="checkbox" name="changeRedeemDate" value="1" /> <font color=blue>Check box to confirm new date entry.</font>
	 <BR>
	 	<?php $ddlFrom->genMonthDDL("month_from")?> / <?php $ddlFrom->genDayDDL("date_from")?> / <?php $ddlFrom->genYearDDL("year_from")?> <br/>
	</P>
	</font>
	</td>
</tr> 	

	<tr><td> Redeemer First Name   </td> <td>  <input  type="text" name="redeemer_first_name" value="<?php echo $result[0]["redeemer_first_name"]?>"> </td></tr>
	<tr><td> Redeemer Last Name </td> <td>  <input  type="text" name="redeemer_last_name" value="<?php echo $result[0]["redeemer_last_name"]?>"> </td></tr>
	<tr><td> Redeemer Zipcode   </td> <td> <?php echo getOptionListLiteral($result_zip,'redeemer_zip','select a zipcode','zip',$result[0]["redeemer_zip"] ) ?> </td></tr>
	<tr><td>Redeemer Address  </td> <td>  <input size="45"  type="text" name="redeemer_address" value="<?php echo $result[0]["redeemer_address"]?>"> </td></tr>
<?php } ?>
<tr><td colspan="2"> </td></tr>
<tr><td> notes  </td> <td>  <textarea name="note" COLS=20 ROWS=6><?php echo $result[0]["note"]?></textarea></td></tr>
</table>
<P>
<input type="hidden" name="serial_number" value="<?php echo $result[0]["serial_number"]?>">
<?php if  ( empty($result[0]["tracking_id"]) ){  ?>
	<B>Distribute Bond</B>  <input type="submit" value="Distribute" onclick="return validate_form(document.f); document.f.submit(); this.disabled = true;">
	<input type="hidden" name="create" value="true">
<?php } else { ?>
	<B>Update Bond Tracking Record</B>  <input type="submit" value="Update" onclick="return validate_form(document.f); document.f.submit(); this.disabled = true;">
	<input type="hidden" name="update" value="true">
<?php }  ?>
</P>
<?php  } ?>
</form>
<P><a href="bond_tracking.php">track different bond</a></P>
</body>
</html>
<?php 


	function create($mySql,$date_to) {
		$qry = "insert into bond_tracking set id = null ,
			serial_number = '" . mysql_real_escape_string($_POST["serial_number"]) . "'"; 		
		if(!empty($_POST["changeDistributeDate"]))
			$qry .= ", date_distributed = '$date_to' " ; 
		if(!empty($_POST["tos"])){ $qry .= ", tos = " . mysql_real_escape_string($_POST["tos"]) ; }
		$qry .= ", handout_event_id = " . (isset($_POST["handout_event_id"]) && ($_POST["handout_event_id"] > 0) ? mysql_real_escape_string($_POST["handout_event_id"]) : " NULL ");
		if(!empty($_POST["handout_event_other"])){ $qry .= ", handout_event_other = '" . mysql_real_escape_string($_POST["handout_event_other"]) . "'"; 		}
		if(!empty($_POST["handout_situation"])){ $qry .= ", handout_situation = '" . mysql_real_escape_string($_POST["handout_situation"]) . "'"; 		}
		if(!empty($_POST["redeemed_event_id"])  && $_POST["redeemed_event_id"] > 0){ $qry .= ", redeemed_event_id = " . mysql_real_escape_string($_POST["redeemed_event_id"]) ;	}
		if(!empty($_POST["redeemed_event_other"])){ $qry .= ", redeemed_event_other = '" . mysql_real_escape_string($_POST["redeemed_event_other"]) . "'"; 		}
		if(!empty($_POST["redeemed_situation"])){ $qry .= ", redeemed_situation = '" . mysql_real_escape_string(base64_decode($_POST["redeemed_event_other"])) . "'"; 		}
		if(!empty($_POST["first_name"])){ $qry .= ", first_name = '" . mysql_real_escape_string($_POST["first_name"]) . "'"; 		}
		if(!empty($_POST["last_name"])){ $qry .= ", last_name = '" . mysql_real_escape_string($_POST["last_name"]) . "'"; 		}
		if(!empty($_POST["zip_code"])){ $qry .= ", zip_code = '" . mysql_real_escape_string($_POST["zip_code"]) . "'"; 		}
		if(!empty($_POST["address"])){ $qry .= ", address = '" . mysql_real_escape_string($_POST["address"]) . "'"; 		}
		if(!empty($_POST["staff_id_handout"]) && $_POST["staff_id_handout"] > 0  ){ $qry .= ", staff_id_handout = " . mysql_real_escape_string($_POST["staff_id_handout"]) ;	}
		if(!empty($_POST["staff_other_handout"])){ $qry .= ", staff_other_handout = '" . mysql_real_escape_string($_POST["staff_other_handout"]) . "'"; 	}	
		if(!empty($_POST["staff_id_redeemed"]) && $_POST["staff_id_redeemed"] > 0 ){ $qry .= ", staff_id_redeemed = " . mysql_real_escape_string($_POST["staff_id_redeemed"]) ;	}
		if(!empty($_POST["staff_other_redeemed"])){ $qry .= ", staff_other_redeemed = '" . mysql_real_escape_string($_POST["staff_other_redeemed"]) . "'"; }		
		if(!empty($_POST["redeemer_first_name"])){ $qry .= ", redeemer_first_name = '" . mysql_real_escape_string($_POST["redeemer_first_name"]) . "'"; }		
		if(!empty($_POST["redeemer_last_name"])){ $qry .= ", redeemer_last_name = '" . mysql_real_escape_string($_POST["redeemer_last_name"]) . "'"; }
		if(!empty($_POST["redeemer_zip"])){ $qry .= ", redeemer_zip = '" . mysql_real_escape_string($_POST["redeemer_zip"]) . "'"; }
		if(!empty($_POST["redeemer_address"])){ $qry .= ", redeemer_address = '" . mysql_real_escape_string($_POST["redeemer_address"]) . "'"; }
		if(!empty($_POST["note"])){ $qry .= ", note = '" . mysql_real_escape_string($_POST["note"]) . "' ;"; 		}
		sqlInsert($qry, $mySql);
	}


	function update($mySql, $date_to) {
		$qry = "update bond_tracking set serial_number = serial_number,
			tos = " . (!empty($_POST["tos"]) ? mysql_real_escape_string($_POST["tos"]) : " 0 "); 
		if(!empty($_POST["changeDistributeDate"]))
			$qry .= ", date_distributed = '$date_to' " ; 
		$qry .= ", handout_event_id = " . (isset($_POST["handout_event_id"]) && ($_POST["handout_event_id"] > 0) ? mysql_real_escape_string($_POST["handout_event_id"]) : " NULL ");
		if(!empty($_POST["handout_event_other"])){ $qry .= ", handout_event_other = '" . mysql_real_escape_string($_POST["handout_event_other"]) . "'"; 		}
		if(!empty($_POST["handout_situation"])){ $qry .= ", handout_situation = '" . mysql_real_escape_string($_POST["handout_situation"]) . "'"; 		}
		if(!empty($_POST["redeemed_event_id"])  && $_POST["redeemed_event_id"] > 0 ){ $qry .= ", redeemed_event_id = " . mysql_real_escape_string($_POST["redeemed_event_id"]) ;	}
		if(!empty($_POST["redeemed_event_other"])){ $qry .= ", redeemed_event_other = '" . mysql_real_escape_string($_POST["redeemed_event_other"]) . "'"; 		}
		if(!empty($_POST["redeemed_situation"])){ $qry .= ", redeemed_situation = '" . mysql_real_escape_string(base64_decode($_POST["redeemed_situation"])) . "'"; 		}
		if(!empty($_POST["first_name"])){ $qry .= ", first_name = '" . mysql_real_escape_string($_POST["first_name"]) . "'"; 		}
		if(!empty($_POST["last_name"])){ $qry .= ", last_name = '" . mysql_real_escape_string($_POST["last_name"]) . "'"; 		}
		if(!empty($_POST["zip_code"])){ $qry .= ", zip_code = '" . mysql_real_escape_string($_POST["zip_code"]) . "'"; 		}
		if(!empty($_POST["address"])){ $qry .= ", address = '" . mysql_real_escape_string($_POST["address"]) . "'"; 		}
		if(!empty($_POST["staff_id_handout"]) && $_POST["staff_id_handout"] > 0  ){ $qry .= ", staff_id_handout = " . mysql_real_escape_string($_POST["staff_id_handout"]) ;	}
		if(!empty($_POST["staff_other_handout"])){ $qry .= ", staff_other_handout = '" . mysql_real_escape_string($_POST["staff_other_handout"]) . "'"; 	}	
		if(!empty($_POST["staff_id_redeemed"]) && $_POST["staff_id_redeemed"] > 0 ){ $qry .= ", staff_id_redeemed = " . mysql_real_escape_string($_POST["staff_id_redeemed"]) ;	}
		if(!empty($_POST["staff_other_redeemed"])){ $qry .= ", staff_other_redeemed = '" . mysql_real_escape_string($_POST["staff_other_redeemed"]) . "'"; }		
		if(!empty($_POST["redeemer_first_name"])){ $qry .= ", redeemer_first_name = '" . mysql_real_escape_string($_POST["redeemer_first_name"]) . "'"; }		
		if(!empty($_POST["redeemer_last_name"])){ $qry .= ", redeemer_last_name = '" . mysql_real_escape_string($_POST["redeemer_last_name"]) . "'"; }
		if(!empty($_POST["redeemer_zip"])){ $qry .= ", redeemer_zip = '" . mysql_real_escape_string($_POST["redeemer_zip"]) . "'"; }
		if(!empty($_POST["redeemer_address"])){ $qry .= ", redeemer_address = '" . mysql_real_escape_string($_POST["redeemer_address"]) . "'"; }		
		if(!empty($_POST["note"])){ $qry .= ", note = '" . mysql_real_escape_string($_POST["note"]) . "'"; 		}
		$qry .= " where serial_number = '" . mysql_real_escape_string($_POST["serial_number"]) . "' ; ";
		sqlUpdate($qry, $mySql);
	}
	

function getOptionList($result, $input_name, $first_option, $name_col, $selected) {
	$dropo = "<select name='" . $input_name . "' >"; 
	if(isset($first_option))
		$dropo .= "<option value=''>" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++) {
		$id = $result[$i]["id"];
		$name = $result[$i][$name_col];
		$dropo .= "<option value='" . ($input_name == "redeemed_situation" ? base64_encode($name) : $id) . "' ";
		if($selected == ($input_name == "redeemed_situation" ? base64_encode($name) : $id)) 
				$dropo .=  " selected ";
		$dropo .= "    >" . $result[$i][$name_col] . "</option>"; 
	}
	return "$dropo</select>";
}
	


function getOptionListLiteral($result, $input_name, $first_option, $name_col, $selected) {
	$dropo = "<select name='" . $input_name . "' >"; 
	if (empty($selected) && !empty($first_option))
		$dropo .= "<option value='' selected >" . $first_option . "</option>";
	for ($i = 0; $i < count($result); $i++) {
		$dropo .= "<option value='" . $result[$i]["$name_col"] . "' ";
		if(!empty($selected) && $selected == $result[$i]["$name_col"])
			$dropo .=  " selected ";
		$dropo .= "    >" . $result[$i][$name_col] . "</option>"; 
	}
	return "$dropo</select>";
}
?>