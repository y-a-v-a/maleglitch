<?php
// never print errors into the image, but do log them
ini_set('display_errors', '0');
date_default_timezone_set('Europe/Amsterdam');

// Maleglitch
// on the server of ax710 Satromizer.php sits next to this file,
// in the repository it lives in share/
if (is_file(__DIR__ . '/Satromizer.php')) {
	require __DIR__ . '/Satromizer.php';
} else {
	require __DIR__ . '/../share/Satromizer.php';
}

$files = @glob('src/*.jpg') ?: array();

if (count($files) == 0) {
	throw new Exception('No files in source dir.');
}

$s = new Satromizer($files[array_rand($files)]);

$attempts = 0;
while ($s->success == false) {
	if (++$attempts > 100) {
		throw new Exception('Cannot build a glitched image.');
	}
	$s->build();
}

header("Cache-Control: max-age=0, must-revalidate");
$fileName = date('Y-m-d_His') . '.jpg';
if (count(@glob('imgs/' . date('Y-m-d_') . '*.jpg') ?: array()) == 0) {
	try {
		$s->export('imgs/', $fileName);
	} catch (Exception $e) {
		// archiving is best effort, still show the glitch
		error_log('Maleglitch: ' . $e->getMessage());
	}
}
//echo $s->export('imgs/')->show();
echo $s->show();
exit;