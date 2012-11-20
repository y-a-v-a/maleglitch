<?php
/**
 * Path on server of ax710:
 * require 'Satromizer.php';
 * $files = @glob('src/*.jpg');
 */
error_reporting(0);
date_default_timezone_set('Europe/Amsterdam');

// Maleglitch
require '../share/Satromizer.php';

$files = @glob('src/*.jpg');

if (count($files) == 0) {
	throw new Exception('No files in source dir.');
}

$s = new Satromizer($files[array_rand($files)]);

while ($s->success == false) {
	$s->build();
}

header("Cache-Control: max-age=0, must-revalidate");
$fileName = date('Y-m-d_His') . '.jpg';
if (count(@glob('imgs/' . date('Y-m-d_') . '*.jpg')) == 0) {
	$s->export('imgs/', $fileName);
}
//echo $s->export('imgs/')->show();
echo $s->show();
exit;