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
        .float-animation-delay-1 {
            animation-delay: 1s;
        }
        .float-animation-delay-2 {
            animation-delay: 2s;
        }
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(167, 139, 250, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(167, 139, 250, 0); }
            100% { box-shadow: 0 0 0 0 rgba(167, 139, 250, 0); }
        }
        .pulse-button {
            animation: pulse-border 2s infinite;
        }
    </style>
</head>
<body>
    
<?php include 'header.php'; ?>
    <!-- Hero Section -->
    <div class="bg-white">
        <div class="relative isolate min-h-screen">
            <!-- Gradient Background -->
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <!-- Hero Content -->
            <div class="mx-auto max-w-7xl px-6 pt-32 pb-24 sm:pt-40 sm:pb-32 lg:flex lg:items-center lg:gap-x-10 lg:px-8 lg:pt-48 lg:pb-40">
                <div class="mx-auto max-w-2xl lg:mx-0 lg:flex-auto">
                    <h1 class="max-w-lg text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                        Descubre el mundo del 
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                            Lenguaje de Señas
                        </span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        En Talk Hands, promovemos la inclusión y el aprendizaje del lenguaje de señas para conectar comunidades y enriquecer vidas. Aprende, traduce y explora con nosotros.
                    </p>
                    <div class="mt-10 flex items-center gap-x-6">
                        <a href="index.php" class="group relative inline-flex items-center justify-center rounded-full bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 text-lg font-semibold text-white transition-all duration-200 ease-in-out hover:scale-105 hover:shadow-lg pulse-button">
                            Comenzar
                            <svg class="ml-2 h-5 w-5 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="aprender.php" class="group text-lg font-semibold leading-6 text-gray-900 transition-all duration-200 ease-in-out hover:text-purple-600">
                            Aprender más 
                            <span class="inline-block transform transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
                <div class="mt-16 sm:mt-24 lg:mt-0 lg:flex-shrink-0 lg:flex-grow">
                    <div class="relative mx-auto w-full max-w-lg lg:max-w-xl">
                        <!-- Imágenes flotantes -->
                        <img src="./imagenes/hero1.png" alt="Lenguaje de señas 1" class="absolute -top-10 left-0 w-48 rounded-lg shadow-xl float-animation">
                        <img src="./imagenes/hero2.png" alt="Lenguaje de señas 2" class="absolute top-20 right-0 w-56 rounded-lg shadow-xl float-animation float-animation-delay-1">
                        
                    </div>
                </div>
            </div>

            <!-- Bottom Gradient -->
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
        </div>
    </div>

    <!-- Nueva Sección: Reconocimiento de Señas -->
    <div class="bg-gradient-to-b from-white to-purple-50 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl sm:text-center">
                <h2 class="text-base font-semibold leading-7 text-purple-600">¡NUEVO!</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Reconocimiento de Señas en Tiempo Real
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Ahora las personas sin habla pueden comunicarse fácilmente utilizando nuestro revolucionario sistema de reconocimiento de lenguaje de señas.
                </p>
            </div>
            
            <div class="mt-16 sm:mt-20 lg:mt-24 relative">
                <div class="mx-auto max-w-4xl rounded-2xl overflow-hidden shadow-xl ring-1 ring-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <!-- Imagen de demostración -->
                        <div class="bg-purple-900 p-8 flex items-center justify-center">
                            <div class="relative w-full h-64 md:h-80 overflow-hidden rounded-xl">
                                <div class="absolute inset-0 flex items-center justify-center bg-purple-800/30 backdrop-blur-sm rounded-xl">
                                    <svg class="w-24 h-24 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <div class="absolute bottom-4 right-4 bg-purple-600 text-white text-xs px-3 py-1 rounded-full">
                                    Reconociendo...
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información y botón -->
                        <div class="bg-white p-8 flex flex-col justify-center">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-4">
                                Comunícate con señas
                            </h3>
                            <ul class="space-y-3 text-gray-600">
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-purple-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Traducción automática de señas a texto</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-purple-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Reconocimiento en tiempo real con la cámara</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-purple-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Compatible con alfabeto manual y palabras completas</span>
                                </li>
                            </ul>
                            
                            <div class="mt-8">
                                <a href="reconocer_senas.php" class="group inline-flex items-center justify-center rounded-md bg-purple-600 px-5 py-3 text-base font-medium text-white shadow-md transition-all duration-200 hover:bg-purple-700 hover:shadow-lg">
                                    Probar ahora
                                    <svg class="ml-2 h-5 w-5 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Insignia de novedad -->
                <div class="absolute -top-4 -right-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white px-4 py-2 rounded-full shadow-lg transform rotate-12 z-10">
                    <span class="text-xs font-bold tracking-wider">¡NOVEDAD!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-32 sm:py-40">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-base font-semibold leading-7 text-purple-600">Explora más</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Todo lo que necesitas para aprender lenguaje de señas
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Descubre herramientas, recursos y guías para dominar el lenguaje de señas y conectar con una comunidad inclusiva.
                </p>
            </div>
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                    <div class="group flex flex-col transition-all duration-300 hover:transform hover:scale-105">
                        <div class="rounded-lg bg-purple-50 p-6 ring-1 ring-purple-900/10">
                            <dt class="text-base font-semibold leading-7 text-purple-600">
                                Aprende a tu ritmo
                            </dt>
                            <dd class="mt-3 text-base leading-7 text-gray-600">
                                Accede a recursos interactivos y guías paso a paso para dominar el lenguaje de señas.
                            </dd>
                        </div>
                    </div>
                    <div class="group flex flex-col transition-all duration-300 hover:transform hover:scale-105">
                        <div class="rounded-lg bg-pink-50 p-6 ring-1 ring-pink-900/10">
                            <dt class="text-base font-semibold leading-7 text-pink-600">
                                Comunidad inclusiva
                            </dt>
                            <dd class="mt-3 text-base leading-7 text-gray-600">
                                Conéctate con personas que comparten tu interés por el lenguaje de señas y aprende juntos.
                            </dd>
                        </div>
                    </div>
                    <div class="group flex flex-col transition-all duration-300 hover:transform hover:scale-105">
                        <div class="rounded-lg bg-indigo-50 p-6 ring-1 ring-indigo-900/10">
                            <dt class="text-base font-semibold leading-7 text-indigo-600">
                                Recursos avanzados
                            </dt>
                            <dd class="mt-3 text-base leading-7 text-gray-600">
                                Explora materiales avanzados para perfeccionar tus habilidades y enseñar a otros.
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <dl class="grid grid-cols-1 gap-x-8 gap-y-16 text-center lg:grid-cols-3">
                <div class="group mx-auto flex max-w-xs flex-col gap-y-4 transition-all duration-300 hover:transform hover:scale-105">
                    <dt class="text-base leading-7 text-gray-600">Personas con discapacidad auditiva en México</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-purple-600 sm:text-5xl">2.4 millones</dd>
                </div>
                <div class="group mx-auto flex max-w-xs flex-col gap-y-4 transition-all duration-300 hover:transform hover:scale-105">
                    <dt class="text-base leading-7 text-gray-600">Personas que usan lenguaje de señas</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-pink-600 sm:text-5xl">700,000</dd>
                </div>
                <div class="group mx-auto flex max-w-xs flex-col gap-y-4 transition-all duration-300 hover:transform hover:scale-105">
                    <dt class="text-base leading-7 text-gray-600">Comunidades activas en México</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-indigo-600 sm:text-5xl">150+</dd>
                </div>
            </dl>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="/assets/js/menu.js"></script>
</body>
</html>
