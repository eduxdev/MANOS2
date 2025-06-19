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
    (
        SELECT COUNT(*) + (
            SELECT COUNT(DISTINCT DATE(p.fecha_practica))
            FROM practicas_ejercicios p
            WHERE p.estudiante_id = ?
            AND p.fecha_practica >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        )
     FROM estudiantes_asignaciones ea2 
     WHERE ea2.estudiante_id = ? 
     AND ea2.fecha_entrega >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND ea2.estado = 'completado'
    ) as ejercicios_ultima_semana
FROM estudiantes_asignaciones ea
WHERE ea.estudiante_id = ?";

$stmt = mysqli_prepare($conexion, $query_progreso);
mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $user_id);
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
    ea.evidencia_path,
    ea.fue_invalidada
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
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }
        .modal.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        .file-drop-zone {
            border: 2px dashed rgba(139, 92, 246, 0.5);
            transition: all 0.3s ease;
        }
        .file-drop-zone:hover {
            border-color: rgba(139, 92, 246, 0.8);
            background-color: rgba(139, 92, 246, 0.1);
        }
    </style>
</head>
<body class="bg-gray-900">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <!-- Modal de subida de evidencias -->
    <div id="evidence-modal" class="modal">
        <div class="modal-content bg-gray-800 rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all border border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white" id="modal-title"></h2>
                <button class="text-gray-400 hover:text-gray-300 transition-colors" onclick="closeModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-300 mb-4" id="modal-description"></p>
                <div class="bg-gray-900/50 rounded-lg p-4 mb-4 backdrop-blur-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm text-gray-400">Fecha límite</span>
                            <span id="modal-deadline" class="font-medium text-gray-200"></span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-400">Estado</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900/50 text-yellow-300">
                                Pendiente
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="evidence-form" class="space-y-6">
                <input type="hidden" id="assignment-id" name="assignment_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-200 mb-2">
                        Evidencia de realización
                    </label>
                    <div class="file-drop-zone mt-1 flex justify-center px-6 pt-5 pb-6 rounded-lg bg-gray-800/50 hover:bg-gray-700/50 transition-all duration-300">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-400">
                                <label for="evidence-upload" class="relative cursor-pointer bg-gray-700 rounded-md font-medium text-purple-400 hover:text-purple-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500 px-3 py-2">
                                    <span>Subir archivo</span>
                                    <input id="evidence-upload" name="evidence" type="file" class="sr-only" accept="image/*,video/*,.pdf">
                                </label>
                                <p class="pl-1 self-center">o arrastrar y soltar</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 10MB</p>
                        </div>
                    </div>
                    <div id="file-preview" class="mt-2 hidden">
                        <div class="flex items-center space-x-2 bg-gray-900/50 rounded-lg p-3">
                            <span id="file-name" class="text-sm text-gray-300"></span>
                            <button type="button" id="remove-file" class="text-red-400 hover:text-red-300 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-300">
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
                <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    ¡Bienvenid@, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!
                </h1>
                <p class="text-gray-300">
                    Continúa aprendiendo y practicando el lenguaje de señas
                </p>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Progreso general -->
                <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-100">Progreso General</h3>
                        <div class="text-purple-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-purple-400 mb-2">
                        <?php 
                        $porcentaje = $progreso['total_ejercicios'] > 0 
                            ? round(($progreso['ejercicios_completados'] / $progreso['total_ejercicios']) * 100) 
                            : 0;
                        echo $porcentaje . '%';
                        ?>
                    </div>
                    <p class="text-gray-300">
                        <?php echo $progreso['ejercicios_completados']; ?> de <?php echo $progreso['total_ejercicios']; ?> ejercicios completados
                    </p>
                    <div class="mt-2 h-2 bg-gray-700 rounded-full">
                        <div class="h-2 bg-purple-600 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                    </div>
                </div>

                <!-- Puntos totales -->
                <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-100">Puntos Totales</h3>
                        <div class="text-pink-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <div class="text-3xl font-bold text-pink-400 mb-1">
                                <?php echo number_format($progreso['puntos_totales'] + $puntos_practica); ?>
                            </div>
                            <p class="text-gray-300">Puntos totales</p>
                        </div>
                        <div class="border-t border-gray-700 pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Ejercicios asignados:</span>
                                <span class="font-medium text-pink-400"><?php echo number_format($progreso['puntos_totales']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-300">Práctica libre:</span>
                                <span class="font-medium text-pink-400"><?php echo number_format($puntos_practica); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Racha actual -->
                <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-100">Actividad Semanal</h3>
                        <div class="text-orange-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                    </div>
                    <?php
                    // Consulta para ejercicios asignados de la semana
                    $query_asignaciones_semana = "SELECT COUNT(*) as total 
                        FROM estudiantes_asignaciones 
                        WHERE estudiante_id = ? 
                        AND fecha_entrega >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        AND estado = 'completado'";
                    $stmt = mysqli_prepare($conexion, $query_asignaciones_semana);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $asignaciones_semana = mysqli_fetch_assoc($result)['total'];

                    // Consulta para días con prácticas libres
                    $query_practicas_semana = "SELECT COUNT(DISTINCT DATE(fecha_practica)) as dias_practica,
                        COUNT(*) as total_practicas,
                        SUM(CASE WHEN respuesta_correcta = 1 THEN 1 ELSE 0 END) as practicas_correctas
                        FROM practicas_ejercicios 
                        WHERE estudiante_id = ? 
                        AND fecha_practica >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    $stmt = mysqli_prepare($conexion, $query_practicas_semana);
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $practicas_data = mysqli_fetch_assoc($result);
                    $dias_practica = $practicas_data['dias_practica'];
                    $total_practicas = $practicas_data['total_practicas'];
                    $practicas_correctas = $practicas_data['practicas_correctas'];

                    // Calcular el total de actividades
                    $total_actividades = $asignaciones_semana + $total_practicas;
                    ?>
                    <div class="text-3xl font-bold text-orange-400 mb-2">
                        <?php echo $total_actividades; ?>
                    </div>
                    <p class="text-gray-300 mb-2">Actividades esta semana</p>
                    
                    <!-- Desglose de actividades -->
                    <div class="mt-4 space-y-3">
                        <!-- Ejercicios asignados -->
                        <div class="bg-gray-900/50 rounded-lg p-3">
                            <div class="flex justify-between items-center text-gray-300">
                                <span class="font-medium">Ejercicios asignados:</span>
                                <span class="text-orange-400 font-bold"><?php echo $asignaciones_semana; ?></span>
                            </div>
                        </div>

                        <!-- Prácticas libres -->
                        <div class="bg-gray-900/50 rounded-lg p-3">
                            <div class="flex justify-between items-center text-gray-300 mb-2">
                                <span class="font-medium">Prácticas libres:</span>
                                <span class="text-purple-400 font-bold"><?php echo $total_practicas; ?></span>
                            </div>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between items-center text-gray-400">
                                    <span>Días activos:</span>
                                    <span class="text-purple-400"><?php echo $dias_practica; ?></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-400">
                                    <span>Aciertos:</span>
                                    <span class="text-purple-400">
                                        <?php 
                                        if ($total_practicas > 0) {
                                            $porcentaje = round(($practicas_correctas / $total_practicas) * 100);
                                            echo $practicas_correctas . ' (' . $porcentaje . '%)';
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de progreso semanal -->
                        <div class="pt-2">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span>Progreso semanal</span>
                                <span><?php echo $total_actividades; ?> actividades</span>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <?php if ($total_actividades > 0): ?>
                                    <?php
                                    $porcentaje_asignaciones = ($asignaciones_semana / $total_actividades) * 100;
                                    $porcentaje_practicas = ($total_practicas / $total_actividades) * 100;
                                    ?>
                                    <div class="flex h-full">
                                        <div class="bg-orange-500 h-full" style="width: <?php echo $porcentaje_asignaciones; ?>%"></div>
                                        <div class="bg-purple-500 h-full" style="width: <?php echo $porcentaje_practicas; ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de práctica -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-8 mb-12 border border-gray-700">
                <h2 class="text-2xl font-bold text-gray-100 mb-6">Progreso de Práctica</h2>
                
                <?php if (!empty($practicas_por_tipo)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Estadísticas por tipo de ejercicio -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-200 mb-4">Ejercicios por Tipo</h3>
                            <div class="space-y-4">
                                <?php foreach ($practicas_por_tipo as $tipo => $datos): ?>
                                    <div class="bg-gray-900/50 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-medium text-gray-200">
                                                <?php 
                                                $nombre_tipo = [
                                                    'letterIdentification' => 'Identificación de Letras',
                                                    'wordCompletion' => 'Completar Palabras',
                                                    'signRecognition' => 'Reconocimiento de Señas',
                                                    'errorDetection' => 'Detección de Errores',
                                                    'sequenceExercise' => 'Secuencia de Señas',
                                                    'memoryExercise' => 'Memorama de Señas'
                                                ];
                                                echo $nombre_tipo[$tipo] ?? $tipo;
                                                ?>
                                            </span>
                                            <span class="text-sm text-purple-400">
                                                <?php 
                                                $porcentaje = $datos['total'] > 0 ? round(($datos['correctas'] / $datos['total']) * 100) : 0;
                                                echo "{$porcentaje}% correctas";
                                                ?>
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-700 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" 
                                                 style="width: <?php echo $porcentaje; ?>%">
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-purple-400">
                                            <?php echo "{$datos['correctas']} de {$datos['total']} ejercicios"; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Actividad reciente -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-200 mb-4">Actividad Últimos 7 Días</h3>
                            <div class="bg-gray-900/50 rounded-lg p-4">
                                <div class="space-y-3">
                                    <?php 
                                    $total_semana = 0;
                                    foreach ($practicas_por_fecha as $fecha => $total): 
                                        $total_semana += $total;
                                        $fecha_formateada = date('d/m/Y', strtotime($fecha));
                                    ?>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-200"><?php echo $fecha_formateada; ?></span>
                                            <span class="text-sm font-medium text-purple-400">
                                                <?php echo $total; ?> ejercicios
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="pt-3 border-t border-gray-700">
                                        <div class="flex justify-between items-center font-medium">
                                            <span class="text-gray-200">Total de la semana</span>
                                            <span class="text-purple-400"><?php echo $total_semana; ?> ejercicios</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-400 py-8">
                        <p>Aún no has realizado ejercicios de práctica.</p>
                        <a href="/frontend/aprender.php" 
                           class="inline-block mt-4 px-6 py-3 bg-purple-600 text-white rounded-full hover:bg-purple-700 transition-colors">
                            Comenzar a practicar
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ejercicios asignados -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-8 mb-12 border border-gray-700">
                <h2 class="text-2xl font-bold text-gray-100 mb-6">Ejercicios Asignados</h2>
                
                <?php if (mysqli_num_rows($asignaciones) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($asignacion = mysqli_fetch_assoc($asignaciones)): ?>
                            <div class="bg-gray-900/50 rounded-lg p-6 transform transition-all duration-300 hover:scale-105 border border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-200 mb-2">
                                    <?php echo htmlspecialchars($asignacion['titulo']); ?>
                                </h3>
                                <p class="text-gray-400 mb-4 text-sm">
                                    <?php echo htmlspecialchars($asignacion['descripcion']); ?>
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-400">
                                        Fecha límite: <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                                    </span>
                                    <?php if ($asignacion['estado'] === 'pendiente'): ?>
                                        <?php if ($asignacion['fue_invalidada']): ?>
                                            <div class="mb-2">
                                                <span class="inline-flex items-center text-[11px] text-red-300">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    Entrega invalidada
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <button onclick="openEvidenceModal(<?php echo htmlspecialchars(json_encode($asignacion)); ?>)" 
                                               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition-colors">
                                            Subir evidencia
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <div class="flex flex-col items-end">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900/50 text-green-300 mb-2">
                                                Completado - <?php echo $asignacion['puntos_obtenidos']; ?> puntos
                                            </span>
                                            <?php if ($asignacion['evidencia_path']): ?>
                                                <a href="/<?php echo htmlspecialchars($asignacion['evidencia_path']); ?>" 
                                                   target="_blank"
                                                   class="text-sm text-purple-400 hover:text-purple-300">
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
                    <div class="text-center text-gray-400 py-8">
                        <p>No tienes ejercicios asignados en este momento.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Insignias -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-8 border border-gray-700">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-100">Tus Insignias</h2>
                    <span class="text-sm text-gray-300">
                        <?php echo mysqli_num_rows($insignias); ?> insignias obtenidas
                    </span>
                </div>
                
                <?php if (!empty($nuevas_insignias)): ?>
                    <div class="mb-6 bg-green-900/20 border border-green-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-300 mb-2">¡Nuevas insignias desbloqueadas!</h3>
                        <ul class="list-disc list-inside text-green-400">
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
                                <h4 class="text-sm font-medium text-gray-200 mb-1">
                                    <?php echo htmlspecialchars($insignia['nombre']); ?>
                                </h4>
                                <p class="text-xs text-gray-400">
                                    <?php echo htmlspecialchars($insignia['descripcion']); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="w-24 h-24 mx-auto mb-4 text-gray-500">
                            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">¡Aún no tienes insignias!</h3>
                        <p class="text-gray-400">
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
        const dropZone = document.querySelector('.file-drop-zone');
        let evidenceFile = null;

        function openEvidenceModal(asignacion) {
            document.getElementById('modal-title').textContent = asignacion.titulo;
            document.getElementById('modal-description').textContent = asignacion.descripcion;
            document.getElementById('modal-deadline').textContent = new Date(asignacion.fecha_limite).toLocaleDateString();
            document.getElementById('assignment-id').value = asignacion.id;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('show');
            evidenceFile = null;
            fileInput.value = '';
            filePreview.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Prevenir que el navegador abra los archivos por defecto
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-purple-500', 'bg-purple-900/20');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-purple-500', 'bg-purple-900/20');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-purple-500', 'bg-purple-900/20');
            
            const file = e.dataTransfer.files[0];
            handleFile(file);
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            handleFile(file);
        });

        function handleFile(file) {
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    showMessageModal('message-modal', 'Error', 'El archivo es demasiado grande. Por favor, selecciona un archivo menor a 10MB.');
                    fileInput.value = '';
                    return;
                }

                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'application/pdf'];
                if (!validTypes.includes(file.type)) {
                    showMessageModal('message-modal', 'Error', 'Tipo de archivo no válido. Por favor, sube una imagen, video o PDF.');
                    fileInput.value = '';
                    return;
                }

                evidenceFile = file;
                fileName.textContent = file.name;
                filePreview.classList.remove('hidden');
            }
        }

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
            
            // Agregar los datos adicionales requeridos
            const data = {
                assignment_id: document.getElementById('assignment-id').value,
                timestamp: new Date().toISOString()
            };
            formData.append('data', JSON.stringify(data));

            try {
                const response = await fetch('/backend/ejercicios/save_evidence.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showMessageModal('message-modal', 'Éxito', 'Evidencia enviada correctamente');
                    closeModal();
                    // Recargar la página después de un breve retraso
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    let errorMessage = 'Error al enviar la evidencia';
                    if (result.error) {
                        errorMessage += ': ' + result.error;
                    }
                    showMessageModal('message-modal', 'Error', errorMessage);
                }
            } catch (error) {
                console.error('Error:', error);
                showMessageModal('message-modal', 'Error', 'Error al enviar la evidencia: ' + error.message);
            }
        });

        // Cerrar modal con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeModal();
            }
        });

        // Cerrar modal al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html> 