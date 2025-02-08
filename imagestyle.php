<?php

$img = imagecreatetruecolor(200, 200);	

// create a 200*200 image
$img = imagecreatefromjpeg('https://static.vecteezy.com/system/resources/previews/020/386/296/non_2x/sai-letter-logo-design-in-illustration-logo-calligraphy-designs-for-logo-poster-invitation-etc-vector.jpg');

// allocate some colors
$white = imagecolorallocate($img, 255, 0, 0);
$red   = imagecolorallocate($img, 255,  0, 0);
$green = imagecolorallocate($img,   0, 255,   0);
$blue  = imagecolorallocate($img,   0,   0, 255);


// draw the head
imagearc($img, 100, 100, 200, 200,  0, 360, $white);
// mouth
imagearc($img, 100, 100, 150, 150, 25, 155, $red);
// left and then the right eye
imagearc($img,  60,  75,  50,  50,  0, 360, $green);
imagearc($img, 140,  75,  50,  50,  0, 360, $blue);

// output image in the browser
header("Content-type: image/png");
imagejpeg($img);


?>
