<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el número de archivos a crear desde el formulario
    $numArchivos = isset($_POST['numArchivos']) ? intval($_POST['numArchivos']) : 0;

    // Directorio actual donde se crearán los archivos
    $directorioActual = './';

    // Función para generar un nombre aleatorio con el formato A12BC
    function generarNombre() {
        $letra = chr(rand(65, 90)); // Letra aleatoria de A a Z
        $numeros = sprintf("%02d", rand(0, 99)); // Dos números aleatorios de 00 a 99
        $letras = chr(rand(65, 90)) . chr(rand(65, 90)); // Dos letras aleatorias de A a Z
        return $letra . $numeros . $letras;
    }

    // Crear los archivos JSON
    for ($i = 1; $i <= $numArchivos; $i++) {
        $nombreArchivo = generarNombre() . '.json';
        $contenido = json_encode(['message' => 'Archivo generado automáticamente']);
        file_put_contents($directorioActual . $nombreArchivo, $contenido);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Archivos JSON</title>
</head>
<body>
    <h2>Crear Archivos JSON con Nombre en Formato A12BC</h2>
    <form method="post">
        <label for="numArchivos">Número de archivos a crear:</label>
        <input type="number" id="numArchivos" name="numArchivos" min="1" required>
        <button type="submit">Crear Archivos</button>
    </form>
</body>
</html>

