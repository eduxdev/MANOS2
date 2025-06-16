<?php
require_once __DIR__ . '/conection.php';

// Leer el contenido del archivo SQL
$sql = file_get_contents(__DIR__ . '/update_tables.sql');

// Dividir el contenido en consultas individuales
$queries = array_filter(array_map('trim', explode(';', $sql)));

// Ejecutar cada consulta
$error = false;
foreach ($queries as $query) {
    if (!empty($query)) {
        if (!mysqli_query($conexion, $query)) {
            echo "Error ejecutando la consulta: " . mysqli_error($conexion) . "\n";
            $error = true;
        }
    }
}

if (!$error) {
    echo "Base de datos actualizada correctamente\n";
}

// Crear directorio para las insignias si no existe
$insignias_dir = __DIR__ . '/../../imagenes/insignias';
if (!file_exists($insignias_dir)) {
    mkdir($insignias_dir, 0777, true);
}
?> 