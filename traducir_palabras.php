<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Traducir Palabras y Frases</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Traducir Palabras y Frases
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Traduce palabras individuales o frases completas a lenguaje de señas de manera sencilla
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 backdrop-blur-sm bg-white/80 transition-all duration-300 hover:shadow-2xl mb-12">
                <div class="mb-8">
                    <label for="wordInput" class="block text-sm font-medium text-gray-700 mb-2">Palabra o frase a traducir</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="wordInput" 
                            list="word-suggestions"
                            class="w-full p-4 border border-gray-200 rounded-xl shadow-inner 
                                   focus:ring-2 focus:ring-purple-500 focus:border-transparent
                                   transition-all duration-300 ease-in-out
                                   text-lg bg-white/50 backdrop-blur-sm
                                   placeholder-gray-400"
                            placeholder="Escribe una palabra o frase para traducir..."
                        >
                        <datalist id="word-suggestions"></datalist>
                    </div>
                </div>

                <div class="flex justify-center">
                    <button 
                        id="translateWordButton" 
                        class="group relative inline-flex items-center justify-center
                               px-8 py-3 text-lg font-semibold
                               bg-gradient-to-r from-purple-600 to-pink-600
                               text-white rounded-full
                               transition-all duration-300 ease-in-out
                               hover:scale-105 hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                        <span class="absolute inset-0 w-full h-full rounded-full opacity-50 filter blur-sm bg-gradient-to-r from-purple-600 to-pink-600"></span>
                        <span class="absolute inset-0 w-full h-full rounded-full opacity-50 filter blur-sm bg-gradient-to-r from-purple-600 to-pink-600"></span>
                        <span class="relative">Traducir a Señas</span>
                        <svg class="ml-2 h-5 w-5 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Área de resultados -->
            <div id="wordOutput" class="transition-all duration-300 ease-in-out">
                <div class="text-center p-8 text-gray-500">
                    Los resultados de la traducción aparecerán aquí
                </div>
            </div>

            <!-- Sección de características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-purple-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Traducción Instantánea</h3>
                    <p class="text-gray-600 text-center">Obtén resultados inmediatos con nuestra herramienta de traducción optimizada.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-pink-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Frases Completas</h3>
                    <p class="text-gray-600 text-center">Traduce múltiples palabras a la vez para formar frases completas en lenguaje de señas.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-indigo-600 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Preciso y Confiable</h3>
                    <p class="text-gray-600 text-center">Traducciones precisas basadas en estándares reconocidos de lenguaje de señas.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/translate_words.js"></script>
</body>
</html> 