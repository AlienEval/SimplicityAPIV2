<?php
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["cmd"])) {
        if ($_GET["cmd"] == "clr") {
            // Borrar el archivo 'request' si existe
            if (file_exists("request")) {
                unlink("request");
                http_response_code(200);
                exit(json_encode(["message" => "success", "status" => "200 OK", "response" => "El archivo request fue borrado correctamente."]));
            } else {
                http_response_code(404);
                exit(json_encode(["message" => "error", "status" => "404 Not Found", "response" => "El archivo request no existe."]));
            }
        } else {
            http_response_code(400);
            exit(json_encode(["message" => "error", "status" => "400 Bad Request", "response" => "Comando no válido. Use 'cmd=clr' para borrar el archivo request."]));
        }
    }

    // Obtener datos de la solicitud y convertirlos a formato JSON
    $datos = $_GET;
    $cadenaDatos = json_encode($datos, JSON_PRETTY_PRINT); // Formato legible

    // Archivo donde se guardará la solicitud
    $archivo = "request";

    // Fecha y hora actual formateada
    $fechaHora = date("d/m/y H:i");

    // IP del cliente que realizó la solicitud
    $host = $_SERVER["REMOTE_ADDR"];

    // Encabezado con información de fecha, hora y IP
    $encabezado = "[Fecha: " . $fechaHora . " - IP: " . $host . "]";

    // Texto a guardar en el archivo 'request'
    $texto = $encabezado . "\n" . $cadenaDatos . "\n\n";

    // Abrir el archivo en modo append (añadir al final)
    $manejadorArchivo = fopen($archivo, "a");

    // Escribir el texto en el archivo
    fwrite($manejadorArchivo, $texto);

    // Cerrar el archivo
    fclose($manejadorArchivo);

    // Log solo con la fecha, hora y IP
    $logArchivo = "log.txt";
    $logTexto = $encabezado . "\n";
    file_put_contents($logArchivo, $logTexto, FILE_APPEND);

    // Respuesta JSON con 200 OK indicando éxito
    http_response_code(200);
    echo json_encode(["message" => "success", "status" => "200 OK"]);
} else {
    // Si no es una solicitud GET, redireccionar a página de error 404
    header("Location: /404", true, 404);
}
?>

