<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener datos del estudiante
$user_id = $_SESSION['user_id'];

// Obtener progreso general
$query_progreso = "SELECT 
    COUNT(DISTINCT e.id) as total_ejercicios,
    COUNT(DISTINCT CASE WHEN pe.completado = 1 THEN e.id END) as ejercicios_completados,
    SUM(pe.puntos_obtenidos) as puntos_totales
FROM ejercicios e
LEFT JOIN progreso_estudiantes pe ON e.id = pe.ejercicio_id AND pe.usuario_id = ?";

$stmt = mysqli_prepare($conexion, $query_progreso);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$progreso = mysqli_fetch_assoc($result);

// Obtener ejercicios asignados pendientes
$query_asignaciones = "SELECT 
    a.id, 
    e.titulo, 
    e.descripcion,
    a.fecha_limite,
    ea.estado
FROM asignaciones a
JOIN ejercicios e ON a.ejercicio_id = e.id
JOIN estudiantes_asignaciones ea ON a.id = ea.asignacion_id
WHERE ea.estudiante_id = ? AND ea.estado = 'pendiente'
ORDER BY a.fecha_limite ASC";

$stmt = mysqli_prepare($conexion, $query_asignaciones);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$asignaciones = mysqli_stmt_get_result($stmt);

// Obtener insignias del estudiante
$query_insignias = "SELECT i.*
FROM insignias i
JOIN insignias_usuarios iu ON i.id = iu.insignia_id
WHERE iu.usuario_id = ?";

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
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>

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
                    <div class="text-3xl font-bold text-pink-600 mb-2">
                        <?php echo number_format($progreso['puntos_totales'] ?? 0); ?>
                    </div>
                    <p class="text-gray-600">Puntos acumulados</p>
                </div>

                <!-- Racha actual -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Racha Actual</h3>
                        <div class="text-orange-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-orange-500 mb-2">7 días</div>
                    <p class="text-gray-600">¡Sigue así!</p>
                </div>
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
                                    <a href="../exercises/view.php?id=<?php echo $asignacion['id']; ?>" 
                                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 transition-colors">
                                        Comenzar
                                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
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
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Tus Insignias</h2>
                
                <?php if (mysqli_num_rows($insignias) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                        <?php while ($insignia = mysqli_fetch_assoc($insignias)): ?>
                            <div class="text-center transform transition-all duration-300 hover:scale-110">
                                <div class="w-20 h-20 mx-auto mb-2 float-animation">
                                    <img src="<?php echo htmlspecialchars($insignia['imagen_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($insignia['nombre']); ?>"
                                         class="w-full h-full object-contain">
                                </div>
                                <h4 class="text-sm font-medium text-gray-800">
                                    <?php echo htmlspecialchars($insignia['nombre']); ?>
                                </h4>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        <p>¡Completa ejercicios para ganar insignias!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
</body>
</html> 