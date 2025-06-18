<?php
session_start();
require_once '../../backend/db/conection.php';

// Configurar el tipo de contenido como JSON
header('Content-Type: application/json');

// Función para enviar respuesta JSON
function sendJsonResponse($success, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    sendJsonResponse(false, 'No tienes permisos para realizar esta acción');
}

// Verificar que se recibieron todos los datos necesarios
if (!isset($_POST['ejercicio_id']) || !isset($_POST['grupos']) || 
    !isset($_POST['fecha_inicio']) || !isset($_POST['fecha_limite']) || 
    !isset($_POST['puntos']) || !isset($_POST['intentos'])) {
    sendJsonResponse(false, 'Faltan datos requeridos');
}

$profesor_id = $_SESSION['user_id'];
$ejercicio_id = $_POST['ejercicio_id'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_limite = $_POST['fecha_limite'];
$puntos = $_POST['puntos'];
$intentos = $_POST['intentos'];
$instrucciones = isset($_POST['instrucciones']) ? $_POST['instrucciones'] : '';
$grupos = $_POST['grupos'];

// Validar fechas
if (strtotime($fecha_inicio) < strtotime('today')) {
    sendJsonResponse(false, 'La fecha de inicio no puede ser anterior a hoy');
}

if (strtotime($fecha_limite) < strtotime($fecha_inicio)) {
    sendJsonResponse(false, 'La fecha límite no puede ser anterior a la fecha de inicio');
}

// Validar puntos e intentos
if ($puntos < 0 || $puntos > 100) {
    sendJsonResponse(false, 'Los puntos deben estar entre 0 y 100');
}

if ($intentos < 1 || $intentos > 10) {
    sendJsonResponse(false, 'Los intentos deben estar entre 1 y 10');
}

// Obtener el tipo de ejercicio
$query_tipo = "SELECT c.nombre as tipo 
               FROM ejercicios e 
               JOIN categorias_ejercicios c ON e.categoria_id = c.id 
               WHERE e.id = ?";
$stmt = mysqli_prepare($conexion, $query_tipo);
mysqli_stmt_bind_param($stmt, "i", $ejercicio_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$tipo_ejercicio = $resultado['tipo'];

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Crear asignación para cada grupo
    foreach ($grupos as $grupo) {
        // Insertar asignación
        $query = "INSERT INTO asignaciones (
                    profesor_id, ejercicio_id, grupo_asignado, 
                    fecha_asignacion, fecha_limite, puntos_maximos, 
                    intentos_maximos, instrucciones, tipo_ejercicio, is_new
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)";
        
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "iisssiiis", 
            $profesor_id, $ejercicio_id, $grupo, 
            $fecha_inicio, $fecha_limite, $puntos, 
            $intentos, $instrucciones, $tipo_ejercicio
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al crear la asignación para el grupo " . $grupo);
        }
        
        $asignacion_id = mysqli_insert_id($conexion);
        
        // Asignar a todos los estudiantes del grupo
        $query_estudiantes = "INSERT INTO estudiantes_asignaciones (asignacion_id, estudiante_id)
                             SELECT ?, id FROM usuarios 
                             WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                             AND grupo = ?";
        
        $stmt = mysqli_prepare($conexion, $query_estudiantes);
        mysqli_stmt_bind_param($stmt, "is", $asignacion_id, $grupo);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al asignar estudiantes del grupo " . $grupo);
        }
    }
    
    // Confirmar transacción
    mysqli_commit($conexion);
    sendJsonResponse(true, 'Asignaciones creadas correctamente');
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($conexion);
    sendJsonResponse(false, $e->getMessage());
} 