<?php
	$a = pack("LCLC", 123,45,67,89);
	$data=unpack("L1/C2/Lin", $a); 
	print_r($data);
?>
