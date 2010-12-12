<?php

// Maleglitch
require 'Satromizer.php';
error_reporting(E_ALL);

// $s = new Satromizer('IMG_0007.JPG');
// $s->show();
// header('Content-type: image/jpeg');
$s = new Satromizer('src/malevich.00'.rand(1,6).'.jpg');
echo $s->build()->export('imgs/')->show();
exit;