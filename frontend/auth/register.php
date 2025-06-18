<?php
session_start();
// Redirigir si ya está logueado
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Registro</title>
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
                    Únete a Talk Hands
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Crea tu cuenta para empezar a aprender
                </p>
            </div>

            <!-- Formulario de registro -->
            <form class="mt-8 space-y-6" action="/backend/auth/register_process.php" method="POST" id="registerForm">
                <?php if(isset($_SESSION['error'])): ?>
                    <div id="error-message" class="rounded-md bg-red-50 p-4 mb-4 fade-out">
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

                <div class="rounded-md shadow-sm space-y-4">
                    <!-- Datos personales -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-900">Datos personales</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input id="nombre" name="nombre" type="text" required 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Nombre">
                            </div>
                            <div>
                                <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                                <input id="apellidos" name="apellidos" type="text" required 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Apellidos">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de cuenta -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-900">Datos de cuenta</h3>
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
                                placeholder="Mínimo 6 caracteres">
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Repite tu contraseña">
                        </div>
                    </div>

                    <!-- Tipo de usuario -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-900">Tipo de usuario</h3>
                        <div>
                            <label for="rol" class="block text-sm font-medium text-gray-700 mb-1">Selecciona tu rol</label>
                            <select id="rol" name="rol" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                                <option value="">Selecciona una opción</option>
                                <option value="estudiante">Estudiante</option>
                                <option value="profesor">Profesor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos específicos para estudiantes -->
                    <div id="campos_estudiante" class="hidden space-y-2">
                        <h3 class="text-lg font-medium text-gray-900">Información académica</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="grado" class="block text-sm font-medium text-gray-700 mb-1">Grado</label>
                                <select id="grado" name="grado" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                                    <option value="">Selecciona tu grado</option>
                                    <option value="1">1° Secundaria</option>
                                    <option value="2">2° Secundaria</option>
                                    <option value="3">3° Secundaria</option>
                                </select>
                            </div>
                            <div>
                                <label for="grupo" class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                                <input id="grupo" name="grupo" type="text" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Ejemplo: A, B, C...">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition-all duration-150 hover:scale-105">
                        Crear cuenta
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes una cuenta? 
                        <a href="login.php" class="font-medium text-purple-600 hover:text-purple-500">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mostrar/ocultar campos según el rol seleccionado
        document.getElementById('rol').addEventListener('change', function() {
            const camposEstudiante = document.getElementById('campos_estudiante');
            const gradoInput = document.getElementById('grado');
            const grupoInput = document.getElementById('grupo');

            if (this.value === 'estudiante') {
                camposEstudiante.classList.remove('hidden');
                gradoInput.required = true;
                grupoInput.required = true;
            } else {
                camposEstudiante.classList.add('hidden');
                gradoInput.required = false;
                grupoInput.required = false;
                gradoInput.value = '';
                grupoInput.value = '';
            }
        });

        // Validación del formulario
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                showError('La contraseña debe tener al menos 6 caracteres');
                return;
            }

            if (password !== confirmPassword) {
                showError('Las contraseñas no coinciden');
                return;
            }

            this.submit();
        });

        // Función para mostrar errores
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.id = 'error-message';
            errorDiv.className = 'rounded-md bg-red-50 p-4 mb-4 fade-out';
            errorDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">${message}</p>
                    </div>
                </div>
            `;

            // Remover error anterior si existe
            const oldError = document.getElementById('error-message');
            if (oldError) oldError.remove();

            // Insertar nuevo error al inicio del formulario
            const form = document.getElementById('registerForm');
            form.insertBefore(errorDiv, form.firstChild);

            // Desvanecer después de 3 segundos
            setTimeout(() => {
                errorDiv.classList.add('hide');
                setTimeout(() => errorDiv.remove(), 500);
            }, 3000);
        }

        // Desvanecer mensaje de error inicial si existe
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.classList.add('hide');
                setTimeout(() => errorMessage.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html> 