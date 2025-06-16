<?php
session_start();
require_once '../db/conection.php';

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para registrar errores
function logError($message, $data = null) {
    $error = [
        'message' => $message,
        'data' => $data,
        'time' => date('Y-m-d H:i:s')
    ];
    error_log(print_r($error, true), 3, '../../logs/evidence_errors.log');
    return $error;
}

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    $error = logError('No autorizado', ['session' => $_SESSION]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Verificar si se recibió el archivo y el ID de la asignación
if (!isset($_FILES['evidence']) || !isset($_POST['assignment_id'])) {
    $error = logError('Faltan datos requeridos', ['files' => $_FILES, 'post' => $_POST]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

$user_id = $_SESSION['user_id'];
$assignment_id = $_POST['assignment_id'];
$file = $_FILES['evidence'];

// Verificar si hubo errores en la subida
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error = logError('Error al subir el archivo', ['file_error' => $file['error']]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Verificar el tamaño del archivo (máximo 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    $error = logError('El archivo es demasiado grande', ['size' => $file['size']]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Verificar el tipo de archivo
$allowed_types = [
    'image/jpeg', 'image/png', 'image/gif', 
    'video/mp4', 'application/pdf',
    'application/msword', // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/vnd.ms-excel', // .xls
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
    'application/vnd.ms-powerpoint', // .ppt
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
    'text/plain' // .txt
];
if (!in_array($file['type'], $allowed_types)) {
    $error = logError('Tipo de archivo no permitido', ['type' => $file['type']]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Crear directorio si no existe
$upload_dir = '../../uploads/evidencias/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        $error = logError('Error al crear el directorio de evidencias', ['dir' => $upload_dir]);
        echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
        exit();
    }
}

// Verificar permisos de escritura
if (!is_writable($upload_dir)) {
    $error = logError('El directorio de evidencias no tiene permisos de escritura', ['dir' => $upload_dir]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Generar nombre único para el archivo
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_name = uniqid('evidence_') . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

// Mover el archivo
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    $error = logError('Error al guardar el archivo', [
        'tmp_name' => $file['tmp_name'],
        'destination' => $file_path,
        'permissions' => [
            'dir_writable' => is_writable($upload_dir),
            'dir_exists' => file_exists($upload_dir),
            'dir_perms' => substr(sprintf('%o', fileperms($upload_dir)), -4)
        ]
    ]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

// Obtener los puntos de la asignación
$query_points = "SELECT a.puntos_maximos 
                FROM asignaciones a 
                WHERE a.id = ?";
$stmt_points = mysqli_prepare($conexion, $query_points);
mysqli_stmt_bind_param($stmt_points, "i", $assignment_id);
mysqli_stmt_execute($stmt_points);
$result_points = mysqli_stmt_get_result($stmt_points);
$row_points = mysqli_fetch_assoc($result_points);
$puntos_maximos = $row_points['puntos_maximos'];
mysqli_stmt_close($stmt_points);

// Actualizar el estado de la asignación y los puntos en la base de datos
$relative_path = 'uploads/evidencias/' . $file_name;
$query = "UPDATE estudiantes_asignaciones 
          SET estado = 'completado', 
              evidencia_path = ?, 
              fecha_entrega = NOW(),
              puntos_obtenidos = ?
          WHERE estudiante_id = ? AND asignacion_id = ?";

$stmt = mysqli_prepare($conexion, $query);
if (!$stmt) {
    $error = logError('Error al preparar la consulta', ['mysql_error' => mysqli_error($conexion)]);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    exit();
}

mysqli_stmt_bind_param($stmt, "siii", $relative_path, $puntos_maximos, $user_id, $assignment_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Actualizar el progreso total del estudiante
        $query_update_progress = "UPDATE progreso_estudiantes 
                                SET puntos_obtenidos = puntos_obtenidos + ?,
                                    completado = true,
                                    ultima_practica = NOW()
                                WHERE usuario_id = ? AND ejercicio_id = (
                                    SELECT ejercicio_id FROM asignaciones WHERE id = ?
                                )";
        $stmt_progress = mysqli_prepare($conexion, $query_update_progress);
        mysqli_stmt_bind_param($stmt_progress, "iii", $puntos_maximos, $user_id, $assignment_id);
        mysqli_stmt_execute($stmt_progress);
        mysqli_stmt_close($stmt_progress);

        echo json_encode(['success' => true]);
    } else {
        $error = logError('No se encontró la asignación para actualizar', [
            'estudiante_id' => $user_id,
            'asignacion_id' => $assignment_id
        ]);
        echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
    }
} else {
    $error = logError('Error al actualizar la base de datos', ['mysql_error' => mysqli_error($conexion)]);
    // Si hay error en la base de datos, eliminar el archivo subido
    unlink($file_path);
    echo json_encode(['success' => false, 'error' => $error['message'], 'debug' => $error]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?> 