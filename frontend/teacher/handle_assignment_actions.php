<?php
// Asegurarnos de que los errores de PHP no se muestren directamente
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Función para enviar respuesta JSON
function sendJsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Manejar errores de PHP
function handleError($errno, $errstr, $errfile, $errline) {
    sendJsonResponse(false, 'Error interno del servidor: ' . $errstr);
}
set_error_handler('handleError');

try {
    session_start();
    require_once '../../backend/db/conection.php';

    // Verificar si el usuario está logueado y es profesor
    if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
        sendJsonResponse(false, 'No autorizado');
    }

    $profesor_id = $_SESSION['user_id'];

    // Obtener datos de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : (isset($input['action']) ? $input['action'] : '');

        switch ($action) {
            case 'invalidate_submission':
                $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
                $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
                
                if ($assignment_id > 0 && $student_id > 0) {
                    // Verificar que la asignación pertenezca al profesor
                    $query_check = "SELECT a.id, a.puntos_maximos, ea.puntos_obtenidos 
                                  FROM asignaciones a 
                                  JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id 
                                  WHERE a.id = ? AND a.profesor_id = ? AND ea.estudiante_id = ?";
                    
                    $stmt = mysqli_prepare($conexion, $query_check);
                    mysqli_stmt_bind_param($stmt, "iii", $assignment_id, $_SESSION['user_id'], $student_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        // Actualizar el estado de la entrega
                        $query_update = "UPDATE estudiantes_asignaciones 
                                       SET estado = 'pendiente',
                                           evidencia_path = NULL,
                                           fecha_entrega = NULL,
                                           puntos_obtenidos = 0,
                                           fue_invalidada = TRUE
                                       WHERE asignacion_id = ? AND estudiante_id = ?";
                        
                        $stmt = mysqli_prepare($conexion, $query_update);
                        mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $student_id);
                        
                        if (mysqli_stmt_execute($stmt)) {
                            echo json_encode(['success' => true, 'message' => 'Entrega invalidada correctamente']);
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Error al invalidar la entrega']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'error' => 'No se encontró la asignación o no tienes permisos']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                }
                break;

            case 'delete_assignment':
                $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
                
                if ($assignment_id > 0) {
                    // Verificar que la asignación pertenezca al profesor
                    $query_check = "SELECT id FROM asignaciones WHERE id = ? AND profesor_id = ?";
                    $stmt = mysqli_prepare($conexion, $query_check);
                    mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $profesor_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $exists = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($stmt);
                    
                    if ($exists) {
                        mysqli_begin_transaction($conexion);
                        
                        try {
                            // Eliminar registros de estudiantes_asignaciones (esto eliminará en cascada los resultados)
                            $query_delete_estudiantes = "DELETE FROM estudiantes_asignaciones WHERE asignacion_id = ?";
                            $stmt = mysqli_prepare($conexion, $query_delete_estudiantes);
                            mysqli_stmt_bind_param($stmt, "i", $assignment_id);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);

                            // Eliminar la asignación
                            $query_delete = "DELETE FROM asignaciones WHERE id = ? AND profesor_id = ?";
                            $stmt = mysqli_prepare($conexion, $query_delete);
                            mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $profesor_id);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);

                            mysqli_commit($conexion);
                            sendJsonResponse(true, 'Asignación eliminada correctamente');
                        } catch (Exception $e) {
                            mysqli_rollback($conexion);
                            sendJsonResponse(false, 'Error al eliminar la asignación');
                        }
                    } else {
                        sendJsonResponse(false, 'Asignación no encontrada');
                    }
                } else {
                    sendJsonResponse(false, 'ID de asignación inválido');
                }
                break;

            case 'mark_as_seen':
                $assignment_ids = $input['assignment_ids'] ?? [];
                
                if (!empty($assignment_ids)) {
                    $placeholders = str_repeat('?,', count($assignment_ids) - 1) . '?';
                    $types = str_repeat('i', count($assignment_ids));
                    
                    $query = "UPDATE asignaciones SET is_new = FALSE WHERE id IN ($placeholders) AND profesor_id = ?";
                    
                    $stmt = mysqli_prepare($conexion, $query);
                    
                    // Añadir el profesor_id al final del array de parámetros
                    $assignment_ids[] = $profesor_id;
                    $types .= 'i';
                    
                    mysqli_stmt_bind_param($stmt, $types, ...$assignment_ids);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        sendJsonResponse(true, 'Asignaciones actualizadas correctamente');
                    } else {
                        sendJsonResponse(false, 'Error al actualizar las asignaciones');
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    sendJsonResponse(false, 'No se proporcionaron IDs de asignaciones');
                }
                break;

            default:
                sendJsonResponse(false, 'Acción no válida');
                break;
        }
    } else {
        sendJsonResponse(false, 'Método no permitido');
    }
} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage());
}
?> 