document.addEventListener('DOMContentLoaded', () => {
    const outputDiv = document.getElementById('output');
    const resultsContainer = outputDiv.querySelector('.results-container');
    const emptyState = outputDiv.querySelector('.empty-state');
    const resultsScroll = resultsContainer.querySelector('.flex.overflow-x-auto');

    document.getElementById('convertButton').addEventListener('click', () => {
        const inputText = document.getElementById('inputText').value;

        // Limpiar el área de resultados
        resultsScroll.innerHTML = '';
        
        if (!inputText.trim()) {
            emptyState.style.display = 'block';
            resultsContainer.classList.add('hidden');
            emptyState.textContent = 'Por favor, ingresa un texto para traducir';
            emptyState.style.color = 'red';
            return;
        }

        // Verificar si existe una imagen para la palabra completa
        const lowerText = inputText.toLowerCase().trim();
        const wordImagePath = `/signs/${lowerText}.png`;

        const wordImage = new Image();
        wordImage.src = wordImagePath;
        wordImage.onload = () => {
            // Si la imagen de la palabra completa existe, mostrarla
            emptyState.style.display = 'none';
            resultsContainer.classList.remove('hidden');
            resultsScroll.innerHTML = '';

            const signContainer = document.createElement('div');
            signContainer.className = 'sign-container';
            
            const img = document.createElement('img');
            img.src = wordImagePath;
            img.alt = inputText;
            img.className = 'sign-image fade-in';
            
            const label = document.createElement('span');
            label.className = 'sign-label';
            label.textContent = inputText;
            
            signContainer.appendChild(img);
            signContainer.appendChild(label);
            resultsScroll.appendChild(signContainer);
        };

        wordImage.onerror = () => {
            // Si no existe imagen para la palabra completa, procesar carácter por carácter
            emptyState.style.display = 'none';
            resultsContainer.classList.remove('hidden');
            resultsScroll.innerHTML = '';

            for (const char of inputText) {
                const lowerChar = char.toLowerCase();
                if (/^[a-z]$/.test(lowerChar)) {
                    const signContainer = document.createElement('div');
                    signContainer.className = 'sign-container';
                    
                    const img = document.createElement('img');
                    img.src = `/signs/${lowerChar}.png`;
                    img.alt = `Seña para: ${char}`;
                    img.className = 'sign-image fade-in';
                    
                    const label = document.createElement('span');
                    label.className = 'sign-label';
                    label.textContent = char.toUpperCase();
                    
                    signContainer.appendChild(img);
                    signContainer.appendChild(label);
                    resultsScroll.appendChild(signContainer);
                } else {
                    const signContainer = document.createElement('div');
                    signContainer.className = 'sign-container';
                    
                    const span = document.createElement('span');
                    span.className = 'sign-label text-red-500 text-2xl font-bold';
                    span.textContent = char;
                    
                    signContainer.appendChild(span);
                    resultsScroll.appendChild(signContainer);
                }
            }
        };
    });
}); 