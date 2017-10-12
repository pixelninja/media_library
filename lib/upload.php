<?php

    // Check the location to save is set and writable
    if(!isset($_REQUEST['location']) || !is_writable($_REQUEST['location'])) {
        header("HTTP/1.0 400 Bad Request", true, 400);
        exit;
    }

    // Upload the file
    // $field = FieldManager::fetch($field_id);
    // if(!($field instanceof FieldMultiUpload)) {
    //     header("HTTP/1.0 400 Bad Request", true, 400);
    //     exit;
    // }
    // else {
        // $message = '';
        // $data = $_FILES['file'];
        // // Do upload
        // // $result = $field->processRawFieldDataIndividual($data, $status, $message, false, $entry_id, $position);
        //
        // // output back to browser..
        // if(is_array($result)) {
        //     header("HTTP/1.0 201 Created", true, 201);
        //     header("Content-Type: application/json");
        //
        //     echo json_encode(array(
        //         'url' => str_replace(WORKSPACE, URL . '/workspace', $field->getFilePath($result['file'])),
        //         'size' => $result['size'],
        //         'mimetype' => $result['mimetype'],
        //         'meta' => unserialize($result['meta'])
        //     ));
        // }
        // else {
        //     header("HTTP/1.0 400 Bad Request", true, 400);
        //     header("Content-Type: application/json");
        //
        //     echo json_encode(array(
        //         'error' => $message
        //     ));
        // }
    // }

    $file = $_FILES['file'];
    $name = $file['name'];
    $directory = $_REQUEST['location'];
    $file_path = $directory . $name;

    $exists = checkFileExists($file_path, $name, $directory);

    function checkFileExists($path, $name, $directory, $iteration = 1) {
        if (file_exists($path)) {
            $info = pathinfo($name);
            $ext = $info['extension']; // get the extension of the file
            $newname = $info['filename'] . '_' . $iteration . '.' . $ext;

            $path = $directory . $newname;
        }
        else {
            return $path;
        }

        if (file_exists($path)) {
            checkFileExists($path, $name, $directory, $iteration + 1);
        }
        else {
            return $path;
        }
    }

    var_dump($exists); exit;
    exit;

    // $info = pathinfo($_FILES['userFile']['name']);
    // $ext = $info['extension']; // get the extension of the file
    // $newname = "newname.".$ext;
    //
    // $target = 'images/'.$newname;
    // move_uploaded_file( $_FILES['userFile']['tmp_name'], $target);

// If a file already exists, then rename the file being uploaded by
// adding `_1` to the filename. If `_1` already exists, the logic
// will keep adding 1 until a filename is available (#672)
// if (file_exists($abs_path . '/' . $data['name'])) {
//     $extension = General::getExtension($data['name']);
//     $new_file = substr($abs_path . '/' . $data['name'], 0, -1 - strlen($extension));
//     $renamed_file = $new_file;
//     $count = 1;
//
//     do {
//         $renamed_file = $new_file . '_' . $count . '.' . $extension;
//         $count++;
//     } while (file_exists($renamed_file));
//
//     // Extract the name filename from `$renamed_file`.
//     $data['name'] = str_replace($abs_path . '/', '', $renamed_file);
// }
