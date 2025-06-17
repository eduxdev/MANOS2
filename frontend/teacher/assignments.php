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

// Configuración de paginación
$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

// Filtros
$filtro_grupo = isset($_GET['grupo']) ? $_GET['grupo'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$filtro_fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir la consulta base
$query_base = "FROM asignaciones a
               JOIN ejercicios e ON a.ejercicio_id = e.id
               WHERE a.profesor_id = ?";
$params = [$profesor_id];
$types = "i";

// Aplicar filtros
if ($filtro_grupo) {
    $query_base .= " AND a.grupo_asignado = ?";
    $params[] = $filtro_grupo;
    $types .= "s";
}

if ($filtro_estado) {
    if ($filtro_estado === 'pendiente') {
        $query_base .= " AND a.fecha_limite >= CURDATE()";
    } elseif ($filtro_estado === 'vencido') {
        $query_base .= " AND a.fecha_limite < CURDATE()";
    }
}

if ($filtro_fecha_desde) {
    $query_base .= " AND a.fecha_asignacion >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if ($filtro_fecha_hasta) {
    $query_base .= " AND a.fecha_asignacion <= ?";
    $params[] = $filtro_fecha_hasta;
    $types .= "s";
}

// Obtener total de registros para paginación
$query_count = "SELECT COUNT(*) as total " . $query_base;
$stmt = mysqli_prepare($conexion, $query_count);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_paginas = ceil($total_registros / $items_por_pagina);

// Obtener asignaciones
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
    e.descripcion as ejercicio_descripcion,
    (SELECT COUNT(DISTINCT estudiante_id) 
     FROM estudiantes_asignaciones 
     WHERE asignacion_id = a.id) as total_estudiantes,
    (SELECT COUNT(DISTINCT estudiante_id) 
     FROM estudiantes_asignaciones 
     WHERE asignacion_id = a.id AND estado = 'completado') as estudiantes_completados,
    (SELECT AVG(puntos_obtenidos) 
     FROM estudiantes_asignaciones 
     WHERE asignacion_id = a.id AND estado = 'completado') as promedio_puntos
    " . $query_base . "
    ORDER BY a.fecha_asignacion DESC
    LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
$params[] = $items_por_pagina;
$params[] = $offset;
$types .= "ii";
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);

// Obtener lista de grupos únicos para el filtro
$query_grupos = "SELECT DISTINCT grupo_asignado 
                FROM asignaciones 
                WHERE profesor_id = ? 
                ORDER BY grupo_asignado";
