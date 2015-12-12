<?php 

$fontFile = $this->captchattf;
$backGround = $this->captchabg;
$text = (isset($_GET['action'])) ? captchavalue($_GET['action']) : captchavalue();

$backgroundSizeX = 2000;
$backgroundSizeY = 350;
$sizeX = 200;
$sizeY = 50;
$textLength = strlen($text);

// generate random security values
$backgroundOffsetX = rand(0, $backgroundSizeX - $sizeX - 1);
$backgroundOffsetY = rand(0, $backgroundSizeY - $sizeY - 1);
$angle = rand(-5, 5);
$fontColorR = rand(0, 127);
$fontColorG = rand(0, 127);
$fontColorB = rand(0, 127);

$fontSize = rand(30, 40);
$textX = rand(0, (int)($sizeX - 0.9 * $textLength * ($fontSize-10))); // these coefficients are empiric
$textY = rand((int)(1.25 * $fontSize), (int)($sizeY - 0.2 * $fontSize)); // don't try to learn how they were taken out

$gdInfoArray = gd_info();
if (! $gdInfoArray['PNG Support'])
return IMAGE_ERROR_GD_TYPE_NOT_SUPPORTED;

// create image with background
$src_im = imagecreatefrompng($backGround);
if (function_exists('imagecreatetruecolor')) {
	// this is more qualitative function, but it doesn't exist in old GD
	$dst_im = imagecreatetruecolor($sizeX, $sizeY);
	$resizeResult = imagecopyresampled($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
} else {
	// this is for old GD versions
	$dst_im = imagecreate( $sizeX, $sizeY );
	$resizeResult = imagecopyresized($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);
}

if (! $resizeResult)
	echo "ERR";
	//return IMAGE_ERROR_GD_RESIZE_ERROR;

// write text on image
if (! function_exists('imagettftext'))
	echo "ERR2";

$color = imagecolorallocate($dst_im, $fontColorR, $fontColorG, $fontColorB);
imagettftext($dst_im, $fontSize, -$angle, $textX, $textY, $color, $fontFile, $text);

// output header
header("Content-Type: image/png");

// output image
imagepng($dst_im);

// free memory
imagedestroy($src_im);
imagedestroy($dst_im);
die();
?>
