<?php

function deleteJsonFile($filename) {
    $file_path = './2fa/' . $filename . '.json';
    if (file_exists($file_path)) {
        unlink($file_path);
        return true;
    }
    return false;
}

function clearJsonFolder() {
    $folder_path = './2fa/';
    $files = scandir($folder_path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            unlink($folder_path . $file);
        }
    }
    return true;
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$date_time = date("Y-m-d H:i:s");
$client_ip = $_SERVER['REMOTE_ADDR'];
$request = $_SERVER['REQUEST_URI'];

$log_message = "[$date_time] Solicitud recibida desde $client_ip: $request\n";
file_put_contents("log.txt", $log_message, FILE_APPEND);

if (isset($_GET['clr'])) {
    $token_to_delete = $_GET['clr'];
    if (deleteJsonFile($token_to_delete)) {
        $log_message = "[$date_time] Se borró el archivo JSON: " . $token_to_delete . ".json\n";
        file_put_contents("log.txt", $log_message, FILE_APPEND);
        echo json_encode(array("mensaje" => "Se borró el archivo JSON: " . $token_to_delete . ".json"));
    } else {
        http_response_code(500);
        echo json_encode(array("mensaje" => "Error al borrar el archivo JSON: " . $token_to_delete . ".json"));
    }
    exit;
}

if (isset($_GET['3d']) && $_GET['3d'] === 'clear') {
    if (clearJsonFolder()) {
        $log_message = "[$date_time] Se borraron todos los archivos JSON en la carpeta /2fa\n";
        file_put_contents("log.txt", $log_message, FILE_APPEND);
        echo json_encode(array("mensaje" => "Se borraron todos los archivos JSON en la carpeta /2fa"));
    } else {
        http_response_code(500);
        echo json_encode(array("mensaje" => "Error al borrar los archivos JSON en la carpeta /2fa"));
    }
    exit;
}

if (isset($_GET['status']) && $_GET['status'] === '!') {
    echo json_encode(array("message" => "success", "status" => "200 OK"));
    exit;
}

$parametro_3d = isset($_GET['3d']) ? $_GET['3d'] : null;

if ($parametro_3d && preg_match('/^[A-Za-z]\d{2}[A-Za-z]{2}$/', $parametro_3d)) {
    $file_path = './2fa/' . $parametro_3d . '.json';

    if (file_exists($file_path)) {
        $json_content = file_get_contents($file_path);
        $json_data = json_decode($json_content, true);

        $par1 = isset($_GET['1']) ? $_GET['1'] : null;
        $par2 = isset($_GET['2']) ? $_GET['2'] : null;
        $par3 = isset($_GET['3']) ? $_GET['3'] : null;
        $par4 = isset($_GET['4']) ? $_GET['4'] : null;
        $par5 = isset($_GET['5']) ? $_GET['5'] : null;
        $par6 = isset($_GET['6']) ? $_GET['6'] : null;

        if ($par1 !== null) { $json_data['par1'] = $par1; }
        if ($par2 !== null) { $json_data['par2'] = $par2; }
        if ($par3 !== null) { $json_data['par3'] = $par3; }
        if ($par4 !== null) { $json_data['par4'] = $par4; }
        if ($par5 !== null) { $json_data['par5'] = $par5; }
        if ($par6 !== null) { $json_data['par6'] = $par6; }

        if ($par1 !== null || $par2 !== null || $par3 !== null || $par4 !== null || $par5 !== null || $par6 !== null) {
            $json_data['Updated'] = "YES";
        }

        $json_content = json_encode($json_data, JSON_PRETTY_PRINT);

        if (file_put_contents($file_path, $json_content) !== false) {
            $log_message = "[$date_time] Se actualizó el archivo JSON: " . $parametro_3d . ".json\n";
            file_put_contents("log.txt", $log_message, FILE_APPEND);
            echo $json_content;
        } else {
            http_response_code(500);
            echo json_encode(array("mensaje" => "Error al actualizar el archivo JSON."));
        }
    } else {
        $par1 = isset($_GET['1']) ? $_GET['1'] : "undefined";
        $par2 = isset($_GET['2']) ? $_GET['2'] : "undefined";
        $par3 = isset($_GET['3']) ? $_GET['3'] : "undefined";
        $par4 = isset($_GET['4']) ? $_GET['4'] : "undefined";
        $par5 = isset($_GET['5']) ? $_GET['5'] : "undefined";
        $par6 = isset($_GET['6']) ? $_GET['6'] : "undefined";

        $json_data = array(
            "Token" => $parametro_3d,
            "Updated" => "NO",
            "par1" => $par1,
            "par2" => $par2,
            "par3" => $par3,
            "par4" => $par4,
            "par5" => $par5,
            "par6" => $par6,
        );

        $json_content = json_encode($json_data, JSON_PRETTY_PRINT);

        if (file_put_contents($file_path, $json_content) !== false) {
            $log_message = "[$date_time] Se creó el archivo JSON nuevo: " . $parametro_3d . ".json\n";
            file_put_contents("log.txt", $log_message, FILE_APPEND);
            echo $json_content;
        } else {
            http_response_code(500);
            echo json_encode(array("mensaje" => "Error al crear el archivo JSON nuevo."));
        }
    }
    exit;
}

http_response_code(400);
echo json_encode(array("mensaje" => "Parámetros no válidos o faltantes para la solicitud."));
?>

