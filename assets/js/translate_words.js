document.addEventListener('DOMContentLoaded', () => {
    const inputText = document.getElementById('wordInput');
    const outputDiv = document.getElementById('wordOutput');
    const translateButton = document.getElementById('translateWordButton');
    const clearButton = document.getElementById('clearButton');
    const suggestionsDiv = document.getElementById('suggestions');
    const popularWordsDiv = document.getElementById('popularWords');
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalWord = document.getElementById('modalWord');
    const closeModal = document.getElementById('closeModal');

    const commonWords = [
        'yo', 'tu', 'el', 'ella', 'ellos', 'nosotros', 'usted', 
        'mi', 'mio', 'tuyo', 'suyo', 'nuestro', 
        'para', 'por', 'en', 'entre', 'conmigo', 'contigo',
        'todo', 'eso', 'todavia', 'tambien', 'primero', 'propio', 'nos'
    ];

    // Cargar palabras populares
    function loadPopularWords() {
        popularWordsDiv.innerHTML = commonWords.map(word => `
            <button 
                class="px-3 py-1.5 text-sm bg-purple-900/50 text-purple-300 rounded-lg
                       hover:bg-purple-800 transition-colors duration-200
                       border border-purple-700 hover:border-purple-600
                       focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                onclick="document.getElementById('wordInput').value='${word}'; document.getElementById('translateWordButton').click();"
            >
                ${word}
            </button>
        `).join('');
    }

    // Mostrar sugerencias mientras se escribe
    function showSuggestions(input) {
        if (!input) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        const matches = commonWords.filter(word => 
            word.toLowerCase().includes(input.toLowerCase())
        );

        if (matches.length === 0) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        suggestionsDiv.innerHTML = matches.map(word => `
            <div class="suggestion-item px-4 py-2 hover:bg-purple-700 cursor-pointer
                        text-white hover:text-white transition-colors duration-200"
                 onclick="document.getElementById('wordInput').value='${word}';
                         document.getElementById('translateWordButton').click();">
                ${word}
            </div>
        `).join('');

        suggestionsDiv.classList.remove('hidden');
    }

    function showLoadingState() {
        outputDiv.innerHTML = `
            <div class="flex items-center justify-center p-8 bg-white/10 backdrop-blur-sm rounded-xl border-2 border-dashed border-gray-700">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-400"></div>
                <span class="ml-3 text-gray-300">Traduciendo...</span>
            </div>
        `;
    }

    function showError(message) {
        outputDiv.innerHTML = `
            <div class="text-center p-8 bg-red-900/20 backdrop-blur-sm rounded-xl border-2 border-dashed border-red-700">
                <div class="text-red-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-red-300 font-medium mb-2">¡Ups! Algo salió mal</p>
                <p class="text-red-400">${message}</p>
            </div>
        `;
    }

    function showNoWordFound(word) {
        return `
            <div class="text-center p-6 bg-yellow-900/20 backdrop-blur-sm rounded-xl border-2 border-dashed border-yellow-700 mb-4">
                <div class="text-yellow-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-yellow-300 font-medium mb-2">Palabra no encontrada</p>
                <p class="text-yellow-400">No se encontró traducción para: <span class="font-semibold">${word}</span></p>
                <div class="mt-4">
                    <p class="text-sm text-yellow-400">Prueba con alguna de las palabras sugeridas abajo</p>
                </div>
            </div>
        `;
    }

    function checkImageExists(imagePath) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(true);
            img.onerror = () => resolve(false);
            img.src = imagePath;
        });
    }

    function showSingleWordResult(word, imagePath) {
        return `
            <div>
                <div class="bg-gray-800/80 rounded-xl shadow-lg p-4 max-w-xs mx-auto mb-4 hover:shadow-xl transition-shadow duration-300">
                    <div class="flex justify-center mb-3">
                        <img src="${imagePath}" 
                             alt="${word}" 
                             class="w-32 h-32 object-contain rounded-lg transition-all duration-300 hover:scale-110 cursor-pointer"
                             title="Seña para: ${word}"
                             onclick="document.getElementById('modalImage').src='${imagePath}';
                                     document.getElementById('modalWord').textContent='${word.toUpperCase()}';
                                     document.getElementById('imageModal').classList.remove('hidden');">
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-semibold text-gray-200">${word.toUpperCase()}</p>
                        <p class="text-sm text-gray-400">Haz clic en la imagen para ampliar</p>
                    </div>
                </div>
            </div>
        `;
    }

    async function translatePhrase(phrase) {
        showLoadingState();
        
        const words = phrase.trim().toLowerCase().split(/\s+/);
        
        if (words.length === 0) {
            showError('Por favor, ingresa al menos una palabra para traducir');
            return;
        }

        let resultsHTML = '';
        let foundAtLeastOne = false;

        resultsHTML += `
            <div class="text-center mb-6">
                <h2 class="text-2xl font-semibold text-purple-400 mb-2">Traducción de: "${phrase}"</h2>
                <p class="text-sm text-gray-400">Mostrando señas disponibles</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        `;

        for (const word of words) {
            const imagePath = `/signs_words/${word}.png`;
            const exists = await checkImageExists(imagePath);
            
            if (exists) {
                resultsHTML += `
                    <div>
                        <div class="bg-gray-800/80 rounded-xl shadow-lg p-4 hover:shadow-xl transition-all duration-300">
                            <div class="flex justify-center mb-3">
                                <img src="${imagePath}" 
                                     alt="${word}" 
                                     class="w-32 h-32 object-contain rounded-lg transition-all duration-300 hover:scale-110 cursor-pointer"
                                     title="Seña para: ${word}"
                                     onclick="document.getElementById('modalImage').src='${imagePath}';
                                             document.getElementById('modalWord').textContent='${word.toUpperCase()}';
                                             document.getElementById('imageModal').classList.remove('hidden');">
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-semibold text-gray-200">${word.toUpperCase()}</p>
                                <p class="text-sm text-gray-400">Haz clic para ampliar</p>
                            </div>
                        </div>
                    </div>
                `;
                foundAtLeastOne = true;
            } else {
                resultsHTML += `
                    <div>
                        <div class="bg-gray-900/80 rounded-xl shadow p-4 hover:shadow-md transition-all duration-300">
                            <div class="flex justify-center mb-3">
                                <div class="w-32 h-32 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-medium text-gray-500">${word.toUpperCase()}</p>
                                <p class="text-sm text-gray-500">Seña no disponible</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        resultsHTML += "</div>";

        if (!foundAtLeastOne) {
            resultsHTML = `
                <div class="text-center p-8 bg-yellow-900/20 backdrop-blur-sm rounded-xl border-2 border-dashed border-yellow-700">
                    <div class="text-yellow-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-yellow-300 font-semibold text-lg mb-3">No se encontraron traducciones</p>
                    <p class="text-yellow-400 mb-6">No hay señas disponibles para las palabras en esta frase.</p>
                    <div class="bg-gray-800/80 rounded-lg p-6 shadow-sm">
                        <p class="text-gray-300 font-medium mb-4">Palabras disponibles para traducir:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            ${commonWords.map(word => `
                                <button 
                                    onclick="document.getElementById('wordInput').value='${word}'; document.getElementById('translateWordButton').click();"
                                    class="px-3 py-1.5 text-sm bg-purple-900/50 text-purple-300 rounded-lg
                                           hover:bg-purple-800 transition-colors duration-200
                                           border border-purple-700 hover:border-purple-600"
                                >
                                    ${word}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
        }

        outputDiv.innerHTML = resultsHTML;
    }

    // Event Listeners
    translateButton.addEventListener('click', () => {
        const phrase = inputText.value.trim();
        suggestionsDiv.classList.add('hidden');
        
        if (!phrase) {
            showError('Por favor, ingresa una palabra o frase para traducir');
            return;
        }

        translatePhrase(phrase);
    });

    clearButton.addEventListener('click', () => {
        inputText.value = '';
        suggestionsDiv.classList.add('hidden');
        outputDiv.innerHTML = `
            <div class="text-center p-8 bg-white/5 backdrop-blur-sm rounded-xl border-2 border-dashed border-gray-700">
                <div class="float">
                    <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <p class="text-gray-300 mb-2">Escribe una palabra o frase para ver su traducción</p>
                <p class="text-sm text-gray-400">Las traducciones aparecerán aquí</p>
            </div>
        `;
        inputText.focus();
    });

    inputText.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            translateButton.click();
        }
    });

    inputText.addEventListener('input', (e) => {
        showSuggestions(e.target.value);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!suggestionsDiv.contains(e.target) && e.target !== inputText) {
            suggestionsDiv.classList.add('hidden');
        }
    });

    // Modal event listeners
    closeModal.addEventListener('click', () => {
        imageModal.classList.add('hidden');
    });

    imageModal.addEventListener('click', (e) => {
        if (e.target === imageModal || e.target.classList.contains('bg-black')) {
            imageModal.classList.add('hidden');
        }
    });

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !imageModal.classList.contains('hidden')) {
            imageModal.classList.add('hidden');
        }
    });

    // Inicializar palabras populares
    loadPopularWords();
}); 