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
$query_stats = "SELECT 
    COUNT(DISTINCT u.id) as total_estudiantes,
    COUNT(DISTINCT a.id) as total_asignaciones,
    COUNT(DISTINCT CASE WHEN ea.estado = 'completada' THEN ea.id END) as asignaciones_completadas
FROM usuarios u
LEFT JOIN estudiantes_asignaciones ea ON u.id = ea.estudiante_id
LEFT JOIN asignaciones a ON ea.asignacion_id = a.id
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
AND (a.profesor_id = ? OR a.profesor_id IS NULL)";

$stmt = mysqli_prepare($conexion, $query_stats);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Obtener últimas asignaciones
$query_asignaciones = "SELECT 
    a.id,
    a.fecha_asignacion,
    a.fecha_limite,
    e.titulo as ejercicio_titulo,
    COUNT(DISTINCT ea.estudiante_id) as total_estudiantes,
    COUNT(DISTINCT CASE WHEN ea.estado = 'completada' THEN ea.estudiante_id END) as estudiantes_completados
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
LEFT JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
WHERE a.profesor_id = ?
GROUP BY a.id
ORDER BY a.fecha_asignacion DESC
LIMIT 5";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
mysqli_stmt_bind_param($stmt, "i", $profesor_id);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);
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

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Bienvenida y acciones rápidas -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-12">
                <div>
                    <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        Panel del Profesor
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
                        <?php echo $stats['total_estudiantes']; ?>
                    </div>
                    <p class="text-gray-600">Estudiantes activos</p>
                </div>

                <!-- Total de asignaciones -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Asignaciones</h3>
                        <div class="text-pink-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-pink-600 mb-2">
                        <?php echo $stats['total_asignaciones']; ?>
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
                        $tasa_completado = $stats['total_asignaciones'] > 0 
                            ? round(($stats['asignaciones_completadas'] / $stats['total_asignaciones']) * 100) 
                            : 0;
                        echo $tasa_completado . '%';
                        ?>
                    </div>
                    <p class="text-gray-600">Promedio de completado</p>
                </div>
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
                                                    <?php echo round($porcentaje); ?>%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="view_assignment.php?id=<?php echo $asignacion['id']; ?>" 
                                               class="text-purple-600 hover:text-purple-900">
                                                Ver detalles
                                            </a>
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
                                    <span class="text-gray-600">Ejercicios completados:</span>
                                    <span class="font-medium">${estudiante.ejercicios_completados}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Puntos totales:</span>
                                    <span class="font-medium">${estudiante.puntos_totales}</span>
                                </div>
                            </div>
                            <a href="student_progress.php?id=${estudiante.id}" 
                               class="mt-4 inline-block text-sm text-purple-600 hover:text-purple-700 font-medium">
                                Ver progreso detallado →
                            </a>
                        `;
                        listaEstudiantes.appendChild(card);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    listaEstudiantes.innerHTML = '<p class="text-center text-red-500 col-span-3">Error al cargar los estudiantes</p>';
                });
        });
    </script>
</body>
</html> 