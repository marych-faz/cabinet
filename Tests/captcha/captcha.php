<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Настройки CAPTCHA
$width = 160;
$height = 40;
$font_size = 22;
$length = 6;
$font = __DIR__ . '/arial.ttf'; // Убедись, что файл есть

// Генерация случайного кода
$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$captcha_code = substr(str_shuffle($characters), 0, $length);
$_SESSION['captcha'] = $captcha_code;

// Создаем изображение
$image = imagecreatetruecolor($width, $height);

// Цвета
$bg_color = imagecolorallocate($image, 255, 255, 255);     // Белый фон
$text_color = imagecolorallocate($image, 0, 0, 0);         // Черные буквы
$noise_color = imagecolorallocate($image, 170, 170, 170);  // Серый шум
$line_color = imagecolorallocate($image, 200, 200, 200);   // Линии

// Заливка фона
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Фоновые линии
for ($i = 0; $i < 3; $i++) {
    imageline($image, rand(0, $width), 0, rand(0, $width), $height, $line_color);
}

// Шумовые точки
for ($i = 0; $i < ($width * $height) / 3; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Расчёт общей ширины текста
$total_text_width = 0;
for ($i = 0; $i < $length; $i++) {
    $bbox = imagettfbbox($font_size, 0, $font, $captcha_code[$i]);
    $total_text_width += ($bbox[2] - $bbox[0]) + 2;
}
$start_x = ($width - $total_text_width) / 2;

// Выводим символы с разным наклоном
$current_x = $start_x;
for ($i = 0; $i < $length; $i++) {
    $angle = rand(-15, 15);
    imagettftext($image, $font_size, $angle, $current_x, $height - 12, $text_color, $font, $captcha_code[$i]);
    $bbox = imagettfbbox($font_size, $angle, $font, $captcha_code[$i]);
    $char_width = $bbox[2] - $bbox[0];
    $current_x += $char_width + 2;
}

// Отправляем заголовок и изображение
header('Content-Type: image/png');
imagepng($image);

// Освобождаем память
imagedestroy($image);