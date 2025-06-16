// Variables globales
const letters = 'abcdefghijklmnopqrstuvwxyz'.split('');
const words = ['hola', 'adios', 'gracias', 'amor', 'paz', 'familia', 'escuela', 'amigo', 'trabajo', 'felicidad'];
let correctLetter = '';
let correctWord = '';
let selectedLetters = [];
let correctSignLetter = '';
let incorrectWord = '';
let incorrectLetter = '';

// Funciones de utilidad
function addFadeInAnimation(element, delay = 0) {
    element.style.opacity = '0';
    element.style.animation = `fadeIn 0.5s ease-out ${delay}s forwards`;
}

function createOptionImage(src, alt, className = '') {
    const img = document.createElement('img');
    img.src = src;
    img.alt = alt;
    img.className = `w-full h-32 object-contain bg-white rounded-xl shadow-md p-4 transition-all duration-300 hover:shadow-xl hover:scale-105 ${className}`;
    return img;
}

// Función para generar el ejercicio de identificación de letras
function generateExercise() {
    const exerciseArea = document.getElementById('exercise-area');
    exerciseArea.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <p class="text-lg font-semibold text-gray-700 mb-4">Identifica la seña correcta para la letra:</p>
                <h2 id="random-letter" class="text-4xl font-bold text-purple-600"></h2>
            </div>
            <div id="exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8"></div>
            <div class="flex justify-between items-center">
                <p id="exercise-feedback" class="text-lg font-medium"></p>
                <button id="check-answer" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition-colors">
                    Verificar respuesta
                </button>
            </div>
        </div>
    `;

    const randomLetterElement = document.getElementById('random-letter');
    const optionsContainer = document.getElementById('exercise-options');
    const feedbackElement = document.getElementById('exercise-feedback');
    const checkAnswerButton = document.getElementById('check-answer');

    correctLetter = letters[Math.floor(Math.random() * letters.length)];
    randomLetterElement.textContent = correctLetter.toUpperCase();
    addFadeInAnimation(randomLetterElement);

    const shuffledLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 4);
    if (!shuffledLetters.includes(correctLetter)) {
        shuffledLetters[Math.floor(Math.random() * 4)] = correctLetter;
    }

    optionsContainer.innerHTML = '';
    shuffledLetters.forEach((letter, index) => {
        const container = document.createElement('div');
        container.className = 'relative group cursor-pointer';
        
        const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
        addFadeInAnimation(img, index * 0.1);
        
        img.dataset.letter = letter;
        img.addEventListener('click', () => {
            document.querySelectorAll('#exercise-options img').forEach(opt => {
                opt.classList.remove('ring-4', 'ring-purple-500');
            });
            img.classList.add('ring-4', 'ring-purple-500');
        });
        
        container.appendChild(img);
        optionsContainer.appendChild(container);
    });

    checkAnswerButton.addEventListener('click', () => {
        const selectedOption = optionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            feedbackElement.textContent = 'Por favor, selecciona una opción';
            feedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctLetter;
        if (isCorrect) {
            feedbackElement.textContent = '¡Correcto! 🎉';
            feedbackElement.className = 'text-lg font-medium text-green-500';
            selectedOption.classList.add('scale-110');
            setTimeout(() => {
                feedbackElement.textContent = '';
                generateExercise();
            }, 1500);
        } else {
            feedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            feedbackElement.className = 'text-lg font-medium text-red-500';
        }

        // Emitir evento de respuesta
        const event = new CustomEvent('exerciseAnswered', {
            detail: {
                type: 'letterIdentification',
                isCorrect,
                letter: correctLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        });
        document.dispatchEvent(event);
    });
}

// Función para generar el ejercicio de palabras
function generateWordExercise() {
    const exerciseArea = document.getElementById('exercise-area');
    exerciseArea.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <p class="text-lg font-semibold text-gray-700 mb-4">Forma la palabra usando las señas:</p>
                <h2 id="random-word" class="text-4xl font-bold text-purple-600"></h2>
            </div>
            <div id="word-exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8"></div>
            <div class="flex justify-between items-center">
                <p id="word-exercise-feedback" class="text-lg font-medium"></p>
                <button id="check-word-answer" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition-colors">
                    Verificar palabra
                </button>
            </div>
        </div>
    `;

    const randomWordElement = document.getElementById('random-word');
    const wordOptionsContainer = document.getElementById('word-exercise-options');
    const wordFeedbackElement = document.getElementById('word-exercise-feedback');
    const checkWordAnswerButton = document.getElementById('check-word-answer');

    correctWord = words[Math.floor(Math.random() * words.length)];
    randomWordElement.textContent = correctWord.toUpperCase();
    addFadeInAnimation(randomWordElement);

    const shuffledLetters = correctWord.split('').sort(() => 0.5 - Math.random());
    wordOptionsContainer.innerHTML = '';
    selectedLetters = [];

    shuffledLetters.forEach((letter, index) => {
        const container = document.createElement('div');
        container.className = 'relative group';

        const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
        addFadeInAnimation(img, index * 0.1);

        const numberBadge = document.createElement('div');
        numberBadge.className = 'absolute -top-2 -right-2 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300';
        
        img.addEventListener('click', () => {
            if (!selectedLetters.includes(letter)) {
                selectedLetters.push(letter);
                numberBadge.textContent = selectedLetters.length;
                numberBadge.style.opacity = '1';
                img.classList.add('ring-4', 'ring-purple-500', 'opacity-75');
            } else {
                const index = selectedLetters.indexOf(letter);
                selectedLetters.splice(index, 1);
                numberBadge.style.opacity = '0';
                img.classList.remove('ring-4', 'ring-purple-500', 'opacity-75');
                
                container.parentElement.querySelectorAll('.relative').forEach((cont, idx) => {
                    const badge = cont.querySelector('div');
                    const img = cont.querySelector('img');
                    if (img.classList.contains('ring-4')) {
                        badge.textContent = idx + 1;
                    }
                });
            }
        });

        container.appendChild(img);
        container.appendChild(numberBadge);
        wordOptionsContainer.appendChild(container);
    });

    checkWordAnswerButton.addEventListener('click', () => {
        const isCorrect = selectedLetters.join('') === correctWord;
        if (isCorrect) {
            wordFeedbackElement.textContent = '¡Correcto! 🎉';
            wordFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                wordFeedbackElement.textContent = '';
                generateWordExercise();
            }, 1500);
        } else {
            wordFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            wordFeedbackElement.className = 'text-lg font-medium text-red-500';
        }

        // Emitir evento de respuesta
        const event = new CustomEvent('exerciseAnswered', {
            detail: {
                type: 'wordCompletion',
                isCorrect,
                word: correctWord,
                selectedWord: selectedLetters.join('')
            }
        });
        document.dispatchEvent(event);
    });
}

