<?php
//echo var_export($_POST,true);
//echo var_export($GLOBALS['HTTP_RAW_POST_DATA'],true);
//echo file_get_contents($_FILES['upload']['tmp_name']);
//copy($_FILES['upload']['tmp_name'], "1.pkg");
//echo var_export($GLOBALS['HTTP_RAW_POST_DATA'],true);
//echo file_get_contents("php://input");
echo var_export($_SERVER,true);
$handleWrite = fopen("1.pkg",'w');
fwrite($handleWrite, file_get_contents("php://input"));
fclose($handleWrite);
?>