$stmt = mysqli_prepare($conexion, $query_grupos);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$grupos = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Todas las Asignaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="flex flex-col min-h-full bg-gradient-to-b from-white to-purple-50">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <main class="flex-grow pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Encabezado y navegación -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="dashboard.php" class="text-purple-600 hover:text-purple-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Todas las Asignaciones
                    </h1>
                </div>
                <div class="flex justify-end">
                    <a href="new_assignment.php" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nueva Asignación
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Filtros de Búsqueda</h2>
                        <p class="text-sm text-gray-500 mt-1">Utiliza los filtros para encontrar asignaciones específicas</p>
                    </div>
                </div>

                <form action="" method="GET" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Filtro de Grupo -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Grupo
                            </label>
                            <select name="grupo" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors bg-white">
                                <option value="">Todos los grupos</option>
                                <?php while ($grupo = mysqli_fetch_assoc($grupos)): ?>
                                    <option value="<?php echo htmlspecialchars($grupo['grupo_asignado']); ?>"
                                            <?php echo $filtro_grupo === $grupo['grupo_asignado'] ? 'selected' : ''; ?>>
                                        Grupo <?php echo htmlspecialchars($grupo['grupo_asignado']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Filtro de Estado -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Estado
                            </label>
                            <select name="estado" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors bg-white">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?php echo $filtro_estado === 'pendiente' ? 'selected' : ''; ?>>
                                    <span>⏳ Pendientes</span>
                                </option>
                                <option value="vencido" <?php echo $filtro_estado === 'vencido' ? 'selected' : ''; ?>>
                                    <span>⚠️ Vencidos</span>
                                </option>
                            </select>
                        </div>

                        <!-- Filtro de Fecha Desde -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Fecha desde
                            </label>
                            <div class="relative">
                                <input type="date" name="fecha_desde" value="<?php echo $filtro_fecha_desde; ?>"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors bg-white pl-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">📅</span>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro de Fecha Hasta -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Fecha hasta
                            </label>
                            <div class="relative">
                                <input type="date" name="fecha_hasta" value="<?php echo $filtro_fecha_hasta; ?>"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors bg-white pl-10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">📅</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="assignments.php" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Limpiar filtros
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Aplicar filtros
                        </button>
                    </div>

                    <?php if ($filtro_grupo || $filtro_estado || $filtro_fecha_desde || $filtro_fecha_hasta): ?>
                    <div class="mt-4 flex items-center bg-purple-50 p-4 rounded-lg">
                        <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-purple-700">
                            Filtros activos:
                            <?php
                            $filtros_activos = [];
                            if ($filtro_grupo) $filtros_activos[] = "Grupo " . $filtro_grupo;
                            if ($filtro_estado) $filtros_activos[] = ucfirst($filtro_estado);
                            if ($filtro_fecha_desde) $filtros_activos[] = "Desde " . date('d/m/Y', strtotime($filtro_fecha_desde));
                            if ($filtro_fecha_hasta) $filtros_activos[] = "Hasta " . date('d/m/Y', strtotime($filtro_fecha_hasta));
                            echo implode(" • ", $filtros_activos);
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Lista de asignaciones -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Lista de Asignaciones</h2>
                </div>

                <?php if (mysqli_num_rows($asignaciones) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ejercicio
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grupo y Grado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fechas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Progreso
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Promedio
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($asignacion = mysqli_fetch_assoc($asignaciones)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo mb_strimwidth(htmlspecialchars($asignacion['ejercicio_descripcion']), 0, 100, "..."); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                Grupo <?php echo htmlspecialchars($asignacion['grupo_asignado']); ?>
                                            </div>
                                            <?php if ($asignacion['grado']): ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($asignacion['grado']); ?>° Grado
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium">Asignado:</span> 
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_asignacion'])); ?>
                                            </div>
                                            <div class="text-sm <?php echo strtotime($asignacion['fecha_limite']) < time() ? 'text-red-600 font-medium' : 'text-gray-500'; ?>">
                                                <span class="font-medium">Límite:</span>
                                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                    <?php 
                                                    $porcentaje = $asignacion['total_estudiantes'] > 0 
                                                        ? ($asignacion['estudiantes_completados'] / $asignacion['total_estudiantes']) * 100 
                                                        : 0;
                                                    ?>
                                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                                                </div>
                                                <span class="text-sm text-gray-600 whitespace-nowrap">
                                                    <?php echo $asignacion['estudiantes_completados']; ?>/<?php echo $asignacion['total_estudiantes']; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium <?php echo $asignacion['promedio_puntos'] >= 7 ? 'text-green-600' : 'text-gray-900'; ?>">
                                                <?php echo $asignacion['promedio_puntos'] ? number_format($asignacion['promedio_puntos'], 1) : '-'; ?>
                                                <?php if ($asignacion['promedio_puntos'] >= 7): ?>
                                                    <svg class="inline-block w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <a href="view_assignment.php?id=<?php echo $asignacion['id']; ?>" 
                                                   class="text-purple-600 hover:text-purple-900 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Ver
                                                </a>
                                                <button onclick="deleteAssignment(<?php echo $asignacion['id']; ?>)"
                                                        class="text-red-600 hover:text-red-900 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row justify-between items-center">
                                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                                    Mostrando <span class="font-medium"><?php echo ($offset + 1); ?></span> a 
                                    <span class="font-medium"><?php echo min($offset + $items_por_pagina, $total_registros); ?></span> de 
                                    <span class="font-medium"><?php echo $total_registros; ?></span> resultados
                                </div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <?php if ($pagina_actual > 1): ?>
                                        <a href="?pagina=<?php echo ($pagina_actual - 1); ?><?php echo $filtro_grupo ? '&grupo=' . urlencode($filtro_grupo) : ''; ?><?php echo $filtro_estado ? '&estado=' . urlencode($filtro_estado) : ''; ?><?php echo $filtro_fecha_desde ? '&fecha_desde=' . urlencode($filtro_fecha_desde) : ''; ?><?php echo $filtro_fecha_hasta ? '&fecha_hasta=' . urlencode($filtro_fecha_hasta) : ''; ?>"
                                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Anterior</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <a href="?pagina=<?php echo $i; ?><?php echo $filtro_grupo ? '&grupo=' . urlencode($filtro_grupo) : ''; ?><?php echo $filtro_estado ? '&estado=' . urlencode($filtro_estado) : ''; ?><?php echo $filtro_fecha_desde ? '&fecha_desde=' . urlencode($filtro_fecha_desde) : ''; ?><?php echo $filtro_fecha_hasta ? '&fecha_hasta=' . urlencode($filtro_fecha_hasta) : ''; ?>"
                                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $pagina_actual ? 'text-purple-600 bg-purple-50 border-purple-500' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($pagina_actual < $total_paginas): ?>
                                        <a href="?pagina=<?php echo ($pagina_actual + 1); ?><?php echo $filtro_grupo ? '&grupo=' . urlencode($filtro_grupo) : ''; ?><?php echo $filtro_estado ? '&estado=' . urlencode($filtro_estado) : ''; ?><?php echo $filtro_fecha_desde ? '&fecha_desde=' . urlencode($filtro_fecha_desde) : ''; ?><?php echo $filtro_fecha_hasta ? '&fecha_hasta=' . urlencode($filtro_fecha_hasta) : ''; ?>"
                                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Siguiente</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay asignaciones</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva asignación para tus estudiantes.</p>
                        <div class="mt-6">
                            <a href="new_assignment.php" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Nueva Asignación
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        // Inicializar Flatpickr para los campos de fecha
        flatpickr("input[type=date]", {
            locale: "es",
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "d/m/Y",
            disableMobile: true
        });

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
    </script>
</body>
</html> 