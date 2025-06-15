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
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        .typing-effect {
            overflow: hidden;
            white-space: nowrap;
            border-right: 2px solid;
            animation: typing 3.5s steps(40, end),
                       blink-caret .75s step-end infinite;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        /* Estilos para el scroll horizontal */
        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgb(233, 213, 255);
            border-radius: 4px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgb(147, 51, 234);
            border-radius: 4px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgb(126, 34, 206);
        }

        /* Estilos para las imágenes de señas */
        .sign-image {
            @apply w-24 h-24 md:w-32 md:h-32 object-contain bg-white rounded-lg shadow-md p-2;
            @apply transition-all duration-300 ease-in-out;
            @apply hover:shadow-lg hover:scale-105;
            min-width: 6rem; /* 96px - w-24 */
        }

        .sign-container {
            @apply relative flex flex-col items-center;
        }

        .sign-label {
            @apply mt-2 text-sm text-gray-600 font-medium;
            @apply whitespace-nowrap overflow-hidden text-ellipsis;
            max-width: 100%;
        }

        @media (max-width: 768px) {
            .sign-container {
                @apply flex flex-col items-center;
                min-width: calc(50% - 1rem); /* 2 imágenes por fila en móvil con gap */
            }
        }

        @media (min-width: 769px) {
            .sign-container {
                @apply flex flex-col items-center;
                min-width: 8rem; /* 128px - w-32 */
            }
        }

        /* Ajustes para el scroll */
        .results-container .flex {
            @apply pb-2; /* Espacio para el scrollbar */
        }

        /* Ajuste para modo responsivo */
        @media (max-width: 768px) {
            .results-container .flex {
                @apply justify-center;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-white to-purple-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen pt-32 pb-24 sm:pt-40 sm:pb-32">
        <div class="container mx-auto px-4 max-w-5xl">
            <!-- Título y descripción -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Comunicación en Lenguaje de Señas
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Traduce texto a lenguaje de señas de manera sencilla y efectiva. Nuestra herramienta te ayuda a comunicarte mejor.
                </p>
            </div>

            <!-- Área de traducción -->
            <div class="bg-white rounded-2xl shadow-xl p-8 backdrop-blur-sm bg-white/80 transition-all duration-300 hover:shadow-2xl">
                <div class="mb-8">
                    <label for="inputText" class="block text-sm font-medium text-gray-700 mb-2">Texto a traducir</label>
                    <textarea 
                        id="inputText" 
                        class="w-full p-4 border border-gray-200 rounded-xl shadow-inner 
                               focus:ring-2 focus:ring-purple-500 focus:border-transparent
                               transition-all duration-300 ease-in-out
                               text-lg bg-white/50 backdrop-blur-sm
                               placeholder-gray-400"
                        placeholder="Escribe aquí el texto que deseas traducir a señas..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="flex justify-center">
                    <button 
                        id="convertButton" 
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

                <!-- Área de resultados -->
                <div id="output" class="mt-8 p-6 bg-white/50 backdrop-blur-sm rounded-xl border border-gray-100 shadow-inner transition-all duration-300 ease-in-out">
                    <!-- Estado inicial -->
                    <p class="text-gray-400 empty-state text-center py-8">Los resultados de la traducción aparecerán aquí</p>
                    
                    <!-- Contenedor de resultados con scroll horizontal -->
                    <div class="results-container hidden">
                        <div class="flex flex-wrap md:flex-nowrap overflow-x-auto gap-4 scrollbar-thin scrollbar-thumb-purple-500 scrollbar-track-purple-100">
                            <!-- Las imágenes se insertarán aquí dinámicamente -->
                        </div>
                        <!-- Indicadores de scroll solo visibles en pantallas medianas y grandes -->
                        <div class="hidden md:flex justify-between items-center mt-4 text-purple-600">
                            <button class="scroll-left p-2 rounded-full hover:bg-purple-100 transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div class="text-sm text-gray-500">
                                Desliza para ver más
                            </div>
                            <button class="scroll-right p-2 rounded-full hover:bg-purple-100 transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="text-purple-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Traducción Instantánea</h3>
                    <p class="text-gray-600">Obtén resultados inmediatos con nuestra herramienta de traducción optimizada.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="text-pink-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Personalizable</h3>
                    <p class="text-gray-600">Adapta la traducción a tus necesidades específicas de comunicación.</p>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg transition-all duration-300 hover:transform hover:scale-105">
                    <div class="text-indigo-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Preciso y Confiable</h3>
                    <p class="text-gray-600">Traducciones precisas basadas en estándares reconocidos de lenguaje de señas.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/translate.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const outputDiv = document.getElementById('output');
            const resultsContainer = outputDiv.querySelector('.results-container');
            const emptyState = outputDiv.querySelector('.empty-state');
            const scrollLeftBtn = outputDiv.querySelector('.scroll-left');
            const scrollRightBtn = outputDiv.querySelector('.scroll-right');
            const resultsScroll = resultsContainer.querySelector('.flex.overflow-x-auto');

            // Función para actualizar el estado de los botones de scroll
            function updateScrollButtons() {
                scrollLeftBtn.disabled = resultsScroll.scrollLeft <= 0;
                scrollRightBtn.disabled = resultsScroll.scrollLeft >= resultsScroll.scrollWidth - resultsScroll.clientWidth;
            }

            // Manejadores de eventos para los botones de scroll
            scrollLeftBtn.addEventListener('click', () => {
                resultsScroll.scrollBy({ left: -200, behavior: 'smooth' });
            });

            scrollRightBtn.addEventListener('click', () => {
                resultsScroll.scrollBy({ left: 200, behavior: 'smooth' });
            });

            // Escuchar el evento de scroll
            resultsScroll.addEventListener('scroll', updateScrollButtons);

            // Modificar la función que agrega imágenes al output
            window.addSignToOutput = function(letter, imgSrc) {
                emptyState.style.display = 'none';
                resultsContainer.classList.remove('hidden');
                
                const signContainer = document.createElement('div');
                signContainer.className = 'sign-container';
                
                const img = document.createElement('img');
                img.src = imgSrc;
                img.alt = `Seña para: ${letter}`;
                img.className = 'sign-image fade-in';
                
                const label = document.createElement('span');
                label.className = 'sign-label';
                label.textContent = letter.toUpperCase();
                
                signContainer.appendChild(img);
                signContainer.appendChild(label);
                
                resultsScroll.appendChild(signContainer);
                updateScrollButtons();
            };
        });
    </script>
</body>
</html>