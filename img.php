<?php
error_reporting(E_ALL);
// Maleglitch
require 'Satromizer.php';

$files = @glob('src/*.jpg');

if (count($files) == 0) {
	throw new Exception('No files in source dir.');
}

$s = new Satromizer($files[array_rand($files)]);

while ($s->success == false) {
	$s->build();
}

header("Cache-Control: max-age=0, must-revalidate");
echo $s->export('imgs/')->show();
exit;