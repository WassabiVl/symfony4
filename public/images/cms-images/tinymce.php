<?php

/*******************************************************
 * Only these origins will be allowed to upload images *
 ******************************************************/
$accepted_origins = array('http://localhost:8000', 'http://192.168.1.1', 'http://dev.radiopharma24.de', 'http://127.0.0.1:8000', __DIR__);

/*********************************************
 * Change this line to set the upload folder *
 *********************************************/
$imageFolder = __DIR__ . '/';

reset ($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])){
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if (\in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins, true)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            header('HTTP/1.1 403 Origin Denied');
            return;
        }
    }

    /*
      If your script needs to receive cookies, set images_upload_credentials : true in
      the configuration and enable the following two headers.
    */
    // header('Access-Control-Allow-Credentials: true');
// header('P3P: CP="There is no P3P policy."');

// Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header('HTTP/1.1 400 Invalid file name.');
        return;
    }

// Verify extension
    if (!\in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array('gif', 'jpg', 'png'))) {
        header('HTTP/1.1 400 Invalid extension.');
        return;
    }

// Accept upload if there was no origin, or if it is an accepted origin
    $fileToWrite = $imageFolder . $temp['name'];
    if (file_exists($fileToWrite)){
        $ext = pathinfo($temp['name'], PATHINFO_EXTENSION);
        $temp['name'] = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10/strlen($x)) )),1,10);
        $temp['name'] .= '.' . $ext;
        $fileToWrite = $imageFolder . $temp['name'];
    }
    move_uploaded_file($temp['tmp_name'], $fileToWrite);
    $fileToWrite2 = '../images/cms-images/'.$temp['name'];
// Respond to the successful upload with JSON.
// Use a location key to specify the path to the saved image resource.
// { location : '/your/uploaded/image/file'}
//    echo json_encode(array('location' => $fileToWrite));
    echo json_encode(array('location' => $fileToWrite2));
} else {
    // Notify editor that the upload failed
    header('HTTP/1.1 500 Server Error');
}
