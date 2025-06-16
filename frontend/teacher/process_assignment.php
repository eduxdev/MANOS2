<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: /frontend/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y validar los datos del formulario
    $ejercicio_id = filter_var($_POST['ejercicio_id'], FILTER_VALIDATE_INT);
    $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : [];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_limite = $_POST['fecha_limite'];
    $puntos = filter_var($_POST['puntos'], FILTER_VALIDATE_INT);
    $intentos = filter_var($_POST['intentos'], FILTER_VALIDATE_INT);
    $instrucciones = mysqli_real_escape_string($conexion, $_POST['instrucciones']);
    $profesor_id = $_SESSION['user_id'];

    // Validar que todos los campos requeridos estén presentes
    if (!$ejercicio_id || empty($grupos) || !$fecha_inicio || !$fecha_limite || !$puntos || !$intentos) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location: /frontend/teacher/new_assignment.php");
        exit();
    }

    // Obtener información del ejercicio
    $query_ejercicio = "SELECT e.*, c.nombre as categoria_nombre 
                       FROM ejercicios e 
                       JOIN categorias_ejercicios c ON e.categoria_id = c.id 
                       WHERE e.id = ?";
    $stmt = mysqli_prepare($conexion, $query_ejercicio);
    mysqli_stmt_bind_param($stmt, "i", $ejercicio_id);
    mysqli_stmt_execute($stmt);
    $ejercicio = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$ejercicio) {
        $_SESSION['error'] = "Ejercicio no encontrado.";
        header("Location: /frontend/teacher/new_assignment.php");
        exit();
    }

    // Iniciar transacción
    mysqli_begin_transaction($conexion);

    try {
        // Insertar la asignación principal
        foreach ($grupos as $grupo) {
            $query = "INSERT INTO asignaciones (
                profesor_id, 
                ejercicio_id, 
                grupo_asignado, 
                fecha_asignacion, 
                fecha_limite, 
                estado,
                puntos_maximos,
                intentos_maximos,
                instrucciones,
                tipo_ejercicio
            ) VALUES (?, ?, ?, ?, ?, 'activa', ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param(
                $stmt, 
                "iisssiiis", 
                $profesor_id, 
                $ejercicio_id, 
                $grupo, 
                $fecha_inicio, 
                $fecha_limite,
                $puntos,
                $intentos,
                $instrucciones,
                $ejercicio['categoria_nombre']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al crear la asignación: " . mysqli_error($conexion));
            }
            
            $asignacion_id = mysqli_insert_id($conexion);

            // Obtener estudiantes del grupo
            $query_estudiantes = "SELECT id FROM usuarios 
                                WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                                AND grupo = ?";
            
            $stmt_estudiantes = mysqli_prepare($conexion, $query_estudiantes);
            mysqli_stmt_bind_param($stmt_estudiantes, "s", $grupo);
            mysqli_stmt_execute($stmt_estudiantes);
            $result_estudiantes = mysqli_stmt_get_result($stmt_estudiantes);

            // Crear registros en estudiantes_asignaciones
            while ($estudiante = mysqli_fetch_assoc($result_estudiantes)) {
                $query_asignar = "INSERT INTO estudiantes_asignaciones (
                    asignacion_id, 
                    estudiante_id, 
                    estado, 
                    intentos_realizados,
                    puntos_obtenidos,
                    fecha_ultimo_intento
                ) VALUES (?, ?, 'pendiente', 0, 0, NULL)";
                
                $stmt_asignar = mysqli_prepare($conexion, $query_asignar);
                mysqli_stmt_bind_param($stmt_asignar, "ii", $asignacion_id, $estudiante['id']);
                
                if (!mysqli_stmt_execute($stmt_asignar)) {
                    throw new Exception("Error al asignar a estudiante: " . mysqli_error($conexion));
                }
            }
        }

        // Confirmar transacción
        mysqli_commit($conexion);
        $_SESSION['success'] = "Asignación creada exitosamente.";
        header("Location: /frontend/teacher/dashboard.php");
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conexion);
        $_SESSION['error'] = $e->getMessage();
        header("Location: /frontend/teacher/new_assignment.php");
        exit();
    }
}

// Si no es POST, redirigir
header("Location: /frontend/teacher/new_assignment.php");
exit();
?> 