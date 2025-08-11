<?php
session_start();

$width = 160;
$height = 50;
$length = 6;
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

$code = substr(str_shuffle($chars), 0, $length);
$_SESSION['captcha'] = $code;

$image = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($image, 255, 255, 255);
$text = imagecolorallocate($image, 0, 0, 0);
$noise = imagecolorallocate($image, 200, 200, 200);

imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Шум
for ($i = 0; $i < ($width * $height) / 3; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise);
}

// Текст
$font = 5; // Встроенный шрифт
imagestring($image, $font, 50, 15, $code, $text);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>