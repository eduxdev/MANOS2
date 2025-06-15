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
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
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
            <form class="mt-8 space-y-6" action="/backend/auth/register_process.php" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="sr-only">Nombre</label>
                            <input id="nombre" name="nombre" type="text" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Nombre">
                        </div>
                        <div>
                            <label for="apellidos" class="sr-only">Apellidos</label>
                            <input id="apellidos" name="apellidos" type="text" required 
                                class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                placeholder="Apellidos">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="sr-only">Correo electrónico</label>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                            placeholder="Correo electrónico">
                    </div>

                    <div>
                        <label for="password" class="sr-only">Contraseña</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                            placeholder="Contraseña">
                    </div>

                    <div>
                        <label for="confirm_password" class="sr-only">Confirmar contraseña</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                            placeholder="Confirmar contraseña">
                    </div>

                    <div>
                        <label for="rol" class="sr-only">Tipo de usuario</label>
                        <select id="rol" name="rol" required 
                            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                            <option value="">Selecciona tu rol</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="profesor">Profesor</option>
                        </select>
                    </div>

                    <!-- Campos específicos para estudiantes -->
                    <div id="campos_estudiante" class="hidden space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="grado" class="sr-only">Grado</label>
                                <select id="grado" name="grado" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm">
                                    <option value="">Selecciona tu grado</option>
                                    <option value="1">1° Secundaria</option>
                                    <option value="2">2° Secundaria</option>
                                    <option value="3">3° Secundaria</option>
                                </select>
                            </div>
                            <div>
                                <label for="grupo" class="sr-only">Grupo</label>
                                <input id="grupo" name="grupo" type="text" 
                                    class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm"
                                    placeholder="Grupo (A, B, C...)">
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
            }
        });

        // Validación de contraseñas
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });

        // Mostrar mensajes de error si existen
        <?php if(isset($_SESSION['error'])): ?>
            alert('<?php echo $_SESSION['error']; ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html> 