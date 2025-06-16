-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar roles básicos
INSERT INTO roles (nombre) VALUES 
('estudiante'),
('profesor'),
('administrador');

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    grado VARCHAR(50),
    grupo VARCHAR(10),
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla de categorías de ejercicios
CREATE TABLE categorias_ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar categorías básicas
INSERT INTO categorias_ejercicios (nombre, descripcion) VALUES 
('Alfabeto', 'Ejercicios para aprender el alfabeto en lenguaje de señas'),
('Números', 'Ejercicios para aprender los números en lenguaje de señas'),
('Palabras Básicas', 'Ejercicios con palabras comunes y básicas'),
('Frases', 'Ejercicios para formar frases completas');

-- Tabla de ejercicios
CREATE TABLE ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT NOT NULL,
    nivel INT NOT NULL DEFAULT 1,
    puntos_maximos INT NOT NULL DEFAULT 10,
    tiempo_limite INT NOT NULL DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_ejercicios(id)
);

-- Tabla de progreso de estudiantes
CREATE TABLE progreso_estudiantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    ejercicio_id INT NOT NULL,
    puntos_obtenidos INT DEFAULT 0,
    tiempo_completado INT, -- en segundos
    completado BOOLEAN DEFAULT false,
    intentos INT DEFAULT 0,
    ultima_practica TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id)
);

-- Tabla de asignaciones
CREATE TABLE asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesor_id INT NOT NULL,
    ejercicio_id INT NOT NULL,
    grupo_asignado VARCHAR(10) NOT NULL,
    fecha_asignacion DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    estado ENUM('activa', 'completada', 'vencida') NOT NULL DEFAULT 'activa',
    puntos_maximos INT NOT NULL,
    intentos_maximos INT NOT NULL DEFAULT 3,
    instrucciones TEXT,
    tipo_ejercicio VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id),
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id)
);

-- Tabla de asignaciones de estudiantes
CREATE TABLE estudiantes_asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    estado ENUM('pendiente', 'en_progreso', 'completado') NOT NULL DEFAULT 'pendiente',
    intentos_realizados INT NOT NULL DEFAULT 0,
    puntos_obtenidos INT NOT NULL DEFAULT 0,
    fecha_ultimo_intento DATETIME,
    evidencia_path VARCHAR(255),
    fecha_entrega DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id)
);

-- Tabla de resultados de ejercicios
CREATE TABLE resultados_ejercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_asignacion_id INT NOT NULL,
    intento_numero INT NOT NULL,
    tipo_ejercicio VARCHAR(100) NOT NULL,
    detalles JSON NOT NULL, -- Almacena detalles específicos del ejercicio (letras correctas, errores, etc.)
    evidencia_path VARCHAR(255) NOT NULL,
    puntos_obtenidos INT NOT NULL,
    tiempo_empleado INT NOT NULL, -- en segundos
    fecha_realizacion DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_asignacion_id) REFERENCES estudiantes_asignaciones(id)
);

-- Tabla de insignias
CREATE TABLE IF NOT EXISTS insignias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255) NOT NULL,
    requisito_puntos INT DEFAULT 0,
    requisito_ejercicios INT DEFAULT 0,
    tipo ENUM('puntos', 'ejercicios', 'especial') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de relación entre usuarios e insignias
CREATE TABLE IF NOT EXISTS insignias_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    insignia_id INT NOT NULL,
    fecha_obtencion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (insignia_id) REFERENCES insignias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_insignia (usuario_id, insignia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunas insignias predefinidas
INSERT INTO insignias (nombre, descripcion, imagen_url, requisito_puntos, requisito_ejercicios, tipo) VALUES
('Principiante', 'Completaste tu primer ejercicio', '/imagenes/insignias/principiante.png', 0, 1, 'ejercicios'),
('Estudiante Dedicado', 'Alcanzaste 100 puntos', '/imagenes/insignias/dedicado.png', 100, 0, 'puntos'),
('Experto', 'Completaste 10 ejercicios', '/imagenes/insignias/experto.png', 0, 10, 'ejercicios'),
('Maestro', 'Alcanzaste 500 puntos', '/imagenes/insignias/maestro.png', 500, 0, 'puntos'),
('Velocista', 'Completaste 3 ejercicios en un día', '/imagenes/insignias/velocista.png', 0, 3, 'especial'),
('Constante', 'Actividad durante 7 días seguidos', '/imagenes/insignias/constante.png', 0, 7, 'especial');

-- Índices para optimizar las consultas
CREATE INDEX idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX idx_progreso_usuario ON progreso_estudiantes(usuario_id);
CREATE INDEX idx_progreso_ejercicio ON progreso_estudiantes(ejercicio_id);
CREATE INDEX idx_asignaciones_profesor ON asignaciones(profesor_id);
CREATE INDEX idx_estudiantes_asignaciones ON estudiantes_asignaciones(estudiante_id, asignacion_id); 