-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS manos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE manos;

-- Crear tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar roles básicos si no existen
INSERT IGNORE INTO roles (nombre) VALUES 
('estudiante'),
('profesor'),
('administrador');

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
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

-- Crear tabla de ejercicios
CREATE TABLE IF NOT EXISTS ejercicios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    nivel_dificultad ENUM('principiante', 'intermedio', 'avanzado') NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de asignaciones
CREATE TABLE IF NOT EXISTS asignaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ejercicio_id INT NOT NULL,
    profesor_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    puntos_maximos INT NOT NULL,
    intentos_permitidos INT NOT NULL DEFAULT 3,
    instrucciones TEXT,
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id),
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id)
);

-- Crear tabla de progreso de estudiantes
CREATE TABLE IF NOT EXISTS progreso_estudiantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    ejercicio_id INT NOT NULL,
    completado BOOLEAN DEFAULT false,
    puntos_obtenidos INT DEFAULT 0,
    fecha_ultimo_intento TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (ejercicio_id) REFERENCES ejercicios(id)
);

-- Crear tabla de asignaciones de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes_asignaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estudiante_id INT NOT NULL,
    asignacion_id INT NOT NULL,
    estado ENUM('pendiente', 'en_progreso', 'completada') DEFAULT 'pendiente',
    intentos_realizados INT DEFAULT 0,
    fecha_completado TIMESTAMP NULL,
    FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id)
);

-- Crear tabla de insignias
CREATE TABLE IF NOT EXISTS insignias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    imagen_url VARCHAR(255),
    puntos_requeridos INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de insignias de usuarios
CREATE TABLE IF NOT EXISTS insignias_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    insignia_id INT NOT NULL,
    fecha_obtencion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (insignia_id) REFERENCES insignias(id)
); 