// Función para generar el ejercicio de reconocimiento de señas
function generateSignRecognition() {
    const exerciseArea = document.getElementById('exercise-area');
    exerciseArea.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <p class="text-lg font-semibold text-gray-700 mb-4">¿Qué letra representa esta seña?</p>
                <img id="random-sign" class="w-32 h-32 object-contain mx-auto" src="" alt="">
            </div>
            <div id="sign-options" class="flex flex-wrap justify-center gap-4 mb-8"></div>
            <div class="flex justify-between items-center">
                <p id="sign-feedback" class="text-lg font-medium"></p>
                <button id="check-sign-answer" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition-colors">
                    Verificar respuesta
                </button>
            </div>
        </div>
    `;

    const randomSignElement = document.getElementById('random-sign');
    const signOptionsContainer = document.getElementById('sign-options');
    const signFeedbackElement = document.getElementById('sign-feedback');
    const checkSignAnswerButton = document.getElementById('check-sign-answer');

    correctSignLetter = letters[Math.floor(Math.random() * letters.length)];
    randomSignElement.src = `/signs/${correctSignLetter}.png`;
    randomSignElement.alt = correctSignLetter;
    addFadeInAnimation(randomSignElement);

    const optionsLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 6);
    if (!optionsLetters.includes(correctSignLetter)) {
        optionsLetters[Math.floor(Math.random() * 6)] = correctSignLetter;
    }

    signOptionsContainer.innerHTML = '';
    optionsLetters.forEach((letter, index) => {
        const button = document.createElement('button');
        button.textContent = letter.toUpperCase();
        button.dataset.letter = letter;
        button.className = 'px-6 py-3 bg-white rounded-full shadow-md text-lg font-semibold text-gray-700 transition-all duration-300 hover:shadow-lg hover:scale-105 fade-in';
        addFadeInAnimation(button, index * 0.1);

        button.addEventListener('click', () => {
            signOptionsContainer.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('bg-purple-600', 'text-white');
            });
            button.classList.add('bg-purple-600', 'text-white');
        });
        signOptionsContainer.appendChild(button);
    });

    checkSignAnswerButton.addEventListener('click', () => {
        const selectedOption = signOptionsContainer.querySelector('.bg-purple-600');
        if (!selectedOption) {
            signFeedbackElement.textContent = 'Por favor, selecciona una opción';
            signFeedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctSignLetter;
        if (isCorrect) {
            signFeedbackElement.textContent = '¡Correcto! 🎉';
            signFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                signFeedbackElement.textContent = '';
                generateSignRecognition();
            }, 1500);
        } else {
            signFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            signFeedbackElement.className = 'text-lg font-medium text-red-500';
        }

        // Emitir evento de respuesta
        const event = new CustomEvent('exerciseAnswered', {
            detail: {
                type: 'signRecognition',
                isCorrect,
                sign: correctSignLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        });
        document.dispatchEvent(event);
    });
}

// Función para generar el ejercicio de detección de errores
function generateIncorrectSignExercise() {
    const exerciseArea = document.getElementById('exercise-area');
    exerciseArea.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <p class="text-lg font-semibold text-gray-700 mb-4">Encuentra la seña que NO corresponde a la palabra:</p>
                <h2 id="incorrect-word" class="text-4xl font-bold text-purple-600"></h2>
            </div>
            <div id="incorrect-sign-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8"></div>
            <div class="flex justify-between items-center">
                <p id="incorrect-sign-feedback" class="text-lg font-medium"></p>
                <button id="check-incorrect-sign-answer" class="px-6 py-3 bg-purple-600 text-white rounded-full font-semibold hover:bg-purple-700 transition-colors">
                    Verificar respuesta
                </button>
            </div>
        </div>
    `;

    const incorrectWordElement = document.getElementById('incorrect-word');
    const incorrectOptionsContainer = document.getElementById('incorrect-sign-options');
    const incorrectFeedbackElement = document.getElementById('incorrect-sign-feedback');
    const checkIncorrectSignAnswerButton = document.getElementById('check-incorrect-sign-answer');

    incorrectWord = words[Math.floor(Math.random() * words.length)];
    incorrectWordElement.textContent = incorrectWord.toUpperCase();
    addFadeInAnimation(incorrectWordElement);

    const correctLetters = incorrectWord.split('');
    incorrectLetter = letters.filter(l => !correctLetters.includes(l))[Math.floor(Math.random() * (letters.length - correctLetters.length))];
    const options = [...correctLetters, incorrectLetter].sort(() => 0.5 - Math.random());

    incorrectOptionsContainer.innerHTML = '';
    options.forEach((letter, index) => {
        const container = document.createElement('div');
        container.className = 'relative group cursor-pointer';
        
        const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
        addFadeInAnimation(img, index * 0.1);
        
        img.dataset.letter = letter;
        img.addEventListener('click', () => {
            incorrectOptionsContainer.querySelectorAll('img').forEach(opt => {
                opt.classList.remove('ring-4', 'ring-red-500');
            });
            img.classList.add('ring-4', 'ring-red-500');
        });
        
        container.appendChild(img);
        incorrectOptionsContainer.appendChild(container);
    });

    checkIncorrectSignAnswerButton.addEventListener('click', () => {
        const selectedOption = incorrectOptionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            incorrectFeedbackElement.textContent = 'Por favor, selecciona una opción';
            incorrectFeedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        const isCorrect = selectedOption.dataset.letter === incorrectLetter;
        if (isCorrect) {
            incorrectFeedbackElement.textContent = '¡Correcto! 🎉';
            incorrectFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                incorrectFeedbackElement.textContent = '';
                generateIncorrectSignExercise();
            }, 1500);
        } else {
            incorrectFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            incorrectFeedbackElement.className = 'text-lg font-medium text-red-500';
        }

        // Emitir evento de respuesta
        const event = new CustomEvent('exerciseAnswered', {
            detail: {
                type: 'errorDetection',
                isCorrect,
                word: incorrectWord,
                incorrectLetter: incorrectLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        });
        document.dispatchEvent(event);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Ejercicio 1: Identificar Letra
    const randomLetterElement = document.getElementById('random-letter');
    const optionsContainer = document.getElementById('exercise-options');
    const feedbackElement = document.getElementById('exercise-feedback');
    const checkAnswerButton = document.getElementById('check-answer');

    let correctLetter = '';

    function generateExercise() {
        correctLetter = letters[Math.floor(Math.random() * letters.length)];
        randomLetterElement.textContent = correctLetter.toUpperCase();
        addFadeInAnimation(randomLetterElement);

        const shuffledLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 4);
        if (!shuffledLetters.includes(correctLetter)) {
            shuffledLetters[Math.floor(Math.random() * 4)] = correctLetter;
        }

        optionsContainer.innerHTML = '';
        shuffledLetters.forEach((letter, index) => {
            const container = document.createElement('div');
            container.className = 'relative group cursor-pointer';
            
            const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
            addFadeInAnimation(img, index * 0.1);
            
            img.dataset.letter = letter;
            img.addEventListener('click', () => {
                document.querySelectorAll('#exercise-options img').forEach(opt => {
                    opt.classList.remove('ring-4', 'ring-purple-500');
                });
                img.classList.add('ring-4', 'ring-purple-500');
            });
            
            container.appendChild(img);
            optionsContainer.appendChild(container);
        });
    }

    checkAnswerButton.addEventListener('click', () => {
        const selectedOption = optionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            feedbackElement.textContent = 'Por favor, selecciona una opción';
            feedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        if (selectedOption.dataset.letter === correctLetter) {
            feedbackElement.textContent = '¡Correcto! 🎉';
            feedbackElement.className = 'text-lg font-medium text-green-500';
            selectedOption.classList.add('scale-110');
            setTimeout(() => {
                feedbackElement.textContent = '';
                generateExercise();
            }, 1500);
        } else {
            feedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            feedbackElement.className = 'text-lg font-medium text-red-500';
        }
    });

    // Ejercicio 2: Completar Palabra
    const randomWordElement = document.getElementById('random-word');
    const wordOptionsContainer = document.getElementById('word-exercise-options');
    const wordFeedbackElement = document.getElementById('word-exercise-feedback');
    const checkWordAnswerButton = document.getElementById('check-word-answer');

    let correctWord = '';
    let selectedLetters = [];

    function generateWordExercise() {
        correctWord = words[Math.floor(Math.random() * words.length)];
        randomWordElement.textContent = correctWord.toUpperCase();
        addFadeInAnimation(randomWordElement);

        const shuffledLetters = correctWord.split('').sort(() => 0.5 - Math.random());
        wordOptionsContainer.innerHTML = '';
        selectedLetters = [];

        shuffledLetters.forEach((letter, index) => {
            const container = document.createElement('div');
            container.className = 'relative group';

            const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
            addFadeInAnimation(img, index * 0.1);

            const numberBadge = document.createElement('div');
            numberBadge.className = 'absolute -top-2 -right-2 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300';
            
            img.addEventListener('click', () => {
                if (!selectedLetters.includes(letter)) {
                    selectedLetters.push(letter);
                    numberBadge.textContent = selectedLetters.length;
                    numberBadge.style.opacity = '1';
                    img.classList.add('ring-4', 'ring-purple-500', 'opacity-75');
                } else {
                    const index = selectedLetters.indexOf(letter);
                    selectedLetters.splice(index, 1);
                    numberBadge.style.opacity = '0';
                    img.classList.remove('ring-4', 'ring-purple-500', 'opacity-75');
                    
                    // Actualizar números
                    container.parentElement.querySelectorAll('.relative').forEach((cont, idx) => {
                        const badge = cont.querySelector('div');
                        const img = cont.querySelector('img');
                        if (img.classList.contains('ring-4')) {
                            badge.textContent = idx + 1;
                        }
                    });
                }
            });

            container.appendChild(img);
            container.appendChild(numberBadge);
            wordOptionsContainer.appendChild(container);
        });
    }

    checkWordAnswerButton.addEventListener('click', () => {
        if (selectedLetters.join('') === correctWord) {
            wordFeedbackElement.textContent = '¡Correcto! 🎉';
            wordFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                wordFeedbackElement.textContent = '';
                generateWordExercise();
            }, 1500);
        } else {
            wordFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            wordFeedbackElement.className = 'text-lg font-medium text-red-500';
        }
    });

    // Ejercicio 3: Reconoce la Seña
    const randomSignElement = document.getElementById('random-sign');
    const signOptionsContainer = document.getElementById('sign-options');
    const signFeedbackElement = document.getElementById('sign-feedback');
    const checkSignAnswerButton = document.getElementById('check-sign-answer');

    let correctSignLetter = '';

    function generateSignRecognition() {
        correctSignLetter = letters[Math.floor(Math.random() * letters.length)];
        randomSignElement.src = `/signs/${correctSignLetter}.png`;
        randomSignElement.alt = correctSignLetter;
        addFadeInAnimation(randomSignElement);

        const optionsLetters = [...letters].sort(() => 0.5 - Math.random()).slice(0, 6);
        if (!optionsLetters.includes(correctSignLetter)) {
            optionsLetters[Math.floor(Math.random() * 6)] = correctSignLetter;
        }

        signOptionsContainer.innerHTML = '';
        optionsLetters.forEach((letter, index) => {
            const button = document.createElement('button');
            button.textContent = letter.toUpperCase();
            button.dataset.letter = letter;
            button.className = 'px-6 py-3 bg-white rounded-full shadow-md text-lg font-semibold text-gray-700 transition-all duration-300 hover:shadow-lg hover:scale-105 fade-in';
            addFadeInAnimation(button, index * 0.1);

            button.addEventListener('click', () => {
                signOptionsContainer.querySelectorAll('button').forEach(btn => {
                    btn.classList.remove('bg-purple-600', 'text-white');
                });
                button.classList.add('bg-purple-600', 'text-white');
            });
            signOptionsContainer.appendChild(button);
        });
    }

    checkSignAnswerButton.addEventListener('click', () => {
        const selectedOption = signOptionsContainer.querySelector('.bg-purple-600');
        if (!selectedOption) {
            signFeedbackElement.textContent = 'Por favor, selecciona una opción';
            signFeedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        if (selectedOption.dataset.letter === correctSignLetter) {
            signFeedbackElement.textContent = '¡Correcto! 🎉';
            signFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                signFeedbackElement.textContent = '';
                generateSignRecognition();
            }, 1500);
        } else {
            signFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            signFeedbackElement.className = 'text-lg font-medium text-red-500';
        }
    });

    // Ejercicio 4: Encuentra la Seña Incorrecta
    const incorrectWordElement = document.getElementById('incorrect-word');
    const incorrectOptionsContainer = document.getElementById('incorrect-sign-options');
    const incorrectFeedbackElement = document.getElementById('incorrect-sign-feedback');
    const checkIncorrectSignAnswerButton = document.getElementById('check-incorrect-sign-answer');

    let incorrectWord = '';
    let incorrectLetter = '';

    function generateIncorrectSignExercise() {
        incorrectWord = words[Math.floor(Math.random() * words.length)];
        incorrectWordElement.textContent = incorrectWord.toUpperCase();
        addFadeInAnimation(incorrectWordElement);

        const correctLetters = incorrectWord.split('');
        incorrectLetter = letters.filter(l => !correctLetters.includes(l))[Math.floor(Math.random() * (letters.length - correctLetters.length))];
        const options = [...correctLetters, incorrectLetter].sort(() => 0.5 - Math.random());

        incorrectOptionsContainer.innerHTML = '';
        options.forEach((letter, index) => {
            const container = document.createElement('div');
            container.className = 'relative group cursor-pointer';
            
            const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
            addFadeInAnimation(img, index * 0.1);
            
            img.dataset.letter = letter;
            img.addEventListener('click', () => {
                incorrectOptionsContainer.querySelectorAll('img').forEach(opt => {
                    opt.classList.remove('ring-4', 'ring-red-500');
                });
                img.classList.add('ring-4', 'ring-red-500');
            });
            
            container.appendChild(img);
            incorrectOptionsContainer.appendChild(container);
        });
    }

    checkIncorrectSignAnswerButton.addEventListener('click', () => {
        const selectedOption = incorrectOptionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            incorrectFeedbackElement.textContent = 'Por favor, selecciona una opción';
            incorrectFeedbackElement.className = 'text-lg font-medium text-red-500';
            return;
        }

        if (selectedOption.dataset.letter === incorrectLetter) {
            incorrectFeedbackElement.textContent = '¡Correcto! 🎉';
            incorrectFeedbackElement.className = 'text-lg font-medium text-green-500';
            setTimeout(() => {
                incorrectFeedbackElement.textContent = '';
                generateIncorrectSignExercise();
            }, 1500);
        } else {
            incorrectFeedbackElement.textContent = 'Incorrecto. Intenta de nuevo';
            incorrectFeedbackElement.className = 'text-lg font-medium text-red-500';
        }
    });

    // Inicializar todos los ejercicios
    generateExercise();
    generateWordExercise();
    generateSignRecognition();
    generateIncorrectSignExercise();

    // Menú móvil
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