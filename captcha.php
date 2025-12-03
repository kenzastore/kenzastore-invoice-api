<?php
session_start();

// Generate a random string
$captcha_text = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);

// Store the CAPTCHA text in session
$_SESSION['captcha'] = $captcha_text;

// Create an image
$image = imagecreate(120, 40);
$background_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0);            // Black text

// Add the text to the image
imagestring($image, 5, 10, 10, $captcha_text, $text_color);

// Output the image as PNG
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);
?>