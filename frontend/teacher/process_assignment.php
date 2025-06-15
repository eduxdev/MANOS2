<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos requeridos
    $ejercicio_id = filter_var($_POST['ejercicio_id'], FILTER_VALIDATE_INT);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_limite = $_POST['fecha_limite'];
    $puntos = filter_var($_POST['puntos'], FILTER_VALIDATE_INT);
    $intentos = filter_var($_POST['intentos'], FILTER_VALIDATE_INT);
    $instrucciones = filter_var($_POST['instrucciones'], FILTER_SANITIZE_STRING);
    $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : [];

    // Validaciones básicas
    if (!$ejercicio_id || !$fecha_inicio || !$fecha_limite || !$puntos || !$intentos || empty($grupos)) {
        $_SESSION['error'] = "Por favor, completa todos los campos requeridos.";
        header("Location: new_assignment.php");
        exit();
    }

    // Validar fechas
    $fecha_inicio_obj = new DateTime($fecha_inicio);
    $fecha_limite_obj = new DateTime($fecha_limite);
    $hoy = new DateTime();

    if ($fecha_inicio_obj < $hoy) {
        $_SESSION['error'] = "La fecha de inicio no puede ser anterior a hoy.";
        header("Location: new_assignment.php");
        exit();
    }

    if ($fecha_limite_obj <= $fecha_inicio_obj) {
        $_SESSION['error'] = "La fecha límite debe ser posterior a la fecha de inicio.";
        header("Location: new_assignment.php");
        exit();
    }

    try {
        // Iniciar transacción
        mysqli_begin_transaction($conexion);

        // Crear la asignación
        $query = "INSERT INTO asignaciones (ejercicio_id, profesor_id, fecha_asignacion, fecha_inicio, fecha_limite, puntos_maximos, intentos_permitidos, instrucciones) 
                 VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "iisssis", 
            $ejercicio_id, 
            $_SESSION['user_id'], 
            $fecha_inicio,
            $fecha_limite,
            $puntos,
            $intentos,
            $instrucciones
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al crear la asignación");
        }

        $asignacion_id = mysqli_insert_id($conexion);

        // Obtener estudiantes de los grupos seleccionados
        $grupos_str = "'" . implode("','", array_map(function($grupo) use ($conexion) {
            return mysqli_real_escape_string($conexion, $grupo);
        }, $grupos)) . "'";

        $query_estudiantes = "SELECT id FROM usuarios 
                            WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                            AND grupo IN ($grupos_str)";
        
        $result_estudiantes = mysqli_query($conexion, $query_estudiantes);

        // Asignar a cada estudiante
        while ($estudiante = mysqli_fetch_assoc($result_estudiantes)) {
            $query = "INSERT INTO estudiantes_asignaciones (estudiante_id, asignacion_id, estado, intentos_realizados) 
                     VALUES (?, ?, 'pendiente', 0)";
            
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, "ii", $estudiante['id'], $asignacion_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al asignar a estudiantes");
            }
        }

        // Confirmar transacción
        mysqli_commit($conexion);

        $_SESSION['success'] = "Asignación creada exitosamente.";
        header("Location: dashboard.php");
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        
        $_SESSION['error'] = "Error al crear la asignación: " . $e->getMessage();
        header("Location: new_assignment.php");
        exit();
    }
}

// Si no es POST, redirigir
header("Location: new_assignment.php");
exit();
?> 