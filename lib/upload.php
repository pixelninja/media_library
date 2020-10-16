<?php
	define('DOCROOT', rtrim(realpath(__DIR__ . '/../../../'), '/'));
	define('DOMAIN', rtrim(rtrim($_SERVER['HTTP_HOST'], '/') . str_replace('/extensions/media_library/lib', NULL, dirname($_SERVER['PHP_SELF'])), '/'));

	// Is there vendor autoloader?
	require_once DOCROOT . '/vendor/autoload.php';
	require_once DOCROOT . '/symphony/lib/boot/bundle.php';

	// Check the location to save is set and writable
	if(!isset($_REQUEST['location']) || !is_writable($_REQUEST['location'])) {
		header("HTTP/1.0 400 Bad Request", true, 400);
		exit;
	}

	// Store the file
	$overwrite = ($_REQUEST['overwrite'] === 'yes') ? true : false ;

	// Store the file
	$file = $_FILES['file'];

	// the file name
	$name_original = $file['name'];
	// File info
	$info = pathinfo($name_original);
	// and the directory to save the file in
	$directory = $_REQUEST['location'];
	// convert the filename into a handle
	$name_handle = General::createHandle($info['filename']) . '.' . $info['extension'];
	// then put them together for the final file path, while converting the filename into a handle
	$file_path = $directory . $name_handle;

	// If the file already exists, append a string to the end and loop until the name is unique
	if (file_exists($file_path) && !$overwrite) {
		$count = 1;

		do {
			$ext = $info['extension']; // get the extension of the file
			$new_name = General::createHandle($info['filename']) . '_' . $count . '.' . $ext;

			$file_path = $directory . $new_name;
			$count++;
		} while (file_exists($file_path));
	}

	// Upload the file
	if (isset($new_name)) {
		$uploaded = uploadFile($directory, $new_name, $file['tmp_name']);
	}
	else {
		$uploaded = uploadFile($directory, $name_handle, $file['tmp_name']);
	}

	// Failed? Return 400
	if ($uploaded === false) {
		header("HTTP/1.0 400 Bad Request", true, 400);
		header("Content-Type: application/json");

		echo json_encode(array(
			'error' => 'Upload failed'
		));
	}
	else {
		// Success! Return 201
		header("HTTP/1.0 200 Created", true, 200);
		header("Content-Type: application/json");

		echo json_encode(array(
			'url' => str_replace(WORKSPACE, URL . '/workspace', $file_path),
			'name' => (isset($new_name)) ? $new_name : $name_handle
		));
	}

	function uploadFile($dest_path, $dest_name, $tmp_name, $perm = 0644) {
		// Upload the file
		if (@is_uploaded_file($tmp_name)) {
			$dest_path = rtrim($dest_path, '/') . '/';
			$dest = $dest_path . $dest_name;

			// Check that destination is writable
			if (!is_writable($dest_path)) {
				return false;
			}
			// Try place the file in the correction location
			if (@move_uploaded_file($tmp_name, $dest)) {
				if (is_null($perm)) {
					$perm = 0644;
				}
				@chmod($dest, intval($perm, 8));
				return true;
			}
		}

		// Could not move the file
		return false;
	}

	/*
	 * Convert bytes into readable format
	 */
	function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));

		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
