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
                        <a href="traducir.php" class="group relative inline-flex items-center justify-center rounded-full bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 text-lg font-semibold text-white transition-all duration-200 ease-in-out hover:scale-105 hover:shadow-lg pulse-button">
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
                        <img src="/imagenes/hero1.png" alt="Lenguaje de señas 1" class="absolute -top-10 left-0 w-48 rounded-lg shadow-xl float-animation">
                        <img src="/imagenes/hero2.png" alt="Lenguaje de señas 2" class="absolute top-20 right-0 w-56 rounded-lg shadow-xl float-animation float-animation-delay-1">
                        
                    </div>
                </div>
            </div>

            <!-- Bottom Gradient -->
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
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
