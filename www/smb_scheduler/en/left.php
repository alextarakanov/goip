<?php
	session_start();
?>

<html>
<meta name="Author" content="Gaby_chen">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Sim Scheduler</title>
<style type=text/css>
body  { background:#C7C7E2; margin:0px; font:9pt 宋体; }
table  { border:0px; }
td  { font:normal 16px 宋体; }
img  { vertical-align:bottom; border:0px; }
a  { font:normal 16px 宋体; color:#000000; text-decoration:none; }
a:hover  { color:#428EFF;text-decoration:underline; }

.sec_menu  { border-left:1px solId white; border-right:1px solId white; border-bottom:1px solId white; overflow:hIdden; background:#f0f3fa; }
.menu_title  { }
.menu_title span  { position:relative; top:2px; left:8px; color:#000000; font-weight:bold; }
.menu_title2  { }
.menu_title2 span  { position:relative; top:2px; left:8px; color:#428EFF; font-weight:bold; }

</style>
<SCRIPT language=javascript1.2>
function showsubmenu(ClassId)
{
whichEl = eval("submenu" + ClassId);
if (whichEl.style.display == "none")
{
eval("submenu" + ClassId + ".style.display=\"\";");
}
else
{
eval("submenu" + ClassId + ".style.display=\"none\";");
}
}
</SCRIPT>
</head>
<BODY leftmargin="0" topmargin="0" marginheight="0" marginwIdth="0">
<table wIdth=155 cellpadding=0 cellspacing=0 border=0 align=left>
    <tr><td valign=top>
<table wIdth=155 border="0" align=center cellpadding=0 cellspacing=0>
  <tr>
  </tr>
</table>
<table cellpadding=0 cellspacing=0 wIdth=155 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle0> 
          <span><a href="main.php" target=main><b>Main</b></a> | <a href=logout.php target=_top><b>Logout</b></a></span> 
        </td>
  </tr>
  <tr>
    <td style="display:" Id='submenu0'>
<div class=sec_menu style="wIdth:155">
<table cellpadding=0 cellspacing=0 align=center wIdth=157>
<tr><td height=20>&nbspUser Name:<?php echo $_SESSION['username'] ?></td>
</tr>
<tr><td height=20>&nbspPermissions:<?php echo $_SESSION['typename_e'] ?></td>
</tr>
</table>
</div>
	</td>
  </tr>
</table>
      <table cellpadding=0 cellspacing=0 wIdth=155 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle44 style="cursor:hand;"> 
          <span>Configuration</span> </td>
  </tr>
  <tr>
    <td style="display" Id='submenu44'>
<div class=sec_menu style="wIdth:155">
<table cellpadding=0 cellspacing=0 align=center wIdth=157>
<tr><td height=20><a href="sim_team.php" target=main>&nbspGroup</a></td>
</tr>
<tr>
<td height=20><a href="sim_bank.php" target=main>&nbspSIM Bank</a></td>
</tr>
<tr>
<td height=20><a href="rm_device.php" target=main>&nbspGoIP</a></td>
</tr>
<!--
<tr>
<td height=20><a href="template.php" target=main>&nbspScheduler Template</a></td>
</tr>
-->
<tr>
<td height=20><a href="scheduler.php" target=main>&nbspGroup Scheduler</a></td>
</tr>
<tr>
<td height=20><a href="human.php" target=main>&nbspHuman Behavior</a></td>
</tr>
<tr>
<td height=20><a href="imei_db.php" target=main>&nbspIMEI Database</a></td>
</tr>

</table>
	  </div>
	</td>
  </tr>
</table>

      <table cellpadding=0 cellspacing=0 wIdth=155 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTit
le45 onClick="showsubmenu(45)" style="cursor:hand;">
          <span>Monitor</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu45'>
<div class=sec_menu style="wIdth:155">
<table cellpadding=0 cellspacing=0 align=center wIdth=157>
<tr>
<td height=20><a href="all_sim.php" target=main>&nbspSIM Slot</a></td>
</tr>
<tr>
<td height=20><a href="all_device_line.php" target=main>&nbspGoIP Channel</a></td>
</tr>
<tr>        
<td height=20><a href="logs.php" target=main>&nbspLogs</a></td>
</tr>
<tr>        
<td height=20><a href="call_record.php" target=main>&nbspCall Record</a></td>
</tr>
<tr>
<td height=20><a href="cdr.php" target=main>&nbspCDR</a></td>
</tr>
</table>
          </div>
        </td>
  </tr>
</table>

<table cellpadding=0 cellspacing=0 wIdth=155 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle4 onClick="showsubmenu(4);" style="cursor:hand;"> 
          <span>Data Manage</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu4'>
<div class=sec_menu style="wIdth:155">
            <table cellpadding=0 cellspacing=0 align=center wIdth=157>
                          <tr>
                <td height=20><a href="sys.php"  target=main>&nbspSystem Manage</a></td>
              </tr>
			  <tr>
                <td height=20><a href="databackup.php"  target=main>&nbspData Backup</a></td>
              </tr>
<?php if($_SESSION['permissions']==1) {
?>
              <tr>
                <td height=20>
                 <a href="datarestore.php" target=main>&nbspData Import</a></td>
              </tr>
<?php } ?>
            </table>
	  </div>
	</td>
  </tr>
</table>

<?php 
//} 
?>
<table cellpadding=0 cellspacing=0 wIdth=155 align=center>
  <tr>
    <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle8 onClick="showsubmenu(8)" style="cursor:hand;"> <span>User Manage</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu8'>
      <div class=sec_menu style="wIdth:155">
        <table cellpadding=0 cellspacing=0 align=center wIdth=157>
         <tr><td height=20><a href="user.php?action=modifyself" target=main>&nbspChange Password</a></td>
</tr>
<?php if($_SESSION['permissions']==1) {
?>
<tr><td height=20><a href="user.php?job=modify" target=main>&nbspManage Other Users</a></td>
</tr>
<?php } ?>
		  
        </table>
      </div>
      <div  style="wIdth:155">
        <table cellpadding=0 cellspacing=0 align=center wIdth=157>
          <tr>
            <td height=20></td>
          </tr>
        </table>

    </div></td>
  </tr>
</table>


	  </div>
	</td>
  </tr>
</table>
</body>
</html>


