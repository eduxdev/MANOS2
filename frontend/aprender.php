<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .letter-card {
            @apply bg-white rounded-xl shadow-md p-4 transition-all duration-300;
            @apply hover:shadow-xl hover:scale-105 hover:bg-gradient-to-br hover:from-purple-50 hover:to-pink-50;
        }
        .exercise-card {
            @apply bg-white rounded-xl shadow-lg p-8 transition-all duration-300;
            @apply hover:shadow-xl hover:bg-gradient-to-br hover:from-white hover:to-purple-50;
        }
        .custom-button {
            @apply px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full;
            @apply transition-all duration-300 transform hover:scale-105 hover:shadow-lg;
            @apply focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-6xl">
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Aprender Lenguaje de Señas
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Descubre y aprende el alfabeto en lenguaje de señas a través de ejercicios interactivos
                </p>
            </div>

            <!-- Alfabeto -->
            <div class="mb-20">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Alfabeto Completo</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                    <?php
                    $letters = range('a', 'z');
                    foreach ($letters as $letter) {
                        echo "<div class='letter-card group'>";
                        echo "<div class='relative overflow-hidden rounded-lg'>";
                        echo "<img class='w-full h-24 object-contain transition-transform group-hover:scale-110' src='/signs/$letter.png' alt='$letter'>";
                        echo "</div>";
                        echo "<p class='text-center text-gray-800 font-semibold mt-3 text-lg uppercase'>{$letter}</p>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Ejercicios -->
            <div class="space-y-12">
                <!-- Ejercicio 1: Identificar Letra -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Identifica la Letra</h2>
                            <p class="text-gray-600 mt-2">Selecciona la imagen que corresponde a la letra mostrada</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                    </div>
                    <div id="exercise-container">
                        <div class="bg-purple-50 rounded-lg p-6 mb-6">
                            <p class="text-lg font-semibold text-gray-800">
                                Letra a identificar: 
                                <span id="random-letter" class="text-2xl font-bold text-purple-600 ml-2"></span>
                            </p>
                        </div>
                        <div id="exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6"></div>
                        <div class="mt-6 flex items-center justify-between">
                            <button id="check-answer" class="custom-button">
                                Verificar Respuesta
                            </button>
                            <p id="exercise-feedback" class="text-lg font-medium"></p>
                        </div>
                    </div>
                </section>

                <!-- Ejercicio 2: Completar Palabra -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Completa la Palabra</h2>
                            <p class="text-gray-600 mt-2">Selecciona las letras en el orden correcto para formar la palabra</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-pink-50 rounded-lg p-6 mb-6">
                        <p class="text-lg font-semibold text-gray-800">
                            Palabra a formar: 
                            <span id="random-word" class="text-2xl font-bold text-pink-600 ml-2"></span>
                        </p>
                    </div>
                    <div id="word-exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6"></div>
                    <div class="mt-6 flex items-center justify-between">
                        <button id="check-word-answer" class="custom-button">
                            Verificar Palabra
                        </button>
                        <p id="word-exercise-feedback" class="text-lg font-medium"></p>
                    </div>
                </section>

                <!-- Ejercicio 3: Reconoce la Seña -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Reconoce la Seña</h2>
                            <p class="text-gray-600 mt-2">Identifica la letra que corresponde a la seña mostrada</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-6 mb-6 flex items-center justify-center">
                        <img id="random-sign" class="w-32 h-32 object-contain" src="" alt="">
                    </div>
                    <div id="sign-options" class="flex flex-wrap justify-center gap-4"></div>
                    <div class="mt-6 flex items-center justify-between">
                        <button id="check-sign-answer" class="custom-button">
                            Verificar Seña
                        </button>
                        <p id="sign-feedback" class="text-lg font-medium"></p>
                    </div>
                </section>

                <!-- Ejercicio 4: Encuentra la Seña Incorrecta -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Encuentra la Seña Incorrecta</h2>
                            <p class="text-gray-600 mt-2">Identifica la seña que no corresponde a la palabra mostrada</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-6 mb-6">
                        <p class="text-lg font-semibold text-gray-800">
                            Palabra: 
                            <span id="incorrect-word" class="text-2xl font-bold text-purple-600 ml-2"></span>
                        </p>
                    </div>
                    <div id="incorrect-sign-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6"></div>
                    <div class="mt-6 flex items-center justify-between">
                        <button id="check-incorrect-sign-answer" class="custom-button">
                            Verificar Selección
                        </button>
                        <p id="incorrect-sign-feedback" class="text-lg font-medium"></p>
                    </div>
                </section>

                <!-- Ejercicio 5: Secuencia de Señas -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Secuencia de Señas</h2>
                            <p class="text-gray-600 mt-2">Memoriza y repite la secuencia de señas mostrada</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-6 mb-6">
                        <div class="flex justify-between items-center">
                            <p class="text-lg font-semibold text-gray-800">
                                Nivel: <span id="sequence-level" class="text-2xl font-bold text-green-600 ml-2">1</span>
                            </p>
                            <p class="text-sm text-gray-600">Puntos: <span id="sequence-points" class="font-bold">0</span></p>
                        </div>
                    </div>
                    <div id="sequence-display" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
                        <!-- Las señas de la secuencia se mostrarán aquí -->
                    </div>
                    <div id="sequence-input" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
                        <!-- Área para que el usuario repita la secuencia -->
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <button id="start-sequence" class="custom-button">
                            Iniciar Secuencia
                        </button>
                        <p id="sequence-feedback" class="text-lg font-medium"></p>
                    </div>
                </section>

                <!-- Ejercicio 6: Memorama de Señas -->
                <section class="exercise-card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Ejercicio: Memorama de Señas</h2>
                            <p class="text-gray-600 mt-2">Encuentra los pares de letras y señas correspondientes</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-6 mb-6">
                        <div class="flex justify-between items-center">
                            <p class="text-lg font-semibold text-gray-800">
                                Pares encontrados: <span id="pairs-found" class="text-2xl font-bold text-blue-600 ml-2">0</span>
                            </p>
                            <p class="text-sm text-gray-600">Intentos: <span id="memory-attempts" class="font-bold">0</span></p>
                        </div>
                    </div>
                    <div id="memory-grid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                        <!-- Las cartas del memorama se generarán aquí -->
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <button id="start-memory" class="custom-button">
                            Nuevo Juego
                        </button>
                        <p id="memory-feedback" class="text-lg font-medium"></p>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/ejercicios.js"></script>
</body>
</html>
