<?php
	define('DOCROOT', rtrim(realpath(__DIR__ . '/../../../'), '/'));
	define('DOMAIN', rtrim(rtrim($_SERVER['HTTP_HOST'], '/') . str_replace('/extensions/media_library/tags', NULL, dirname($_SERVER['PHP_SELF'])), '/'));

	// Is there vendor autoloader?
	require_once DOCROOT . '/vendor/autoload.php';
	require_once DOCROOT . '/symphony/lib/boot/bundle.php';

	// File to write to
	$file = DOCROOT . '/extensions/media_library/json/tags.json';
	$image = $_REQUEST['image'];
	$tags = $_REQUEST['tags'];

	// Check if the JSON file is writable and parameters are set
	if(!is_writable($file) || !isset($image) || !isset($tags)) {
		header("HTTP/1.0 400 Bad Request", true, 400);
		exit;
	}

	// Remove any white space and trailing commas, and make lowercase
	$tags = strtolower(trim(str_replace(' ', '', htmlspecialchars($tags)), ','));

	// Get the json data
	$json = file_get_contents($file);
	$json_data = json_decode($json, true);

	// Add or replace the key/value
	$json_data[$image] = $tags;

	// Write to the file
	General::writeFile($file, json_encode($json_data));

	print_r($tags);
?>