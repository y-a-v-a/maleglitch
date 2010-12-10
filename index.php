<?php
require 'Satromizer.php';
// error_reporting(E_ALL);

// $s = new Satromizer('IMG_0007.JPG');
// $s->show();
header('Content-type: image/jpeg');
$s = new Satromizer('IMG_0085.JPG');
echo $s->show();
$s->export('imgs/');
exit;