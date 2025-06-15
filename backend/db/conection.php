<?php
// Parámetros de conexión
$host = "localhost";
$user = "root";
$password = "";
$database = "manos";

// Crear conexión
$conexion = mysqli_connect($host, $user, $password, $database);

// Verificar conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer charset
mysqli_set_charset($conexion, "utf8mb4");

// Establecer zona horaria
date_default_timezone_set('America/Mexico_City');
?>