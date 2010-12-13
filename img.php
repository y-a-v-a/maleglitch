<?php

// Maleglitch
require 'Satromizer.php';
error_reporting(E_ALL);

$s = new Satromizer('src/malevich.' . sprintf("%03d", rand(1,13)) . '.jpg');

while ($s->success == false) {
	$s->build();
}
echo $s->export('imgs/')->show();
exit;