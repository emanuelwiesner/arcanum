<?php
if (is_array($msg)){
	$arr = $msg;
	$msg = "";
	foreach ( $arr as $message ){
		$msg .= ' + '. $message . "<br>";
						
	}
} 

echo $msg;
?>