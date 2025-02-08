<?php

$im = imagecreatetruecolor(450, 450);
$color = imagecolorallocate($im, 250, 0, 0);
imagefill($im, 0, 0, $color);
$blue = imagecolorallocate($im, 0, 0,250);

imagerectangle($im, 50, 50, 100, 100, $blue);
imagefilledrectangle($im, 100, 100, 150, 150, $blue);

// $im2 = imagecreatefromjpeg("modi.jpeg");

$im2 = imagecreatefrompng("spider.png");



//imagecopy($im, $im2, 20, 20, 0, 0, imagesx($im2), imagesy($im2) );
//imagecopyresampled( imagesx($im2), imagesy($im2) );

imagecopyresampled($im, $im2, 10, 10, 40, 40, 200, 200, imagesx($im2), imagesy($im2));

// imagecopyresampled(dst_image, src_image, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)

header("Content-Type: image/png");
imagejpeg($im);

?>