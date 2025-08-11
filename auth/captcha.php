<?php
session_start();

$width = 180; // Увеличена ширина
$height = 50;  // Стандартная высота
$length = 6;
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

$code = substr(str_shuffle($chars), 0, $length);
$_SESSION['captcha'] = $code;

$image = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($image, 245, 245, 245); // Светло-серый фон
$text = imagecolorallocate($image, 50, 50, 50);  // Темно-серый текст
$noise = imagecolorallocate($image, 200, 200, 200);

imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Добавляем шумовые точки
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise);
}

// Добавляем шумовые линии
for ($i = 0; $i < 5; $i++) {
    imageline($image, 
        rand(0, $width), rand(0, $height),
        rand(0, $width), rand(0, $height),
        $noise);
}

// Используем встроенный шрифт большего размера
$font = 5;
$text_width = imagefontwidth($font) * strlen($code);
$text_height = imagefontheight($font);

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font, $x, $y, $code, $text);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>