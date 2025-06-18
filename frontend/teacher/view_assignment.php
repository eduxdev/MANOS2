<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

// Verificar si se proporcionó un ID de asignación
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$asignacion_id = $_GET['id'];
$profesor_id = $_SESSION['user_id'];

// Obtener detalles de la asignación
$query_asignacion = "SELECT 
    a.*,
    e.titulo as ejercicio_titulo,
    e.descripcion as ejercicio_descripcion,
    a.grupo_asignado
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
WHERE a.id = ? AND a.profesor_id = ?";

$stmt = mysqli_prepare($conexion, $query_asignacion);
mysqli_stmt_bind_param($stmt, "ii", $asignacion_id, $profesor_id);
mysqli_stmt_execute($stmt);
$asignacion = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$asignacion) {
    header("Location: dashboard.php");
    exit();
}

// Obtener progreso de los estudiantes del grupo asignado
$query_estudiantes = "SELECT 
    u.id,
    u.nombre,
    u.apellidos,
    u.grupo,
    ea.estado,
    ea.fecha_entrega,
    ea.puntos_obtenidos,
    ea.evidencia_path
FROM usuarios u
LEFT JOIN estudiantes_asignaciones ea ON u.id = ea.estudiante_id AND ea.asignacion_id = ?
WHERE u.rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
AND u.grupo = ?
ORDER BY u.apellidos, u.nombre";

$stmt = mysqli_prepare($conexion, $query_estudiantes);
mysqli_stmt_bind_param($stmt, "is", $asignacion_id, $asignacion['grupo_asignado']);
mysqli_stmt_execute($stmt);
$estudiantes = mysqli_stmt_get_result($stmt);

// Calcular estadísticas
$total_estudiantes = mysqli_num_rows($estudiantes);
$estudiantes_completados = 0;
$puntos_totales = 0;
$estudiantes_data = [];

while ($estudiante = mysqli_fetch_assoc($estudiantes)) {
    $estudiantes_data[] = $estudiante;
    if ($estudiante['estado'] === 'completado') {
        $estudiantes_completados++;
        $puntos_totales += $estudiante['puntos_obtenidos'];
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Asignación - Talk Hands</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-full">
    <?php include '../header.php'; ?>

    <main class="flex-grow pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Encabezado -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="dashboard.php" class="text-purple-600 hover:text-purple-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                    </h1>
                </div>
                <p class="text-gray-600">
                    <?php echo htmlspecialchars($asignacion['ejercicio_descripcion']); ?>
                </p>
            </div>

            <!-- Información de la asignación -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Fecha de Asignación</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            <?php echo date('d/m/Y', strtotime($asignacion['fecha_asignacion'])); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Fecha Límite</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Grupo Asignado</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            Grupo <?php echo htmlspecialchars($asignacion['grupo_asignado']); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Progreso General</h3>
                        <div class="mt-2 flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <?php 
                                $porcentaje = $total_estudiantes > 0 
                                    ? ($estudiantes_completados / $total_estudiantes) * 100 
                                    : 0;
                                ?>
                                <div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                <?php echo $estudiantes_completados; ?>/<?php echo $total_estudiantes; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de estudiantes -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Progreso de Estudiantes - Grupo <?php echo htmlspecialchars($asignacion['grupo_asignado']); ?>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estudiante
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha de Entrega
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Puntos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Evidencia
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($estudiantes_data)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No hay estudiantes en este grupo
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($estudiantes_data as $estudiante): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($estudiante['estado'] === 'completado'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completado
                                                </span>
                                            <?php elseif ($estudiante['estado'] === 'pendiente'): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pendiente
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Sin iniciar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo $estudiante['fecha_entrega'] 
                                                    ? date('d/m/Y H:i', strtotime($estudiante['fecha_entrega']))
                                                    : '-'; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo $estudiante['puntos_obtenidos'] ?? '-'; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($estudiante['evidencia_path']): ?>
                                                <a href="/<?php echo htmlspecialchars($estudiante['evidencia_path']); ?>" 
                                                   target="_blank"
                                                   class="text-purple-600 hover:text-purple-900">
                                                    Ver evidencia
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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