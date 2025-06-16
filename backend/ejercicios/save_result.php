<?php
session_start();
require_once '../db/conection.php';
require_once 'check_badges.php';

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Verificar si se recibió el archivo y los datos
if (!isset($_FILES['evidence']) || !isset($_POST['data'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

// Decodificar los datos JSON
$data = json_decode($_POST['data'], true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit();
}

// Validar el archivo
$file = $_FILES['evidence'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4'];
$max_size = 10 * 1024 * 1024; // 10MB

if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido']);
    exit();
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Archivo demasiado grande']);
    exit();
}

// Crear directorio para evidencias si no existe
$upload_dir = '../../uploads/evidencias/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generar nombre único para el archivo
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_name = uniqid('evidence_') . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

// Mover el archivo
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo']);
    exit();
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Obtener el número de intento actual
    $query = "SELECT intentos_realizados, puntos_obtenidos as puntos_anteriores 
              FROM estudiantes_asignaciones 
              WHERE id = ? AND estudiante_id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ii", $data['estudiante_asignacion_id'], $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $intento_numero = $row['intentos_realizados'] + 1;
    $puntos_anteriores = $row['puntos_anteriores'];

    // Calcular puntos adicionales (solo si mejoró su puntuación)
    $puntos_adicionales = max(0, $data['puntos_obtenidos'] - $puntos_anteriores);

    // Insertar resultado
    $query = "INSERT INTO resultados_ejercicios 
              (estudiante_asignacion_id, puntos_obtenidos, tiempo_empleado, detalles, 
               intento_numero, evidencia_path, fecha_realizacion) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conexion, $query);
    $detalles_json = json_encode($data['detalles']);
    mysqli_stmt_bind_param($stmt, "iiisis", 
        $data['estudiante_asignacion_id'],
        $data['puntos_obtenidos'],
        $data['tiempo_empleado'],
        $detalles_json,
        $intento_numero,
        $file_name
    );
    mysqli_stmt_execute($stmt);

    // Actualizar mejor puntuación si corresponde
    $query = "UPDATE estudiantes_asignaciones 
              SET intentos_realizados = intentos_realizados + 1,
                  puntos_obtenidos = GREATEST(puntos_obtenidos, ?),
                  estado = 'completado'
              WHERE id = ? AND estudiante_id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "iii", 
        $data['puntos_obtenidos'],
        $data['estudiante_asignacion_id'],
        $_SESSION['user_id']
    );
    mysqli_stmt_execute($stmt);

    // Actualizar puntos de práctica con los puntos adicionales
    if ($puntos_adicionales > 0) {
        $query = "UPDATE usuarios 
                  SET puntos_practica = COALESCE(puntos_practica, 0) + ? 
                  WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "ii", $puntos_adicionales, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
    }

    // Verificar y otorgar insignias
    $insignias_otorgadas = checkAndAwardBadges($_SESSION['user_id']);

    // Confirmar transacción
    mysqli_commit($conexion);
    
    // Obtener puntos totales actualizados
    $query = "SELECT COALESCE(puntos_practica, 0) as puntos_practica FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $puntos_totales = mysqli_fetch_assoc($result)['puntos_practica'];

    echo json_encode([
        'success' => true,
        'puntos_ganados' => $puntos_adicionales,
        'puntos_totales' => $puntos_totales,
        'insignias_otorgadas' => $insignias_otorgadas
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conexion);
    unlink($file_path); // Eliminar archivo subido
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar los resultados']);
}
?> 