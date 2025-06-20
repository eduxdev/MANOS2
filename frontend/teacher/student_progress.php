<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

// Verificar si se proporcionó un ID de estudiante
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$estudiante_id = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

// Obtener información del estudiante
$query_estudiante = "SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.grupo,
    u.grado,
    u.email,
    u.puntos_practica,
    COUNT(DISTINCT CASE WHEN ea.estado = 'completado' THEN ea.asignacion_id END) as ejercicios_completados,
    COUNT(DISTINCT ea.asignacion_id) as total_asignaciones,
    COALESCE(SUM(CASE WHEN ea.estado = 'completado' THEN ea.puntos_obtenidos ELSE 0 END), 0) as puntos_asignaciones,
    (SELECT COUNT(*) FROM practicas_ejercicios pe WHERE pe.estudiante_id = u.id) as total_ejercicios_practica,
    (SELECT COUNT(*) FROM practicas_ejercicios pe WHERE pe.estudiante_id = u.id AND pe.respuesta_correcta = 1) as ejercicios_practica_correctos
FROM usuarios u
LEFT JOIN estudiantes_asignaciones ea ON u.id = ea.estudiante_id
LEFT JOIN asignaciones a ON ea.asignacion_id = a.id AND a.profesor_id = ?
WHERE u.id = ? AND u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
GROUP BY u.id";

$stmt = mysqli_prepare($conexion, $query_estudiante);
mysqli_stmt_bind_param($stmt, "ii", $profesor_id, $estudiante_id);
mysqli_stmt_execute($stmt);
$estudiante = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$estudiante) {
    header("Location: dashboard.php");
    exit();
}

// Obtener estadísticas de ejercicios de práctica por tipo
$query_stats_practica = "SELECT 
    tipo_ejercicio,
    COUNT(*) as total_intentos,
    SUM(CASE WHEN respuesta_correcta = 1 THEN 1 ELSE 0 END) as correctos
FROM practicas_ejercicios
WHERE estudiante_id = ?
GROUP BY tipo_ejercicio";

$stmt = mysqli_prepare($conexion, $query_stats_practica);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$stats_practica = mysqli_stmt_get_result($stmt);

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el total de registros para la paginación
$query_total = "SELECT COUNT(*) as total FROM practicas_ejercicios WHERE estudiante_id = ?";
$stmt = mysqli_prepare($conexion, $query_total);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener las últimas prácticas con paginación
$query_ultimas_practicas = "SELECT 
    tipo_ejercicio,
    respuesta_correcta,
    fecha_practica
FROM practicas_ejercicios
WHERE estudiante_id = ?
ORDER BY fecha_practica DESC
LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conexion, $query_ultimas_practicas);
mysqli_stmt_bind_param($stmt, "iii", $estudiante_id, $registros_por_pagina, $offset);
mysqli_stmt_execute($stmt);
$ultimas_practicas = mysqli_stmt_get_result($stmt);

