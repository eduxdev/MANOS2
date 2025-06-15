document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const clearBtn = document.getElementById('clearButton');
    const videoElement = document.getElementById('videoElement');
    const noCamera = document.getElementById('noCamera');
    const detectionOverlay = document.getElementById('detectionOverlay');
    const statusIndicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    const resultText = document.getElementById('resultText');
    const modeSelector = document.getElementById('modeSelector');

    // Variables de estado
    let stream = null;
    let model = null;
    let isModelLoading = false;
    let isDetecting = false;
    let detectionInterval = null;
    let lastDetectedSign = '';
    let detectionCount = 0;
    let lastDetectionTime = 0;
    const detectionCooldown = 1500; // 1.5 segundos entre detecciones
    let recognizedText = '';
    let detectionMode = 'alphabet'; // 'alphabet' o 'words'

    // Canvas para procesamiento
    const ctx = detectionOverlay.getContext('2d');

    // Clases para el reconocimiento del alfabeto (ejemplo)
    const alphabetClasses = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 
        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 
        'u', 'v', 'w', 'x', 'y', 'z', 'espacio', 'borrar'
    ];

    // Clases para el reconocimiento de palabras (ejemplo)
    const wordClasses = [
        'yo', 'tu', 'el', 'ella', 'nosotros', 'ellos',
        'hola', 'adios', 'por favor', 'gracias',
        'ayuda', 'si', 'no', 'espacio', 'borrar'
    ];

    // Función para actualizar el estado de la UI
    function updateStatus(status, color) {
        statusIndicator.style.backgroundColor = color;
        statusText.textContent = status;
    }

    // Función para cargar el modelo de TensorFlow.js
    async function loadModel() {
        if (isModelLoading) return;
        
        isModelLoading = true;
        updateStatus('Cargando modelo...', '#fbbf24'); // Amarillo

        try {
            // En una implementación real, deberías cargar tu modelo entrenado
            // Esto es un placeholder para demostración:
            // model = await tf.loadLayersModel('/path/to/your/model.json');
            
            // Simulamos la carga del modelo para esta demostración
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            updateStatus('Modelo cargado - Listo para detectar', '#22c55e'); // Verde
            isModelLoading = false;
            return true;
        } catch (error) {
            console.error('Error al cargar el modelo:', error);
            updateStatus('Error al cargar el modelo', '#ef4444'); // Rojo
            isModelLoading = false;
            return false;
        }
    }

    // Iniciar la cámara
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            
            videoElement.srcObject = stream;
            noCamera.style.display = 'none';
            
            // Ajustar tamaño del canvas al video
            videoElement.onloadedmetadata = () => {
                const width = videoElement.videoWidth;
                const height = videoElement.videoHeight;
                
                detectionOverlay.width = width;
                detectionOverlay.height = height;
                
                // Cargar modelo después de iniciar la cámara
                loadModel().then(success => {
                    if (success) {
                        startDetection();
                    }
                });
            };
            
            // Actualizar UI
            startCameraBtn.disabled = true;
            stopCameraBtn.disabled = false;
            clearBtn.disabled = false;
            updateStatus('Cámara iniciada', '#3b82f6'); // Azul
            
            return true;
        } catch (error) {
            console.error('Error al acceder a la cámara:', error);
            updateStatus('Error al acceder a la cámara', '#ef4444'); // Rojo
            return false;
        }
    }

    // Detener la cámara
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            videoElement.srcObject = null;
            stopDetection();
            
            // Actualizar UI
            startCameraBtn.disabled = false;
            stopCameraBtn.disabled = true;
            clearBtn.disabled = true;
            noCamera.style.display = 'flex';
            updateStatus('Cámara detenida', '#6b7280'); // Gris
        }
    }

    // Iniciar detección
    function startDetection() {
        if (isDetecting) return;
        
        isDetecting = true;
        
        // Establecer un intervalo para la detección
        detectionInterval = setInterval(() => {
            if (videoElement.readyState === 4) {
                detectSigns();
            }
        }, 200); // Detectar cada 200ms
    }

    // Detener detección
    function stopDetection() {
        isDetecting = false;
        if (detectionInterval) {
            clearInterval(detectionInterval);
            detectionInterval = null;
        }
    }

    // Función para detectar señas (simulada para demostración)
    async function detectSigns() {
        // En una implementación real, procesarías el frame del video
        // y lo pasarías al modelo para obtener una predicción
        
        // Dibujar el frame de video en el canvas (para visualización)
        ctx.clearRect(0, 0, detectionOverlay.width, detectionOverlay.height);
        ctx.drawImage(videoElement, 0, 0, detectionOverlay.width, detectionOverlay.height);
        
        // Simulación de detección para demostración
        const currentTime = Date.now();
        if (currentTime - lastDetectionTime > detectionCooldown) {
            // Obtener clases según el modo
            const classes = detectionMode === 'alphabet' ? alphabetClasses : wordClasses;
            
            // Simular detección aleatoria (en un caso real, usarías tu modelo)
            const randomIndex = Math.floor(Math.random() * classes.length);
            const detectedSign = classes[randomIndex];
            
            if (detectedSign === lastDetectedSign) {
                detectionCount++;
            } else {
                detectionCount = 1;
                lastDetectedSign = detectedSign;
            }
            
            // Si se detecta la misma seña varias veces, se confirma
            if (detectionCount >= 3) {
                processDetectedSign(detectedSign);
                lastDetectionTime = currentTime;
                detectionCount = 0;
            }
            
            // Dibujar un rectángulo alrededor de la mano (simulado)
            const handX = detectionOverlay.width / 2 - 100;
            const handY = detectionOverlay.height / 2 - 100;
            const handWidth = 200;
            const handHeight = 200;
            
            ctx.strokeStyle = '#8b5cf6';
            ctx.lineWidth = 3;
            ctx.strokeRect(handX, handY, handWidth, handHeight);
            
            // Mostrar la etiqueta de lo que se está detectando
            ctx.fillStyle = 'rgba(139, 92, 246, 0.7)';
            ctx.fillRect(handX, handY - 30, handWidth, 30);
            ctx.fillStyle = 'white';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(`Detectando: ${lastDetectedSign}`, handX + handWidth / 2, handY - 10);
        }
    }

    // Procesar la seña detectada
    function processDetectedSign(sign) {
        // Lógica para manejar la seña detectada
        if (sign === 'borrar') {
            // Borrar el último carácter
            recognizedText = recognizedText.slice(0, -1);
        } else if (sign === 'espacio') {
            // Añadir un espacio
            recognizedText += ' ';
        } else {
            // Añadir la letra o palabra
            recognizedText += sign;
        }
        
        // Actualizar el texto reconocido en la UI
        updateRecognizedText();
        
        // Efecto visual para mostrar que se detectó algo
        statusIndicator.classList.add('pulse');
        setTimeout(() => {
            statusIndicator.classList.remove('pulse');
        }, 300);
    }

    // Actualizar el texto reconocido en la UI
    function updateRecognizedText() {
        if (recognizedText.trim() === '') {
            resultText.innerHTML = '<p class="text-gray-400 italic">El texto reconocido aparecerá aquí...</p>';
        } else {
            resultText.innerHTML = `<p class="text-gray-800">${recognizedText}</p>`;
            
            // Opcionalmente, convertir a voz
            speakText(recognizedText);
        }
    }

    // Convertir texto a voz
    function speakText(text) {
        // Solo pronunciar la última palabra o letra
        const lastWord = text.split(' ').pop();
        
        if ('speechSynthesis' in window && lastWord) {
            const speech = new SpeechSynthesisUtterance(lastWord);
            speech.lang = 'es-ES';
            window.speechSynthesis.speak(speech);
        }
    }

    // Limpiar el texto reconocido
    function clearRecognizedText() {
        recognizedText = '';
        updateRecognizedText();
    }

    // Event Listeners
    startCameraBtn.addEventListener('click', startCamera);
    stopCameraBtn.addEventListener('click', stopCamera);
    clearBtn.addEventListener('click', clearRecognizedText);
    
    // Cambiar modo de detección
    modeSelector.addEventListener('change', () => {
        detectionMode = modeSelector.value;
        updateStatus(`Modo: ${detectionMode}`, '#3b82f6');
    });
    
    // Estilos CSS adicionales para animaciones
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1); }
        }
        .pulse {
            animation: pulse 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);
}); 