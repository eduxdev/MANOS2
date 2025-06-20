<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: /frontend/auth/login.php");
    exit();
}

// Obtener ID del ejercicio
$asignacion_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

if (!$asignacion_id) {
    $_SESSION['error'] = "Ejercicio no válido.";
    header("Location: /frontend/student/dashboard.php");
    exit();
}

// Obtener información de la asignación y el ejercicio
$query = "SELECT 
    a.*, 
    e.titulo as ejercicio_titulo,
    e.descripcion as ejercicio_descripcion,
    e.nivel as ejercicio_nivel,
    c.nombre as categoria_nombre,
    ea.estado as estado_estudiante,
    ea.intentos_realizados,
    ea.puntos_obtenidos,
    ea.id as estudiante_asignacion_id
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
JOIN categorias_ejercicios c ON e.categoria_id = c.id
JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
WHERE a.id = ? AND ea.estudiante_id = ?";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "ii", $asignacion_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$asignacion = mysqli_fetch_assoc($result);

if (!$asignacion) {
    $_SESSION['error'] = "No tienes acceso a este ejercicio.";
    header("Location: /frontend/student/dashboard.php");
    exit();
}

// Verificar si la asignación está activa y dentro del plazo
$hoy = new DateTime();
$fecha_limite = new DateTime($asignacion['fecha_limite']);
$fecha_inicio = new DateTime($asignacion['fecha_asignacion']);

if ($hoy < $fecha_inicio) {
    $_SESSION['error'] = "Este ejercicio aún no está disponible.";
    header("Location: /frontend/student/dashboard.php");
    exit();
}

if ($hoy > $fecha_limite) {
    $_SESSION['error'] = "Este ejercicio ya ha vencido.";
    header("Location: /frontend/student/dashboard.php");
    exit();
}

if ($asignacion['intentos_realizados'] >= $asignacion['intentos_maximos']) {
    $_SESSION['error'] = "Has alcanzado el número máximo de intentos.";
    header("Location: /frontend/student/dashboard.php");
    exit();
}

// Obtener el último resultado si existe
$query_ultimo_resultado = "SELECT * FROM resultados_ejercicios 
                         WHERE estudiante_asignacion_id = ? 
                         ORDER BY fecha_realizacion DESC LIMIT 1";
