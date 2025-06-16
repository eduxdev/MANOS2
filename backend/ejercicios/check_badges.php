<?php
require_once __DIR__ . '/../../backend/db/conection.php';

function checkAndAwardBadges($usuario_id) {
    global $conexion;
    
    // Obtener estadísticas del usuario
    $query_stats = "SELECT 
        COUNT(DISTINCT ea.asignacion_id) as total_ejercicios,
        COALESCE(SUM(ea.puntos_obtenidos), 0) + COALESCE(u.puntos_practica, 0) as total_puntos,
        COUNT(DISTINCT CASE WHEN DATE(ea.fecha_entrega) = CURDATE() THEN ea.id END) as ejercicios_hoy,
        COUNT(DISTINCT DATE(ea.fecha_entrega)) as dias_activos
    FROM usuarios u
    LEFT JOIN estudiantes_asignaciones ea ON ea.estudiante_id = u.id AND ea.estado = 'completado'
    WHERE u.id = ?
    GROUP BY u.id";
    
    $stmt = mysqli_prepare($conexion, $query_stats);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    // Obtener todas las insignias disponibles
    $query_insignias = "SELECT id, nombre, requisito_puntos, requisito_ejercicios, tipo 
                       FROM insignias 
                       WHERE id NOT IN (
                           SELECT insignia_id 
                           FROM insignias_usuarios 
                           WHERE usuario_id = ?
                       )";
    
    $stmt = mysqli_prepare($conexion, $query_insignias);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $insignias = mysqli_stmt_get_result($stmt);
    
    $insignias_otorgadas = [];
    
    while ($insignia = mysqli_fetch_assoc($insignias)) {
        $otorgar = false;
        
        switch ($insignia['tipo']) {
            case 'puntos':
                if ($stats['total_puntos'] >= $insignia['requisito_puntos']) {
                    $otorgar = true;
                }
                break;
                
            case 'ejercicios':
                if ($stats['total_ejercicios'] >= $insignia['requisito_ejercicios']) {
                    $otorgar = true;
                }
                break;
                
            case 'especial':
                if ($insignia['nombre'] === 'Velocista' && $stats['ejercicios_hoy'] >= 3) {
                    $otorgar = true;
                }
                elseif ($insignia['nombre'] === 'Constante' && $stats['dias_activos'] >= 7) {
                    $otorgar = true;
                }
                break;
        }
        
        if ($otorgar) {
            // Otorgar la insignia
            $query_otorgar = "INSERT IGNORE INTO insignias_usuarios (usuario_id, insignia_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($conexion, $query_otorgar);
            mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $insignia['id']);
            
            if (mysqli_stmt_execute($stmt)) {
                $insignias_otorgadas[] = $insignia['nombre'];
            }
        }
    }
    
    return $insignias_otorgadas;
}
?> 