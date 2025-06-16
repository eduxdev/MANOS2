-- Agregar columnas a la tabla estudiantes_asignaciones
ALTER TABLE estudiantes_asignaciones
ADD COLUMN evidencia_path VARCHAR(255) AFTER fecha_ultimo_intento,
ADD COLUMN fecha_entrega DATETIME AFTER evidencia_path;

-- Actualizar el ENUM de estado para cambiar 'completada' a 'completado'
ALTER TABLE estudiantes_asignaciones
MODIFY COLUMN estado ENUM('pendiente', 'en_progreso', 'completado') NOT NULL DEFAULT 'pendiente'; 