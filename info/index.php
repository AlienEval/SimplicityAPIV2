<?php

$base_paths = array(
    'users' => '../users/',
    '2fa' => '../users/2fa/'
);

function get_json_files($folder) {
    $files = glob("$folder/*.json");
    $file_names = array_map(function($file) {
        return basename($file, '.json');
    }, $files);
    return $file_names;
}

if (isset($_GET['users']) && $_GET['users'] === '!') {
    $json_response = array();

    foreach ($base_paths as $key => $base_path) {
        $json_response[$key] = get_json_files($base_path);
    }

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
    
    header('Content-Type: application/json');
    echo json_encode($json_response);
} else {
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(array("error" => "Parámetro ?users=! no encontrado"));
}

