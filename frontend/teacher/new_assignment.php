<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: ../auth/login.php");
    exit();
}

// Obtener lista de ejercicios disponibles
$query_ejercicios = "SELECT id, titulo, descripcion, nivel_dificultad 
                    FROM ejercicios 
                    ORDER BY nivel_dificultad, titulo";
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
</head>
<body class="bg-gradient-to-b from-white to-purple-50 min-h-screen">
    <?php include '../header.php'; ?>

    <main class="pt-32 pb-24">
        <div class="container mx-auto px-4 max-w-3xl">
            <div class="mb-12">
                <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Nueva Asignación
                </h1>
                <p class="text-gray-600">
                    Asigna ejercicios a tus estudiantes y establece fechas límite
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <form action="process_assignment.php" method="POST" class="space-y-6">
                    <!-- Selección de ejercicio -->
                    <div>
                        <label for="ejercicio" class="block text-sm font-medium text-gray-700 mb-2">
                            Ejercicio a asignar
                        </label>
                        <select id="ejercicio" name="ejercicio_id" required
                            class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 rounded-md">
                            <option value="">Selecciona un ejercicio</option>
                            <?php while ($ejercicio = mysqli_fetch_assoc($ejercicios)): ?>
                                <option value="<?php echo $ejercicio['id']; ?>">
                                    <?php echo htmlspecialchars($ejercicio['titulo']); ?> 
                                    (Dificultad: <?php echo htmlspecialchars($ejercicio['nivel_dificultad']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Descripción del ejercicio seleccionado -->
                    <div id="descripcion_ejercicio" class="hidden">
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h4 class="font-medium text-purple-900 mb-2">Descripción del ejercicio</h4>
                            <p class="text-sm text-purple-700" id="descripcion_texto"></p>
                        </div>
                    </div>

                    <!-- Selección de grupos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Asignar a grupos
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php while ($grupo = mysqli_fetch_assoc($grupos)): ?>
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="grupos[]" value="<?php echo htmlspecialchars($grupo['grupo']); ?>"
                                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">
                                            Grupo <?php echo htmlspecialchars($grupo['grupo']); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de inicio
                            </label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label for="fecha_limite" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha límite
                            </label>
                            <input type="date" id="fecha_limite" name="fecha_limite" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>

                    <!-- Puntos y requisitos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="puntos" class="block text-sm font-medium text-gray-700 mb-2">
                                Puntos máximos
                            </label>
                            <input type="number" id="puntos" name="puntos" min="0" max="100" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ej: 10">
                        </div>
                        <div>
                            <label for="intentos" class="block text-sm font-medium text-gray-700 mb-2">
                                Intentos permitidos
                            </label>
                            <input type="number" id="intentos" name="intentos" min="1" max="10" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Ej: 3">
                        </div>
                    </div>

                    <!-- Instrucciones adicionales -->
                    <div>
                        <label for="instrucciones" class="block text-sm font-medium text-gray-700 mb-2">
                            Instrucciones adicionales (opcional)
                        </label>
                        <textarea id="instrucciones" name="instrucciones" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                            placeholder="Agrega instrucciones específicas para esta asignación..."></textarea>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end space-x-4">
                        <a href="dashboard.php" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Crear Asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        // Mostrar descripción del ejercicio seleccionado
        const ejercicios = <?php 
            mysqli_data_seek($ejercicios, 0);
            $ejercicios_data = [];
            while ($ejercicio = mysqli_fetch_assoc($ejercicios)) {
                $ejercicios_data[$ejercicio['id']] = [
                    'titulo' => $ejercicio['titulo'],
                    'descripcion' => $ejercicio['descripcion']
                ];
            }
            echo json_encode($ejercicios_data);
        ?>;

        document.getElementById('ejercicio').addEventListener('change', function() {
            const descripcionDiv = document.getElementById('descripcion_ejercicio');
            const descripcionTexto = document.getElementById('descripcion_texto');
            
            if (this.value && ejercicios[this.value]) {
                descripcionTexto.textContent = ejercicios[this.value].descripcion;
                descripcionDiv.classList.remove('hidden');
            } else {
                descripcionDiv.classList.add('hidden');
            }
        });

        // Validación de fechas
        document.querySelector('form').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaLimite = new Date(document.getElementById('fecha_limite').value);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaInicio < hoy) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser anterior a hoy');
                return;
            }

            if (fechaLimite <= fechaInicio) {
                e.preventDefault();
                alert('La fecha límite debe ser posterior a la fecha de inicio');
                return;
            }

            const grupos = document.querySelectorAll('input[name="grupos[]"]:checked');
            if (grupos.length === 0) {
                e.preventDefault();
                alert('Debes seleccionar al menos un grupo');
                return;
            }
        });

        // Establecer fecha mínima en los inputs de fecha
        const fechaHoy = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_inicio').min = fechaHoy;
        document.getElementById('fecha_limite').min = fechaHoy;
    </script>
</body>
</html> 