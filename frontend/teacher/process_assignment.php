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

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Crear una asignación por cada grupo seleccionado
    foreach ($grupos as $grupo) {
        $query = "INSERT INTO asignaciones (profesor_id, ejercicio_id, grupo_asignado, fecha_asignacion, 
                                          fecha_limite, puntos_maximos, intentos_maximos, instrucciones, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activa')";
        
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "iisssiis", 
            $profesor_id, $ejercicio_id, $grupo, $fecha_inicio, 
            $fecha_limite, $puntos, $intentos, $instrucciones
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al crear la asignación para el grupo " . $grupo);
        }

        $asignacion_id = mysqli_insert_id($conexion);

        // Obtener estudiantes del grupo y crear registros en estudiantes_asignaciones
        $query_estudiantes = "SELECT id FROM usuarios 
                            WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                            AND grupo = ?";
        
        $stmt_estudiantes = mysqli_prepare($conexion, $query_estudiantes);
        mysqli_stmt_bind_param($stmt_estudiantes, "s", $grupo);
        mysqli_stmt_execute($stmt_estudiantes);
        $result_estudiantes = mysqli_stmt_get_result($stmt_estudiantes);

        // Crear registros en estudiantes_asignaciones
        while ($estudiante = mysqli_fetch_assoc($result_estudiantes)) {
            $query_asignar = "INSERT INTO estudiantes_asignaciones (
                asignacion_id, 
                estudiante_id, 
                estado, 
                intentos_realizados,
                puntos_obtenidos,
                fecha_ultimo_intento
            ) VALUES (?, ?, 'pendiente', 0, 0, NULL)";
            
            $stmt_asignar = mysqli_prepare($conexion, $query_asignar);
            mysqli_stmt_bind_param($stmt_asignar, "ii", $asignacion_id, $estudiante['id']);
            
            if (!mysqli_stmt_execute($stmt_asignar)) {
                throw new Exception("Error al asignar a estudiante: " . mysqli_error($conexion));
            }
        }
    }

    // Si todo salió bien, confirmar la transacción
    mysqli_commit($conexion);
    sendJsonResponse(true, 'Asignaciones creadas correctamente');

} catch (Exception $e) {
    // Si algo salió mal, revertir la transacción
    mysqli_rollback($conexion);
    sendJsonResponse(false, $e->getMessage());
} 