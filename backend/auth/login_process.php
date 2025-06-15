<?php
session_start();

// Incluir el archivo de conexión
require_once __DIR__ . '/../db/conection.php';

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Por favor, complete todos los campos.";
        header("Location: ../../frontend/auth/login.php");
        exit();
    }

    // Consulta preparada para obtener los datos del usuario y su rol
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.email = ?";

    if ($stmt = mysqli_prepare($conexion, $query)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verificar la contraseña
            if (password_verify($password, $user['password'])) {
                // Iniciar sesión y guardar datos del usuario
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellidos'] = $user['apellidos'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['rol'] = $user['rol_nombre'];

                // Redirigir según el rol
                switch ($user['rol_nombre']) {
                    case 'estudiante':
                        header("Location: /frontend/student/dashboard.php");
                        break;
                    case 'profesor':
                        header("Location: /frontend/teacher/dashboard.php");
                        break;
                    case 'administrador':
                        header("Location: /frontend/admin/dashboard.php");
                        break;
                    default:
                        header("Location: /frontend/inicio.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Contraseña incorrecta.";
            }
        } else {
            $_SESSION['error'] = "No se encontró ningún usuario con ese correo electrónico.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Error en la consulta: " . mysqli_error($conexion);
    }

    // Si llegamos aquí, hubo un error
    header("Location: /frontend/auth/login.php");
    exit();
}

// Si no es una solicitud POST, redirigir al login
header("Location: frontend/auth/login.php");
exit();
?> 