<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Aprende Lenguaje de Señas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .scroll-down {
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
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
<body class="bg-white">
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="relative isolate min-h-screen flex flex-col justify-center overflow-hidden">
        <!-- Background Gradient -->
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 sm:py-24">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                <!-- Texto del Hero -->
                <div class="col-span-6 text-center sm:text-left">
                    <div class="fade-in" style="animation-delay: 0.2s">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-gray-900 mb-6">
                            Aprende lenguaje de señas de forma <span class="gradient-text">interactiva</span>
                        </h1>
                        <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-3xl">
                            Descubre una nueva forma de comunicarte. Nuestra plataforma te ayuda a aprender lenguaje de señas de manera divertida y efectiva.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center sm:justify-start">
                            <a href="auth/register.php" 
                               class="w-full sm:w-auto rounded-xl bg-purple-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 transition-all duration-300 hover:scale-105">
                                Comienza ahora
                            </a>
                            <a href="#features" 
                               class="w-full sm:w-auto text-sm font-semibold leading-6 text-gray-900 hover:text-purple-600 transition-colors duration-300 flex items-center justify-center">
                                Aprende más <span aria-hidden="true" class="ml-2">→</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- SVG Animado -->
                <div class="col-span-6 mt-8 lg:mt-0">
                    <div class="relative w-full max-w-[450px] mx-auto aspect-square">
                        <svg class="w-full h-full" viewBox="0 0 450 450" preserveAspectRatio="xMidYMid meet">
                            <!-- Gradientes -->
                            <defs>
                                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#c084fc;stop-opacity:0.2" />
                                    <stop offset="100%" style="stop-color:#818cf8;stop-opacity:0.2" />
                                </linearGradient>
                                <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#e879f9;stop-opacity:0.2" />
                                    <stop offset="100%" style="stop-color:#c084fc;stop-opacity:0.2" />
                                </linearGradient>
                                <linearGradient id="grad3" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#818cf8;stop-opacity:0.2" />
                                    <stop offset="100%" style="stop-color:#e879f9;stop-opacity:0.2" />
                                </linearGradient>
                            </defs>

                            <!-- Formas animadas de fondo -->
                            <g class="circle-animation">
                                <circle cx="225" cy="225" r="150" fill="url(#grad1)" />
                            </g>
                            <g class="blob">
                                <path d="M100,100 C150,50 250,50 300,100 C350,150 350,250 300,300 C250,350 150,350 100,300 C50,250 50,150 100,100" fill="url(#grad2)" />
                            </g>
                            <g class="blob" style="animation-delay: -5s">
                                <path d="M150,150 C200,100 300,100 350,150 C400,200 400,300 350,350 C300,400 200,400 150,350 C100,300 100,200 150,150" fill="url(#grad3)" />
                            </g>

                            <!-- Mano central -->
                            <g class="hand-wave" transform="translate(175, 175)">
                                <!-- Palma -->
                                <path d="M50,0 C77.6,0 100,22.4 100,50 L100,150 C100,177.6 77.6,200 50,200 C22.4,200 0,177.6 0,150 L0,50 C0,22.4 22.4,0 50,0" 
                                      fill="#6366f1" />
                                <!-- Dedos -->
                                <path d="M40,0 L60,0 L60,80 L40,80 Z" fill="#4338ca" />
                                <path d="M70,20 L90,20 L90,100 L70,100 Z" fill="#4338ca" />
                                <path d="M10,20 L30,20 L30,100 L10,100 Z" fill="#4338ca" />
                                <path d="M100,50 L120,50 L120,130 L100,130 Z" fill="#4338ca" />
                                <!-- Detalles -->
                                <circle cx="50" cy="120" r="15" fill="#4338ca" />
                            </g>

                            <!-- Texto animado -->
                            <text class="fade-in hidden sm:block" style="animation-delay: 0.5s" x="225" y="50" text-anchor="middle" fill="#6366f1" font-size="24" font-weight="bold">
                                Talk Hands
                            </text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Down Indicator -->
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 text-center hidden sm:block">
            <a href="#features" class="scroll-down inline-flex flex-col items-center text-gray-500 hover:text-purple-600 transition-colors duration-300">
                <span class="text-sm font-medium mb-2">Descubre más</span>
                <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </a>
        </div>

        <!-- Bottom Gradient -->
        <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
            <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center fade-in" style="animation-delay: 0.4s">
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
                    <!-- Tarjeta 1 -->
                    <div class="feature-card rounded-2xl bg-white p-1">
                        <div class="h-full rounded-xl bg-gradient-to-r from-purple-50 to-pink-50 p-8 ring-1 ring-purple-900/5">
                            <dt class="inline-flex items-center gap-x-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                </svg>
                                <span class="text-lg font-semibold text-gray-900">Aprende a tu ritmo</span>
                            </dt>
                            <dd class="mt-4 text-base leading-7 text-gray-600">
                                Accede a recursos interactivos y guías paso a paso para dominar el lenguaje de señas a tu propio ritmo.
                            </dd>
                        </div>
                    </div>

                    <!-- Tarjeta 2 -->
                    <div class="feature-card rounded-2xl bg-white p-1">
                        <div class="h-full rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 p-8 ring-1 ring-purple-900/5">
                            <dt class="inline-flex items-center gap-x-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span class="text-lg font-semibold text-gray-900">Comunidad activa</span>
                            </dt>
                            <dd class="mt-4 text-base leading-7 text-gray-600">
                                Únete a una comunidad vibrante de estudiantes y profesores comprometidos con el aprendizaje del lenguaje de señas.
                            </dd>
                        </div>
                    </div>

                    <!-- Tarjeta 3 -->
                    <div class="feature-card rounded-2xl bg-white p-1">
                        <div class="h-full rounded-xl bg-gradient-to-r from-pink-50 to-rose-50 p-8 ring-1 ring-purple-900/5">
                            <dt class="inline-flex items-center gap-x-3">
                                <svg class="h-6 w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.7c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.7c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                                </svg>
                                <span class="text-lg font-semibold text-gray-900">Logros y progreso</span>
                            </dt>
                            <dd class="mt-4 text-base leading-7 text-gray-600">
                                Gana insignias, sigue tu progreso y celebra tus logros mientras dominas nuevas habilidades.
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
