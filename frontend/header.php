<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función auxiliar para obtener la ruta del dashboard según el rol
function getDashboardPath($rol) {
    switch($rol) {
        case 'estudiante':
            return '/frontend/student/dashboard.php';
        case 'profesor':
            return '/frontend/teacher/dashboard.php';
        case 'administrador':
            return '/frontend/admin/dashboard.php';
        default:
            return '/frontend/inicio.php';
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900">
    <header id="header" class="fixed w-full inset-x-0 top-0 z-50 transition-all duration-300 bg-gray-900/80 backdrop-blur-lg">
      <nav class="flex items-center justify-between p-6 lg:px-8 transition-all duration-300" id="nav-container" aria-label="Global">
        <div class="flex lg:flex-1">
          <a href="/frontend/inicio.php" class="-m-1.5 p-1.5">
            <span class="sr-only">Talk Hands</span>
            <img class="h-12 w-auto rounded-lg transition-all duration-300" id="logo-img" src="/imagenes/logo2.png" alt="Logo">
          </a>
        </div>
        <div class="flex lg:hidden">
          <button id="open-menu" type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-blue-200 hover:text-blue-300">
            <span class="sr-only">Abrir menú principal</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>
        <div class="hidden lg:flex lg:gap-x-12">
          <a href="/frontend/inicio.php" class="text-sm font-semibold text-gray-300 hover:text-gray-100 transition">Inicio</a>
          <a href="/frontend/traducir.php" class="text-sm font-semibold text-gray-300 hover:text-gray-100 transition">Traducir</a>
          <a href="/frontend/traducir_palabras.php" class="text-sm font-semibold text-gray-300 hover:text-gray-100 transition">Traducir Palabras</a>
          <a href="/frontend/aprender.php" class="text-sm font-semibold text-gray-300 hover:text-gray-100 transition">Aprender</a>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center lg:gap-x-4">
          <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Si el usuario está logueado -->
            <div class="flex items-center gap-x-4">
              <a href="<?php echo getDashboardPath($_SESSION['rol']); ?>" 
                 class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800/50 hover:bg-gray-700/50 transition-all duration-300">
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="text-sm font-semibold text-gray-300">Dashboard</span>
              </a>
              <div class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gray-800/50">
                <div class="relative">
                  <div class="w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold text-[10px]">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                  </div>
                  <?php if($_SESSION['rol'] === 'profesor'): ?>
                    <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-purple-500 flex items-center justify-center">
                      <svg class="w-1 h-1 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-300">
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                  </span>
                </div>
              </div>
              <a href="/backend/auth/logout.php" 
                 class="rounded-full bg-purple-600 hover:bg-purple-500 px-4 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 transition-all duration-300">
                Cerrar Sesión
              </a>
            </div>
          <?php else: ?>
            <!-- Si el usuario no está logueado -->
            <a href="/frontend/auth/login.php" 
               class="rounded-full bg-purple-600 hover:bg-purple-500 px-4 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 transition-all duration-300">
              Iniciar Sesión
            </a>
          <?php endif; ?>
        </div>
      </nav>
    </header>

    <!-- Menú móvil -->
    <div id="mobile-menu" class="fixed inset-0 -translate-x-full transform transition-transform duration-300 ease-in-out w-full z-50 bg-gray-900/95 backdrop-blur-sm">
      <div class="flex items-center justify-between p-6">
        <a href="#" class="-m-1.5 p-1.5">
          <span class="sr-only">Talk Hands</span>
          <img class="h-12 w-auto" src="/imagenes/logo1.png" alt="Logo">
        </a>
        <button id="close-menu" type="button" class="rounded-md p-2.5 text-gray-300 hover:text-gray-100">
          <span class="sr-only">Cerrar menú</span>
          <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="mt-6 flow-root px-6">
        <div class="-my-6 divide-y divide-gray-700">
          <div class="space-y-4 py-6">
            <a href="/frontend/inicio.php" class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Inicio
            </a>
            <a href="/frontend/traducir.php" class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
              </svg>
              Traducir
            </a>
            <a href="/frontend/traducir_palabras.php" class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
              Traducir Palabras
            </a>
            <a href="/frontend/aprender.php" class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Aprender
            </a>
            <?php if(isset($_SESSION['user_id'])): ?>
              <div class="pt-6 space-y-4 border-t border-gray-700">
                <a href="<?php echo getDashboardPath($_SESSION['rol']); ?>" 
                   class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
                  <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                  </svg>
                  Dashboard
                </a>
                <div class="flex items-center gap-3 px-4 py-3">
                  <div class="relative">
                    <div class="w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold text-[10px]">
                      <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                    </div>
                    <?php if($_SESSION['rol'] === 'profesor'): ?>
                      <div class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-purple-500 flex items-center justify-center">
                        <svg class="w-1 h-1 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-lg font-medium text-gray-300">
                      <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </span>
                    <span class="text-sm text-gray-400 font-medium capitalize">
                      <?php echo $_SESSION['rol']; ?>
                    </span>
                  </div>
                </div>
                <a href="/backend/auth/logout.php" 
                   class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
                  <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Cerrar Sesión
                </a>
              </div>
            <?php else: ?>
              <a href="/frontend/auth/login.php" 
                 class="block rounded-lg px-4 py-3 text-lg font-semibold text-gray-300 hover:bg-gray-800/50 transition flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Iniciar Sesión
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <script src="/assets/js/menu.js"></script>
</body>
</html>