<?php

session_start();
$_SESSION = array();
session_destroy();
echo "<meta http-equiv=refresh content=1;url=\"index.php\">";

//echo "<meta http-equiv=refresh content=1;url=\"login.php\">";
?>
