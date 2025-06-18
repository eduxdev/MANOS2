<?php
session_start();
require_once '../db/conection.php';
require_once 'check_badges.php';

header('Content-Type: application/json');

// Obtener y validar datos
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['tipo_ejercicio']) || !isset($data['respuesta_correcta'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    // Si no hay sesión, solo mostrar "Correcto" o "Incorrecto"
    echo json_encode([
        'success' => true,
        'puntos_ganados' => 0,
        'puntos_totales' => 0,
        'insignias_otorgadas' => [],
        'mensaje' => $data['respuesta_correcta'] ? "¡Correcto!" : "Incorrecto"
    ]);
    exit();
}

$estudiante_id = $_SESSION['user_id'];
$tipo_ejercicio = $data['tipo_ejercicio'];
$respuesta_correcta = $data['respuesta_correcta'];
$detalles = isset($data['detalles']) ? json_encode($data['detalles']) : null;

// Puntos por tipo de ejercicio
$puntos_por_tipo = [
    'letterIdentification' => 1,
    'wordCompletion' => 2,
    'signRecognition' => 1,
    'errorDetection' => 2,
    'sequenceExercise' => 3, // Base points for sequence exercise
    'memoryExercise' => 2,   // Base points for memory exercise
    'phraseTranslation' => 5, // Base points for phrase translation exercise
    'speedExercise' => 3     // Base points for speed exercise
];

$puntos_base = $puntos_por_tipo[$tipo_ejercicio] ?? 1;

// Calcular puntos según el tipo de ejercicio y detalles
if ($tipo_ejercicio === 'sequenceExercise' && isset($data['detalles']['nivel'])) {
    $puntos_base *= $data['detalles']['nivel']; // Multiplicar por el nivel alcanzado
} elseif ($tipo_ejercicio === 'memoryExercise' && isset($data['detalles']['intentos'])) {
    // Más puntos por menos intentos, mínimo 1 punto
    $puntos_base = max(1, $puntos_base * (10 - min($data['detalles']['intentos'], 8)));
} elseif ($tipo_ejercicio === 'phraseTranslation' && isset($data['detalles']['nivel'])) {
    $puntos_base *= $data['detalles']['nivel']; // Multiplicar por el nivel de dificultad
} elseif ($tipo_ejercicio === 'speedExercise' && isset($data['detalles']['tiempo_limite'])) {
    // Más puntos por menos tiempo disponible
    $puntos_base += max(0, (5000 - $data['detalles']['tiempo_limite']) / 500);
}

$puntos_ganados = $respuesta_correcta ? $puntos_base : 0;

try {
    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    // 1. Registrar la práctica
    $query = "INSERT INTO practicas_ejercicios (estudiante_id, tipo_ejercicio, respuesta_correcta, detalles) 
              VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "isss", $estudiante_id, $tipo_ejercicio, $respuesta_correcta, $detalles);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error al guardar la práctica");
    }

    // 2. Actualizar puntos si la respuesta es correcta
    if ($puntos_ganados > 0) {
        $query_puntos = "UPDATE usuarios 
                        SET puntos_practica = COALESCE(puntos_practica, 0) + ? 
                        WHERE id = ?";
        $stmt_puntos = mysqli_prepare($conexion, $query_puntos);
        mysqli_stmt_bind_param($stmt_puntos, "ii", $puntos_ganados, $estudiante_id);
        
        if (!mysqli_stmt_execute($stmt_puntos)) {
            throw new Exception("Error al actualizar los puntos");
        }
    }

    // 3. Obtener puntos totales actualizados
    $query_total = "SELECT COALESCE(puntos_practica, 0) as puntos_totales FROM usuarios WHERE id = ?";
    $stmt_total = mysqli_prepare($conexion, $query_total);
    mysqli_stmt_bind_param($stmt_total, "i", $estudiante_id);
    mysqli_stmt_execute($stmt_total);
    $result = mysqli_stmt_get_result($stmt_total);
    $puntos_totales = mysqli_fetch_assoc($result)['puntos_totales'];

    // 4. Verificar y otorgar insignias
    $insignias_otorgadas = checkAndAwardBadges($estudiante_id);

    // Confirmar transacción
    mysqli_commit($conexion);

    // Devolver respuesta exitosa con mensaje personalizado según si hay sesión
    echo json_encode([
        'success' => true,
        'puntos_ganados' => $puntos_ganados,
        'puntos_totales' => $puntos_totales,
        'insignias_otorgadas' => $insignias_otorgadas,
        'mensaje' => $respuesta_correcta ? 
            ($puntos_ganados > 0 ? "¡Correcto! +{$puntos_ganados} puntos" : "¡Correcto!") : 
            "Incorrecto"
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conexion);
?> 