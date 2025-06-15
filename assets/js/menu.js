document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('header');
    const navContainer = document.getElementById('nav-container');
    const logoImg = document.getElementById('logo-img');
    const scrollThreshold = 50;
    const openMenuButton = document.getElementById('open-menu');
    const closeMenuButton = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');
    
    // Función para actualizar el estilo del header al hacer scroll
    function updateHeaderStyle() {
        if (window.scrollY > scrollThreshold) {
            header.classList.add('bg-gradient-to-r', 'from-blue-900/90', 'to-purple-900/90', 'backdrop-blur-sm', 'shadow-lg');
            header.classList.remove('bg-transparent');
            
            // Reducir el padding y el tamaño del logo
            navContainer.classList.remove('p-6', 'lg:px-8');
            navContainer.classList.add('py-2', 'px-4', 'lg:px-4');
            logoImg.classList.remove('h-12');
            logoImg.classList.add('h-8');
        } else {
            header.classList.remove('bg-gradient-to-r', 'from-blue-900/90', 'to-purple-900/90', 'backdrop-blur-sm', 'shadow-lg');
            header.classList.add('bg-transparent');
            
            // Restaurar el padding y el tamaño del logo
            navContainer.classList.add('p-6', 'lg:px-8');
            navContainer.classList.remove('py-2', 'px-4', 'lg:px-4');
            logoImg.classList.add('h-12');
            logoImg.classList.remove('h-8');
        }
    }
    
    // Verificar al cargar
    updateHeaderStyle();
    
    // Verificar al hacer scroll
    window.addEventListener('scroll', updateHeaderStyle);
    
    // Gestionar el menú móvil
    openMenuButton.addEventListener('click', () => {
        mobileMenu.classList.remove('-translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden');
    });
    
    closeMenuButton.addEventListener('click', () => {
        mobileMenu.classList.remove('translate-x-0');
        mobileMenu.classList.add('-translate-x-full');
        document.body.classList.remove('overflow-hidden');
    });
});
  