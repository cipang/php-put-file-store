<?php
//
// Script to Backup to Remote Server via HTTP
// Version: 1.2 (2 Oct 2015)
// Copyright (C) 2013-2015 Patrick Pang (mail@patrickpang.net).
//

define(HTTP_400, "400 Bad Request");
define(HTTP_403, "403 Forbidden");
define(HTTP_405, "405 Method Not Allowed");
define(HTTP_500, "500 Internal Server Error");

// Function definitions.
function get_pass($area) {
    $a = parse_ini_file("pass.ini");
    return $a && isset($a[$area]) ? $a[$area] : false;
}

function error($status, $message = "") {
    header("HTTP/1.0 " . $status);
    die(!empty($message) ? $message : $status);
}

function get_local_filename($area, $filename) {
    return basename($area) . DIRECTORY_SEPARATOR . basename($filename);
}

function check($area, $json) {
    $files = json_decode($json, true);
    if (!is_array($files))
        error(HTTP_400, "File list is not an array.");

    $accepted_list = array();
    foreach ($files as $f) {
        $real_size = @filesize(get_local_filename($area, $f["name"]));
        if ($real_size === false || $f["size"] != $real_size)
            $accepted_list[] = array("name" => $f["name"], "path" => $f["path"]);
    }
    return json_encode($accepted_list);
}

function store($area, $file) {
    $in = @fopen("php://input", "r");
    if (!$in)
        error(HTTP_500, "Cannot open input file.");
    $out = @fopen(get_local_filename($area, $file), "w");
    if (!$out)
        error(HTTP_500, "Cannot open output file.");
    while ($data = fread($in, 4096))
        fwrite($out, $data);
    fclose($in);
    fclose($out);
}

// Main script starts here.
$area = $_REQUEST["area"];
$pin = $_REQUEST["pin"];

// Check action to perform.
if ($_SERVER["REQUEST_METHOD"] == "POST")
    $op = "check";
elseif ($_SERVER["REQUEST_METHOD"] == "PUT")
    $op = "store";
else
    error(HTTP_405);

// Check password.
if (empty($area) || empty($pin))
    error(HTTP_400);
elseif ($pin !== get_pass($area))
    error(HTTP_403);

switch ($op) {
    case "check":
        // Process file list request.
        header("Content-Type: application/json");
        echo check($area, file_get_contents("php://input"));
        break;

    case "store":
        // Save file for PUT requests.
        $file = $_REQUEST["filename"];
        store($area, $file);
        break;
}

?>
