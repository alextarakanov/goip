<?php

session_start();
$_SESSION = array();
session_destroy();
//define('OK',true);
//require_once('global.php');
echo "<meta http-equiv=refresh content=1;url=\"index.php\">";
?>