$stmt = mysqli_prepare($conexion, $query_ultimo_resultado);
mysqli_stmt_bind_param($stmt, "i", $asignacion['estudiante_asignacion_id']);
mysqli_stmt_execute($stmt);
$ultimo_resultado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
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
<body class="bg-gray-900 flex flex-col min-h-full">
    <?php include '../header.php'; ?>

    <main class="flex-grow pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Encabezado del ejercicio -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                </h1>
                <div class="flex flex-wrap gap-4 text-sm">
                    <span class="px-3 py-1 bg-purple-900/50 text-purple-300 rounded-full border border-purple-700">
                        <?php echo htmlspecialchars($asignacion['categoria_nombre']); ?>
                    </span>
                    <span class="px-3 py-1 bg-pink-900/50 text-pink-300 rounded-full border border-pink-700">
                        Nivel <?php echo htmlspecialchars($asignacion['ejercicio_nivel']); ?>
                    </span>
                    <span class="px-3 py-1 bg-blue-900/50 text-blue-300 rounded-full border border-blue-700">
                        <?php echo $asignacion['puntos_maximos']; ?> puntos
                    </span>
                </div>
            </div>

            <!-- Información del ejercicio -->
            <div class="bg-gray-800/80 backdrop-blur-sm rounded-xl shadow-lg p-8 mb-8 border border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-100 mb-2">Detalles</h3>
                        <ul class="space-y-2 text-gray-300">
                            <li>
                                <span class="font-medium text-gray-200">Fecha límite:</span>
                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                            </li>
                            <li>
                                <span class="font-medium text-gray-200">Intentos restantes:</span>
                                <?php echo $asignacion['intentos_maximos'] - $asignacion['intentos_realizados']; ?>
                            </li>
                            <li>
                                <span class="font-medium text-gray-200">Mejor puntuación:</span>
                                <?php echo $asignacion['puntos_obtenidos']; ?> / <?php echo $asignacion['puntos_maximos']; ?>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-100 mb-2">Instrucciones</h3>
                        <p class="text-gray-300">
                            <?php echo nl2br(htmlspecialchars($asignacion['instrucciones'])); ?>
                        </p>
                    </div>
                </div>

                <?php if ($ultimo_resultado): ?>
                <div class="border-t border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-100 mb-2">Último intento</h3>
                    <div class="bg-gray-900/50 rounded-lg p-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-gray-400">Fecha</span>
                                <span class="font-medium text-gray-200">
                                    <?php echo date('d/m/Y H:i', strtotime($ultimo_resultado['fecha_realizacion'])); ?>
                                </span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Puntos obtenidos</span>
                                <span class="font-medium text-gray-200"><?php echo $ultimo_resultado['puntos_obtenidos']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Tiempo empleado</span>
                                <span class="font-medium text-gray-200"><?php echo $ultimo_resultado['tiempo_empleado']; ?>s</span>
                            </div>
                            <div>
                                <span class="block text-gray-400">Intento #</span>
                                <span class="font-medium text-gray-200"><?php echo $ultimo_resultado['intento_numero']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botón para comenzar -->
                <div class="mt-8 flex justify-center">
                    <button id="start-exercise" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg
                                   font-semibold shadow-lg hover:shadow-xl transform transition-all duration-300
                                   hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                        Comenzar ejercicio
                    </button>
                </div>
            </div>

            <!-- Área del ejercicio -->
            <div id="exercise-area" class="hidden">
                <!-- El contenido del ejercicio se cargará aquí dinámicamente -->
            </div>
        </div>
    </main>

    <!-- Modal de confirmación -->
    <div id="confirmation-modal" class="modal">
        <div class="bg-gray-800 rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all border border-gray-700">
            <h2 class="text-2xl font-bold text-gray-100 mb-4">Confirmar envío de ejercicio</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-200 mb-2">Resumen del ejercicio</h3>
                <div class="bg-gray-900/50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm text-gray-400">Puntos obtenidos</span>
                            <span id="modal-points" class="font-medium text-gray-200">-</span>
                        </div>
                        <div>
                            <span class="block text-sm text-gray-400">Tiempo empleado</span>
                            <span id="modal-time" class="font-medium text-gray-200">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-200 mb-2">
                    Evidencia de realización
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-600 border-dashed rounded-lg bg-gray-900/50">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-400">
                            <label for="evidence-upload" class="relative cursor-pointer rounded-md font-medium text-purple-400 hover:text-purple-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500 focus-within:ring-offset-gray-800">
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
                        <span id="file-name" class="text-sm text-gray-400"></span>
                        <button id="remove-file" class="text-red-400 hover:text-red-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button id="cancel-submit" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-gray-100">
                    Cancelar
                </button>
                <button id="confirm-submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-800">
                    Enviar ejercicio
                </button>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script>
        // Datos del ejercicio para JavaScript
        const ejercicioData = {
            tipo: '<?php echo $asignacion['categoria_nombre']; ?>',
            tiempo_limite: <?php echo $asignacion['tiempo_limite']; ?>,
            puntos_maximos: <?php echo $asignacion['puntos_maximos']; ?>,
            estudiante_asignacion_id: <?php echo $asignacion['estudiante_asignacion_id']; ?>,
            numero_ejercicios: <?php echo $asignacion['numero_ejercicios'] ?? 10; ?>
        };
    </script>
    <script src="/assets/js/ejercicios.js"></script>
    <script src="/assets/js/ejercicios_asignados.js"></script>
</body>
</html> 