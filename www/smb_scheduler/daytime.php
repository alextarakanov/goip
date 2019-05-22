<?php
define("OK", true);
session_start();
require_once("global.php");
$query=$db->query("select period_limit from sim where period_limit!=''");
while($row=$db->fetch_array($query)) { //each sim
	$a=explode(";", $row[0]);
	$i=0;
	$xingqi=array();
	$start_h=array();
	$start_m=array();
	$stop_h=array();
	$stop_m=array();
	$limit_t=array();
	foreach($a as $limit){ //each period
		if($limit=='') break;
		//$xingqi=$limit[0];
		//$limit=substr($limit,1,strlen($limit));
		//sscanf($limit, "%1s%2s%2s-%2s%2s:%d",&($xingqi[$i]),&($start_h[$i]),&($start_m[$i]),&($stop_h[$i]),&($stop_m[$i]),&($limit[$i]));
		sscanf($limit, "%1s%2s%2s-%2s%2s:%d",$xingqi0,$start_h0,$start_m0,$stop_h0,$stop_m0,$limit0);
		$check++;
		$xingqi[]=$xingqi0;
		$start_h[]=$start_h0;
		$start_m[]=$start_m0;
		$stop_h[]=$stop_h0;
		$stop_m[]=$stop_m0;
		$limit_t[]=$limit0;
		echo "$check $limit, $xingqi0, $start_h0,$start_m0,$stop_h0,$stop_m0,$limit0 <br>";
		$i++;
	}
	//print_r($row);
}
?>
<form method=post action="post.php">
 <script type="text/javascript">
var str = "";
document.writeln("<div id=\"_contents\" style=\"padding:6px; background-color:#E3E3E3; font-size: 12px; border: 1px solid #777777;  position:absolute; left:?px; top:?px; width:?px; height:?px; z-index:1; visibility:hidden\">");
str += "\u65f6<select name=\"_hour\" >";
for (h = 0; h <= 9; h++) {
    str += "<option value=\"0" + h + "\">0" + h + "</option>";
}
for (h = 10; h <= 23; h++) {
    str += "<option value=\"" + h + "\">" + h + "</option>";
}
str += "</select> <input id=\"queding\" name=\"queding\" type=\"button\" onclick=\"_select()\" value=\"\u786e\u5b9a\" style=\"font-size:12px\" /></div>";
document.writeln(str);
var _fieldname;
function _SetTime(tt) {
    _fieldname = tt;
    var ttop = tt.offsetTop;    //TT控件的定位点高
    var thei = tt.clientHeight;    //TT控件本身的高
    var tleft = tt.offsetLeft;    //TT控件的定位点宽
    while (tt = tt.offsetParent) {
        ttop += tt.offsetTop;
        tleft += tt.offsetLeft;
    }
    document.all._contents.style.top = ttop + thei + 4;
    document.all._contents.style.left = tleft;
    document.all._contents.style.visibility = "visible";
}
function _select() {
    //document.getElementById('queding').onclick="document.getElementById('_contents').style.visibility = 'hidden';";
    //_fieldname.value = document.all._hour.value + ":" + document.all._minute.value;
    document.getElementById('_contents').style.visibility = "hidden";
}

function a()
{
	alert("2222222");
	//document.getElementById('start_h1').value="alert('111111')";
	document.getElementById('start_h1').onclick=new Function("alert('111111')");
}
 </script>
 控件调用函数：_SetTime(field)
 例如
<?php
for($i=1;$i<=10;$i++){
if($i <= $check) $checked='checked';
else $checked='';
print <<<EOT
<br>
时段$i<input name="time_ck$i" type="checkbox" $checked>
<input name="start_h$i" id="start_h$i" type="text" onclick="_SetTime(this)">
<select name="start_h$i" onclick="_SetTime(this)">
EOT;

print <<<EOT
</select>时
<select name="start_m$i" onclick="_SetTime(this)">
	
</select>分-
<select name="stop_h$i" onclick="_SetTime(this)">
	
</select>时
<select name="stop_m$i" onclick="_SetTime(this)">
	
</select>分
EOT;
}
?>
<br>
<input name="post" type="submit"  value="save"/>
</form>
