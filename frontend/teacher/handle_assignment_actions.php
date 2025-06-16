<?php
session_start();
require_once '../../backend/db/conection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$profesor_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch($action) {
    case 'delete_assignment':
        if (!isset($_POST['assignment_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID de asignación no proporcionado']);
            exit();
        }

        $assignment_id = $_POST['assignment_id'];

        // Verificar que la asignación pertenezca al profesor
        $check_query = "SELECT id FROM asignaciones WHERE id = ? AND profesor_id = ?";
        $stmt = mysqli_prepare($conexion, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $profesor_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Asignación no encontrada']);
            exit();
        }

        // Eliminar registros relacionados en estudiantes_asignaciones
        $delete_ea = "DELETE FROM estudiantes_asignaciones WHERE asignacion_id = ?";
        $stmt = mysqli_prepare($conexion, $delete_ea);
        mysqli_stmt_bind_param($stmt, "i", $assignment_id);
        mysqli_stmt_execute($stmt);

        // Eliminar la asignación
        $delete_assignment = "DELETE FROM asignaciones WHERE id = ? AND profesor_id = ?";
        $stmt = mysqli_prepare($conexion, $delete_assignment);
        mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $profesor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Asignación eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la asignación']);
        }
        break;

    case 'invalidate_submission':
        if (!isset($_POST['assignment_id']) || !isset($_POST['student_id'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit();
        }

        $assignment_id = $_POST['assignment_id'];
        $student_id = $_POST['student_id'];

        // Verificar que la asignación pertenezca al profesor
        $check_query = "SELECT a.id 
                       FROM asignaciones a
                       JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
                       WHERE a.id = ? AND a.profesor_id = ? AND ea.estudiante_id = ?";
        $stmt = mysqli_prepare($conexion, $check_query);
        mysqli_stmt_bind_param($stmt, "iii", $assignment_id, $profesor_id, $student_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Entrega no encontrada']);
            exit();
        }

    
        $update_submission = "UPDATE estudiantes_asignaciones 
                            SET estado = 'pendiente',
                                puntos_obtenidos = 0,
                                fecha_entrega = NULL
                            WHERE asignacion_id = ? AND estudiante_id = ?";
        $stmt = mysqli_prepare($conexion, $update_submission);
        mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $student_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Entrega invalidada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al invalidar la entrega']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?> 