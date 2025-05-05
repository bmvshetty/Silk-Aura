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
$bg_color = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 64, 64, 64);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add noise
for ($i = 0; $i < 10; $i++) {
    imageline($image, rand(0,150), rand(0,40), rand(0,150), rand(0,40), $line_color);
}

// Path to font file
$font_path = __DIR__ . '/arial.ttf';  // Ensure arial.ttf is in the same folder

// Draw text
imagestring($image, 5, 35, 10, $captcha_text, $text_color);

// Output image
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
