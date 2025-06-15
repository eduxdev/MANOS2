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
    id INT PRIMARY KEY AUTO_INCREMENT,
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
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT NOT NULL,
    nivel INT DEFAULT 1,
    puntos_maximos INT DEFAULT 100,
    tiempo_limite INT, -- en segundos, NULL si no hay límite
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

-- Tabla de asignaciones (corregida)
CREATE TABLE asignaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    profesor_id INT NOT NULL,
    ejercicio_id INT NOT NULL,
    grupo_asignado VARCHAR(50),
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_limite TIMESTAMP NULL DEFAULT NULL,
    estado VARCHAR(20) DEFAULT 'activa', -- activa, completada, vencida
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id),
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id)
);


-- Tabla de estudiantes_asignaciones (para manejar las asignaciones individuales)
CREATE TABLE estudiantes_asignaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asignacion_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, completada, vencida
    calificacion INT,
    fecha_completado TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id)
);

-- Tabla de insignias
CREATE TABLE insignias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255),
    puntos_requeridos INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de insignias_usuarios
CREATE TABLE insignias_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    insignia_id INT NOT NULL,
    fecha_obtencion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (insignia_id) REFERENCES insignias(id)
);

-- Índices para optimizar las consultas
CREATE INDEX idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX idx_progreso_usuario ON progreso_estudiantes(usuario_id);
CREATE INDEX idx_progreso_ejercicio ON progreso_estudiantes(ejercicio_id);
CREATE INDEX idx_asignaciones_profesor ON asignaciones(profesor_id);
CREATE INDEX idx_estudiantes_asignaciones ON estudiantes_asignaciones(estudiante_id, asignacion_id); 