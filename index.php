<?php
/**
 * PHP Content Delivery
 *
 * Copyright (c) 2012 Brett O'Donnell <brett@mrphp.com.au>
 * Source Code: https://github.com/cornernote/php-content-delivery
 * Home Page: http://mrphp.com.au/blog/content-delivery-networks-php
 * License: GPLv3
 */

$error = false;
$download = true;

// filename
if (!$error) {
    $length = strlen(dirname($_SERVER['SCRIPT_NAME']));
    $file = substr($_SERVER['REQUEST_URI'], $length);
    if (strpos($file, '/') === 0) $file = substr($file, 1);
    if ($file) {
        $local_file = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
        $local_enc_file = dirname($_SERVER['SCRIPT_FILENAME']) . '/md5/' . substr(md5($file), 0, 2) . '/' . substr(md5($file), 2, 4) . '/' . substr(md5($file), 6, 6) . '/' . md5($file);
        $remote_file = 'http://img.cdn.thereadingroom.com/img/covers/' . $file;
    }
    else {
        $error = true;
    }
}

// serve existing file
if (!$error) {
    if (file_exists($local_enc_file)) {
        $download = false;
        $local_file = $local_enc_file;
    }
}

// download from the remote location
if (!$error && $download) {
    $contents = @file_get_contents($remote_file);
    if (!$contents) {
        $error = true;
    }
}

// save the file to disk
if (!$error && $download) {
    if (!file_exists(dirname($local_file))) {
        mkdir(dirname($local_file), 0755, true);
    }
    if (!@file_put_contents($local_file, $contents)) {
        $local_file = $local_enc_file;
        if (!file_exists(dirname($local_file))) {
            mkdir(dirname($local_file), 0755, true);
        }
        if (!file_put_contents($local_file, $contents)) {
            $error = true;
        }
    }
}

// send the response
if ($error) {
    header("HTTP/1.0 404 Not Found");
}
else {
    header('Content-Length: ' . filesize($local_file));
    header('Content-Type: ' . mime_content_type($local_file));
    readfile($local_file);
}