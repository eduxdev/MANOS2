<?php
session_start();
// Redirigir si ya está logueado
if(isset($_SESSION['user_id'])) {
    header("Location: ../inicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .fade-out.hide {
            opacity: 0;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Botón para regresar a inicio -->
            <div class="flex justify-start mb-4">
                <a href="/frontend/inicio.php" class="inline-flex items-center text-purple-600 hover:text-purple-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm font-medium">Volver al inicio</span>
                </a>
            </div>

            <!-- Logo y título -->
            <div class="text-center">
                <img class="mx-auto h-20 w-auto rounded-lg" src="/imagenes/logo2.png" alt="Talk Hands">
                <h2 class="mt-6 text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                    Bienvenido a Talk Hands
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Inicia sesión para continuar
                </p>
            </div>

            <!-- Mensajes de error o éxito -->
            <?php if(isset($_SESSION['error'])): ?>
                <div id="error-message" class="rounded-md bg-red-50 p-4 fade-out">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                <?php echo $_SESSION['error']; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div id="success-message" class="rounded-md bg-green-50 p-4 fade-out">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                <?php echo $_SESSION['success']; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Formulario de login -->
            <form class="mt-8 space-y-6" action="/backend/auth/login_process.php" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                            placeholder="Correo electrónico">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                            placeholder="Contraseña">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition-all duration-150 hover:scale-105">
                        Iniciar sesión
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿No tienes una cuenta? 
                        <a href="register.php" class="font-medium text-purple-600 hover:text-purple-500">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Desvanecer mensajes después de 3 segundos
        const messages = document.querySelectorAll('.fade-out');
        messages.forEach(message => {
            setTimeout(() => {
                message.classList.add('hide');
                setTimeout(() => message.remove(), 500);
            }, 3000);
        });
    </script>
</body>
</html> 