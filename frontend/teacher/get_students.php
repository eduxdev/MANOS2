<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Obtener y validar el grupo
$grupo = isset($_GET['grupo']) ? $_GET['grupo'] : '';
if (empty($grupo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Grupo no especificado']);
    exit();
}

// Consulta para obtener estudiantes y sus estadísticas
$query = "SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.grupo,
    COUNT(DISTINCT CASE WHEN pe.completado = 1 THEN pe.ejercicio_id END) as ejercicios_completados,
    COALESCE(SUM(pe.puntos_obtenidos), 0) as puntos_totales
FROM usuarios u
LEFT JOIN progreso_estudiantes pe ON u.id = pe.usuario_id
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
AND u.grupo = ?
GROUP BY u.id
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
        'ejercicios_completados' => (int)$row['ejercicios_completados'],
        'puntos_totales' => (int)$row['puntos_totales']
    ];
}

// Devolver resultados en formato JSON
header('Content-Type: application/json');
echo json_encode($estudiantes);
?> 