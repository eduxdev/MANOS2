<?php
session_start();
require_once '../db/conection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $apellidos = filter_var($_POST['apellidos'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $rol = $_POST['rol'];
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($rol)) {
        $_SESSION['error'] = "Por favor, completa todos los campos obligatorios.";
        header("Location: ../../frontend/auth/register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header("Location: ../../frontend/auth/register.php");
        exit();
    }

    // Verificar si el correo ya existe
    $check_email = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conexion, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Ya existe una cuenta con ese correo electrónico.";
        header("Location: ../../frontend/auth/register.php");
        exit();
    }

    // Obtener el ID del rol
    $get_rol_id = "SELECT id FROM roles WHERE nombre = ?";
    $stmt = mysqli_prepare($conexion, $get_rol_id);
    mysqli_stmt_bind_param($stmt, "s", $rol);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rol_data = mysqli_fetch_assoc($result);

    if (!$rol_data) {
        $_SESSION['error'] = "Rol no válido.";
        header("Location: ../../frontend/auth/register.php");
        exit();
    }

    $rol_id = $rol_data['id'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Preparar la consulta de inserción
    if ($rol === 'estudiante') {
        $grado = $_POST['grado'] ?? null;
        $grupo = $_POST['grupo'] ?? null;

        $query = "INSERT INTO usuarios (nombre, apellidos, email, password, rol_id, grado, grupo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $apellidos, $email, $hashed_password, $rol_id, $grado, $grupo);
    } else {
        $query = "INSERT INTO usuarios (nombre, apellidos, email, password, rol_id) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $apellidos, $email, $hashed_password, $rol_id);
    }

    // Ejecutar la inserción
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Cuenta creada exitosamente. Por favor, inicia sesión.";
        header("Location: ../../frontend/auth/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al crear la cuenta. Por favor, intenta nuevamente.";
        header("Location: ../../frontend/auth/register.php");
        exit();
    }
}
?> 