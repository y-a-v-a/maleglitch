<?php
header("Cache-Control: max-age=0, must-revalidate");
// Maleglitch
require 'Satromizer.php';
error_reporting(E_ALL);

$s = new Satromizer('src/malevich.' . sprintf("%03d", rand(1,60)) . '.jpg');

while ($s->success == false) {
	$s->build();
}
echo $s->export('imgs/')->show();
exit;