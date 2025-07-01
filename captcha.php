<?php
require_once 'includes/config.php';
session_start();

// Генерация случайной CAPTCHA
$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$captcha = '';
for ($i = 0; $i < CAPTCHA_LENGTH; $i++) {
    $captcha .= $chars[rand(0, strlen($chars) - 1)];
}

// Сохранение CAPTCHA в сессии
$_SESSION['captcha'] = $captcha;

// Создание изображения
$width = 150;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Цвета
$bgColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);
$noiseColor = imagecolorallocate($image, 100, 120, 180);

// Заполнение фона
imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Добавление шума
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
}

// Добавление линий
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand(0, $height), $width, rand(0, $height), $noiseColor);
}

// Добавление текста
$font = 5;
$x = 10;
$y = 15;
imagestring($image, $font, $x, $y, $captcha, $textColor);

// Вывод изображения
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>