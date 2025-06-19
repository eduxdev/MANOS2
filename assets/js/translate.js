document.addEventListener('DOMContentLoaded', () => {
    const outputDiv = document.getElementById('output');
    const resultsContainer = outputDiv.querySelector('.results-container');
    const emptyState = outputDiv.querySelector('.empty-state');
    const resultsScroll = resultsContainer.querySelector('.flex.overflow-x-auto');
    const scrollLeftBtn = outputDiv.querySelector('.scroll-left');
    const scrollRightBtn = outputDiv.querySelector('.scroll-right');
    const convertButton = document.getElementById('convertButton');
    const clearButton = document.getElementById('clearButton');
    const inputText = document.getElementById('inputText');
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalWord = document.getElementById('modalWord');
    const closeModal = document.getElementById('closeModal');

    // Función para mostrar el estado de carga
    function showLoading() {
        resultsScroll.innerHTML = `
            <div class="w-full flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-400"></div>
                <span class="ml-3 text-gray-300">Traduciendo...</span>
            </div>
        `;
        resultsContainer.classList.remove('hidden');
        emptyState.style.display = 'none';
    }

    // Función para mostrar error
    function showError(message) {
        resultsScroll.innerHTML = `
            <div class="w-full text-center p-8">
                <div class="text-red-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-red-300 font-medium mb-2">¡Ups! Algo salió mal</p>
                <p class="text-red-400">${message}</p>
            </div>
        `;
        resultsContainer.classList.remove('hidden');
        emptyState.style.display = 'none';
    }

    // Función para actualizar el estado de los botones de scroll
    function updateScrollButtons() {
        if (scrollLeftBtn && scrollRightBtn) {
            scrollLeftBtn.disabled = resultsScroll.scrollLeft <= 0;
            scrollRightBtn.disabled = resultsScroll.scrollLeft >= resultsScroll.scrollWidth - resultsScroll.clientWidth;
        }
    }

    // Función para agregar una seña al output
    function addSignToOutput(letter, imgSrc) {
        const signContainer = document.createElement('div');
        signContainer.className = 'sign-container fade-in mx-2';
        
        const img = document.createElement('img');
        img.src = imgSrc;
        img.alt = `Seña para: ${letter}`;
        img.className = 'sign-image';
        img.title = `Haz clic para ampliar`;
        
        img.addEventListener('click', () => {
            modalImage.src = imgSrc;
            modalWord.textContent = letter.toUpperCase();
            imageModal.classList.remove('hidden');
        });
        
        const label = document.createElement('span');
        label.className = 'sign-label text-gray-200';
        label.textContent = letter.toUpperCase();
        
        signContainer.appendChild(img);
        signContainer.appendChild(label);
        
        resultsScroll.appendChild(signContainer);
        updateScrollButtons();
    }

    // Función para limpiar y reiniciar
    function resetTranslator() {
        inputText.value = '';
        resultsContainer.classList.add('hidden');
        emptyState.style.display = 'block';
        resultsScroll.innerHTML = '';
        inputText.focus();
    }

    // Event Listeners para los botones de scroll
    if (scrollLeftBtn && scrollRightBtn) {
        scrollLeftBtn.addEventListener('click', () => {
            resultsScroll.scrollBy({ left: -200, behavior: 'smooth' });
        });

        scrollRightBtn.addEventListener('click', () => {
            resultsScroll.scrollBy({ left: 200, behavior: 'smooth' });
        });

        resultsScroll.addEventListener('scroll', updateScrollButtons);
    }

    // Event Listener para el botón de traducir
    convertButton.addEventListener('click', () => {
        const text = inputText.value.trim();
        
        if (!text) {
            showError('Por favor, ingresa un texto para traducir');
            return;
        }

        showLoading();

        // Verificar si existe una imagen para la palabra completa
        const lowerText = text.toLowerCase();
        const wordImagePath = `/signs/${lowerText}.png`;

        const wordImage = new Image();
        wordImage.src = wordImagePath;
        
        wordImage.onload = () => {
            // Si la imagen de la palabra completa existe, mostrarla
            resultsScroll.innerHTML = '';
            addSignToOutput(text, wordImagePath);
        };

        wordImage.onerror = () => {
            // Si no existe imagen para la palabra completa, procesar carácter por carácter
            resultsScroll.innerHTML = '';
            let isFirstChar = true;
            
            for (const char of text) {
                const lowerChar = char.toLowerCase();
                
                if (char === ' ') {
                    // Agregar un separador visual entre palabras
                    const separator = document.createElement('div');
                    separator.className = 'sign-container fade-in flex items-center justify-center px-2';
                    
                    const separatorLine = document.createElement('div');
                    separatorLine.className = 'w-1 h-16 bg-purple-500/20 rounded-full';
                    
                    separator.appendChild(separatorLine);
                    resultsScroll.appendChild(separator);
                    isFirstChar = true;
                } else if (/^[a-z]$/.test(lowerChar)) {
                    if (!isFirstChar) {
                        // Reducir el margen entre letras de la misma palabra
                        resultsScroll.lastChild.classList.add('mr-0');
                    }
                    addSignToOutput(char, `/signs/${lowerChar}.png`);
                    isFirstChar = false;
                } else if (/\S/.test(char)) {
                    const signContainer = document.createElement('div');
                    signContainer.className = 'sign-container fade-in';
                    
                    const span = document.createElement('span');
                    span.className = 'text-2xl font-bold text-gray-200';
                    span.textContent = char;
                    
                    signContainer.appendChild(span);
                    resultsScroll.appendChild(signContainer);
                    isFirstChar = false;
                }
            }
        };
    });

    // Event Listener para el botón de limpiar
    clearButton.addEventListener('click', resetTranslator);

    // Event Listener para la tecla Enter
    inputText.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            convertButton.click();
        }
    });

    // Event Listeners para el modal
    closeModal.addEventListener('click', () => {
        imageModal.classList.add('hidden');
    });

    imageModal.addEventListener('click', (e) => {
        if (e.target === imageModal || e.target.classList.contains('bg-black')) {
            imageModal.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !imageModal.classList.contains('hidden')) {
            imageModal.classList.add('hidden');
        }
    });
}); 