-- Agregar columnas a la tabla estudiantes_asignaciones
ALTER TABLE estudiantes_asignaciones
ADD COLUMN evidencia_path VARCHAR(255) AFTER fecha_ultimo_intento,
ADD COLUMN fecha_entrega DATETIME AFTER evidencia_path;

-- Actualizar el ENUM de estado para cambiar 'completada' a 'completado'
ALTER TABLE estudiantes_asignaciones
MODIFY COLUMN estado ENUM('pendiente', 'en_progreso', 'completado') NOT NULL DEFAULT 'pendiente';

-- Eliminar tablas existentes si existen
DROP TABLE IF EXISTS insignias_usuarios;
DROP TABLE IF EXISTS insignias;

-- Crear tabla de insignias
CREATE TABLE insignias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255) NOT NULL,
    requisito_puntos INT DEFAULT 0,
    requisito_ejercicios INT DEFAULT 0,
    tipo ENUM('puntos', 'ejercicios', 'especial') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de relación entre usuarios e insignias
CREATE TABLE insignias_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    insignia_id INT NOT NULL,
    fecha_obtencion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (insignia_id) REFERENCES insignias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_insignia (usuario_id, insignia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar insignias predefinidas
INSERT INTO insignias (nombre, descripcion, imagen_url, requisito_puntos, requisito_ejercicios, tipo) VALUES
('Principiante', 'Completaste tu primer ejercicio', '/imagenes/insignias/principiante.png', 0, 1, 'ejercicios'),
('Estudiante Dedicado', 'Alcanzaste 100 puntos', '/imagenes/insignias/dedicado.png', 100, 0, 'puntos'),
('Experto', 'Completaste 10 ejercicios', '/imagenes/insignias/experto.png', 0, 10, 'ejercicios'),
('Maestro', 'Alcanzaste 500 puntos', '/imagenes/insignias/maestro.png', 500, 0, 'puntos'),
('Velocista', 'Completaste 3 ejercicios en un día', '/imagenes/insignias/velocista.png', 0, 3, 'especial'),
('Constante', 'Actividad durante 7 días seguidos', '/imagenes/insignias/constante.png', 0, 7, 'especial'),
('Aprendiz', 'Alcanzaste 50 puntos', '/imagenes/insignias/aprendiz.png', 50, 0, 'puntos'),
('Estudiante Avanzado', 'Alcanzaste 250 puntos', '/imagenes/insignias/avanzado.png', 250, 0, 'puntos'),
('Erudito', 'Alcanzaste 1000 puntos', '/imagenes/insignias/erudito.png', 1000, 0, 'puntos'),
('Sabio', 'Alcanzaste 2000 puntos', '/imagenes/insignias/sabio.png', 2000, 0, 'puntos'),
('Legendario', 'Alcanzaste 5000 puntos', '/imagenes/insignias/legendario.png', 5000, 0, 'puntos');


-- Actualizar las rutas de las imágenes para usar una imagen por defecto
UPDATE insignias SET imagen_url = '/imagenes/logo2.png';

-- Tabla para registrar las prácticas de ejercicios
CREATE TABLE IF NOT EXISTS practicas_ejercicios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estudiante_id INT NOT NULL,
    tipo_ejercicio VARCHAR(50) NOT NULL,
    respuesta_correcta BOOLEAN NOT NULL,
    detalles JSON,
    fecha_practica TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para mejorar el rendimiento de las consultas
CREATE INDEX idx_estudiante_fecha ON practicas_ejercicios(estudiante_id, fecha_practica);
CREATE INDEX idx_tipo_ejercicio ON practicas_ejercicios(tipo_ejercicio);

-- Agregar columna de puntos de práctica a la tabla usuarios
ALTER TABLE usuarios
ADD COLUMN puntos_practica INT DEFAULT 0;

-- Índice para mejorar el rendimiento de las consultas de puntos
CREATE INDEX idx_puntos_practica ON usuarios(puntos_practica); 

-- Agregar más insignias basadas en puntos
INSERT INTO insignias (nombre, descripcion, imagen_url, requisito_puntos, requisito_ejercicios, tipo) VALUES
('Novato Prometedor', 'Alcanzaste 25 puntos', '/imagenes/logo2.png', 25, 0, 'puntos'),
('Estudiante Entusiasta', 'Alcanzaste 75 puntos', '/imagenes/logo2.png', 75, 0, 'puntos'),
('Estudiante Sobresaliente', 'Alcanzaste 150 puntos', '/imagenes/logo2.png', 150, 0, 'puntos'),
('Estudiante Elite', 'Alcanzaste 350 puntos', '/imagenes/logo2.png', 350, 0, 'puntos'),
('Estudiante Excepcional', 'Alcanzaste 750 puntos', '/imagenes/logo2.png', 750, 0, 'puntos'),
('Gran Maestro', 'Alcanzaste 1500 puntos', '/imagenes/logo2.png', 1500, 0, 'puntos'),
('Iluminado', 'Alcanzaste 3000 puntos', '/imagenes/logo2.png', 3000, 0, 'puntos'),
('Virtuoso', 'Alcanzaste 4000 puntos', '/imagenes/logo2.png', 4000, 0, 'puntos'),
('Gran Sabio', 'Alcanzaste 7500 puntos', '/imagenes/logo2.png', 7500, 0, 'puntos'),
('Supremo', 'Alcanzaste 10000 puntos', '/imagenes/logo2.png', 10000, 0, 'puntos'); 