<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener lista de ejercicios disponibles
$query_ejercicios = "SELECT e.id, e.titulo, e.descripcion, e.nivel, e.puntos_maximos as puntos_sugeridos, 
                            e.tiempo_limite as tiempo_sugerido, c.nombre as categoria 
                     FROM ejercicios e 
                     JOIN categorias_ejercicios c ON e.categoria_id = c.id 
                     ORDER BY c.nombre, e.nivel, e.titulo";
$ejercicios = mysqli_query($conexion, $query_ejercicios);

// Obtener lista de grupos
$query_grupos = "SELECT DISTINCT grupo 
                FROM usuarios 
                WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'estudiante')
                AND grupo IS NOT NULL
                ORDER BY grupo";
$grupos = mysqli_query($conexion, $query_grupos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands - Nueva Asignación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>
    <?php include '../components/modal.php'; ?>
    <?php showModal('message-modal'); ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-4xl">
            <!-- Encabezado -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <a href="dashboard.php" class="text-purple-600 hover:text-purple-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Nueva Asignación</h1>
                        <p class="mt-2 text-gray-600">Crea una nueva asignación para tus estudiantes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center text-purple-600">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h2 class="text-xl font-semibold">Detalles de la Asignación</h2>
                    </div>
                </div>

                <form action="process_assignment.php" method="POST" class="p-6 space-y-8">
                    <!-- Sección: Selección de Ejercicio -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Paso 1: Selecciona el Ejercicio
                        </h3>
                        <div class="space-y-4">
                            <select id="ejercicio" name="ejercicio_id" required
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Selecciona un ejercicio</option>
                                <?php 
                                $current_category = '';
                                while ($ejercicio = mysqli_fetch_assoc($ejercicios)): 
                                    if ($current_category != $ejercicio['categoria']) {
                                        if ($current_category != '') {
                                            echo '</optgroup>';
                                        }
                                        $current_category = $ejercicio['categoria'];
                                        echo '<optgroup label="' . htmlspecialchars($ejercicio['categoria']) . '">';
                                    }
                                ?>
                                    <option value="<?php echo $ejercicio['id']; ?>" 
                                            data-puntos="<?php echo $ejercicio['puntos_sugeridos']; ?>"
                                            data-tiempo="<?php echo $ejercicio['tiempo_sugerido']; ?>"
                                            data-descripcion="<?php echo htmlspecialchars($ejercicio['descripcion']); ?>">
                                        <?php echo htmlspecialchars($ejercicio['titulo']); ?> 
                                        (Nivel: <?php echo htmlspecialchars($ejercicio['nivel']); ?>)
                                    </option>
                                <?php 
                                endwhile;
                                if ($current_category != '') {
                                    echo '</optgroup>';
                                }
                                ?>
                            </select>

                            <!-- Descripción del ejercicio -->
                            <div id="descripcion_ejercicio" class="hidden mt-4 bg-white p-4 rounded-lg border border-purple-100">
                                <h4 class="font-medium text-purple-900 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Información del Ejercicio
                                </h4>
                                <p class="text-sm text-gray-700 mb-3" id="descripcion_texto"></p>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-medium text-gray-700">Tiempo sugerido:</span>
                                        <span id="tiempo_sugerido" class="ml-2 text-purple-600"></span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        <span class="font-medium text-gray-700">Puntos sugeridos:</span>
                                        <span id="puntos_sugeridos" class="ml-2 text-purple-600"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Grupos -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Paso 2: Selecciona los Grupos
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php while ($grupo = mysqli_fetch_assoc($grupos)): ?>
                                <label class="relative flex items-center p-3 rounded-lg border border-gray-200 hover:border-purple-500 cursor-pointer transition-colors">
                                    <input type="checkbox" name="grupos[]" value="<?php echo htmlspecialchars($grupo['grupo']); ?>"
                                        class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm font-medium text-gray-700">
                                        Grupo <?php echo htmlspecialchars($grupo['grupo']); ?>
                                    </span>
                                </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Sección: Fechas y Configuración -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Paso 3: Configura los Detalles
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de inicio
                                </label>
                                <div class="relative">
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 pl-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">📅</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha límite
                                </label>
                                <div class="relative">
                                    <input type="date" id="fecha_limite" name="fecha_limite" required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 pl-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">📅</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Puntos máximos
                                </label>
                                <div class="relative">
                                    <input type="number" id="puntos" name="puntos" min="0" max="100" required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 pl-10"
                                        placeholder="Ej: 10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">🎯</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Intentos permitidos
                                </label>
                                <div class="relative">
                                    <input type="number" id="intentos" name="intentos" min="1" max="10" required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 pl-10"
                                        placeholder="Ej: 3">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">🔄</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Instrucciones -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Paso 4: Instrucciones Adicionales
                        </h3>
                        <textarea id="instrucciones" name="instrucciones" rows="4"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                            placeholder="Agrega instrucciones específicas para esta asignación..."></textarea>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end space-x-4 pt-6">
                        <a href="dashboard.php" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Crear Asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        // Inicializar Flatpickr para los campos de fecha
        flatpickr("input[type=date]", {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today",
            altInput: true,
            altFormat: "d/m/Y",
            disableMobile: true
        });

        // Mostrar descripción del ejercicio seleccionado
        const ejercicios = <?php 
            mysqli_data_seek($ejercicios, 0);
            $ejercicios_data = [];
            while ($ejercicio = mysqli_fetch_assoc($ejercicios)) {
                $ejercicios_data[$ejercicio['id']] = [
                    'titulo' => $ejercicio['titulo'],
                    'descripcion' => $ejercicio['descripcion'],
                    'puntos_sugeridos' => $ejercicio['puntos_sugeridos'],
                    'tiempo_sugerido' => $ejercicio['tiempo_sugerido']
                ];
            }
            echo json_encode($ejercicios_data);
        ?>;

        document.getElementById('ejercicio').addEventListener('change', function() {
            const descripcionDiv = document.getElementById('descripcion_ejercicio');
            const descripcionTexto = document.getElementById('descripcion_texto');
            const puntosSugeridos = document.getElementById('puntos_sugeridos');
            const tiempoSugerido = document.getElementById('tiempo_sugerido');
            const puntosInput = document.getElementById('puntos');
            
            if (this.value && ejercicios[this.value]) {
                const ejercicio = ejercicios[this.value];
                descripcionTexto.textContent = ejercicio.descripcion;
                puntosSugeridos.textContent = ejercicio.puntos_sugeridos + ' puntos';
                tiempoSugerido.textContent = ejercicio.tiempo_sugerido + ' segundos';
                puntosInput.value = ejercicio.puntos_sugeridos;
                descripcionDiv.classList.remove('hidden');
            } else {
                descripcionDiv.classList.add('hidden');
            }
        });

        // Manejo del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Verificar que se haya seleccionado al menos un grupo
            const gruposSeleccionados = document.querySelectorAll('input[name="grupos[]"]:checked');
            if (gruposSeleccionados.length === 0) {
                showMessageModal('message-modal', 'Error', 'Debes seleccionar al menos un grupo', null, false);
                return;
            }

            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaLimite = document.getElementById('fecha_limite').value;

            if (fechaLimite < fechaInicio) {
                showMessageModal('message-modal', 'Error', 'La fecha límite no puede ser anterior a la fecha de inicio', null, false);
                return;
            }

            // Si todo está bien, enviar el formulario
            const formData = new FormData(this);
            
            fetch('process_assignment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessageModal(
                        'message-modal',
                        '¡Éxito!',
                        'La asignación se ha creado correctamente.',
                        function() {
                            window.location.href = 'assignments.php';
                        },
                        false
                    );
                } else {
                    showMessageModal('message-modal', 'Error', data.message || 'Ha ocurrido un error al crear la asignación', null, false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessageModal('message-modal', 'Error', 'Ha ocurrido un error al procesar la solicitud', null, false);
            });
        });
    </script>
</body>
</html> 