document.addEventListener('DOMContentLoaded', () => {
    const letters = 'abcdefghijklmnopqrstuvwxyz'.split('');
    const randomLetterElement = document.getElementById('random-letter');
    const optionsContainer = document.getElementById('exercise-options');
    const feedbackElement = document.getElementById('exercise-feedback');
    const checkAnswerButton = document.getElementById('check-answer');

    let correctLetter = '';

    function generateExercise() {
        correctLetter = letters[Math.floor(Math.random() * letters.length)];
        randomLetterElement.textContent = correctLetter.toUpperCase();

        const shuffledLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 4);
        if (!shuffledLetters.includes(correctLetter)) {
            shuffledLetters[Math.floor(Math.random() * 4)] = correctLetter;
        }

        optionsContainer.innerHTML = '';
        shuffledLetters.forEach(letter => {
            const img = document.createElement('img');
            img.src = `signs/${letter}.png`;
            img.alt = letter;
            img.dataset.letter = letter;
            img.classList.add('exercise-option');
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                document.querySelectorAll('.exercise-option').forEach(option => {
                    option.classList.remove('selected');
                });
                img.classList.add('selected');
            });
            optionsContainer.appendChild(img);
        });
    }

    checkAnswerButton.addEventListener('click', () => {
        const selectedOption = document.querySelector('.exercise-option.selected');
        if (!selectedOption) {
            feedbackElement.textContent = 'Por favor, selecciona una opción.';
            feedbackElement.style.color = 'red';
            return;
        }

        if (selectedOption.dataset.letter === correctLetter) {
            feedbackElement.textContent = '¡Correcto! Reiniciando ejercicio...';
            feedbackElement.style.color = 'green';
            setTimeout(() => {
                feedbackElement.textContent = '';
                generateExercise();
            }, 2000);
        } else {
            feedbackElement.textContent = 'Incorrecto. Intenta de nuevo.';
            feedbackElement.style.color = 'red';
        }
    });

    generateExercise();

    const words = ['hola', 'adios', 'gracias', 'amor', 'paz'];
    const randomWordElement = document.getElementById('random-word');
    const wordOptionsContainer = document.getElementById('word-exercise-options');
    const wordFeedbackElement = document.getElementById('word-exercise-feedback');
    const checkWordAnswerButton = document.getElementById('check-word-answer');

    let correctWord = '';
    let selectedLetters = [];

    function generateWordExercise() {
        correctWord = words[Math.floor(Math.random() * words.length)];
        randomWordElement.textContent = correctWord.toUpperCase();

        const shuffledLetters = correctWord.split('').sort(() => 0.5 - Math.random());

        wordOptionsContainer.innerHTML = '';
        selectedLetters = [];

        shuffledLetters.forEach((letter, index) => {
            const container = document.createElement('div');
            container.style.position = 'relative';

            const img = document.createElement('img');
            img.src = `signs/${letter}.png`;
            img.alt = letter;
            img.dataset.letter = letter;
            img.classList.add('exercise-option');
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                if (!selectedLetters.includes(letter)) {
                    selectedLetters.push(letter);
                    const number = document.createElement('div');
                    number.classList.add('selected-number');
                    number.textContent = selectedLetters.length;
                    container.appendChild(number);
                    img.style.opacity = '0.5';
                    img.style.pointerEvents = 'none';
                } else {
                    const index = selectedLetters.indexOf(letter);
                    if (index !== -1) {
                        selectedLetters.splice(index, 1);
                        const selectedNumber = container.querySelector('.selected-number');
                        if (selectedNumber) {
                            selectedNumber.remove();
                        }
                        img.style.opacity = '1';
                        img.style.pointerEvents = 'auto';

                        // Actualizar los números de selección
                        const selectedNumbers = wordOptionsContainer.querySelectorAll('.selected-number');
                        selectedNumbers.forEach((num, idx) => {
                            num.textContent = idx + 1;
                        });
                    }
                }
            });

            container.appendChild(img);
            wordOptionsContainer.appendChild(container);
        });
    }

    checkWordAnswerButton.addEventListener('click', () => {
        if (selectedLetters.join('') === correctWord) {
            wordFeedbackElement.textContent = '¡Correcto! Reiniciando ejercicio...';
            wordFeedbackElement.style.color = 'green';
            setTimeout(() => {
                wordFeedbackElement.textContent = '';
                generateWordExercise();
            }, 2000);
        } else {
            wordFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo.';
            wordFeedbackElement.style.color = 'red';
        }
    });

    generateWordExercise();
});

document.getElementById('convertButton').addEventListener('click', () => {
    const inputText = document.getElementById('inputText').value;
    const outputDiv = document.getElementById('output');

    // Simulación de conversión de texto a señas
    outputDiv.innerHTML = '';

    // Verificar si existe una imagen para la palabra completa
    const lowerText = inputText.toLowerCase().trim();
    const wordImagePath = `signs/${lowerText}.png`;

    const wordImage = new Image();
    wordImage.src = wordImagePath;
    wordImage.onload = () => {
        // Si la imagen de la palabra completa existe, mostrarla
        outputDiv.innerHTML = '';
        wordImage.alt = inputText;
        wordImage.style.margin = '5px';
        outputDiv.appendChild(wordImage);
    };
    wordImage.onerror = () => {
        // Si no existe imagen para la palabra completa, procesar carácter por carácter
        for (const char of inputText) {
            const lowerChar = char.toLowerCase();
            if (/^[a-z]$/.test(lowerChar)) { // Verifica si el carácter es una letra
                const img = document.createElement('img');
                img.src = `signs/${lowerChar}.png`; // Ruta de las imágenes de señas
                img.alt = char;
                img.style.margin = '5px';
                outputDiv.appendChild(img);
            } else {
                const span = document.createElement('span');
                span.textContent = char; // Muestra el carácter directamente si no es una letra
                span.style.margin = '5px';
                span.style.color = 'red'; // Opcional: resaltar caracteres no soportados
                outputDiv.appendChild(span);
            }
        }
    };
});