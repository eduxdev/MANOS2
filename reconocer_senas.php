<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Reconocer Señas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.18.0/dist/tf.min.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            border-radius: 1rem;
            overflow: hidden;
        }
        #videoElement {
            width: 100%;
            transform: scaleX(-1); /* Efecto espejo */
            border-radius: 1rem;
        }
        .detection-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Reconocer Señas
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Haz señas frente a la cámara y traduce automáticamente al lenguaje escrito
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 backdrop-blur-sm bg-white/80 transition-all duration-300 hover:shadow-2xl mb-12">
                <!-- Configuración y controles de la cámara -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Cámara en vivo</h2>
                        <div class="flex space-x-2">
                            <button id="startCamera" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Iniciar Cámara
                            </button>
                            <button id="stopCamera" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors" disabled>
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                </svg>
                                Detener
                            </button>
                        </div>
                    </div>
                    
                    <!-- Vista de la cámara -->
                    <div class="camera-container bg-gray-100">
                        <video id="videoElement" autoplay playsinline></video>
                        <canvas id="detectionOverlay" class="detection-overlay"></canvas>
                        <div id="noCamera" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-xl">
                            <div class="text-center p-6">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-600 text-lg">Haz clic en "Iniciar Cámara" para comenzar el reconocimiento de señas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicador de estado y modo -->
                <div class="flex flex-col md:flex-row items-center justify-between mb-8 bg-purple-50 p-4 rounded-xl">
                    <div class="flex items-center mb-4 md:mb-0">
                        <div id="statusIndicator" class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div>
                        <span id="statusText" class="text-gray-600">Esperando cámara...</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="modeSelector" class="text-gray-700">Modo de detección:</label>
                        <select id="modeSelector" class="border border-purple-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="alphabet">Alfabeto manual</option>
                            <option value="words">Palabras comunes</option>
                        </select>
                    </div>
                </div>

                <!-- Resultado de la traducción -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Texto reconocido:</h3>
                    <div class="flex items-center space-x-2">
                        <div id="resultText" class="flex-1 min-h-16 p-4 bg-white border border-gray-200 rounded-xl shadow-inner">
                            <p class="text-gray-400 italic">El texto reconocido aparecerá aquí...</p>
                        </div>
                        <button id="clearButton" class="p-2 text-gray-500 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors" disabled>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-8">
                    <h3 class="text-blue-800 font-semibold mb-2">Cómo usar el reconocimiento de señas:</h3>
                    <ol class="list-decimal list-inside text-blue-700 space-y-1">
                        <li>Asegúrate de estar bien iluminado y frente a la cámara</li>
                        <li>Selecciona el modo de detección (alfabeto o palabras)</li>
                        <li>Realiza las señas de forma clara y a una distancia adecuada</li>
                        <li>Mantén cada seña durante 1-2 segundos para mejor reconocimiento</li>
                        <li>El texto reconocido aparecerá automáticamente</li>
                    </ol>
                </div>
            </div>

            <!-- Sección de características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-purple-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Reconocimiento en Tiempo Real</h3>
                    <p class="text-gray-600 text-center">Detecta tus señas al instante con tecnología avanzada de visión por computadora.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-pink-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Múltiples Modos</h3>
                    <p class="text-gray-600 text-center">Reconoce tanto el alfabeto manual como palabras completas en lenguaje de señas.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="flex justify-center text-indigo-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">Traducción a Voz</h3>
                    <p class="text-gray-600 text-center">Convierte automáticamente las señas reconocidas en texto y voz para una comunicación efectiva.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/recognize_signs.js"></script>
</body>
</html> 