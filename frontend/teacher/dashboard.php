<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener datos del profesor
$profesor_id = $_SESSION['user_id'];

// Obtener lista de grupos únicos
$query_grupos = "SELECT DISTINCT grupo 
                FROM usuarios 
                WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                AND grupo IS NOT NULL
                ORDER BY grupo";
$result_grupos = mysqli_query($conexion, $query_grupos);

// Obtener estadísticas generales
$query_general_stats = "SELECT 
    COUNT(DISTINCT u.id) as total_estudiantes,
    COUNT(DISTINCT a.id) as total_asignaciones,
    COUNT(DISTINCT CASE WHEN ea.estado = 'completado' THEN ea.id END) as asignaciones_completadas,
    SUM(ea.puntos_obtenidos) as total_puntos_asignaciones,
    SUM(u.puntos_practica) as total_puntos_practica,
    (SELECT COUNT(*) FROM practicas_ejercicios pe 
     JOIN usuarios u2 ON pe.estudiante_id = u2.id 
     WHERE u2.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
     AND pe.respuesta_correcta = 1) as ejercicios_correctos,
    (SELECT COUNT(*) FROM practicas_ejercicios pe 
     JOIN usuarios u2 ON pe.estudiante_id = u2.id 
     WHERE u2.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')) as total_ejercicios
FROM usuarios u
LEFT JOIN estudiantes_asignaciones ea ON u.id = ea.estudiante_id
LEFT JOIN asignaciones a ON ea.asignacion_id = a.id AND a.profesor_id = ?
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')";

$stmt = mysqli_prepare($conexion, $query_general_stats);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$general_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Obtener últimas asignaciones
$query_asignaciones = "SELECT 
    a.id,
    a.fecha_asignacion,
    a.fecha_limite,
    a.grupo_asignado,
    (SELECT DISTINCT u.grado 
     FROM usuarios u 
     WHERE u.grupo = a.grupo_asignado 
     LIMIT 1) as grado,
    e.titulo as ejercicio_titulo,
    (SELECT COUNT(DISTINCT estudiante_id) 
     FROM estudiantes_asignaciones 
     WHERE asignacion_id = a.id) as total_estudiantes,
    (SELECT COUNT(DISTINCT estudiante_id) 
     FROM estudiantes_asignaciones 
     WHERE asignacion_id = a.id AND estado = 'completado') as estudiantes_completados
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
WHERE a.profesor_id = ?
ORDER BY a.fecha_asignacion DESC
LIMIT 5";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);

// Obtener estadísticas detalladas de asignaciones
$query_assignment_stats = "SELECT 
    COUNT(*) as total_asignaciones,
    SUM(CASE WHEN ea.estado = 'completado' THEN 1 ELSE 0 END) as completadas,
    SUM(CASE WHEN ea.estado = 'pendiente' AND a.fecha_limite < CURDATE() THEN 1 ELSE 0 END) as vencidas,
    SUM(ea.puntos_obtenidos) as total_puntos
FROM asignaciones a
JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
WHERE a.profesor_id = ?";

$stmt = mysqli_prepare($conexion, $query_assignment_stats);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$assignment_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Obtener últimas entregas
$query_entregas = "SELECT 
    u.nombre,
    u.apellidos,
    e.titulo as ejercicio,
    ea.fecha_entrega,
    ea.puntos_obtenidos,
    ea.evidencia_path,
    ea.asignacion_id,
    ea.estudiante_id
FROM estudiantes_asignaciones ea
JOIN asignaciones a ON ea.asignacion_id = a.id
JOIN usuarios u ON ea.estudiante_id = u.id
JOIN ejercicios e ON a.ejercicio_id = e.id
WHERE a.profesor_id = ? AND ea.estado = 'completado'
ORDER BY ea.fecha_entrega DESC
LIMIT 10";

$stmt = mysqli_prepare($conexion, $query_entregas);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$entregas = mysqli_stmt_get_result($stmt);

// Obtener ranking de estudiantes por puntos
$query_ranking = "SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.grupo,
    u.grado,
    u.puntos_practica,
    COALESCE(SUM(ea.puntos_obtenidos), 0) as puntos_asignaciones,
    (u.puntos_practica + COALESCE(SUM(ea.puntos_obtenidos), 0)) as puntos_totales,
    COUNT(DISTINCT CASE WHEN ea.estado = 'completado' THEN ea.asignacion_id END) as ejercicios_completados,
    (SELECT COUNT(*) FROM practicas_ejercicios pe WHERE pe.estudiante_id = u.id) as total_ejercicios,
    (SELECT COUNT(*) FROM practicas_ejercicios pe WHERE pe.estudiante_id = u.id AND pe.respuesta_correcta = 1) as ejercicios_correctos
FROM usuarios u
LEFT JOIN estudiantes_asignaciones ea ON u.id = ea.estudiante_id
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
GROUP BY u.id, u.nombre, u.apellidos, u.grupo, u.grado, u.puntos_practica
ORDER BY puntos_totales DESC
LIMIT 5";

$result_ranking = mysqli_query($conexion, $query_ranking);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Dashboard Profesor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Bienvenida y acciones rápidas -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-12">
                <div>
                    <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        Panel del Docente
                    </h1>
                    <p class="text-gray-600">
                        Gestiona tus clases y monitorea el progreso de tus estudiantes
                    </p>
                </div>
                <div class="mt-6 md:mt-0">
                    <a href="new_assignment.php" 
                       class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nueva Asignación
                    </a>
                </div>
            </div>

            <!-- Estadísticas generales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Total de estudiantes -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Estudiantes</h3>
                        <div class="text-purple-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-purple-600 mb-2">
                        <?php echo $general_stats['total_estudiantes']; ?>
                    </div>
                    <p class="text-gray-600">Estudiantes activos</p>
                </div>

                <!-- Total de asignaciones -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Asignaciones</h3>
                        <div class="text-pink-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-pink-600 mb-2">
                        <?php echo $general_stats['total_asignaciones']; ?>
                    </div>
                    <p class="text-gray-600">Total de asignaciones</p>
                </div>

                <!-- Tasa de completado -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Tasa de Completado</h3>
                        <div class="text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2">
                        <?php 
                        $tasa_completado = $general_stats['total_asignaciones'] > 0 
                            ? round(($general_stats['asignaciones_completadas'] / $general_stats['total_asignaciones']) * 100) 
                            : 0;
                        echo $tasa_completado . '%';
                        ?>
                    </div>
                    <p class="text-gray-600">Promedio de completado</p>
                </div>
            </div>

            <!-- Estadísticas detalladas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Asignaciones</h3>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $assignment_stats['total_asignaciones']; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Completadas</h3>
                    <p class="text-3xl font-bold text-green-600"><?php echo $assignment_stats['completadas']; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Vencidas</h3>
                    <p class="text-3xl font-bold text-red-600"><?php echo $assignment_stats['vencidas']; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Puntos Otorgados</h3>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $assignment_stats['total_puntos']; ?></p>
                </div>
            </div>

            <!-- Estadísticas de ejercicios de práctica -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Estadísticas de Práctica</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-purple-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Ejercicios</h3>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $general_stats['total_ejercicios']; ?></p>
                        <p class="text-sm text-gray-600 mt-2">Ejercicios realizados</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Ejercicios Correctos</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo $general_stats['ejercicios_correctos']; ?></p>
                        <p class="text-sm text-gray-600 mt-2">Respuestas correctas</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tasa de Éxito</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            <?php 
                            $tasa_exito = $general_stats['total_ejercicios'] > 0 
                                ? round(($general_stats['ejercicios_correctos'] / $general_stats['total_ejercicios']) * 100) 
                                : 0;
                            echo $tasa_exito . '%';
                            ?>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Porcentaje de aciertos</p>
                    </div>
                    <div class="bg-pink-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Puntos de Práctica</h3>
                        <p class="text-3xl font-bold text-pink-600"><?php echo $general_stats['total_puntos_practica']; ?></p>
                        <p class="text-sm text-gray-600 mt-2">Total acumulado</p>
                    </div>
                </div>
            </div>

            <!-- Resumen de puntos -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Resumen de Puntos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Puntos de Asignaciones</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php echo $general_stats['total_puntos_asignaciones']; ?>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Obtenidos en tareas</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-green-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Puntos de Práctica</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            <?php echo $general_stats['total_puntos_practica']; ?>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Obtenidos en ejercicios</p>
                    </div>
                    <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Puntos Totales</h3>
                        <p class="text-3xl font-bold text-pink-600">
                            <?php 
                            $total_puntos = $general_stats['total_puntos_asignaciones'] + $general_stats['total_puntos_practica'];
                            echo $total_puntos;
                            ?>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Total acumulado</p>
                    </div>
                </div>
            </div>

            <!-- Últimas entregas -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Últimas Entregas</h2>
                
                <?php if (mysqli_num_rows($entregas) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estudiante
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ejercicio
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha de Entrega
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Puntos
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($entrega = mysqli_fetch_assoc($entregas)): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($entrega['nombre'] . ' ' . $entrega['apellidos']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo htmlspecialchars($entrega['ejercicio']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo $entrega['puntos_obtenidos']; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($entrega['evidencia_path']): ?>
                                                <div class="flex items-center space-x-4">
                                                    <a href="/<?php echo htmlspecialchars($entrega['evidencia_path']); ?>" 
                                                       target="_blank"
                                                       class="text-purple-600 hover:text-purple-900">
                                                        Ver evidencia
                                                    </a>
                                                    <button onclick="invalidateSubmission(<?php echo $entrega['asignacion_id']; ?>, <?php echo $entrega['estudiante_id']; ?>)"
                                                            class="text-red-600 hover:text-red-900">
                                                        Invalidar
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        <p>No hay entregas recientes.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Últimas asignaciones -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Últimas Asignaciones</h2>
                    <a href="assignments.php" class="text-purple-600 hover:text-purple-700 font-medium">
                        Ver todas
                    </a>
                </div>

                <?php if (mysqli_num_rows($asignaciones) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ejercicio
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grupo y Grado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Asignación
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Límite
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Progreso
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($asignacion = mysqli_fetch_assoc($asignaciones)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php 
                                                echo "Grupo " . htmlspecialchars($asignacion['grupo_asignado']);
                                                if ($asignacion['grado']) {
                                                    echo " - " . htmlspecialchars($asignacion['grado']) . "° Grado";
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_asignacion'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <?php 
                                                    $porcentaje = $asignacion['total_estudiantes'] > 0 
                                                        ? ($asignacion['estudiantes_completados'] / $asignacion['total_estudiantes']) * 100 
                                                        : 0;
                                                    ?>
                                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-600">
                                                    <?php echo $asignacion['estudiantes_completados']; ?>/<?php echo $asignacion['total_estudiantes']; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-4">
                                                <a href="view_assignment.php?id=<?php echo $asignacion['id']; ?>" 
                                                   class="text-purple-600 hover:text-purple-900">
                                                    Ver detalles
                                                </a>
                                                <button onclick="deleteAssignment(<?php echo $asignacion['id']; ?>)"
                                                        class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        <p>No hay asignaciones recientes.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Separador decorativo -->
            <div class="relative py-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-gradient-to-b from-white to-purple-50 px-4 text-sm font-semibold text-gray-500">
                        Ranking y Estudiantes
                    </span>
                </div>
            </div>

            <!-- Ranking de estudiantes -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Top 5 Estudiantes</h2>
                <div class="grid grid-cols-1 gap-4">
                    <?php 
                    $posicion = 1;
                    while ($estudiante = mysqli_fetch_assoc($result_ranking)): 
                        $tasa_exito = $estudiante['total_ejercicios'] > 0 
                            ? round(($estudiante['ejercicios_correctos'] / $estudiante['total_ejercicios']) * 100) 
                            : 0;
                    ?>
                        <div class="bg-gradient-to-r <?php echo $posicion === 1 ? 'from-yellow-50 to-orange-50 border-2 border-yellow-200' : 'from-purple-50 to-pink-50'; ?> rounded-xl p-6 transition-transform hover:scale-[1.02]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-12 h-12 <?php echo $posicion === 1 ? 'bg-yellow-200' : 'bg-purple-200'; ?> rounded-full flex items-center justify-center">
                                        <span class="text-2xl font-bold <?php echo $posicion === 1 ? 'text-yellow-800' : 'text-purple-800'; ?>">
                                            #<?php echo $posicion; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Grupo <?php echo htmlspecialchars($estudiante['grupo']); ?> - 
                                            <?php echo htmlspecialchars($estudiante['grado']); ?>° Grado
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold <?php echo $posicion === 1 ? 'text-yellow-600' : 'text-purple-600'; ?>">
                                        <?php echo $estudiante['puntos_totales']; ?>
                                    </p>
                                    <p class="text-sm text-gray-600">puntos totales</p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                                <div class="text-center">
                                    <p class="font-medium text-gray-900"><?php echo $estudiante['ejercicios_completados']; ?></p>
                                    <p class="text-gray-600">tareas completadas</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-medium text-gray-900"><?php echo $tasa_exito; ?>%</p>
                                    <p class="text-gray-600">tasa de éxito</p>
                                </div>
                                <div class="text-center">
                                    <p class="font-medium text-gray-900"><?php echo $estudiante['puntos_practica']; ?></p>
                                    <p class="text-gray-600">puntos práctica</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="student_progress.php?id=<?php echo $estudiante['id']; ?>" 
                                   class="text-sm font-medium <?php echo $posicion === 1 ? 'text-yellow-600 hover:text-yellow-700' : 'text-purple-600 hover:text-purple-700'; ?>">
                                    Ver progreso detallado →
                                </a>
                            </div>
                        </div>
                    <?php 
                        $posicion++;
                    endwhile; 
                    ?>
                </div>
            </div>

            <!-- Selector de grupo y lista de estudiantes -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Estudiantes por Grupo</h2>
                    <select id="grupo_selector" class="rounded-lg border-gray-300 text-gray-700 text-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Seleccionar grupo</option>
                        <?php while ($grupo = mysqli_fetch_assoc($result_grupos)): ?>
                            <option value="<?php echo htmlspecialchars($grupo['grupo']); ?>">
                                Grupo <?php echo htmlspecialchars($grupo['grupo']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div id="lista_estudiantes" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Los estudiantes se cargarán dinámicamente aquí -->
                </div>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        // Función para invalidar entrega
        function invalidateSubmission(assignmentId, studentId) {
            showMessageModal(
                'message-modal',
                'Confirmar invalidación',
                '¿Estás seguro de que deseas invalidar esta entrega? Se restarán los puntos obtenidos.',
                'handleInvalidateSubmission(' + assignmentId + ', ' + studentId + ')'
            );
        }

        function handleInvalidateSubmission(assignmentId, studentId) {
            fetch('handle_assignment_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=invalidate_submission&assignment_id=' + assignmentId + '&student_id=' + studentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessageModal('message-modal', 'Éxito', 'Entrega invalidada correctamente', 'location.reload()');
                } else {
                    showMessageModal('message-modal', 'Error', 'Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageModal('message-modal', 'Error', 'Error al procesar la solicitud');
            });
        }

        // Función para eliminar asignación
        function deleteAssignment(assignmentId) {
            showMessageModal(
                'message-modal',
                'Confirmar eliminación',
                '¿Estás seguro de que deseas eliminar esta asignación? Esta acción no se puede deshacer.',
                function() {
                    handleDeleteAssignment(assignmentId);
                }
            );
        }

        function handleDeleteAssignment(assignmentId) {
            fetch('handle_assignment_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete_assignment&assignment_id=' + assignmentId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessageModal('message-modal', 'Éxito', 'Asignación eliminada correctamente', function() {
                        location.reload();
                    });
                } else {
                    showMessageModal('message-modal', 'Error', 'Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageModal('message-modal', 'Error', 'Error al procesar la solicitud');
            });
        }

        // Cargar estudiantes por grupo
        document.getElementById('grupo_selector').addEventListener('change', function() {
            const grupo = this.value;
            const listaEstudiantes = document.getElementById('lista_estudiantes');
            
            if (!grupo) {
                listaEstudiantes.innerHTML = '<p class="text-center text-gray-500 col-span-3">Selecciona un grupo para ver sus estudiantes</p>';
                return;
            }

            // Realizar petición AJAX para obtener estudiantes
            fetch(`get_students.php?grupo=${grupo}`)
                .then(response => response.json())
                .then(estudiantes => {
                    listaEstudiantes.innerHTML = '';
                    
                    estudiantes.forEach(estudiante => {
                        const card = document.createElement('div');
                        card.className = 'bg-purple-50 rounded-lg p-6';
                        card.innerHTML = `
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-200 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-purple-700 font-semibold">
                                        ${estudiante.nombre.charAt(0)}${estudiante.apellidos.charAt(0)}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">${estudiante.nombre} ${estudiante.apellidos}</h4>
                                    <p class="text-sm text-gray-500">Grupo ${estudiante.grupo}</p>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Ejercicios de práctica:</span>
                                    <span class="font-medium">${estudiante.ejercicios_correctos}/${estudiante.total_ejercicios}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tareas completadas:</span>
                                    <span class="font-medium">${estudiante.ejercicios_completados}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Puntos de práctica:</span>
                                    <span class="font-medium text-purple-600">${estudiante.puntos_practica}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Puntos de tareas:</span>
                                    <span class="font-medium text-pink-600">${estudiante.puntos_asignaciones}</span>
                                </div>
                                <div class="flex justify-between text-sm font-semibold mt-2 pt-2 border-t border-purple-100">
                                    <span class="text-gray-700">Puntos totales:</span>
                                    <span class="text-blue-600">${estudiante.puntos_totales}</span>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <a href="student_progress.php?id=${estudiante.id}" 
                                   class="inline-block text-sm text-purple-600 hover:text-purple-700 font-medium">
                                    Ver progreso detallado →
                                </a>
                                <div class="text-xs text-gray-500">
                                    Tasa de éxito: ${estudiante.total_ejercicios > 0 ? Math.round((estudiante.ejercicios_correctos / estudiante.total_ejercicios) * 100) : 0}%
                                </div>
                            </div>
                        `;
                        listaEstudiantes.appendChild(card);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessageModal('message-modal', 'Error', 'Error al cargar los estudiantes');
                });
        });
    </script>
</body>
</html> 