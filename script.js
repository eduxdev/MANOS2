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
            img.classList.add('exercise-option', 'border', 'border-gray-300', 'rounded-lg', 'p-2');
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                document.querySelectorAll('.exercise-option').forEach(option => {
                    option.classList.remove('ring-4', 'ring-indigo-500');
                });
                img.classList.add('ring-4', 'ring-indigo-500');
            });
            optionsContainer.appendChild(img);
        });
    }

    checkAnswerButton.addEventListener('click', () => {
        const selectedOption = document.querySelector('.exercise-option.ring-4');
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

    const words = ['hola', 'adios', 'gracias', 'amor', 'paz', 'familia', 'escuela', 'amigo', 'trabajo', 'felicidad']; // Added more words
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

        shuffledLetters.forEach((letter) => {
            const container = document.createElement('div');
            container.style.position = 'relative';
            container.style.display = 'inline-block'; // Ensure proper alignment
            container.style.margin = '10px'; // Add spacing between images

            const img = document.createElement('img');
            img.src = `signs/${letter}.png`;
            img.alt = letter;
            img.dataset.letter = letter;
            img.classList.add('exercise-option', 'border', 'border-gray-300', 'rounded-lg', 'p-2');
            img.style.cursor = 'pointer';

            const number = document.createElement('div');
            number.classList.add('selected-number');
            number.style.position = 'absolute';
            number.style.top = '50%'; // Align vertically
            number.style.left = '105%'; // Position to the right of the image
            number.style.transform = 'translateY(-50%)'; // Center vertically
            number.style.backgroundColor = 'indigo';
            number.style.color = 'white';
            number.style.borderRadius = '50%';
            number.style.width = '20px';
            number.style.height = '20px';
            number.style.display = 'flex';
            number.style.alignItems = 'center';
            number.style.justifyContent = 'center';
            number.style.fontSize = '12px';
            number.style.visibility = 'hidden'; // Initially hidden

            img.addEventListener('click', () => {
                if (!selectedLetters.includes(letter)) {
                    selectedLetters.push(letter);
                    number.textContent = selectedLetters.length;
                    number.style.visibility = 'visible';
                    img.style.opacity = '0.5';
                } else {
                    const index = selectedLetters.indexOf(letter);
                    if (index !== -1) {
                        selectedLetters.splice(index, 1);
                        number.style.visibility = 'hidden';
                        img.style.opacity = '1';

                        // Update numbers for remaining selections
                        const selectedNumbers = wordOptionsContainer.querySelectorAll('.selected-number');
                        selectedNumbers.forEach((num, idx) => {
                            num.textContent = idx + 1;
                        });
                    }
                }
            });

            container.appendChild(img);
            container.appendChild(number);
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

document.addEventListener('DOMContentLoaded', () => {
    const exerciseOptions = document.getElementById('exercise-options');
    const checkAnswerButton = document.getElementById('check-answer');
    const feedback = document.getElementById('exercise-feedback');
    const questionLetter = document.getElementById('random-letter');

    let selectedOption = null;

    // Add click event to dynamically generated options
    exerciseOptions.addEventListener('click', (event) => {
        if (event.target.tagName === 'IMG') {
            // Remove selection from previous option
            if (selectedOption) {
                selectedOption.classList.remove('ring-4', 'ring-indigo-500');
            }

            // Highlight the selected option
            selectedOption = event.target;
            selectedOption.classList.add('ring-4', 'ring-indigo-500');
        }
    });

    // Check answer logic
    checkAnswerButton.addEventListener('click', () => {
        if (!selectedOption) {
            feedback.textContent = 'Por favor selecciona una imagen antes de verificar.';
            feedback.style.color = 'red';
            return;
        }

        const selectedLetter = selectedOption.alt;
        if (selectedLetter === questionLetter.textContent) {
            feedback.textContent = '¡Correcto!';
            feedback.style.color = 'green';
        } else {
            feedback.textContent = `Incorrecto. La letra correcta es: ${questionLetter.textContent}`;
            feedback.style.color = 'red';
        }
    });
});

// Ejercicio: Ordena las Palabras
const phrases = [
    'hola mundo',
    'buenos dias',
    'gracias amigo',
    'familia feliz',
    'trabajo en equipo'
];
const randomPhraseElement = document.getElementById('random-phrase');
const wordOrderOptionsContainer = document.getElementById('word-order-options');
const wordOrderFeedbackElement = document.getElementById('word-order-feedback');
const checkWordOrderAnswerButton = document.getElementById('check-word-order-answer');

let correctPhrase = '';
let selectedWords = [];

function generateWordOrderExercise() {
    correctPhrase = phrases[Math.floor(Math.random() * phrases.length)];
    randomPhraseElement.textContent = correctPhrase.toUpperCase();

    const shuffledWords = correctPhrase.split(' ').sort(() => 0.5 - Math.random());

    wordOrderOptionsContainer.innerHTML = '';
    selectedWords = [];

    shuffledWords.forEach((word) => {
        const button = document.createElement('button');
        button.textContent = word;
        button.classList.add('bg-gray-200', 'rounded', 'px-4', 'py-2', 'm-2', 'hover:bg-gray-300');
        button.addEventListener('click', () => {
            if (!selectedWords.includes(word)) {
                selectedWords.push(word);
                button.style.opacity = '0.5';
                button.style.pointerEvents = 'none';
            }
        });
        wordOrderOptionsContainer.appendChild(button);
    });
}

checkWordOrderAnswerButton.addEventListener('click', () => {
    if (selectedWords.join(' ') === correctPhrase) {
        wordOrderFeedbackElement.textContent = '¡Correcto! Reiniciando ejercicio...';
        wordOrderFeedbackElement.style.color = 'green';
        setTimeout(() => {
            wordOrderFeedbackElement.textContent = '';
            generateWordOrderExercise();
        }, 2000);
    } else {
        wordOrderFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo.';
        wordOrderFeedbackElement.style.color = 'red';
    }
});

generateWordOrderExercise();

// Ejercicio: Encuentra la Letra
const findRandomLetterElement = document.getElementById('find-random-letter');
const findLetterOptionsContainer = document.getElementById('find-letter-options');
const findLetterFeedbackElement = document.getElementById('find-letter-feedback');
const checkFindLetterAnswerButton = document.getElementById('check-find-letter-answer');

let findCorrectLetter = '';

function generateFindLetterExercise() {
    findCorrectLetter = letters[Math.floor(Math.random() * letters.length)];
    findRandomLetterElement.textContent = findCorrectLetter.toUpperCase();

    const shuffledLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 6);
    if (!shuffledLetters.includes(findCorrectLetter)) {
        shuffledLetters[Math.floor(Math.random() * 6)] = findCorrectLetter;
    }

    findLetterOptionsContainer.innerHTML = '';

    shuffledLetters.forEach((letter) => {
        const img = document.createElement('img');
        img.src = `signs/${letter}.png`;
        img.alt = letter;
        img.dataset.letter = letter;
        img.classList.add('exercise-option', 'border', 'border-gray-300', 'rounded-lg', 'p-2');
        img.style.cursor = 'pointer';
        img.addEventListener('click', () => {
            document.querySelectorAll('.exercise-option').forEach(option => {
                option.classList.remove('ring-4', 'ring-indigo-500');
            });
            img.classList.add('ring-4', 'ring-indigo-500');
        });
        findLetterOptionsContainer.appendChild(img);
    });
}

checkFindLetterAnswerButton.addEventListener('click', () => {
    const selectedOption = document.querySelector('.exercise-option.ring-4');
    if (!selectedOption) {
        findLetterFeedbackElement.textContent = 'Por favor, selecciona una opción.';
        findLetterFeedbackElement.style.color = 'red';
        return;
    }

    if (selectedOption.dataset.letter === findCorrectLetter) {
        findLetterFeedbackElement.textContent = '¡Correcto! Reiniciando ejercicio...';
        findLetterFeedbackElement.style.color = 'green';
        setTimeout(() => {
            findLetterFeedbackElement.textContent = '';
            generateFindLetterExercise();
        }, 2000);
    } else {
        findLetterFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo.';
        findLetterFeedbackElement.style.color = 'red';
    }
});

generateFindLetterExercise();

document.addEventListener('DOMContentLoaded', () => {
    const openMenuButton = document.getElementById('open-menu');
    const closeMenuButton = document.getElementById('close-menu');
    const mobileMenu = document.getElementById('mobile-menu');

    if (openMenuButton && closeMenuButton && mobileMenu) {
        openMenuButton.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden');
        });

        closeMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
    } else {
        console.error('No se encontraron los elementos necesarios para el menú móvil.');
    }
});