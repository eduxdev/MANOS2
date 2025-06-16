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
<html lang="es">
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
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Encabezado del ejercicio -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    <?php echo htmlspecialchars($asignacion['ejercicio_titulo']); ?>
                </h1>
                <div class="flex flex-wrap gap-4 text-sm">
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full">
                        <?php echo htmlspecialchars($asignacion['categoria_nombre']); ?>
                    </span>
                    <span class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full">
                        Nivel <?php echo htmlspecialchars($asignacion['ejercicio_nivel']); ?>
                    </span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full">
                        <?php echo $asignacion['puntos_maximos']; ?> puntos
                    </span>
                </div>
            </div>

            <!-- Información del ejercicio -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Detalles</h3>
                        <ul class="space-y-2 text-gray-600">
                            <li>
                                <span class="font-medium">Fecha límite:</span>
                                <?php echo date('d/m/Y', strtotime($asignacion['fecha_limite'])); ?>
                            </li>
                            <li>
                                <span class="font-medium">Intentos restantes:</span>
                                <?php echo $asignacion['intentos_maximos'] - $asignacion['intentos_realizados']; ?>
                            </li>
                            <li>
                                <span class="font-medium">Mejor puntuación:</span>
                                <?php echo $asignacion['puntos_obtenidos']; ?> / <?php echo $asignacion['puntos_maximos']; ?>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Instrucciones</h3>
                        <p class="text-gray-600">
                            <?php echo nl2br(htmlspecialchars($asignacion['instrucciones'])); ?>
                        </p>
                    </div>
                </div>

                <?php if ($ultimo_resultado): ?>
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Último intento</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="block text-gray-500">Fecha</span>
                                <span class="font-medium">
                                    <?php echo date('d/m/Y H:i', strtotime($ultimo_resultado['fecha_realizacion'])); ?>
                                </span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Puntos obtenidos</span>
                                <span class="font-medium"><?php echo $ultimo_resultado['puntos_obtenidos']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Tiempo empleado</span>
                                <span class="font-medium"><?php echo $ultimo_resultado['tiempo_empleado']; ?>s</span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Intento #</span>
                                <span class="font-medium"><?php echo $ultimo_resultado['intento_numero']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botón para comenzar -->
                <div class="mt-8 flex justify-center">
                    <button id="start-exercise" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full
                                   font-semibold shadow-lg hover:shadow-xl transform transition-all duration-300
                                   hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
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