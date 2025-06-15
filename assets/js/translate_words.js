document.addEventListener('DOMContentLoaded', () => {
    const inputText = document.getElementById('wordInput');
    const outputDiv = document.getElementById('wordOutput');
    const translateButton = document.getElementById('translateWordButton');
    const commonWords = [
        'yo', 'tu', 'el', 'ella', 'ellos', 'nosotros', 'usted', 
        'mi', 'mio', 'tuyo', 'suyo', 'nuestro', 
        'para', 'por', 'en', 'entre', 'conmigo', 'contigo',
        'todo', 'eso', 'todavia', 'tambien', 'primero', 'propio', 'nos'
    ];

    function showLoadingState() {
        outputDiv.innerHTML = `
            <div class="flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                <span class="ml-2 text-gray-600">Traduciendo...</span>
            </div>
        `;
    }

    function showError(message) {
        outputDiv.innerHTML = `
            <div class="text-center p-4">
                <div class="text-red-500 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-gray-600">${message}</p>
            </div>
        `;
    }

    function showNoWordFound(word) {
        return `
            <div class="text-center p-4 mb-4 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-sm">No se encontró traducción para: <span class="font-semibold">${word}</span></p>
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
            <div class="fade-in">
                <div class="bg-white rounded-lg shadow p-2 md:p-3 max-w-xs mx-auto mb-4">
                    <div class="flex justify-center">
                        <img src="${imagePath}" alt="${word}" class="w-20 md:w-24 h-auto object-contain rounded mb-2 transition-all duration-300 hover:scale-110">
                    </div>
                    <p class="text-center text-gray-700 text-sm font-medium">${word.toUpperCase()}</p>
                </div>
            </div>
        `;
    }

    async function translatePhrase(phrase) {
        showLoadingState();
        
        // Dividir la frase en palabras
        const words = phrase.trim().toLowerCase().split(/\s+/);
        
        if (words.length === 0) {
            showError('Por favor, ingresa al menos una palabra para traducir');
            return;
        }

        let resultsHTML = '';
        let foundAtLeastOne = false;

        // Título para la traducción
        resultsHTML += `
            <div class="text-center mb-4 fade-in">
                <h2 class="text-xl md:text-2xl font-semibold text-purple-700">Traducción de: "${phrase}"</h2>
                <p class="text-xs text-gray-500">Mostrando imágenes para las palabras disponibles</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        `;

        // Comprobar cada palabra
        for (const word of words) {
            const imagePath = `/signs_words/${word}.png`;
            const exists = await checkImageExists(imagePath);
            
            if (exists) {
                resultsHTML += `
                    <div class="fade-in">
                        <div class="bg-white rounded-lg shadow p-2 md:p-3 mx-auto">
                            <div class="flex justify-center">
                                <img src="${imagePath}" alt="${word}" class="w-20 md:w-24 h-auto object-contain rounded mb-2 transition-all duration-300 hover:scale-110">
                            </div>
                            <p class="text-center text-gray-700 text-sm font-medium">${word.toUpperCase()}</p>
                        </div>
                    </div>
                `;
                foundAtLeastOne = true;
            } else {
                resultsHTML += `
                    <div class="fade-in">
                        <div class="bg-gray-100 rounded-lg shadow p-2 md:p-3 mx-auto">
                            <div class="flex justify-center">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-center text-gray-500 text-sm font-medium">${word.toUpperCase()}</p>
                        </div>
                    </div>
                `;
            }
        }

        resultsHTML += "</div>";

        if (!foundAtLeastOne) {
            resultsHTML += `
                <div class="text-center p-4 mt-4 bg-purple-50 rounded-lg">
                    <p class="text-gray-600 text-sm mb-3">No se encontraron traducciones para ninguna palabra en esta frase.</p>
                    <div>
                        <p class="text-xs text-purple-600 mb-2">Palabras disponibles para traducir:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            ${commonWords.slice(0, 12).map(word => `
                                <button onclick="document.getElementById('wordInput').value='${word}'" 
                                        class="px-2 py-1 text-xs bg-white rounded-full shadow-sm hover:shadow-md transition-all duration-300 text-purple-600 hover:bg-purple-50">
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

    translateButton.addEventListener('click', () => {
        const phrase = inputText.value.trim();
        
        if (!phrase) {
            showError('Por favor, ingresa una palabra o frase para traducir');
            return;
        }

        translatePhrase(phrase);
    });

    inputText.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            translateButton.click();
        }
    });

    // Sugerencias de autocompletado
    const datalist = document.getElementById('word-suggestions');
    commonWords.forEach(word => {
        const option = document.createElement('option');
        option.value = word;
        datalist.appendChild(option);
    });
}); 