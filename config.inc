<?php 
ini_set('display_errors', 1);
date_default_timezone_set('America/New_York');
session_start();
define('REJECTED', 0);
define('PENDING', 1);
define('REDEEMED_BUT_QUESTIONABLE', 2);
define('REDEEMED', 3);
$statuses = array("Rejected", "Pending", "Redeemed but questionable", "Redeemed");
$_TAG_PREFIX = "tag_";
$errors = array();
include_once("includes/db_include.inc");
include_once("includes/lib.inc");
?>