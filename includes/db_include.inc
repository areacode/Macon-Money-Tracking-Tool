<?php
if (!isset ( $db_user )){
	switch($_SERVER['SERVER_NAME']) {
		default:			
			$db_user = "username";
			$db_pass = "password";
			break;
	}
} else {
	echo $db_user;
}

function sqlConnect($_db_user, $_db_pass) {	

	$db = "staging";
	$user = $_db_user;
	$password = $_db_pass;
	$host = "mysql";
	switch($_SERVER['SERVER_NAME']) {
		case 'production.com':
			$db = "staging";
			break;
		case 'staging.com': 
			$db = "production";
			break;
	}
	$mysql = mysql_connect($host, $user, $password, false, 65536) or die(mysql_error()); // 65536 == CLIENT_MULTI_STATEMENTS
	$mySql = mysql_connect($host, $user, $password) or die(mysql_error());
	mysql_select_db($db, $mySql) or die(mysql_error());
	mysql_query("SET time_zone = '-4:00';");
	
	return $mySql;
}

function sqlQuery($query, $mySql) {	

	$result = mysql_query($query, $mySql) or die(mysql_error());
	$num_rows = mysql_num_rows($result);
	$num_fields = mysql_num_fields($result);

	$row_cnt = 0;
	while ($row_data = mysql_fetch_array($result)) {
		for ($cnt = 0; $cnt < $num_fields; $cnt++) {
			$field_name = mysql_field_name($result, $cnt);
			$data[$row_cnt][$field_name] = $row_data[$cnt];
		}
		$row_cnt++;
	}

	if (!isset ($data)){
		$data = array();
	}

	return $data;
}

function sqlUpdate($query, $mySql) {   
	$result = mysql_query($query, $mySql) or die(mysql_error());
	return $result;
}

function sqlInsert($query, $mySql) {
	$result = sqlUpdate($query, $mySql);		
	return $result;
}

function convert_smart_quotes($string)  { 
    $search = array(chr(145), chr(146), chr(147), chr(148), chr(151)); 
    $replace = array("'", "'", '"', '"', '-'); 
    return str_replace($search, $replace, $string); 
} 

$mySql = sqlConnect($db_user, $db_pass);

function isTagMatch($prefix) {

	$isTagMatched = false;	
	
	foreach ( $_POST as $ind=>$val ) {
		if(strpos($ind,$prefix) == 0) {
		 	// tag found in post
			if (isset($_SESSION[$ind])) {
				// it's int the session obj
				$isTagMatched = true;	
				// now remove it
				$_SESSION[$ind] = null;
				unset($_SESSION[$ind]);
			} 
		}
	}
 	return $isTagMatched;
}

function getSessionTag($sess, $prefix) {
	// make a random tag name and place in session
	$tag = $prefix . rand(1,10000000);
	$_SESSION[$tag] = true;
	return $tag;
}

?>
