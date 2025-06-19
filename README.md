# Talk Hands - Plataforma de Aprendizaje de Lenguaje de Señas

## Descripción General
Talk Hands es una plataforma web educativa diseñada para facilitar el aprendizaje del lenguaje de señas. El sistema permite a los usuarios traducir palabras y frases a señas, practicar con ejercicios interactivos y realizar un seguimiento de su progreso de aprendizaje.

## Estructura del Proyecto
```
MANOS2/
├── assets/           # Recursos estáticos (CSS, JavaScript, etc.)
├── backend/          # Lógica del servidor y base de datos
├── frontend/         # Interfaces de usuario y vistas
├── imagenes/        # Imágenes generales del sitio
├── signs/           # Imágenes de señas individuales
└── signs_words/     # Imágenes de señas para palabras completas
```

### Directorios Principales
- **assets/**: Contiene los archivos CSS y JavaScript para la interfaz y funcionalidad del sitio
- **backend/**: Maneja la lógica del servidor, autenticación y operaciones de base de datos
- **frontend/**: Contiene todas las páginas y componentes de la interfaz de usuario
- **signs/ y signs_words/**: Almacenan las imágenes de las señas utilizadas en la traducción

## Funcionalidades Principales

### 1. Sistema de Traducción
- Traducción de letras individuales a señas
- Traducción de palabras completas a señas
- Interfaz interactiva para visualizar las señas

### 2. Sistema de Aprendizaje
- Ejercicios prácticos
- Seguimiento del progreso
- Sistema de badges y logros

### 3. Panel de Control
- **Para Estudiantes:**
  - Visualización de progreso
  - Acceso a ejercicios asignados
  - Registro de práctica

- **Para Profesores:**
  - Gestión de estudiantes
  - Asignación de ejercicios
  - Seguimiento del progreso de los estudiantes

## Tecnologías Utilizadas
- **Frontend:**
  - HTML5
  - CSS3 (Tailwind CSS)
  - JavaScript
  - PHP para las vistas

- **Backend:**
  - PHP
  - MySQL

- **Herramientas:**
  - XAMPP (Servidor local)

## Guía de Instalación

1. **Requisitos Previos:**
   - XAMPP instalado
   - PHP 7.4 o superior
   - MySQL 5.7 o superior

2. **Pasos de Instalación:**
   ```bash
   # 1. Clonar el repositorio en la carpeta htdocs de XAMPP
   git clone [URL_DEL_REPOSITORIO] MANOS2

   # 2. Importar la base de datos
   # Usar los archivos SQL en backend/db/ en el siguiente orden:
   # - create_database.sql
   # - tables.sql
   # - insert_ejercicios.sql
   ```

3. **Configuración:**
   - Configurar la conexión a la base de datos en `backend/db/conection.php`
   - Asegurarse de que el servidor Apache y MySQL estén corriendo en XAMPP

## Guía de Uso

### Para Estudiantes
1. Registrarse como estudiante
2. Acceder a las herramientas de traducción
3. Realizar ejercicios asignados
4. Revisar progreso en el dashboard

### Para Profesores
1. Registrarse como profesor
2. Acceder al panel de control
3. Gestionar estudiantes y asignaciones
4. Monitorear el progreso de los estudiantes

## Características de Seguridad
- Autenticación de usuarios
- Protección de rutas
- Validación de formularios
- Sanitización de datos

## Mantenimiento
- Actualizar regularmente las imágenes de señas en `signs/` y `signs_words/`
- Realizar copias de seguridad de la base de datos
- Mantener actualizadas las dependencias

## Contribución
Para contribuir al proyecto:
1. Hacer fork del repositorio
2. Crear una rama para nuevas características
3. Enviar pull request con los cambios propuestos

## Licencia
[Especificar la licencia del proyecto]