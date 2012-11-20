<?php

$path = strrev(strstr(strrev($_SERVER['REQUEST_URI']), '/'));
$url = 'http://' . $_SERVER['SERVER_NAME'] . $path;

header('Content-type: application/json');
date_default_timezone_set('Europe/Amsterdam');
$files = @glob('imgs/*.jpg');

$file = $files[array_rand($files)];

$data = array();
$data['status'] = 200;
$data['image'] = $url . $file;
$data['title'] = 'Hypersuprematist composition';
$data['copyright'] = 'Maleglitch by ax710 and y-a-v-a.org 2009-' . date("Y");

echo json_encode($data);
exit;