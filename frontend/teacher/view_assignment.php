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
<body class="bg-gray-900 min-h-screen flex flex-col">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <main class="flex-grow pt-32 pb-24 min-h-screen">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Encabezado -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="dashboard.php" class="text-purple-400 hover:text-purple-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-white">
                        <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                    </h1>
                </div>
                <p class="text-gray-300">
                    <?php echo htmlspecialchars($asignacion['ejercicio_descripcion']); ?>
                </p>
            </div>

            <!-- Información de la asignación -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-6 mb-8 border border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-400">Fecha de Asignación</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-100">
                            <?php echo date('d/m/Y', strtotime($asignacion['fecha_asignacion'])); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-400">Fecha Límite</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-100">
                            <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-400">Grupo Asignado</h3>
                        <p class="mt-1 text-lg font-semibold text-gray-100">
                            Grupo <?php echo htmlspecialchars($asignacion['grupo_asignado']); ?>
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-400">Progreso General</h3>
                        <div class="mt-2 flex items-center">
                            <div class="flex-1 bg-gray-700 rounded-full h-2.5">
                                <?php 
                                $porcentaje = $total_estudiantes > 0 
                                    ? ($estudiantes_completados / $total_estudiantes) * 100 
                                    : 0;
                                ?>
                                <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-200">
                                <?php echo $estudiantes_completados; ?>/<?php echo $total_estudiantes; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de estudiantes -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg overflow-hidden border border-gray-700">
                <div class="p-6 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-100">
                        Progreso de Estudiantes - Grupo <?php echo htmlspecialchars($asignacion['grupo_asignado']); ?>
                    </h2>
                </div>

                <?php if (empty($estudiantes_data)): ?>
                    <div class="text-center text-gray-400 py-8">
                        <p>No hay estudiantes en este grupo</p>
                    </div>
                <?php else: ?>
                    <!-- Vista móvil -->
                    <div class="md:hidden">
                        <?php foreach ($estudiantes_data as $estudiante): ?>
                            <div class="border-b border-gray-700 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-100">
                                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                                        </h4>
                                        <div class="mt-2 space-y-2">
                                            <div class="flex items-center">
                                                <?php if ($estudiante['estado'] === 'completado'): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-300 border border-green-700">
                                                        Completado
                                                    </span>
                                                <?php elseif ($estudiante['estado'] === 'pendiente'): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900/50 text-yellow-300 border border-yellow-700">
                                                        Pendiente
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-800/50 text-gray-300 border border-gray-600">
                                                        Sin iniciar
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($estudiante['fecha_entrega']): ?>
                                                <div class="text-xs text-gray-400">
                                                    Entregado: <?php echo date('d/m/y H:i', strtotime($estudiante['fecha_entrega'])); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($estudiante['puntos_obtenidos']): ?>
                                                <div class="text-xs text-purple-400">
                                                    Puntos: <?php echo $estudiante['puntos_obtenidos']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($estudiante['evidencia_path']): ?>
                                        <div class="flex flex-col gap-2 items-end">
                                            <a href="/<?php echo htmlspecialchars($estudiante['evidencia_path']); ?>" 
                                               target="_blank"
                                               class="text-purple-400 hover:text-purple-300 transition-colors text-xs">
                                                Ver evidencia
                                            </a>
                                            <?php if ($estudiante['estado'] === 'completado'): ?>
                                                <button onclick="invalidateSubmission(<?php echo $asignacion_id; ?>, <?php echo $estudiante['id']; ?>)"
                                                        class="text-red-400 hover:text-red-300 transition-colors text-xs">
                                                    Invalidar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Vista desktop -->
                    <div class="hidden md:block overflow-x-auto">
                        <div class="inline-block min-w-full align-middle">
                            <div class="overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-700">
                                    <thead class="bg-gray-900/50">
                                        <tr>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                                Estudiante
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                                Fecha de Entrega
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                                Puntos
                                            </th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                                Evidencia
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-gray-900/30 divide-y divide-gray-700">
                                        <?php foreach ($estudiantes_data as $estudiante): ?>
                                            <tr class="hover:bg-gray-700/50">
                                                <td class="px-3 py-4 whitespace-normal">
                                                    <div class="text-sm font-medium text-gray-100">
                                                        <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellidos']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <?php if ($estudiante['estado'] === 'completado'): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-300 border border-green-700">
                                                            Completado
                                                        </span>
                                                    <?php elseif ($estudiante['estado'] === 'pendiente'): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900/50 text-yellow-300 border border-yellow-700">
                                                            Pendiente
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-800/50 text-gray-300 border border-gray-600">
                                                            Sin iniciar
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-300">
                                                        <?php echo $estudiante['fecha_entrega'] 
                                                            ? date('d/m/y H:i', strtotime($estudiante['fecha_entrega']))
                                                            : '-'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-300">
                                                        <?php echo $estudiante['puntos_obtenidos'] ?? '-'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <?php if ($estudiante['evidencia_path']): ?>
                                                        <div class="flex items-center gap-3">
                                                            <a href="/<?php echo htmlspecialchars($estudiante['evidencia_path']); ?>" 
                                                               target="_blank"
                                                               class="text-purple-400 hover:text-purple-300 transition-colors text-sm">
                                                                Ver
                                                            </a>
                                                            <?php if ($estudiante['estado'] === 'completado'): ?>
                                                                <button onclick="invalidateSubmission(<?php echo $asignacion_id; ?>, <?php echo $estudiante['id']; ?>)"
                                                                        class="text-red-400 hover:text-red-300 transition-colors text-sm">
                                                                    Invalidar
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-gray-600">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="mt-auto">
        <?php include '../footer.php'; ?>
    </footer>

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
    </script>
</body>
</html> 