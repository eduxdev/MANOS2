// Variables globales
const letters = 'abcdefghijklmnopqrstuvwxyz'.split('');
const words = ['hola', 'adios', 'gracias', 'amor', 'paz', 'familia', 'escuela', 'amigo', 'trabajo', 'felicidad'];
let correctLetter = '';
let correctWord = '';
let selectedLetters = [];
let correctSignLetter = '';
let incorrectWord = '';
let incorrectLetter = '';

// Ejercicio 5: Secuencia de Señas
let currentSequence = [];
let userSequence = [];
let sequenceLevel = 1;
let sequencePoints = 0;
let isShowingSequence = false; // Bandera para controlar si se está mostrando la secuencia

// Ejercicio 6: Memorama de Señas
let memoryCards = [];
let flippedCards = [];
let matchedPairs = 0;
let attempts = 0;

// Ejercicio 7: Velocidad de Señas
let speedLetters = [];
let speedTimer = null;
let speedPoints = 0;
let speedLevel = 1;
let speedTimeLimit = 5000; // 5 segundos iniciales
let isSpeedExerciseActive = false; // Bandera para controlar si el ejercicio está activo

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

    checkAnswerButton.addEventListener('click', async () => {
        const selectedOption = optionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctLetter;
        const practiceData = {
            tipo_ejercicio: 'letterIdentification',
            respuesta_correcta: isCorrect,
            detalles: {
                letter: correctLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            selectedOption.classList.add('scale-110');
            setTimeout(() => {
                generateExercise();
            }, 1500);
        }
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

    checkWordAnswerButton.addEventListener('click', async () => {
        const isCorrect = selectedLetters.join('') === correctWord;
        const practiceData = {
            tipo_ejercicio: 'wordCompletion',
            respuesta_correcta: isCorrect,
            detalles: {
                word: correctWord,
                selectedWord: selectedLetters.join('')
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateWordExercise();
            }, 1500);
        }
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

    checkSignAnswerButton.addEventListener('click', async () => {
        const selectedOption = signOptionsContainer.querySelector('.bg-purple-600');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctSignLetter;
        const practiceData = {
            tipo_ejercicio: 'signRecognition',
            respuesta_correcta: isCorrect,
            detalles: {
                letter: correctSignLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateSignRecognition();
            }, 1500);
        }
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

    checkIncorrectSignAnswerButton.addEventListener('click', async () => {
        const selectedOption = incorrectOptionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === incorrectLetter;
        const practiceData = {
            tipo_ejercicio: 'errorDetection',
            respuesta_correcta: isCorrect,
            detalles: {
                word: incorrectWord,
                incorrectLetter: incorrectLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateIncorrectSignExercise();
            }, 1500);
        }
    });
}

// Función para mostrar notificación
function showNotification(message, isSuccess = true) {
    // Crear notificación flotante
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-4 p-4 rounded-lg shadow-lg transform translate-x-full transition-all duration-500 ${isSuccess ? 'bg-green-500' : 'bg-red-500'} text-white z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Animación de entrada
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });

    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// Función para guardar práctica y mostrar notificación
async function savePractice(data) {
    try {
        // Asignar puntos base según el tipo de ejercicio
        let puntos_base = 0;
        switch (data.tipo_ejercicio) {
            case 'letterIdentification':
            case 'wordCompletion':
            case 'signRecognition':
            case 'errorDetection':
                puntos_base = 5;
                break;
            case 'sequenceExercise':
                puntos_base = 10 * data.detalles.nivel;
                break;
            case 'memoryExercise':
                puntos_base = Math.max(20 - data.detalles.intentos, 5);
                break;
            case 'speedExercise':
                puntos_base = 10 + Math.round((5000 - data.detalles.tiempo_limite) / 500);
                break;
        }

        // Multiplicador por respuesta correcta
        const puntos = data.respuesta_correcta ? puntos_base : 0;
        data.puntos = puntos;

        const response = await fetch('/backend/ejercicios/save_practice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (data.respuesta_correcta) {
                showNotification(result.puntos_ganados > 0 ? `¡Correcto! +${result.puntos_ganados} puntos` : '¡Correcto!', true);
            } else {
                showNotification('Incorrecto. ¡Inténtalo de nuevo!', false);
            }
        } else {
            showNotification('¡Correcto!', true);
        }
        
        return result;
    } catch (error) {
        console.error('Error:', error);
        showNotification('¡Correcto!', true);
        return null;
    }
}

// Ejercicio 5: Secuencia de Señas
function initSequenceExercise() {
    const startButton = document.getElementById('start-sequence');
    const sequenceDisplay = document.getElementById('sequence-display');
    const sequenceInput = document.getElementById('sequence-input');
    
    if (!startButton) return;

    startButton.addEventListener('click', startNewSequence);
}

function setupUserInput() {
    const sequenceInput = document.getElementById('sequence-input');
    sequenceInput.innerHTML = '';
    
    // Crear botones con todas las letras posibles
    const letters = Array.from({length: 26}, (_, i) => String.fromCharCode(97 + i));
    letters.forEach(letter => {
        const card = createSignCard(letter);
        card.classList.add('cursor-pointer');
        card.addEventListener('click', () => handleSequenceInput(letter));
        sequenceInput.appendChild(card);
    });
}

function resetUserSequence() {
    userSequence = [];
    const sequenceDisplay = document.getElementById('sequence-display');
    sequenceDisplay.innerHTML = '';
    
    // Recrear la estructura del header
    const headerContainer = document.createElement('div');
    headerContainer.className = 'flex justify-between items-center mb-4';
    
    const userSequenceTitle = document.createElement('div');
    userSequenceTitle.className = 'text-xl font-bold text-purple-600';
    userSequenceTitle.textContent = 'Tu secuencia:';
    headerContainer.appendChild(userSequenceTitle);
    
    const resetButton = document.createElement('button');
    resetButton.className = 'px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-semibold rounded-full flex items-center gap-2 hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-sm hover:shadow focus:ring-2 focus:ring-red-500 focus:ring-offset-1';
    resetButton.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>Reiniciar</span>
    `;
    resetButton.addEventListener('click', resetUserSequence);
    headerContainer.appendChild(resetButton);
    
    sequenceDisplay.appendChild(headerContainer);
    
    // Limpiar el feedback
    const feedback = document.getElementById('sequence-feedback');
    feedback.textContent = '';
    feedback.className = 'text-lg font-medium';
}

function handleSequenceInput(letter) {
    userSequence.push(letter);
    const feedback = document.getElementById('sequence-feedback');
    
    // Actualizar la visualización de la secuencia del usuario
    const sequenceDisplay = document.getElementById('sequence-display');
    sequenceDisplay.innerHTML = '';
    
    // Contenedor superior para el título y botón de reiniciar
    const headerContainer = document.createElement('div');
    headerContainer.className = 'flex justify-between items-center mb-4';
    
    // Título para la secuencia del usuario
    const userSequenceTitle = document.createElement('div');
    userSequenceTitle.className = 'text-xl font-bold text-purple-600';
    userSequenceTitle.textContent = 'Tu secuencia:';
    headerContainer.appendChild(userSequenceTitle);
    
    // Botón de reiniciar minimalista
    const resetButton = document.createElement('button');
    resetButton.className = 'px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-semibold rounded-full flex items-center gap-2 hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-sm hover:shadow focus:ring-2 focus:ring-red-500 focus:ring-offset-1';
    resetButton.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>Reiniciar</span>
    `;
    resetButton.addEventListener('click', resetUserSequence);
    headerContainer.appendChild(resetButton);
    
    sequenceDisplay.appendChild(headerContainer);
    
    // Contenedor para las cartas en fila sin scroll
    const cardsContainer = document.createElement('div');
    cardsContainer.className = 'flex flex-wrap gap-4 justify-start';
    sequenceDisplay.appendChild(cardsContainer);
    
    userSequence.forEach((l, index) => {
        const card = createSignCard(l, 'normal');
        card.classList.add('relative');
        
        // Agregar número de orden
        const orderBadge = document.createElement('div');
        orderBadge.className = 'absolute -top-2 -right-2 w-6 h-6 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold';
        orderBadge.textContent = index + 1;
        card.appendChild(orderBadge);
        
        // Agregar botón de eliminar
        const deleteButton = document.createElement('button');
        deleteButton.className = 'absolute -bottom-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600';
        deleteButton.innerHTML = '×';
        deleteButton.addEventListener('click', (e) => {
            e.stopPropagation();
            userSequence.splice(index, 1);
            handleSequenceUpdate();
        });
        card.appendChild(deleteButton);
        
        cardsContainer.appendChild(card);
    });
    
    if (userSequence.length === currentSequence.length) {
        const correct = userSequence.every((l, i) => l === currentSequence[i]);
        if (correct) {
            feedback.textContent = '¡Correcto! ¡Has completado este nivel!';
            feedback.className = 'text-lg font-medium text-green-600';
            sequenceLevel++;
            sequencePoints += 10 * sequenceLevel;
            
            // Guardar práctica
            const practiceData = {
                tipo_ejercicio: 'sequenceExercise',
                respuesta_correcta: true,
                detalles: {
                    nivel: sequenceLevel,
                    secuencia: currentSequence,
                    secuencia_usuario: userSequence
                }
            };
            savePractice(practiceData);
            
            updateSequenceUI();
            
            // Mostrar botón más pequeño para pasar al siguiente nivel
            const nextLevelButton = document.createElement('button');
            nextLevelButton.className = 'px-4 py-2 mt-4 bg-gradient-to-r from-green-500 to-teal-500 text-white text-sm font-bold rounded-full shadow-md hover:shadow-lg transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center space-x-2 mx-auto';
            nextLevelButton.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
                <span>Nivel ${sequenceLevel}</span>
            `;
            nextLevelButton.addEventListener('click', () => {
                startNewSequence();
            });
            
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'flex justify-center mt-4';
            buttonContainer.appendChild(nextLevelButton);
            sequenceDisplay.appendChild(buttonContainer);
            
        } else {
            feedback.textContent = 'Incorrecto. ¡Inténtalo de nuevo!';
            feedback.className = 'text-lg font-medium text-red-600';
            
            // Guardar práctica incorrecta
            const practiceData = {
                tipo_ejercicio: 'sequenceExercise',
                respuesta_correcta: false,
                detalles: {
                    nivel: sequenceLevel,
                    secuencia: currentSequence,
                    secuencia_usuario: userSequence
                }
            };
            savePractice(practiceData);
            
            sequenceLevel = 1;
            sequencePoints = Math.max(0, sequencePoints - 5);
            updateSequenceUI();
        }
        userSequence = [];
    }
}

function handleSequenceUpdate() {
    // Esta función se llama cuando el usuario elimina una carta de su secuencia
    if (userSequence.length === 0) {
        resetUserSequence();
    } else {
        handleSequenceInput(userSequence[userSequence.length - 1]);
        // Eliminamos el último elemento porque handleSequenceInput lo vuelve a agregar
        userSequence.pop();
    }
}

function showSequence() {
    const sequenceDisplay = document.getElementById('sequence-display');
    sequenceDisplay.innerHTML = '';
    let delay = 0;
    
    isShowingSequence = true;
    
    // Crear modal para mostrar la secuencia
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    document.body.appendChild(modal);
    
    // Contenedor de la secuencia dentro del modal
    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white rounded-xl shadow-2xl p-8 max-w-lg w-full mx-4 transform transition-all';
    modal.appendChild(modalContent);
    
    // Título del modal
    const modalTitle = document.createElement('h3');
    modalTitle.className = 'text-2xl font-bold text-purple-800 mb-6 text-center';
    modalTitle.textContent = `Memoriza la Secuencia - Nivel ${sequenceLevel}`;
    modalContent.appendChild(modalTitle);
    
    // Contenedor para la carta actual
    const cardContainer = document.createElement('div');
    cardContainer.className = 'flex justify-center items-center min-h-[300px]';
    modalContent.appendChild(cardContainer);
    
    // Indicador de progreso
    const progressContainer = document.createElement('div');
    progressContainer.className = 'mt-6 flex justify-center gap-2';
    modalContent.appendChild(progressContainer);
    
    // Crear indicadores de progreso
    for (let i = 0; i < currentSequence.length; i++) {
        const dot = document.createElement('div');
        dot.className = 'w-3 h-3 rounded-full bg-gray-300';
        progressContainer.appendChild(dot);
    }
    
    // Mostrar cada letra de la secuencia
    currentSequence.forEach((letter, index) => {
        setTimeout(() => {
            // Actualizar el contenedor de cartas
            cardContainer.innerHTML = '';
            
            // Crear carta grande
            const card = createSignCard(letter, 'large');
            
            // Agregar número de orden
            const orderBadge = document.createElement('div');
            orderBadge.className = 'absolute -top-4 -right-4 w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center text-xl font-bold';
            orderBadge.textContent = index + 1;
            card.appendChild(orderBadge);
            
            cardContainer.appendChild(card);
            
            // Actualizar indicador de progreso
            const dots = progressContainer.querySelectorAll('div');
            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.className = 'w-3 h-3 rounded-full bg-purple-600';
                } else if (i < index) {
                    dot.className = 'w-3 h-3 rounded-full bg-green-500';
                } else {
                    dot.className = 'w-3 h-3 rounded-full bg-gray-300';
                }
            });
            
            // Después de mostrar la última carta
            if (index === currentSequence.length - 1) {
                setTimeout(() => {
                    // Eliminar el modal
                    document.body.removeChild(modal);
                    isShowingSequence = false;
                    
                    // Mostrar mensaje en el área de secuencia
                    sequenceDisplay.innerHTML = '';
                    const repeatDiv = document.createElement('div');
                    repeatDiv.className = 'text-center text-2xl font-bold text-purple-600 mb-6';
                    repeatDiv.textContent = '¡Repite la secuencia!';
                    sequenceDisplay.appendChild(repeatDiv);
                    
                    // Configurar área de entrada
                    setupUserInput();
                }, 1000);
            }
        }, delay);
        delay += 1500;
    });
}

function startNewSequence() {
    // Si ya se está mostrando una secuencia, no hacer nada
    if (isShowingSequence) return;
    
    currentSequence = [];
    userSequence = [];
    const length = sequenceLevel + 2; // La secuencia aumenta con el nivel
    
    // Deshabilitar el botón durante la secuencia
    const startButton = document.getElementById('start-sequence');
    if (startButton) {
        startButton.disabled = true;
        startButton.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Generar secuencia aleatoria
    for (let i = 0; i < length; i++) {
        const randomLetter = String.fromCharCode(97 + Math.floor(Math.random() * 26));
        currentSequence.push(randomLetter);
    }
    
    // Mostrar secuencia
    showSequence();
    
    // Habilitar el botón después de mostrar la secuencia
    setTimeout(() => {
        if (startButton) {
            startButton.disabled = false;
            startButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }, length * 1500 + 1000);
    
    updateExerciseButtons();
}

function updateSequenceUI() {
    document.getElementById('sequence-level').textContent = sequenceLevel;
    document.getElementById('sequence-points').textContent = sequencePoints;
}

// Ejercicio 6: Memorama de Señas
function initMemoryExercise() {
    const startButton = document.getElementById('start-memory');
    if (!startButton) return;
    
    startButton.addEventListener('click', startNewMemoryGame);
}

function startNewMemoryGame() {
    const memoryGrid = document.getElementById('memory-grid');
    memoryGrid.innerHTML = '';
    matchedPairs = 0;
    attempts = 0;
    flippedCards = [];
    updateMemoryUI();
    
    // Limpiar el mensaje de feedback
    const feedback = document.getElementById('memory-feedback');
    feedback.textContent = '';
    feedback.className = 'text-lg font-medium';
    
    // Crear pares de cartas (8 pares = 16 cartas)
    const letters = shuffleArray(Array.from({length: 26}, (_, i) => String.fromCharCode(97 + i))).slice(0, 8);
    memoryCards = [];
    
    // Crear par de cada letra (letra y seña)
    letters.forEach(letter => {
        memoryCards.push(
            { type: 'letter', value: letter, matched: false },
            { type: 'sign', value: letter, matched: false }
        );
    });
    
    // Mezclar cartas
    memoryCards = shuffleArray(memoryCards);
    
    // Crear y mostrar cartas
    memoryCards.forEach((card, index) => {
        const cardElement = document.createElement('div');
        cardElement.className = 'memory-card bg-white rounded-lg shadow-md p-4 cursor-pointer transform transition-all duration-300 hover:scale-105';
        cardElement.dataset.index = index;
        cardElement.innerHTML = '<div class="card-back bg-purple-100 w-full h-24 rounded-lg flex items-center justify-center">?</div>';
        cardElement.addEventListener('click', () => flipCard(index));
        memoryGrid.appendChild(cardElement);
    });

    // Actualizar los botones
    updateExerciseButtons();
}

function flipCard(index) {
    if (flippedCards.length === 2 || memoryCards[index].matched || flippedCards.includes(index)) return;
    
    const card = memoryCards[index];
    const cardElement = document.querySelector(`[data-index="${index}"]`);
    
    // Mostrar carta
    if (card.type === 'letter') {
        cardElement.innerHTML = `<div class="w-full h-24 rounded-lg flex items-center justify-center text-4xl font-bold text-purple-600">${card.value.toUpperCase()}</div>`;
    } else {
        cardElement.innerHTML = `<img src="/signs/${card.value}.png" class="w-full h-24 object-contain" alt="${card.value}">`;
    }
    
    flippedCards.push(index);
    
    if (flippedCards.length === 2) {
        attempts++;
        updateMemoryUI();
        
        const card1 = memoryCards[flippedCards[0]];
        const card2 = memoryCards[flippedCards[1]];
        
        if (card1.value === card2.value) {
            // Par encontrado
            card1.matched = true;
            card2.matched = true;
            matchedPairs++;
            updateMemoryUI();
            flippedCards = [];
            
            // Guardar práctica exitosa
            const practiceData = {
                tipo_ejercicio: 'memoryExercise',
                respuesta_correcta: true,
                detalles: {
                    letra: card1.value,
                    intentos: attempts,
                    pares_encontrados: matchedPairs
                }
            };
            savePractice(practiceData);
            
            if (matchedPairs === 8) {
                const feedback = document.getElementById('memory-feedback');
                feedback.textContent = '¡Felicitaciones! ¡Has completado el juego!';
                feedback.className = 'text-lg font-medium text-green-600';
                
                // Reiniciar el juego después de 2 segundos
                setTimeout(() => {
                    feedback.textContent = '¡Iniciando nuevo juego!';
                    setTimeout(() => {
                        startNewMemoryGame();
                    }, 1000);
                }, 2000);
            }
        } else {
            // No coinciden, voltear de nuevo sin mostrar mensaje de error
            setTimeout(() => {
                flippedCards.forEach(cardIndex => {
                    const element = document.querySelector(`[data-index="${cardIndex}"]`);
                    element.innerHTML = '<div class="card-back bg-purple-100 w-full h-24 rounded-lg flex items-center justify-center">?</div>';
                });
                flippedCards = [];
            }, 1000);
        }
    }
}

function updateMemoryUI() {
    document.getElementById('pairs-found').textContent = matchedPairs;
    document.getElementById('memory-attempts').textContent = attempts;
}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

// Función auxiliar para crear tarjetas de señas
function createSignCard(letter, size = 'normal') {
    const card = document.createElement('div');
    
    // Determinar tamaño según el parámetro
    let sizeClass = 'w-full h-24';
    if (size === 'large') {
        sizeClass = 'w-40 h-40';
    } else if (size === 'small') {
        sizeClass = 'w-20 h-20';
    }
    
    card.className = `bg-white rounded-lg shadow-md p-4 transition-all duration-300 hover:shadow-xl relative ${size === 'large' ? 'scale-animation' : ''}`;
    card.innerHTML = `<img src="/signs/${letter}.png" class="${sizeClass} object-contain" alt="${letter}">`;
    
    // Añadir animación de escala si es grande
    if (size === 'large') {
        card.style.animation = 'pulse 1s ease-in-out';
    }
    
    return card;
}

// Función para generar el ejercicio de velocidad de señas
function generateSpeedExercise() {
    const exerciseArea = document.getElementById('speed-exercise-area');
    exerciseArea.innerHTML = `
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <div class="flex justify-between items-center mb-4">
                    <p class="text-lg font-semibold text-gray-700">Nivel: <span id="speed-level-display" class="text-purple-600">${speedLevel}</span></p>
                    <p class="text-lg font-semibold text-gray-700">Puntos: <span id="speed-points-display" class="text-purple-600">${speedPoints}</span></p>
                </div>
                <p class="text-lg font-semibold text-gray-700 mb-4">¡Selecciona la seña correcta antes de que se acabe el tiempo!</p>
                <div class="relative w-full h-4 bg-gray-200 rounded-full overflow-hidden mb-4">
                    <div id="speed-timer" class="absolute top-0 left-0 h-full bg-purple-600 transition-all duration-100"></div>
                </div>
                <h2 id="speed-letter" class="text-4xl font-bold text-purple-600"></h2>
            </div>
            <div id="speed-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8"></div>
            <p id="speed-feedback" class="text-lg font-medium text-center"></p>
        </div>
    `;

    // Mostrar el botón de detener y ocultar el de iniciar
    document.getElementById('start-speed').classList.add('hidden');
    document.getElementById('stop-speed').classList.remove('hidden');
    
    isSpeedExerciseActive = true;
    startSpeedRound();
}

function startSpeedRound() {
    if (!isSpeedExerciseActive) return;
    
    if (speedTimer) clearTimeout(speedTimer);
    
    // Seleccionar letra aleatoria
    const correctLetter = letters[Math.floor(Math.random() * letters.length)];
    document.getElementById('speed-letter').textContent = correctLetter.toUpperCase();
    
    // Generar opciones
    const options = [correctLetter];
    while (options.length < 4) {
        const randomLetter = letters[Math.floor(Math.random() * letters.length)];
        if (!options.includes(randomLetter)) {
            options.push(randomLetter);
        }
    }
    
    // Mezclar opciones
    const shuffledOptions = options.sort(() => 0.5 - Math.random());
    
    // Mostrar opciones
    const optionsContainer = document.getElementById('speed-options');
    optionsContainer.innerHTML = '';
    shuffledOptions.forEach((letter, index) => {
        const container = document.createElement('div');
        container.className = 'relative group cursor-pointer';
        
        const img = createOptionImage(`/signs/${letter}.png`, letter, 'fade-in');
        img.dataset.letter = letter;
        addFadeInAnimation(img, index * 0.1);
        
        img.addEventListener('click', () => handleSpeedAnswer(letter, correctLetter));
        container.appendChild(img);
        optionsContainer.appendChild(container);
    });
    
    // Iniciar temporizador con animación CSS
    const timerBar = document.getElementById('speed-timer');
    timerBar.style.transition = 'none';
    timerBar.style.width = '100%';
    
    // Forzar reflow para reiniciar la animación
    void timerBar.offsetWidth;
    
    // Iniciar animación
    timerBar.style.transition = `width ${speedTimeLimit}ms linear`;
    timerBar.style.width = '0%';
    
    // Configurar temporizador
    speedTimer = setTimeout(() => {
        handleSpeedAnswer(null, correctLetter);
    }, speedTimeLimit);
    
    // Actualizar el feedback
    const feedback = document.getElementById('speed-feedback');
    if (feedback) {
        feedback.textContent = '';
    }
}

function stopSpeedExercise() {
    isSpeedExerciseActive = false;
    
    if (speedTimer) {
        clearTimeout(speedTimer);
        speedTimer = null;
    }
    
    // Mostrar el botón de iniciar y ocultar el de detener
    document.getElementById('start-speed').classList.remove('hidden');
    document.getElementById('stop-speed').classList.add('hidden');
    
    // Mostrar mensaje de finalización
    const exerciseArea = document.getElementById('speed-exercise-area');
    if (exerciseArea) {
        exerciseArea.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8 text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Ejercicio finalizado</h3>
                <p class="text-lg text-gray-600 mb-2">Puntos obtenidos: ${speedPoints}</p>
                <p class="text-lg text-gray-600">Nivel alcanzado: ${speedLevel}</p>
            </div>
        `;
    }
    
    // Actualizar los contadores en la interfaz
    document.getElementById('speed-level').textContent = speedLevel;
    document.getElementById('speed-points').textContent = speedPoints;
}

async function handleSpeedAnswer(selectedLetter, correctLetter) {
    if (!isSpeedExerciseActive) return;
    
    if (speedTimer) clearTimeout(speedTimer);
    
    const isCorrect = selectedLetter === correctLetter;
    const feedback = document.getElementById('speed-feedback');
    
    const practiceData = {
        tipo_ejercicio: 'speedExercise',
        respuesta_correcta: isCorrect,
        detalles: {
            nivel: speedLevel,
            letra: correctLetter,
            tiempo_limite: speedTimeLimit
        }
    };

    await savePractice(practiceData);

    if (isCorrect) {
        feedback.textContent = '¡Correcto!';
        feedback.className = 'text-lg font-medium text-green-600';
        
        speedPoints += Math.round((speedTimeLimit / 1000) * speedLevel);
        if (speedPoints >= speedLevel * 50) {
            speedLevel = Math.min(5, speedLevel + 1);
            speedTimeLimit = Math.max(2000, speedTimeLimit - 500);
        }
        
        document.getElementById('speed-level-display').textContent = speedLevel;
        document.getElementById('speed-points-display').textContent = speedPoints;
        document.getElementById('speed-level').textContent = speedLevel;
        document.getElementById('speed-points').textContent = speedPoints;
    } else {
        feedback.textContent = 'Incorrecto';
        feedback.className = 'text-lg font-medium text-red-600';
    }
    
    setTimeout(() => {
        if (isSpeedExerciseActive) {
            startSpeedRound();
        }
    }, 1000);
}

// Modificar los event listeners para usar el nuevo sistema
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

    checkAnswerButton.addEventListener('click', async () => {
        const selectedOption = optionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctLetter;
        const practiceData = {
            tipo_ejercicio: 'letterIdentification',
            respuesta_correcta: isCorrect,
            detalles: {
                letter: correctLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            selectedOption.classList.add('scale-110');
            setTimeout(() => {
                generateExercise();
            }, 1500);
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

    checkWordAnswerButton.addEventListener('click', async () => {
        const isCorrect = selectedLetters.join('') === correctWord;
        const practiceData = {
            tipo_ejercicio: 'wordCompletion',
            respuesta_correcta: isCorrect,
            detalles: {
                word: correctWord,
                selectedWord: selectedLetters.join('')
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateWordExercise();
            }, 1500);
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

    checkSignAnswerButton.addEventListener('click', async () => {
        const selectedOption = signOptionsContainer.querySelector('.bg-purple-600');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === correctSignLetter;
        const practiceData = {
            tipo_ejercicio: 'signRecognition',
            respuesta_correcta: isCorrect,
            detalles: {
                letter: correctSignLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateSignRecognition();
            }, 1500);
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

    checkIncorrectSignAnswerButton.addEventListener('click', async () => {
        const selectedOption = incorrectOptionsContainer.querySelector('img.ring-4');
        if (!selectedOption) {
            showNotification('Por favor, selecciona una opción', false);
            return;
        }

        const isCorrect = selectedOption.dataset.letter === incorrectLetter;
        const practiceData = {
            tipo_ejercicio: 'errorDetection',
            respuesta_correcta: isCorrect,
            detalles: {
                word: incorrectWord,
                incorrectLetter: incorrectLetter,
                selectedLetter: selectedOption.dataset.letter
            }
        };

        await savePractice(practiceData);

        if (isCorrect) {
            setTimeout(() => {
                generateIncorrectSignExercise();
            }, 1500);
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

    initSequenceExercise();
    initMemoryExercise();
    
    // Inicializar ejercicio de velocidad
    const speedExerciseButton = document.getElementById('start-speed');
    if (speedExerciseButton) {
        speedExerciseButton.addEventListener('click', generateSpeedExercise);
    }

    const stopSpeedButton = document.getElementById('stop-speed');
    if (stopSpeedButton) {
        stopSpeedButton.addEventListener('click', stopSpeedExercise);
    }

    // Mejorar los botones de los ejercicios
    updateExerciseButtons();

    // Agregar estilos CSS para la animación de escala
    addSequenceStyles();
});

// Mejorar los botones de los ejercicios
function updateExerciseButtons() {
    // Botón de secuencia
    const startSequenceButton = document.getElementById('start-sequence');
    if (startSequenceButton) {
        startSequenceButton.className = 'px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
        startSequenceButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Iniciar Secuencia</span>
        `;
    }

    // Botón de memorama
    const startMemoryButton = document.getElementById('start-memory');
    if (startMemoryButton) {
        startMemoryButton.className = 'px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
        startMemoryButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Nuevo Juego</span>
        `;
    }

    // Botón de traducción de frases
    const startPhraseButton = document.getElementById('start-phrase');
    if (startPhraseButton) {
        startPhraseButton.className = 'px-6 py-3 bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
        startPhraseButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>Iniciar Traducción</span>
        `;
    }

    // Botón de velocidad de señas
    const startSpeedButton = document.getElementById('start-speed');
    if (startSpeedButton) {
        startSpeedButton.className = 'px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
        startSpeedButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Iniciar Velocidad</span>
        `;
    }

    // Botones de verificación
    const verificationButtons = [
        'check-answer',
        'check-word-answer',
        'check-sign-answer',
        'check-incorrect-sign-answer',
        'check-sequence-answer'
    ];

    const buttonTexts = {
        'check-answer': 'Verificar Respuesta',
        'check-word-answer': 'Verificar Palabra',
        'check-sign-answer': 'Verificar Seña',
        'check-incorrect-sign-answer': 'Verificar Selección',
        'check-sequence-answer': 'Verificar Secuencia'
    };

    verificationButtons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button && !button.classList.contains('verification-button-styled')) {
            button.className = 'verification-button-styled px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
            button.innerHTML = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>${buttonTexts[buttonId]}</span>
            `;
        }
    });
}

// Función para crear botones de verificación con estilo consistente
function createVerificationButton(id, text) {
    const button = document.createElement('button');
    button.id = id;
    button.className = 'verification-button-styled px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-lg font-bold rounded-full shadow-lg hover:shadow-xl transform transition-all duration-300 hover:scale-105 hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center space-x-2';
    button.innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>${text}</span>
    `;
    return button;
}

// Agregar estilos CSS para la animación de escala
function addSequenceStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .scale-animation {
            animation: pulse 1s ease-in-out;
        }
    `;
    document.head.appendChild(style);
}