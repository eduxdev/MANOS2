<?php
session_start();
require_once '../../backend/db/conection.php';

// Verificar si el usuario está logueado y es profesor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    http_response_code(403);
    exit(json_encode(['error' => 'No autorizado']));
}

// Verificar parámetros necesarios
if (!isset($_GET['id']) || !isset($_GET['pagina'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Parámetros incompletos']));
}

$estudiante_id = $_GET['id'];
$pagina_actual = (int)$_GET['pagina'];
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el total de registros para la paginación
$query_total = "SELECT COUNT(*) as total FROM practicas_ejercicios WHERE estudiante_id = ?";
$stmt = mysqli_prepare($conexion, $query_total);
mysqli_stmt_bind_param($stmt, "i", $estudiante_id);
mysqli_stmt_execute($stmt);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener las prácticas para la página actual
$query_practicas = "SELECT 
    tipo_ejercicio,
    respuesta_correcta,
    fecha_practica
FROM practicas_ejercicios
WHERE estudiante_id = ?
ORDER BY fecha_practica DESC
LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conexion, $query_practicas);
mysqli_stmt_bind_param($stmt, "iii", $estudiante_id, $registros_por_pagina, $offset);
mysqli_stmt_execute($stmt);
$practicas = mysqli_stmt_get_result($stmt);

// Definir tipos de ejercicio
$tipos_ejercicio = [
    'letterIdentification' => 'Identificación de Letras',
    'wordCompletion' => 'Completar Palabras',
    'signRecognition' => 'Reconocimiento de Señas',
    'errorDetection' => 'Detección de Errores'
];

// Generar HTML para las prácticas
ob_start();
while ($practica = mysqli_fetch_assoc($practicas)): ?>
    <tr class="hover:bg-gray-700/50">
        <td class="px-3 py-4 whitespace-normal text-sm">
            <div class="text-gray-200 break-words">
                <?php 
                $tipo = $tipos_ejercicio[$practica['tipo_ejercicio']] ?? $practica['tipo_ejercicio'];
                echo htmlspecialchars(substr($tipo, 0, 20));
                ?>
            </div>
        </td>
        <td class="px-3 py-4 whitespace-nowrap">
            <?php if ($practica['respuesta_correcta']): ?>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-300">
                    Correcto
                </span>
            <?php else: ?>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900/50 text-red-300">
                    Incorrecto
                </span>
            <?php endif; ?>
        </td>
        <td class="px-3 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-400">
                <?php echo date('d/m/y', strtotime($practica['fecha_practica'])); ?>
            </div>
        </td>
    </tr>
<?php endwhile;
$practicas_html = ob_get_clean();

// Generar HTML para la paginación
ob_start();
if ($total_paginas > 1): ?>
    <nav class="relative z-0 inline-flex flex-wrap justify-center gap-1" aria-label="Pagination">
        <?php if ($pagina_actual > 1): ?>
            <button onclick="cargarPagina(<?php echo ($pagina_actual - 1); ?>)" 
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600 bg-gray-700 border border-gray-600 rounded-md">
                <span class="sr-only">Anterior</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        <?php endif; ?>

        <?php 
        $start_page = max(1, min($pagina_actual - 1, $total_paginas - 2));
        $end_page = min($total_paginas, max($pagina_actual + 1, 3));
        
        if ($start_page > 1): ?>
            <button onclick="cargarPagina(1)" 
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-md">
                1
            </button>
            <?php if ($start_page > 2): ?>
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400">
                    ...
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <button onclick="cargarPagina(<?php echo $i; ?>)" 
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 <?php echo $i === $pagina_actual ? 'bg-gray-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'; ?> rounded-md">
                <?php echo $i; ?>
            </button>
        <?php endfor; ?>

        <?php if ($end_page < $total_paginas): ?>
            <?php if ($end_page < $total_paginas - 1): ?>
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400">
                    ...
                </span>
            <?php endif; ?>
            <button onclick="cargarPagina(<?php echo $total_paginas; ?>)" 
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium border border-gray-600 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-md">
                <?php echo $total_paginas; ?>
            </button>
        <?php endif; ?>

        <?php if ($pagina_actual < $total_paginas): ?>
            <button onclick="cargarPagina(<?php echo ($pagina_actual + 1); ?>)" 
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600 bg-gray-700 border border-gray-600 rounded-md">
                <span class="sr-only">Siguiente</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        <?php endif; ?>
    </nav>
<?php endif;
$paginacion_html = ob_get_clean();

// Devolver la respuesta JSON
header('Content-Type: application/json');
echo json_encode([
    'practicas_html' => $practicas_html,
    'paginacion_html' => $paginacion_html
]); 