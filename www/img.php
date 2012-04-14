<?php
/**
 * Path on server of ax710:
 * require 'Satromizer.php';
 * $files = @glob('src/*.jpg');
 */
error_reporting(E_ALL);
// Maleglitch
require '../share/Satromizer.php';

$files = @glob('../www/src/*.jpg');

if (count($files) == 0) {
	throw new Exception('No files in source dir.');
}

$s = new Satromizer($files[array_rand($files)]);

while ($s->success == false) {
	$s->build();
}

header("Cache-Control: max-age=0, must-revalidate");
//echo $s->export('imgs/')->show();
echo $s->show();
exit;