<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
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
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #e879f9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }
        .suggestions-container {
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #a855f7 #f3f4f6;
        }
        .suggestions-container::-webkit-scrollbar {
            width: 6px;
        }
        .suggestions-container::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }
        .suggestions-container::-webkit-scrollbar-thumb {
            background-color: #a855f7;
            border-radius: 3px;
        }
        @keyframes handWave {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(15deg); }
            75% { transform: rotate(-15deg); }
            100% { transform: rotate(0deg); }
        }
        .hand-wave {
            animation: handWave 2s ease-in-out infinite;
            transform-origin: bottom center;
        }
        .circle-animation {
            animation: circleMove 20s linear infinite;
        }
        @keyframes circleMove {
            0% { transform: translate(0, 0); }
            25% { transform: translate(20px, 20px); }
            50% { transform: translate(0, 40px); }
            75% { transform: translate(-20px, 20px); }
            100% { transform: translate(0, 0); }
        }
        .blob {
            animation: blobMove 15s ease-in-out infinite;
        }
        @keyframes blobMove {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(10px, -10px) scale(1.1); }
            50% { transform: translate(-5px, 10px) scale(0.9); }
            75% { transform: translate(-10px, -5px) scale(1.05); }
        }
        @media (max-width: 640px) {
            .circle-animation {
                animation: circleMove 15s linear infinite;
            }
            @keyframes circleMove {
                0% { transform: translate(0, 0); }
                25% { transform: translate(10px, 10px); }
                50% { transform: translate(0, 20px); }
                75% { transform: translate(-10px, 10px); }
                100% { transform: translate(0, 0); }
            }
        }
    </style>
</head>
<body class="bg-gray-900">
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="relative isolate min-h-screen flex flex-col justify-center overflow-hidden pt-16 sm:pt-24">
        <!-- Background Gradient -->
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 sm:py-24">
            <!-- Encabezado -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 text-white">
                    Traductor de <span class="gradient-text">Palabras</span> y Frases
                </h1>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Escribe una palabra o frase y descubre su traducción en lenguaje de señas
                </p>
            </div>

            <!-- Área de traducción -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl p-6 md:p-8 transition-all duration-300 hover:shadow-2xl mb-8">
                <div class="relative mb-6">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="wordInput" 
                        class="w-full pl-12 pr-4 py-4 border-2 border-gray-600 rounded-xl shadow-sm
                               focus:ring-2 focus:ring-purple-500 focus:border-purple-500
                               transition-all duration-300 ease-in-out
                               text-lg bg-gray-800/50 backdrop-blur-sm text-white
                               placeholder-gray-400"
                        placeholder="Escribe una palabra o frase..."
                        autocomplete="off"
                    >
                    <!-- Sugerencias en vivo -->
                    <div id="suggestions" class="suggestions-container hidden absolute z-10 w-full mt-2 bg-gray-800 rounded-xl shadow-lg border border-gray-700">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <button 
                        id="translateWordButton" 
                        class="group relative inline-flex items-center justify-center
                               px-6 py-3 text-base font-semibold w-full sm:w-auto
                               bg-gradient-to-r from-purple-600 to-pink-600
                               text-white rounded-xl
                               transition-all duration-300 ease-in-out
                               hover:scale-105 hover:shadow-lg
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                    >
                        <span class="absolute inset-0 w-full h-full rounded-xl opacity-50 filter blur-sm bg-gradient-to-r from-purple-600 to-pink-600"></span>
                        <span class="relative flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            Traducir
                        </span>
                    </button>
                    <button 
                        id="clearButton"
                        class="inline-flex items-center justify-center
                               px-6 py-3 text-base font-semibold w-full sm:w-auto
                               border-2 border-gray-600 rounded-xl
                               text-gray-300 bg-gray-800/50
                               transition-all duration-300 ease-in-out
                               hover:bg-gray-700 hover:border-gray-500
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- Área de resultados -->
            <div id="wordOutput" class="transition-all duration-300 ease-in-out">
                <div class="text-center p-8 bg-white/5 backdrop-blur-sm rounded-xl border-2 border-dashed border-gray-700">
                    <div class="float">
                        <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <p class="text-gray-300 mb-2">Escribe una palabra o frase para ver su traducción</p>
                    <p class="text-sm text-gray-400">Las traducciones aparecerán aquí</p>
                </div>
            </div>

            <!-- Palabras sugeridas -->
            <div class="mt-8 bg-white/10 backdrop-blur-lg rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-200 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Palabras populares
                </h3>
                <div class="flex flex-wrap gap-2" id="popularWords">
                    <!-- Las palabras populares se cargarán dinámicamente aquí -->
                </div>
            </div>

            <!-- Características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                <div class="feature-card rounded-2xl bg-gray-800 p-1">
                    <div class="h-full rounded-xl bg-gradient-to-r from-purple-900/10 to-pink-900/10 p-8 ring-1 ring-purple-400/5">
                        <dt class="inline-flex items-center gap-x-3">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="text-lg font-semibold text-white">Traducción Instantánea</span>
                        </dt>
                        <dd class="mt-4 text-base leading-7 text-gray-300">
                            Resultados inmediatos mientras escribes, con sugerencias en tiempo real.
                        </dd>
                    </div>
                </div>

                <div class="feature-card rounded-2xl bg-gray-800 p-1">
                    <div class="h-full rounded-xl bg-gradient-to-r from-blue-900/10 to-purple-900/10 p-8 ring-1 ring-purple-400/5">
                        <dt class="inline-flex items-center gap-x-3">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <span class="text-lg font-semibold text-white">Frases Completas</span>
                        </dt>
                        <dd class="mt-4 text-base leading-7 text-gray-300">
                            Traduce palabras individuales o frases completas con un solo clic.
                        </dd>
                    </div>
                </div>

                <div class="feature-card rounded-2xl bg-gray-800 p-1">
                    <div class="h-full rounded-xl bg-gradient-to-r from-pink-900/10 to-rose-900/10 p-8 ring-1 ring-purple-400/5">
                        <dt class="inline-flex items-center gap-x-3">
                            <svg class="h-6 w-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="text-lg font-semibold text-white">Preciso y Confiable</span>
                        </dt>
                        <dd class="mt-4 text-base leading-7 text-gray-300">
                            Traducciones basadas en estándares reconocidos de lenguaje de señas.
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Gradient -->
        <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
            <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>
    </div>

    <!-- Modal para imagen ampliada -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button type="button" id="closeModal" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <div class="mt-2">
                                <img id="modalImage" src="" alt="" class="w-full h-auto max-h-[60vh] object-contain mx-auto">
                            </div>
                            <div class="mt-4 text-center">
                                <h3 id="modalWord" class="text-xl font-semibold text-gray-900"></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/translate_words.js"></script>
</body>
</html> 