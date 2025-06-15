<?php include 'header.php'; ?>

<main class="pt-24 lg:pt-28 pb-16 overflow-hidden">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-b from-blue-900 to-purple-900 text-white py-16">
        <div class="absolute inset-0 overflow-hidden">
            <svg class="absolute bottom-0 left-0 w-full h-64 text-white" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="currentColor" d="M0,192L48,208C96,224,192,256,288,250.7C384,245,480,203,576,192C672,181,768,203,864,224C960,245,1056,267,1152,250.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-pink-300 to-purple-300 bg-clip-text text-transparent">
                    Conoce a Talk Hands
                </h1>
                <p class="text-lg md:text-xl text-pink-200 max-w-3xl mx-auto">
                    Estamos transformando la comunicación entre personas sordas y oyentes a través de tecnología innovadora y accesible para todos.
                </p>
            </div>
        </div>
    </div>

    <!-- Nuestra Misión -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1">
                    <h2 class="text-3xl font-bold mb-6 text-blue-900">Nuestra Misión</h2>
                    <p class="text-gray-700 mb-6">
                        En <span class="font-semibold text-purple-800">Talk Hands</span>, nos dedicamos a eliminar las barreras de comunicación para la comunidad sorda. Nuestra misión es crear un mundo donde el lenguaje de señas sea accesible para todos, facilitando la inclusión y el entendimiento mutuo.
                    </p>
                    <p class="text-gray-700">
                        Creemos que la tecnología debe servir como puente, no como obstáculo. Por eso hemos desarrollado herramientas intuitivas que permiten tanto la traducción de texto a señas como el reconocimiento de señas a texto.
                    </p>
                </div>
                <div class="order-1 md:order-2 flex justify-center">
                    <div class="rounded-lg overflow-hidden shadow-2xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        <img src="imagenes/mision.jpg" alt="Nuestra misión" class="w-full h-auto max-w-md" onerror="this.src='https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestro Equipo -->
    <section class="py-16 bg-gradient-to-r from-blue-50 to-purple-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-blue-900 mb-4">Nuestro Equipo</h2>
                <p class="text-gray-700 max-w-3xl mx-auto">
                    Somos un grupo apasionado de desarrolladores, diseñadores e intérpretes de lenguaje de señas unidos por el objetivo de hacer el mundo más accesible e inclusivo.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Miembro 1 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="imagenes/team1.jpg" alt="María Rodríguez" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-blue-900 mb-2">María Rodríguez</h3>
                        <p class="text-pink-600 font-medium mb-3">Fundadora & CEO</p>
                        <p class="text-gray-600">Intérprete de lenguaje de señas con más de 10 años de experiencia, apasionada por la accesibilidad digital.</p>
                    </div>
                </div>
                
                <!-- Miembro 2 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="imagenes/team2.jpg" alt="Carlos Vega" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-blue-900 mb-2">Carlos Vega</h3>
                        <p class="text-pink-600 font-medium mb-3">CTO & Desarrollador</p>
                        <p class="text-gray-600">Ingeniero en IA especializado en visión por computadora y reconocimiento de patrones de movimiento.</p>
                    </div>
                </div>
                
                <!-- Miembro 3 -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:-translate-y-2">
                    <div class="h-60 overflow-hidden">
                        <img src="imagenes/team3.jpg" alt="Ana Mendoza" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-blue-900 mb-2">Ana Mendoza</h3>
                        <p class="text-pink-600 font-medium mb-3">Diseñadora UX</p>
                        <p class="text-gray-600">Especialista en interfaces accesibles con experiencia en diseño universal y experiencia de usuario inclusiva.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestra Historia -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-blue-900 mb-8 text-center">Nuestra Historia</h2>
                
                <!-- Timeline -->
                <div class="relative">
                    <!-- Línea vertical -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-gradient-to-b from-blue-400 to-purple-500"></div>
                    
                    <!-- Item 1 -->
                    <div class="relative mb-16">
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8 text-right">
                                <h3 class="text-xl font-bold text-blue-900 mb-2">2020</h3>
                                <p class="text-gray-700">Nace la idea de Talk Hands a partir de la experiencia personal de nuestra fundadora con familiares sordos.</p>
                            </div>
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white rounded-full border-4 border-pink-400 shadow">
                                <div class="w-4 h-4 bg-pink-400 rounded-full"></div>
                            </div>
                            <div class="w-1/2 pl-8"></div>
                        </div>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="relative mb-16">
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8"></div>
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white rounded-full border-4 border-pink-400 shadow">
                                <div class="w-4 h-4 bg-pink-400 rounded-full"></div>
                            </div>
                            <div class="w-1/2 pl-8">
                                <h3 class="text-xl font-bold text-blue-900 mb-2">2021</h3>
                                <p class="text-gray-700">Formamos nuestro equipo inicial y comenzamos el desarrollo del primer prototipo de reconocimiento de señas.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="relative mb-16">
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8 text-right">
                                <h3 class="text-xl font-bold text-blue-900 mb-2">2022</h3>
                                <p class="text-gray-700">Lanzamos la primera versión beta de Talk Hands con capacidades básicas de traducción.</p>
                            </div>
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white rounded-full border-4 border-pink-400 shadow">
                                <div class="w-4 h-4 bg-pink-400 rounded-full"></div>
                            </div>
                            <div class="w-1/2 pl-8"></div>
                        </div>
                    </div>
                    
                    <!-- Item 4 -->
                    <div class="relative">
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8"></div>
                            <div class="z-10 flex items-center justify-center w-12 h-12 bg-white rounded-full border-4 border-pink-400 shadow">
                                <div class="w-4 h-4 bg-pink-400 rounded-full"></div>
                            </div>
                            <div class="w-1/2 pl-8">
                                <h3 class="text-xl font-bold text-blue-900 mb-2">Hoy</h3>
                                <p class="text-gray-700">Continuamos expandiendo nuestras capacidades y trabajando para hacer que el lenguaje de señas sea accesible para todos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section class="py-16 bg-gradient-to-r from-blue-900 to-purple-900 text-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4 bg-gradient-to-r from-pink-300 to-purple-300 bg-clip-text text-transparent">
                    Lo Que Dicen Nuestros Usuarios
                </h2>
                <p class="text-pink-200 max-w-3xl mx-auto">
                    Talk Hands ha transformado la vida de muchas personas, facilitando la comunicación y creando puentes entre comunidades.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonio 1 -->
                <div class="bg-white/10 backdrop-blur-sm p-8 rounded-xl shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="imagenes/user1.jpg" alt="Usuario" class="h-full w-full object-cover" onerror="this.src='https://randomuser.me/api/portraits/women/44.jpg'">
                        </div>
                        <div>
                            <h3 class="font-bold text-pink-300">Laura García</h3>
                            <p class="text-xs text-pink-200">Estudiante</p>
                        </div>
                    </div>
                    <p class="text-pink-100 italic">
                        "Como estudiante de interpretación, Talk Hands ha sido una herramienta invaluable para practicar y mejorar mis habilidades de lenguaje de señas."
                    </p>
                </div>
                
                <!-- Testimonio 2 -->
                <div class="bg-white/10 backdrop-blur-sm p-8 rounded-xl shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="imagenes/user2.jpg" alt="Usuario" class="h-full w-full object-cover" onerror="this.src='https://randomuser.me/api/portraits/men/32.jpg'">
                        </div>
                        <div>
                            <h3 class="font-bold text-pink-300">Roberto Méndez</h3>
                            <p class="text-xs text-pink-200">Profesor</p>
                        </div>
                    </div>
                    <p class="text-pink-100 italic">
                        "Como profesor de estudiantes sordos, esta plataforma ha revolucionado la forma en que puedo comunicarme con ellos y hacer mis clases más inclusivas."
                    </p>
                </div>
                
                <!-- Testimonio 3 -->
                <div class="bg-white/10 backdrop-blur-sm p-8 rounded-xl shadow-lg">
                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full overflow-hidden mr-4">
                            <img src="imagenes/user3.jpg" alt="Usuario" class="h-full w-full object-cover" onerror="this.src='https://randomuser.me/api/portraits/women/68.jpg'">
                        </div>
                        <div>
                            <h3 class="font-bold text-pink-300">Sofía Torres</h3>
                            <p class="text-xs text-pink-200">Persona sorda</p>
                        </div>
                    </div>
                    <p class="text-pink-100 italic">
                        "Talk Hands me ha permitido comunicarme más fácilmente en situaciones cotidianas, especialmente en entornos donde no hay intérpretes disponibles."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-blue-900 mb-4">Contáctanos</h2>
                    <p class="text-gray-700">
                        ¿Tienes preguntas, sugerencias o quieres colaborar con nosotros? ¡Nos encantaría saber de ti!
                    </p>
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-8 rounded-xl shadow-lg">
                    <form class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
                            </div>
                        </div>
                        <div>
                            <label for="asunto" class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                            <input type="text" id="asunto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>
                        <div>
                            <label for="mensaje" class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
                            <textarea id="mensaje" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-400"></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg shadow-lg transition duration-300 transform hover:-translate-y-1">
                                Enviar Mensaje
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?> 