document.addEventListener('DOMContentLoaded', () => {
    const startButton = document.getElementById('start-exercise');
    const exerciseArea = document.getElementById('exercise-area');
    const modal = document.getElementById('confirmation-modal');
    const modalPoints = document.getElementById('modal-points');
    const modalTime = document.getElementById('modal-time');
    const fileInput = document.getElementById('evidence-upload');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const removeFile = document.getElementById('remove-file');
    const confirmSubmit = document.getElementById('confirm-submit');
    const cancelSubmit = document.getElementById('cancel-submit');
    
    let startTime;
    let exerciseTimer;
    let exerciseResults = {
        correctas: 0,
        total: 0,
        palabrasCorrectas: 0,
        totalPalabras: 0,
        señasCorrectas: 0,
        totalSeñas: 0,
        erroresDetectados: 0,
        totalErrores: 0
    };
    let evidenceFile = null;

    // Función para iniciar el ejercicio
    startButton.addEventListener('click', () => {
        startButton.parentElement.classList.add('hidden');
        exerciseArea.classList.remove('hidden');
        startTime = new Date();
        initializeExercise();
    });

    // Inicializar el ejercicio según su tipo
    function initializeExercise() {
        switch(ejercicioData.tipo) {
            case 'Identificación de Letras':
                setupLetterIdentification();
                break;
            case 'Palabras Completas':
                setupWordCompletion();
                break;
            case 'Reconocimiento de Señas':
                setupSignRecognition();
                break;
            case 'Detección de Errores':
                setupErrorDetection();
                break;
        }

        // Iniciar temporizador si hay límite de tiempo
        if (ejercicioData.tiempo_limite) {
            startTimer(ejercicioData.tiempo_limite);
        }

        // Escuchar eventos de respuesta
        document.addEventListener('exerciseAnswered', handleExerciseAnswer);
    }

    // Manejar las respuestas de los ejercicios
    function handleExerciseAnswer(event) {
        const { type, isCorrect } = event.detail;
        
        switch(type) {
            case 'letterIdentification':
                exerciseResults.total++;
                if (isCorrect) exerciseResults.correctas++;
                break;
            case 'wordCompletion':
                exerciseResults.totalPalabras++;
                if (isCorrect) exerciseResults.palabrasCorrectas++;
                break;
            case 'signRecognition':
                exerciseResults.totalSeñas++;
                if (isCorrect) exerciseResults.señasCorrectas++;
                break;
            case 'errorDetection':
                exerciseResults.totalErrores++;
                if (isCorrect) exerciseResults.erroresDetectados++;
                break;
        }

        // Verificar si se alcanzó el número de ejercicios requeridos
        if (shouldFinishExercise()) {
            showConfirmationModal();
        }
    }

    // Verificar si se debe terminar el ejercicio
    function shouldFinishExercise() {
        const requiredExercises = ejercicioData.numero_ejercicios || 10; // Default a 10 si no está especificado
        
        switch(ejercicioData.tipo) {
            case 'Identificación de Letras':
                return exerciseResults.total >= requiredExercises;
            case 'Palabras Completas':
                return exerciseResults.totalPalabras >= requiredExercises;
            case 'Reconocimiento de Señas':
                return exerciseResults.totalSeñas >= requiredExercises;
            case 'Detección de Errores':
                return exerciseResults.totalErrores >= requiredExercises;
            default:
                return false;
        }
    }

    // Temporizador para el ejercicio
    function startTimer(seconds) {
        let timeLeft = seconds;
        const timerDisplay = document.createElement('div');
        timerDisplay.className = 'fixed top-4 right-4 bg-white rounded-full shadow-lg px-4 py-2 text-lg font-semibold';
        document.body.appendChild(timerDisplay);

        exerciseTimer = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = `Tiempo: ${timeLeft}s`;

            if (timeLeft <= 0) {
                clearInterval(exerciseTimer);
                showConfirmationModal();
            }
        }, 1000);
    }

    // Mostrar modal de confirmación
    function showConfirmationModal() {
        clearInterval(exerciseTimer);
        document.removeEventListener('exerciseAnswered', handleExerciseAnswer);
        
        const endTime = new Date();
        const timeElapsed = Math.round((endTime - startTime) / 1000);
        const points = calculatePoints(exerciseResults);

        modalPoints.textContent = `${points} / ${ejercicioData.puntos_maximos}`;
        modalTime.textContent = `${timeElapsed} segundos`;

        modal.classList.add('show');
    }

    // Manejar subida de archivo
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('El archivo es demasiado grande. Por favor, selecciona un archivo menor a 10MB.');
                fileInput.value = '';
                return;
            }
            evidenceFile = file;
            fileName.textContent = file.name;
            filePreview.classList.remove('hidden');
        }
    });

    // Remover archivo
    removeFile.addEventListener('click', () => {
        evidenceFile = null;
        fileInput.value = '';
        filePreview.classList.add('hidden');
    });

    // Cancelar envío
    cancelSubmit.addEventListener('click', () => {
        modal.classList.remove('show');
    });

    // Confirmar envío
    confirmSubmit.addEventListener('click', () => {
        if (!evidenceFile) {
            alert('Por favor, sube una evidencia antes de enviar el ejercicio.');
            return;
        }
        finishExercise(exerciseResults);
    });

    // Función para finalizar el ejercicio
    function finishExercise(results) {
        const endTime = new Date();
        const timeElapsed = Math.round((endTime - startTime) / 1000);

        // Crear FormData para enviar archivo
        const formData = new FormData();
        formData.append('evidence', evidenceFile);
        formData.append('data', JSON.stringify({
            estudiante_asignacion_id: ejercicioData.estudiante_asignacion_id,
            tipo_ejercicio: ejercicioData.tipo,
            tiempo_empleado: timeElapsed,
            detalles: results,
            puntos_obtenidos: calculatePoints(results)
        }));

        // Enviar resultados al servidor
        fetch('/backend/ejercicios/save_result.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCompletionMessage(calculatePoints(results));
            } else {
                alert('Error al guardar los resultados: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar los resultados');
        });
    }

    // Calcular puntos basados en los resultados
    function calculatePoints(results) {
        let points = 0;
        const maxPoints = ejercicioData.puntos_maximos;

        switch(ejercicioData.tipo) {
            case 'Identificación de Letras':
                points = Math.round((results.correctas / results.total) * maxPoints);
                break;
            case 'Palabras Completas':
                points = Math.round((results.palabrasCorrectas / results.totalPalabras) * maxPoints);
                break;
            case 'Reconocimiento de Señas':
                points = Math.round((results.señasCorrectas / results.totalSeñas) * maxPoints);
                break;
            case 'Detección de Errores':
                points = Math.round((results.erroresDetectados / results.totalErrores) * maxPoints);
                break;
        }

        return Math.max(0, Math.min(points, maxPoints));
    }

    // Mostrar mensaje de finalización
    function showCompletionMessage(points) {
        modal.classList.remove('show');
        exerciseArea.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">¡Ejercicio completado!</h2>
                <p class="text-lg text-gray-600 mb-6">Has obtenido ${points} puntos</p>
                <a href="/frontend/student/dashboard.php" 
                   class="inline-block px-6 py-3 bg-purple-600 text-white rounded-full font-semibold
                          hover:bg-purple-700 transition-colors">
                    Volver al dashboard
                </a>
            </div>
        `;
    }

    // Configuración específica para cada tipo de ejercicio
    function setupLetterIdentification() {
        generateExercise();
    }

    function setupWordCompletion() {
        generateWordExercise();
    }

    function setupSignRecognition() {
        generateSignRecognition();
    }

    function setupErrorDetection() {
        generateIncorrectSignExercise();
    }
}); 