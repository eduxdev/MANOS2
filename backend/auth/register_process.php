<?php
session_start();
require_once '../db/conection.php';

// Función para validar el email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para sanitizar inputs
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método de solicitud no válido';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

// Obtener y sanitizar datos del formulario
$nombre = sanitizeInput($_POST['nombre']);
$apellidos = sanitizeInput($_POST['apellidos']);
$email = sanitizeInput($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$rol = sanitizeInput($_POST['rol']);
$grado = isset($_POST['grado']) ? sanitizeInput($_POST['grado']) : null;
$grupo = isset($_POST['grupo']) ? sanitizeInput($_POST['grupo']) : null;

// Validaciones
if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($rol)) {
    $_SESSION['error'] = 'Todos los campos son obligatorios';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

if (!isValidEmail($email)) {
    $_SESSION['error'] = 'El correo electrónico no es válido';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Las contraseñas no coinciden';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

// Validar que el rol sea estudiante o profesor
if ($rol !== 'estudiante' && $rol !== 'profesor') {
    $_SESSION['error'] = 'Rol no válido';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

// Validaciones adicionales para estudiantes
if ($rol === 'estudiante') {
    if (empty($grado) || empty($grupo)) {
        $_SESSION['error'] = 'El grado y grupo son obligatorios para estudiantes';
        header('Location: ../../frontend/auth/register.php');
        exit();
    }
}

// Verificar si el email ya existe
$stmt = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $_SESSION['error'] = 'Este correo electrónico ya está registrado';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

// Obtener el ID del rol
$stmt = mysqli_prepare($conexion, "SELECT id FROM roles WHERE nombre = ?");
mysqli_stmt_bind_param($stmt, "s", $rol);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rol_id = mysqli_fetch_assoc($result)['id'];

// Hash de la contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, apellidos, email, password, rol_id, grado, grupo) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $apellidos, $email, $hashed_password, $rol_id, $grado, $grupo);

if (mysqli_stmt_execute($stmt)) {
    // Guardar mensaje de éxito
    $_SESSION['success'] = '¡Cuenta creada exitosamente! Por favor, inicia sesión.';
    header('Location: ../../frontend/auth/login.php');
    exit();
} else {
    $_SESSION['error'] = 'Error al crear la cuenta. Por favor, intenta nuevamente';
    header('Location: ../../frontend/auth/register.php');
    exit();
}

mysqli_close($conexion);
?> 