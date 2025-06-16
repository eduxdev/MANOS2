<?php
session_start();
require_once __DIR__ . '/../../backend/db/conection.php';
require_once __DIR__ . '/../../backend/ejercicios/check_badges.php';

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener datos del estudiante
$user_id = $_SESSION['user_id'];

// Obtener estadísticas de práctica
$query_practicas = "SELECT 
    COUNT(*) as total_practicas,
    SUM(CASE WHEN respuesta_correcta = 1 THEN 1 ELSE 0 END) as respuestas_correctas,
    tipo_ejercicio,
    DATE(fecha_practica) as fecha
FROM practicas_ejercicios
WHERE estudiante_id = ?
AND fecha_practica >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY tipo_ejercicio, DATE(fecha_practica)
ORDER BY fecha_practica DESC";

$stmt = mysqli_prepare($conexion, $query_practicas);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$practicas = mysqli_stmt_get_result($stmt);

// Procesar los datos para el gráfico
$practicas_por_tipo = [];
$practicas_por_fecha = [];
while ($practica = mysqli_fetch_assoc($practicas)) {
    // Agrupar por tipo
    if (!isset($practicas_por_tipo[$practica['tipo_ejercicio']])) {
        $practicas_por_tipo[$practica['tipo_ejercicio']] = [
            'total' => 0,
            'correctas' => 0
        ];
    }
    $practicas_por_tipo[$practica['tipo_ejercicio']]['total'] += $practica['total_practicas'];
    $practicas_por_tipo[$practica['tipo_ejercicio']]['correctas'] += $practica['respuestas_correctas'];

    // Agrupar por fecha
    if (!isset($practicas_por_fecha[$practica['fecha']])) {
        $practicas_por_fecha[$practica['fecha']] = 0;
    }
    $practicas_por_fecha[$practica['fecha']] += $practica['total_practicas'];
}

// Obtener progreso general actualizado
$query_progreso = "SELECT 
    (SELECT COUNT(*) FROM ejercicios) as total_ejercicios,
    (SELECT COUNT(DISTINCT ea.asignacion_id) 
     FROM estudiantes_asignaciones ea 
     JOIN asignaciones a ON ea.asignacion_id = a.id 
     WHERE ea.estudiante_id = ? AND ea.estado = 'completado') as ejercicios_completados,
    COALESCE(SUM(ea.puntos_obtenidos), 0) as puntos_totales,
    (SELECT COUNT(*) 
     FROM estudiantes_asignaciones ea2 
     WHERE ea2.estudiante_id = ? 
     AND ea2.fecha_entrega >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     AND ea2.estado = 'completado') as ejercicios_ultima_semana
FROM estudiantes_asignaciones ea
WHERE ea.estudiante_id = ?";

$stmt = mysqli_prepare($conexion, $query_progreso);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$progreso = mysqli_fetch_assoc($result);

// Obtener puntos de práctica
$query_puntos = "SELECT puntos_practica FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query_puntos);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$puntos_practica = mysqli_fetch_assoc($result)['puntos_practica'];

// Obtener ejercicios asignados pendientes
$query_asignaciones = "SELECT 
    a.id, 
    e.titulo, 
    e.descripcion,
    a.fecha_limite,
    ea.estado,
    ea.puntos_obtenidos,
    ea.evidencia_path
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
WHERE ea.estudiante_id = ?
ORDER BY ea.estado = 'pendiente' DESC, a.fecha_limite ASC";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);

// Verificar y otorgar nuevas insignias
$nuevas_insignias = checkAndAwardBadges($user_id);

// Obtener todas las insignias del estudiante con detalles
$query_insignias = "SELECT i.*, iu.fecha_obtencion 
                   FROM insignias i
                   JOIN insignias_usuarios iu ON i.id = iu.insignia_id
                   WHERE iu.usuario_id = ?
                   ORDER BY iu.fecha_obtencion DESC";

