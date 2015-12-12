<?
echo nl2br(trim(e('howitworks_text', array('<b class="info">'.REGISTERED_USERS.'</b>'))));

$i = "<br /><br />PHP: ".phpversion();
if(extension_loaded('eaccelerator'))
	$i .= ' & eAccelerator';

echo $i;
?>
<br /><br /><br />
<img src='<?=$this->relpath?>dl/img/ipv6_logo_512-220x220.png' alt='ipv6 ready' title='ipv6 ready' width='55px' border='0' /></a>
