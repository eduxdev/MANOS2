-- Primero, asegurémonos de que las categorías existan
INSERT IGNORE INTO categorias_ejercicios (nombre, descripcion) VALUES 
('Alfabeto', 'Ejercicios para aprender el alfabeto en lenguaje de señas'),
('Números', 'Ejercicios para aprender los números en lenguaje de señas'),
('Palabras Básicas', 'Ejercicios con palabras comunes y básicas'),
('Frases', 'Ejercicios para formar frases completas'),
('Vocabulario', 'Ejercicios de vocabulario general'),
('Gramática', 'Ejercicios de estructura gramatical');

-- Ahora insertamos los ejercicios
INSERT INTO ejercicios (titulo, descripcion, categoria_id, nivel, puntos_maximos, tiempo_limite) VALUES
-- Ejercicios de Alfabeto (categoría 1)
('Alfabeto Básico A-M', 'Aprende y practica las señas de las letras A hasta M del alfabeto.', 1, 1, 50, 300),
('Alfabeto Avanzado N-Z', 'Aprende y practica las señas de las letras N hasta Z del alfabeto.', 1, 2, 50, 300),
('Deletreo de Palabras', 'Practica deletreando palabras completas usando el alfabeto de señas.', 1, 3, 100, 600),

-- Ejercicios de Números (categoría 2)
('Números del 1 al 10', 'Aprende y practica los números básicos del 1 al 10.', 2, 1, 50, 300),
('Números del 11 al 100', 'Aprende y practica números más avanzados.', 2, 2, 75, 450),
('Operaciones Matemáticas', 'Practica operaciones matemáticas básicas usando señas.', 2, 3, 100, 600),

-- Ejercicios de Palabras Básicas (categoría 3)
('Saludos y Despedidas', 'Aprende las señas básicas para saludar y despedirte.', 3, 1, 50, 300),
('Familia y Amigos', 'Aprende señas relacionadas con la familia y relaciones personales.', 3, 2, 75, 450),
('Emociones y Sentimientos', 'Practica señas que expresan diferentes estados emocionales.', 3, 3, 100, 600),

-- Ejercicios de Frases (categoría 4)
('Frases Cotidianas', 'Aprende frases comunes para comunicación diaria.', 4, 1, 75, 450),
('Preguntas y Respuestas', 'Practica hacer y responder preguntas básicas.', 4, 2, 100, 600),
('Conversación Básica', 'Desarrolla habilidades para mantener una conversación simple.', 4, 3, 150, 900),

-- Ejercicios de Vocabulario (categoría 5)
('Colores y Formas', 'Aprende señas para colores y formas básicas.', 5, 1, 50, 300),
('Alimentos y Bebidas', 'Vocabulario relacionado con comidas y bebidas.', 5, 2, 75, 450),
('Lugares y Direcciones', 'Aprende a describir ubicaciones y dar direcciones.', 5, 3, 100, 600),

-- Ejercicios de Gramática (categoría 6)
('Estructura Básica', 'Aprende la estructura básica de oraciones en lenguaje de señas.', 6, 1, 75, 450),
('Tiempos Verbales', 'Practica expresar acciones en diferentes tiempos.', 6, 2, 100, 600),
('Expresiones Complejas', 'Aprende a formar expresiones más elaboradas.', 6, 3, 150, 900);

-- Insertar categorías de ejercicios
INSERT INTO categorias_ejercicios (nombre, descripcion) VALUES
('Identificación de Letras', 'Ejercicios para identificar letras individuales en lenguaje de señas'),
('Palabras Completas', 'Ejercicios para formar palabras completas usando lenguaje de señas'),
('Reconocimiento de Señas', 'Ejercicios para reconocer señas y traducirlas a letras'),
('Detección de Errores', 'Ejercicios para identificar señas incorrectas en una secuencia');

-- Insertar ejercicios de ejemplo para cada categoría
INSERT INTO ejercicios (titulo, descripcion, categoria_id, nivel, puntos_maximos, tiempo_limite) VALUES
-- Ejercicios de Identificación de Letras (Nivel 1-3)
('Identificar Letra Básico', 'Identifica la seña correcta para una letra del alfabeto mostrada', 1, 1, 10, 60),
('Identificar Letra Intermedio', 'Identifica señas de letras con tiempo limitado', 1, 2, 15, 45),
('Identificar Letra Avanzado', 'Identifica señas de letras con múltiples opciones', 1, 3, 20, 30),

-- Ejercicios de Palabras Completas (Nivel 1-3)
('Formar Palabra Simple', 'Forma una palabra simple seleccionando las señas correctas', 2, 1, 15, 120),
('Formar Palabra Intermedia', 'Forma palabras de dificultad media con tiempo limitado', 2, 2, 20, 90),
('Formar Palabra Compleja', 'Forma palabras complejas con múltiples señas', 2, 3, 25, 60),

-- Ejercicios de Reconocimiento de Señas (Nivel 1-3)
('Reconocer Seña Básica', 'Identifica la letra correspondiente a una seña mostrada', 3, 1, 10, 60),
('Reconocer Seña Intermedia', 'Reconoce señas con tiempo limitado', 3, 2, 15, 45),
('Reconocer Seña Avanzada', 'Reconoce señas complejas y variantes', 3, 3, 20, 30),

-- Ejercicios de Detección de Errores (Nivel 1-3)
('Detectar Seña Incorrecta Básico', 'Encuentra la seña que no corresponde en una palabra simple', 4, 1, 15, 90),
('Detectar Seña Incorrecta Intermedio', 'Identifica señas incorrectas en palabras de dificultad media', 4, 2, 20, 75),
('Detectar Seña Incorrecta Avanzado', 'Detecta señas incorrectas en palabras complejas', 4, 3, 25, 60); 