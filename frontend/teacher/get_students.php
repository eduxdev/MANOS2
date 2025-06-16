<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Obtener el grupo de la URL
$grupo = isset($_GET['grupo']) ? $_GET['grupo'] : '';

if (empty($grupo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Grupo no especificado']);
    exit();
}

// Consulta para obtener estudiantes con sus estadísticas
$query = "SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.grupo,
    u.puntos_practica,
    (SELECT COUNT(*) 
     FROM practicas_ejercicios pe 
     WHERE pe.estudiante_id = u.id 
     AND pe.respuesta_correcta = 1) as ejercicios_correctos,
    (SELECT COUNT(*) 
     FROM practicas_ejercicios pe 
     WHERE pe.estudiante_id = u.id) as total_ejercicios,
    (SELECT COUNT(*) 
     FROM estudiantes_asignaciones ea 
     WHERE ea.estudiante_id = u.id 
     AND ea.estado = 'completado') as ejercicios_completados,
    (SELECT COALESCE(SUM(ea.puntos_obtenidos), 0) 
     FROM estudiantes_asignaciones ea 
     WHERE ea.estudiante_id = u.id) as puntos_asignaciones,
    COALESCE(u.puntos_practica, 0) + 
    COALESCE((SELECT SUM(ea.puntos_obtenidos) 
              FROM estudiantes_asignaciones ea 
              WHERE ea.estudiante_id = u.id), 0) as puntos_totales
FROM usuarios u
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
AND u.grupo = ?
ORDER BY u.apellidos, u.nombre";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "s", $grupo);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$estudiantes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $estudiantes[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'apellidos' => $row['apellidos'],
        'grupo' => $row['grupo'],
        'ejercicios_correctos' => $row['ejercicios_correctos'],
        'total_ejercicios' => $row['total_ejercicios'],
        'ejercicios_completados' => $row['ejercicios_completados'],
        'puntos_practica' => $row['puntos_practica'],
        'puntos_asignaciones' => $row['puntos_asignaciones'],
        'puntos_totales' => $row['puntos_totales']
    ];
}

echo json_encode($estudiantes);
?> 