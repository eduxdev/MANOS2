<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header id="header" class="fixed w-full inset-x-0 top-0 z-50 transition-all duration-300">
      <nav class="flex items-center justify-between p-6 lg:px-8 transition-all duration-300" id="nav-container" aria-label="Global">
        <div class="flex lg:flex-1">
          <a href="inicio.php" class="-m-1.5 p-1.5">
            <span class="sr-only">Talk Hands</span>
            <img class="h-12 w-auto rounded-lg transition-all duration-300" id="logo-img" src="./imagenes/logo2.png" alt="Logo">
          </a>
        </div>
        <div class="flex lg:hidden">
          <button id="open-menu" type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-pink-300 hover:text-pink-400">
            <span class="sr-only">Abrir menú principal</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
          <a href="inicio.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Inicio</a>
          <a href="index.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Traducir</a>
          <a href="traducir_palabras.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Traducir Palabras</a>
          <a href="reconocer_senas.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Reconocer Señas</a>
          <a href="aprender.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Aprender</a>
          <a href="sobre_nosotros.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Sobre Nosotros</a>
        </div>
      </nav>
    </header>

    <!-- Menú móvil -->
    <div id="mobile-menu" class="fixed inset-y-0 left-0 -translate-x-full transform transition-transform duration-300 ease-in-out w-64 z-50 bg-gradient-to-br from-blue-900/95 to-purple-900/95 backdrop-blur-sm">
      <div class="flex items-center justify-between p-6">
        <a href="#" class="-m-1.5 p-1.5">
          <span class="sr-only">Talk Hands</span>
          <img class="h-8 w-auto" src="./imagenes/logo1.png" alt="Logo">
        </a>
        <button id="close-menu" type="button" class="rounded-md p-2.5 text-pink-300 hover:text-pink-400">
          <span class="sr-only">Cerrar menú</span>
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="mt-6 flow-root px-6">
        <div class="-my-6 divide-y divide-pink-300/10">
          <div class="space-y-2 py-6">
            <a href="inicio.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Inicio</a>
            <a href="index.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Traducir</a>
            <a href="traducir_palabras.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Traducir Palabras</a>
            <a href="reconocer_senas.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Reconocer Señas</a>
            <a href="aprender.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Aprender</a>
            <a href="sobre_nosotros.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Sobre Nosotros</a>
          </div>
        </div>
      </div>
    </div>
    <script src="/assets/js/menu.js"></script>
</body>
</html>