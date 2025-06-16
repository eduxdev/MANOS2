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
          <a href="/frontend/inicio.php" class="-m-1.5 p-1.5">
            <span class="sr-only">Talk Hands</span>
            <img class="h-12 w-auto rounded-lg transition-all duration-300" id="logo-img" src="/imagenes/logo2.png" alt="Logo">
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
          <a href="/frontend/inicio.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Inicio</a>
          <a href="/frontend/traducir.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Traducir</a>
          <a href="/frontend/traducir_palabras.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Traducir Palabras</a>
          <a href="/frontend/aprender.php" class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">Aprender</a>
        </div>
        <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:items-center lg:gap-x-4">
          <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Si el usuario está logueado -->
            <div class="flex items-center gap-x-4">
              <a href="<?php echo getDashboardPath($_SESSION['rol']); ?>" 
                 class="text-sm font-semibold text-pink-300 hover:text-pink-400 transition">
                Dashboard
              </a>
              <span class="text-sm font-medium text-pink-300">
                <?php echo htmlspecialchars($_SESSION['nombre']); ?>
              </span>
              <a href="/backend/auth/logout.php" 
                 class="rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-all duration-300">
                Cerrar Sesión
              </a>
            </div>
          <?php else: ?>
            <!-- Si el usuario no está logueado -->
            <a href="/frontend/auth/login.php" 
               class="rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600 transition-all duration-300">
              Iniciar Sesión
            </a>
          <?php endif; ?>
        </div>
      </nav>
    </header>

    <!-- Menú móvil -->
    <div id="mobile-menu" class="fixed inset-y-0 left-0 -translate-x-full transform transition-transform duration-300 ease-in-out w-64 z-50 bg-gradient-to-br from-blue-900/95 to-purple-900/95 backdrop-blur-sm">
      <div class="flex items-center justify-between p-6">
        <a href="#" class="-m-1.5 p-1.5">
          <span class="sr-only">Talk Hands</span>
          <img class="h-8 w-auto" src="/imagenes/logo1.png" alt="Logo">
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
            <a href="/frontend/inicio.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Inicio</a>
            <a href="/frontend/traducir.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Traducir</a>
            <a href="/frontend/traducir_palabras.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Traducir Palabras</a>
            <a href="/frontend/aprender.php" class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">Aprender</a>
            <?php if(isset($_SESSION['user_id'])): ?>
              <div class="pt-4 space-y-2">
                <a href="<?php echo getDashboardPath($_SESSION['rol']); ?>" 
                   class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">
                  Dashboard
                </a>
                <span class="block px-3 py-2 text-base font-medium text-pink-300">
                  <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </span>
                <a href="/backend/auth/logout.php" 
                   class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">
                  Cerrar Sesión
                </a>
              </div>
            <?php else: ?>
              <a href="/frontend/auth/login.php" 
                 class="block rounded-lg px-3 py-2 text-base font-semibold text-pink-300 hover:bg-pink-300/10 transition">
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