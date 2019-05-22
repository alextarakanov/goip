
	error_reporting(0);
	define("OK", true);
	require_once("global.php");

        $rs=@$db->fetch_array(@$db->updatequery("select version from system"));
        if(!$rs || $rs[0] < 101){ //version 1.01 must add table 
		return 1;
	}
	else return 2;