// Obtener las últimas asignaciones del estudiante
$query_asignaciones = "SELECT 
    a.id,
    e.titulo as ejercicio_titulo,
    a.fecha_asignacion,
    a.fecha_limite,
    ea.estado,
    ea.fecha_entrega,
    ea.puntos_obtenidos,
    ea.evidencia_path
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
LEFT JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id AND ea.estudiante_id = ?
WHERE a.profesor_id = ? AND a.grupo_asignado = ?
ORDER BY a.fecha_asignacion DESC";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
mysqli_stmt_bind_param($stmt, "iis", $estudiante_id, $profesor_id, $estudiante['grupo']);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso del Estudiante - Talk Hands</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen">
    <?php include '../header.php'; ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Encabezado y navegación -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="dashboard.php" class="text-purple-400 hover:text-purple-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-white">
                        Progreso del Estudiante
                    </h1>
                </div>
            </div>

            <!-- Información del estudiante -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg p-6 mb-8 border border-gray-700">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-2">
                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                        </h2>
                        <div class="text-gray-300">
                            <p>Grupo <?php echo htmlspecialchars($estudiante['grupo']); ?> - <?php echo htmlspecialchars($estudiante['grado']); ?>° Grado</p>
                            <p class="text-sm"><?php echo htmlspecialchars($estudiante['email']); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Tareas Completadas</p>
                            <p class="text-2xl font-bold text-purple-400">
                                <?php echo $estudiante['ejercicios_completados']; ?>/<?php echo $estudiante['total_asignaciones']; ?>
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Ejercicios Correctos</p>
                            <p class="text-2xl font-bold text-green-400">
                                <?php echo $estudiante['ejercicios_practica_correctos']; ?>/<?php echo $estudiante['total_ejercicios_practica']; ?>
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Puntos Práctica</p>
                            <p class="text-2xl font-bold text-pink-400">
                                <?php echo $estudiante['puntos_practica']; ?>
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Puntos Totales</p>
                            <p class="text-2xl font-bold text-blue-400">
                                <?php echo $estudiante['puntos_practica'] + $estudiante['puntos_asignaciones']; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de ejercicios de práctica -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg p-6 mb-8 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-6">Estadísticas por Tipo de Ejercicio</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php 
                    $tipos_ejercicio = [
                        'letterIdentification' => 'Identificación de Letras',
                        'wordCompletion' => 'Completar Palabras',
                        'signRecognition' => 'Reconocimiento de Señas',
                        'errorDetection' => 'Detección de Errores'
                    ];
                    
                    $stats_por_tipo = [];
                    while ($stat = mysqli_fetch_assoc($stats_practica)) {
                        $stats_por_tipo[$stat['tipo_ejercicio']] = $stat;
                    }

                    foreach ($tipos_ejercicio as $tipo => $nombre): 
                        $stats = isset($stats_por_tipo[$tipo]) ? $stats_por_tipo[$tipo] : ['total_intentos' => 0, 'correctos' => 0];
                        $porcentaje = $stats['total_intentos'] > 0 ? round(($stats['correctos'] / $stats['total_intentos']) * 100) : 0;
                    ?>
                        <div class="bg-gray-700/50 rounded-lg p-4">
                            <h4 class="font-semibold text-white mb-2"><?php echo $nombre; ?></h4>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-300">Correctos:</span>
                                <span class="font-medium text-gray-200"><?php echo $stats['correctos']; ?>/<?php echo $stats['total_intentos']; ?></span>
                            </div>
                            <div class="w-full bg-gray-600 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <p class="text-sm text-gray-400 mt-2">
                                Tasa de éxito: <?php echo $porcentaje; ?>%
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Últimas prácticas -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg p-6 mb-8 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-6">Últimas Prácticas</h3>
                <div id="practicas-container" class="overflow-x-auto">
                    <div class="inline-block min-w-full align-middle">
                        <div class="overflow-hidden md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Resultado
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="practicas-tbody" class="bg-gray-800/30 divide-y divide-gray-700">
                                    <?php while ($practica = mysqli_fetch_assoc($ultimas_practicas)): ?>
                                        <tr class="hover:bg-gray-700/50">
                                            <td class="px-3 py-4 whitespace-normal text-sm">
                                                <div class="text-gray-200 break-words">
                                                    <?php 
                                                    $tipo = $tipos_ejercicio[$practica['tipo_ejercicio']] ?? $practica['tipo_ejercicio'];
                                                    echo htmlspecialchars(substr($tipo, 0, 20));
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <?php if ($practica['respuesta_correcta']): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-300">
                                                        Correcto
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900/50 text-red-300">
                                                        Incorrecto
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-400">
                                                    <?php echo date('d/m/y', strtotime($practica['fecha_practica'])); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Controles de paginación -->
                <?php if ($total_paginas > 1): ?>
                <div id="paginacion-container" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex flex-wrap justify-center gap-1" aria-label="Pagination">
                        <!-- Botón Anterior -->
                        <?php if ($pagina_actual > 1): ?>
                            <button onclick="cargarPagina(<?php echo ($pagina_actual - 1); ?>)" 
                               class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600 bg-gray-700 border border-gray-600 rounded-md">
                                <span class="sr-only">Anterior</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        <?php endif; ?>

                        <!-- Números de página -->
                        <?php 
                        $start_page = max(1, min($pagina_actual - 1, $total_paginas - 2));
                        $end_page = min($total_paginas, max($pagina_actual + 1, 3));
                        
                        if ($start_page > 1): ?>
                            <button onclick="cargarPagina(1)" 
                               class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-md">
                                1
                            </button>
                            <?php if ($start_page > 2): ?>
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400">
                                    ...
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <button onclick="cargarPagina(<?php echo $i; ?>)" 
                               class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 <?php echo $i === $pagina_actual ? 'bg-gray-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'; ?> rounded-md">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_paginas): ?>
                            <?php if ($end_page < $total_paginas - 1): ?>
                                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400">
                                    ...
                                </span>
                            <?php endif; ?>
                            <button onclick="cargarPagina(<?php echo $total_paginas; ?>)" 
                               class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-md">
                                <?php echo $total_paginas; ?>
                            </button>
                        <?php endif; ?>

                        <!-- Botón Siguiente -->
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <button onclick="cargarPagina(<?php echo ($pagina_actual + 1); ?>)" 
                               class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600 bg-gray-700 border border-gray-600 rounded-md">
                                <span class="sr-only">Siguiente</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

            <script>
            async function cargarPagina(pagina) {
                try {
                    const estudiante_id = <?php echo $estudiante_id; ?>;
                    const response = await fetch(`get_practicas.php?id=${estudiante_id}&pagina=${pagina}`);
                    
                    if (!response.ok) {
                        throw new Error('Error al cargar los datos');
                    }

                    const data = await response.json();
                    
                    // Actualizar la tabla
                    const tbody = document.getElementById('practicas-tbody');
                    tbody.innerHTML = data.practicas_html;
                    
                    // Actualizar la paginación
                    const paginacionContainer = document.getElementById('paginacion-container');
                    paginacionContainer.innerHTML = data.paginacion_html;
                    
                    // Actualizar la URL sin recargar la página
                    const nuevaUrl = new URL(window.location.href);
                    nuevaUrl.searchParams.set('pagina', pagina);
                    window.history.pushState({}, '', nuevaUrl);
                    
                } catch (error) {
                    console.error('Error:', error);
                }
            }
            </script>

            <!-- Historial de asignaciones -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg overflow-hidden border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-xl font-bold text-white">Historial de Asignaciones</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Ejercicio
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Fecha Asignación
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Fecha Límite
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Fecha Entrega
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Puntos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                    Evidencia
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800/30 divide-y divide-gray-700">
                            <?php if (mysqli_num_rows($asignaciones) > 0): ?>
                                <?php while ($asignacion = mysqli_fetch_assoc($asignaciones)): ?>
                                    <tr class="hover:bg-gray-700/50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-200">
                                                <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-400">
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_asignacion'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-400">
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($asignacion['estado'] === 'completado'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-300">
                                                    Completado
                                                </span>
                                            <?php elseif ($asignacion['estado'] === 'pendiente'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900/50 text-yellow-300">
                                                    Pendiente
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-700/50 text-gray-300">
                                                    Sin iniciar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-400">
                                                <?php echo $asignacion['fecha_entrega'] 
                                                    ? date('d/m/Y H:i', strtotime($asignacion['fecha_entrega']))
                                                    : '-'; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-200">
                                                <?php echo $asignacion['puntos_obtenidos'] ?? '-'; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($asignacion['evidencia_path']): ?>
                                                <a href="/<?php echo htmlspecialchars($asignacion['evidencia_path']); ?>" 
                                                   target="_blank"
                                                   class="text-purple-400 hover:text-purple-300">
                                                    Ver evidencia
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                                        No hay asignaciones para este estudiante
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
</body>
</html>