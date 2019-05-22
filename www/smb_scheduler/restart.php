<?php
	if(system("./run_scheduler") != "smb_scheduler started") echo "reboot error! <a href=''  target=_top>Return</a>";
	else echo "reboot done! <a href=''  target=_top>Return</a>";
?>
