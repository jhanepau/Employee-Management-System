<?php
session_start();

$code = '';
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
for ($i = 0; $i < 6; $i++) {
    $code .= $chars[rand(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $code;

$width = 150;
$height = 50;
$image = imagecreatetruecolor($width, $height);

$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 100, 100, 100);

imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $line_color);
}

$font_size = 5;
$x = ($width - imagefontwidth($font_size) * strlen($code)) / 2;
$y = ($height - imagefontheight($font_size)) / 2;
imagestring($image, $font_size, $x, $y, $code, $text_color);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>