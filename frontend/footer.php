<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <footer class="bg-gradient-to-r from-blue-900 to-purple-900 text-pink-300 mt-auto">
        <div class="mx-auto max-w-3xl overflow-hidden py-10 px-6 sm:py-12 lg:px-8">
            <div class="flex flex-col items-center gap-y-4">
                <!-- Logo y título -->
                <div class="flex flex-col items-center text-center">
                    <img class="h-10 w-auto rounded-lg mb-2" src="/imagenes/logo2.png" alt="Logo">
                    <h2 class="text-2xl font-bold bg-gradient-to-r from-pink-300 to-purple-300 bg-clip-text text-transparent">
                        Talk Hands
                    </h2>
                    <p class="text-sm text-pink-200">Conectando a través del lenguaje de señas</p>
                </div>

                <!-- Enlaces de navegación -->
                <nav class="flex justify-center gap-x-8 mt-4">
                    <a href="/frontend/inicio.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition-colors duration-300">
                        Inicio
                    </a>
                    <a href="/frontend/traducir.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition-colors duration-300">
                        Traducción
                    </a>
                    <a href="/frontend/traducir_palabras.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition-colors duration-300">
                        Traducir Palabras
                    </a>
                    <a href="/frontend/aprender.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition-colors duration-300">
                        Aprender
                    </a>
                </nav>

                <!-- Línea divisoria con gradiente -->
                <div class="w-full mt-6">
                    <div class="h-px bg-gradient-to-r from-transparent via-pink-300/50 to-transparent"></div>
                </div>

                <!-- Copyright -->
                <p class="mt-4 text-center text-xs text-pink-300">
                    &copy; 2025 Talk Hands. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>