$stmt = mysqli_prepare($conexion, $query_insignias);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$insignias = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Dashboard Estudiante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <!-- Modal de subida de evidencias -->
    <div id="evidence-modal" class="modal">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800" id="modal-title"></h2>
                <button class="text-gray-400 hover:text-gray-500" onclick="closeModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-600 mb-4" id="modal-description"></p>
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm text-gray-500">Fecha límite</span>
                            <span id="modal-deadline" class="font-medium"></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-500">Estado</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pendiente
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="evidence-form" class="space-y-6">
                <input type="hidden" id="assignment-id" name="assignment_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Evidencia de realización
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="evidence-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                    <span>Subir archivo</span>
                                    <input id="evidence-upload" name="evidence" type="file" class="sr-only" accept="image/*,video/*,.pdf">
                                </label>
                                <p class="pl-1">o arrastrar y soltar</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 10MB</p>
                        </div>
                    </div>
                    <div id="file-preview" class="mt-2 hidden">
                        <div class="flex items-center space-x-2">
                            <span id="file-name" class="text-sm text-gray-500"></span>
                            <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Enviar evidencia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Bienvenida y resumen -->
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!
                </h1>
                <p class="text-gray-600">
                    Continúa aprendiendo y practicando el lenguaje de señas
                </p>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Progreso general -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Progreso General</h3>
                        <div class="text-purple-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-purple-600 mb-2">
                        <?php 
                        $porcentaje = $progreso['total_ejercicios'] > 0 
                            ? round(($progreso['ejercicios_completados'] / $progreso['total_ejercicios']) * 100) 
                            : 0;
                        echo $porcentaje . '%';
                        ?>
                    </div>
                    <p class="text-gray-600">
                        <?php echo $progreso['ejercicios_completados']; ?> de <?php echo $progreso['total_ejercicios']; ?> ejercicios completados
                    </p>
                    <div class="mt-2 h-2 bg-gray-200 rounded-full">
                        <div class="h-2 bg-purple-600 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                    </div>
                </div>

                <!-- Puntos totales -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Puntos Totales</h3>
                        <div class="text-pink-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <div class="text-3xl font-bold text-pink-600 mb-1">
                                <?php echo number_format($progreso['puntos_totales'] + $puntos_practica); ?>
                            </div>
                            <p class="text-gray-600">Puntos totales</p>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Ejercicios asignados:</span>
                                <span class="font-medium text-pink-600"><?php echo number_format($progreso['puntos_totales']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Práctica libre:</span>
                                <span class="font-medium text-pink-600"><?php echo number_format($puntos_practica); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Racha actual -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Actividad Semanal</h3>
                        <div class="text-orange-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-orange-500 mb-2">
                        <?php echo $progreso['ejercicios_ultima_semana']; ?>
                    </div>
                    <p class="text-gray-600">Ejercicios esta semana</p>
                </div>
            </div>

            <!-- Estadísticas de práctica -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Progreso de Práctica</h2>
                
                <?php if (!empty($practicas_por_tipo)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Estadísticas por tipo de ejercicio -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ejercicios por Tipo</h3>
                            <div class="space-y-4">
                                <?php foreach ($practicas_por_tipo as $tipo => $datos): ?>
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-purple-900">
                                                <?php 
                                                $nombre_tipo = [
                                                    'letterIdentification' => 'Identificación de Letras',
                                                    'wordCompletion' => 'Completar Palabras',
                                                    'signRecognition' => 'Reconocimiento de Señas',
                                                    'errorDetection' => 'Detección de Errores'
                                                ];
                                                echo $nombre_tipo[$tipo] ?? $tipo;
                                                ?>
                                            </span>
                                            <span class="text-sm text-purple-600">
                                                <?php 
                                                $porcentaje = $datos['total'] > 0 ? round(($datos['correctas'] / $datos['total']) * 100) : 0;
                                                echo "{$porcentaje}% correctas";
                                                ?>
                                            </span>
                                        </div>
                                        <div class="w-full bg-purple-200 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" 
                                                 style="width: <?php echo $porcentaje; ?>%">
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-purple-600">
                                            <?php echo "{$datos['correctas']} de {$datos['total']} ejercicios"; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Actividad reciente -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Actividad Últimos 7 Días</h3>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <div class="space-y-3">
                                    <?php 
                                    $total_semana = 0;
                                    foreach ($practicas_por_fecha as $fecha => $total): 
                                        $total_semana += $total;
                                        $fecha_formateada = date('d/m/Y', strtotime($fecha));
                                    ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-purple-900"><?php echo $fecha_formateada; ?></span>
                                            <span class="text-sm font-medium text-purple-600">
                                                <?php echo $total; ?> ejercicios
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="pt-3 border-t border-purple-200">
                                        <div class="flex justify-between items-center font-medium">
                                            <span class="text-purple-900">Total de la semana</span>
                                            <span class="text-purple-600"><?php echo $total_semana; ?> ejercicios</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        <p>Aún no has realizado ejercicios de práctica.</p>
                        <a href="/frontend/aprender.php" 
                           class="inline-block mt-4 px-6 py-3 bg-purple-600 text-white rounded-full hover:bg-purple-700 transition-colors">
                            Comenzar a practicar
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ejercicios asignados -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Ejercicios Asignados</h2>
                
                <?php if (mysqli_num_rows($asignaciones) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($asignacion = mysqli_fetch_assoc($asignaciones)): ?>
                            <div class="bg-purple-50 rounded-lg p-6 transform transition-all duration-300 hover:scale-105">
                                <h3 class="text-lg font-semibold text-purple-900 mb-2">
                                    <?php echo htmlspecialchars($asignacion['titulo']); ?>
                                </h3>
                                <p class="text-gray-600 mb-4 text-sm">
                                    <?php echo htmlspecialchars($asignacion['descripcion']); ?>
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-600">
                                        Fecha límite: <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                                    </span>
                                    <?php if ($asignacion['estado'] === 'pendiente'): ?>
                                        <button onclick="openEvidenceModal(<?php echo htmlspecialchars(json_encode($asignacion)); ?>)" 
                                               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition-colors">
                                            Subir evidencia
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <div class="flex flex-col items-end">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">
                                                Completado - <?php echo $asignacion['puntos_obtenidos']; ?> puntos
                                            </span>
                                            <?php if ($asignacion['evidencia_path']): ?>
                                                <a href="/<?php echo htmlspecialchars($asignacion['evidencia_path']); ?>" 
                                                   target="_blank"
                                                   class="text-sm text-purple-600 hover:text-purple-800">
                                                    Ver evidencia
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        <p>No tienes ejercicios asignados en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Insignias -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Tus Insignias</h2>
                    <span class="text-sm text-gray-600">
                        <?php echo mysqli_num_rows($insignias); ?> insignias obtenidas
                    </span>
                </div>
                
                <?php if (!empty($nuevas_insignias)): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-800 mb-2">¡Nuevas insignias desbloqueadas!</h3>
                        <ul class="list-disc list-inside text-green-700">
                            <?php foreach ($nuevas_insignias as $insignia): ?>
                                <li><?php echo htmlspecialchars($insignia); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (mysqli_num_rows($insignias) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                        <?php while ($insignia = mysqli_fetch_assoc($insignias)): ?>
                            <div class="text-center transform transition-all duration-300 hover:scale-110">
                                <div class="relative">
                                    <div class="w-20 h-20 mx-auto mb-2 float-animation">
                                        <img src="<?php echo htmlspecialchars($insignia['imagen_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($insignia['nombre']); ?>"
                                             class="w-full h-full object-contain">
                                    </div>
                                    <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                                        <span class="sr-only">Fecha de obtención</span>
                                        <?php echo date('d/m', strtotime($insignia['fecha_obtencion'])); ?>
                                    </div>
                                </div>
                                <h4 class="text-sm font-medium text-gray-800 mb-1">
                                    <?php echo htmlspecialchars($insignia['nombre']); ?>
                                </h4>
                                <p class="text-xs text-gray-600">
                                    <?php echo htmlspecialchars($insignia['descripcion']); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">¡Aún no tienes insignias!</h3>
                        <p class="text-gray-600">
                            Completa ejercicios y gana puntos para desbloquear insignias.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        const modal = document.getElementById('evidence-modal');
        const fileInput = document.getElementById('evidence-upload');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const removeFile = document.getElementById('remove-file');
        const evidenceForm = document.getElementById('evidence-form');
        let evidenceFile = null;

        function openEvidenceModal(asignacion) {
            document.getElementById('modal-title').textContent = asignacion.titulo;
            document.getElementById('modal-description').textContent = asignacion.descripcion;
            document.getElementById('modal-deadline').textContent = new Date(asignacion.fecha_limite).toLocaleDateString();
            document.getElementById('assignment-id').value = asignacion.id;
            modal.classList.add('show');
        }

        function closeModal() {
            modal.classList.remove('show');
            evidenceFile = null;
            fileInput.value = '';
            filePreview.classList.add('hidden');
        }

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    showMessageModal('message-modal', 'Error', 'El archivo es demasiado grande. Por favor, selecciona un archivo menor a 10MB.');
                    fileInput.value = '';
                    return;
                }
                evidenceFile = file;
                fileName.textContent = file.name;
                filePreview.classList.remove('hidden');
            }
        });

        removeFile.addEventListener('click', () => {
            evidenceFile = null;
            fileInput.value = '';
            filePreview.classList.add('hidden');
        });

        evidenceForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!evidenceFile) {
                showMessageModal('message-modal', 'Error', 'Por favor, sube una evidencia antes de enviar.');
                return;
            }

            const formData = new FormData();
            formData.append('evidence', evidenceFile);
            formData.append('assignment_id', document.getElementById('assignment-id').value);

            try {
                const response = await fetch('/backend/ejercicios/save_evidence.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    showMessageModal('message-modal', 'Éxito', 'Evidencia enviada correctamente', 'location.reload()');
                    closeModal();
                } else {
                    showMessageModal('message-modal', 'Error', 'Error al enviar la evidencia: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessageModal('message-modal', 'Error', 'Error al enviar la evidencia');
            }
        });
    </script>
</body>
</html> 