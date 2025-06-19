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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-900">
    <!-- Background Gradient -->
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
        <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Botón para regresar a inicio -->
            <div class="flex justify-start mb-4">
                <a href="/frontend/inicio.php" class="inline-flex items-center text-gray-300 hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm font-medium">Volver al inicio</span>
                </a>
            </div>

            <!-- Logo y título -->
            <div class="text-center fade-in">
                <img class="mx-auto h-20 w-auto rounded-lg" src="/imagenes/logo2.png" alt="Talk Hands">
                <h2 class="mt-6 text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                    Únete a Talk Hands
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    Crea tu cuenta para empezar a aprender
                </p>
            </div>

            <!-- Formulario de registro -->
            <form class="mt-8 space-y-6 fade-in" action="/backend/auth/register_process.php" method="POST" id="registerForm">
                <?php if(isset($_SESSION['error'])): ?>
                    <div id="error-message" class="rounded-md bg-red-900/50 backdrop-blur-sm p-4 mb-4 fade-out border border-red-700">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-300">
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
                        <h3 class="text-lg font-medium text-gray-200">Datos personales</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                                <input id="nombre" name="nombre" type="text" required 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Nombre">
                            </div>
                            <div>
                                <label for="apellidos" class="block text-sm font-medium text-gray-300 mb-1">Apellidos</label>
                                <input id="apellidos" name="apellidos" type="text" required 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Apellidos">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de cuenta -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-200">Datos de cuenta</h3>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Correo electrónico</label>
                            <input id="email" name="email" type="email" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Correo electrónico">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                            <input id="password" name="password" type="password" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Mínimo 6 caracteres">
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirmar contraseña</label>
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Repite tu contraseña">
                        </div>
                    </div>

                    <!-- Tipo de usuario -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-medium text-gray-200">Tipo de usuario</h3>
                        <div>
                            <label for="rol" class="block text-sm font-medium text-gray-300 mb-1">Selecciona tu rol</label>
                            <select id="rol" name="rol" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                                <option value="">Selecciona una opción</option>
                                <option value="estudiante">Estudiante</option>
                                <option value="profesor">Profesor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos específicos para estudiantes -->
                    <div id="campos_estudiante" class="hidden space-y-2">
                        <h3 class="text-lg font-medium text-gray-200">Información académica</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="grado" class="block text-sm font-medium text-gray-300 mb-1">Grado</label>
                                <select id="grado" name="grado" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                                    <option value="">Selecciona tu grado</option>
                                    <option value="1">1° Secundaria</option>
                                    <option value="2">2° Secundaria</option>
                                    <option value="3">3° Secundaria</option>
                                </select>
                            </div>
                            <div>
                                <label for="grupo" class="block text-sm font-medium text-gray-300 mb-1">Grupo</label>
                                <input id="grupo" name="grupo" type="text" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-600 bg-gray-800/50 backdrop-blur-sm placeholder-gray-500 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Ejemplo: A, B, C...">
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition-all duration-300 hover:scale-105">
                        <span class="absolute inset-0 w-full h-full rounded-xl opacity-50 filter blur-sm bg-gradient-to-r from-purple-600 to-pink-600"></span>
                        <span class="relative">Crear cuenta</span>
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-400">
                        ¿Ya tienes una cuenta? 
                        <a href="login.php" class="font-medium text-purple-400 hover:text-purple-300 transition-colors">
                            Inicia sesión aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Gradient -->
    <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
        <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
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
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                showError('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showError('Las contraseñas no coinciden');
                return;
            }
        });

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'rounded-md bg-red-900/50 backdrop-blur-sm p-4 mb-4 fade-out border border-red-700';
            errorDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-300">${message}</p>
                    </div>
                </div>
            `;
            
            const form = document.getElementById('registerForm');
            form.insertBefore(errorDiv, form.firstChild);
            
            setTimeout(() => {
                errorDiv.classList.add('hide');
                setTimeout(() => errorDiv.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html> 