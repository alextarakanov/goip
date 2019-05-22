<?php
//ob_start();
error_reporting(E_ALL & ~E_NOTICE);
include_once 'config.inc.php';
include_once 'forbId.php';

//var_dump(headers_list());


//var_dump(headers_list());

function myaddslashes($var)
{
	if(!get_magic_quotes_gpc())
		return addslashes($var);
	else
		return $var;
}

function getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
    } 

$check_time=0;
/*
class DB {
	function DB(){
		global $dbhost,$dbuser,$dbpw,$dbname;

		$conn=new mysqli($dbhost,$dbuser,$dbpw, $dbname);// or die("Could not connect");
		//$conn=mysql_connect($dbhost,$dbuser,$dbpw) or die("Could not connect");
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

		//mysql_select_db($dbname,$conn);
		$conn->query("SET NAMES 'utf8'");		
	}
	function query($sql) {
		global $check_time;
		if($check_time) $start_time=getmicrotime();
		echo $sql;
		$result=$this->query($sql);// or die("$Bad query: ".$this->error());
		echo "222";
		$end_time=getmicrotime();
		if($check_time) $time=(getmicrotime()-$start_time)*1000;
		if($check_time) echo "<br>".$sql."(".$time."ms)";
		return $result;
	}
	function updatequery($sql) {

                $result=$this->mquery($sql);
                return $result;
        }

	function fetch_array($query) {
		return $this->fetch_array($query);
	}
	
	function fetch_assoc($query) {
		return $this->fetch_assoc($query);
	}
	
	function num_rows($query) {
		return $this->num_rows($query);
	}
	function real_escape_string($item){
		return $this->real_escape_string($item);
	}
}
*/

class DB {
	function DB(){
		global $dbhost,$dbuser,$dbpw,$dbname;

		$conn=mysql_connect($dbhost,$dbuser,$dbpw) or die("Could not connect");
		mysql_select_db($dbname,$conn);
		mysql_query("SET NAMES 'utf8'");		
	}
	function query($sql) {
		global $check_time;
		if($check_time) $start_time=getmicrotime();
		$result=mysql_query($sql) or die("$Bad query: ".mysql_error());
		$end_time=getmicrotime();
		if($check_time) $time=(getmicrotime()-$start_time)*1000;
		if($check_time) echo "<br>".$sql."(".$time."ms)";
		
		return $result;
	}
	function updatequery($sql) {

                $result=mysql_query($sql);
                return $result;
        }

	function fetch_array($query) {
		return mysql_fetch_array($query);
	}
	
	function fetch_assoc($query) {
		return mysql_fetch_assoc($query);
	}
	
	function num_rows($query) {
		return mysql_num_rows($query);
	}
	function real_escape_string($item){
		return mysql_real_escape_string($item);
	}
}

$db=new DB;

?>
