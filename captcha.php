<?php
session_start();

// Generate a random 6-character string
$captcha_text = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"), 0, 6);
$_SESSION['captcha'] = $captcha_text;

// Create image
$width = 150;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 255, 255, 255); // white background
$text_color = imagecolorallocate($image, 0, 0, 0);     // black text

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Use built-in font (no need for TTF)
$font_size = 5;
$text_x = ($width - imagefontwidth($font_size) * strlen($captcha_text)) / 2;
$text_y = ($height - imagefontheight($font_size)) / 2;

// Draw text (centered)
imagestring($image, $font_size, $text_x, $text_y, $captcha_text, $text_color);

// Output image
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
