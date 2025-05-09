<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands</title>
    <link rel="stylesheet" href="./assets/css/styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="bg-gray-100 min-h-screen py-10">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-bold text-center text-gray-800 mb-6">Aprender Lenguaje de Señas</h1>
            <p class="text-lg text-center text-gray-600 mb-10">Aquí puedes aprender el alfabeto en lenguaje de señas:</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                <?php
                $letters = range('a', 'z');
                foreach ($letters as $letter) {
                    echo "<div class='flex flex-col items-center bg-white shadow-md rounded-lg p-4'>";
                    echo "<img class='w-16 h-16 object-contain' src='signs/$letter.png' alt='$letter'>";
                    echo "<p class='text-gray-800 font-semibold mt-2'>$letter</p>";
                    echo "</div>";
                }
                ?>
            </div>

            <section class="mt-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ejercicios Interactivos</h2>
                <p class="text-gray-600 mb-6">Selecciona la imagen que corresponde a la letra mostrada:</p>
                <div id="exercise-container" class="bg-white shadow-md rounded-lg p-6">
                    <p id="exercise-question" class="text-lg font-semibold text-gray-800 mb-4">Letra: <span id="random-letter" class="text-blue-500"></span></p>
                    <div id="exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <!-- Opciones generadas dinámicamente -->
                    </div>
                    <button id="check-answer" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Verificar Respuesta</button>
                    <p id="exercise-feedback" class="mt-2 text-gray-600"></p>
                </div>
            </section>

            <section class="mt-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ejercicio: Completa la Palabra</h2>
                <p class="text-gray-600 mb-6">Completa la palabra seleccionando las letras correctas en orden:</p>
                <div id="word-exercise-container" class="bg-white shadow-md rounded-lg p-6">
                    <p id="word-exercise-question" class="text-lg font-semibold text-gray-800 mb-4">Palabra: <span id="random-word" class="text-blue-500"></span></p>
                    <div id="word-exercise-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 items-center justify-center"> <!-- Adjusted grid for better alignment -->
                        <!-- Opciones generadas dinámicamente -->
                    </div>
                    <button id="check-word-answer" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Verificar Respuesta</button>
                    <p id="word-exercise-feedback" class="mt-2 text-gray-600"></p>
                </div>
            </section>

            <section class="mt-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ejercicio: Ordena las Palabras</h2>
                <p class="text-gray-600 mb-6">Ordena las palabras en el orden correcto seleccionándolas:</p>
                <div id="word-order-exercise-container" class="bg-white shadow-md rounded-lg p-6">
                    <p id="word-order-exercise-question" class="text-lg font-semibold text-gray-800 mb-4">Frase: <span id="random-phrase" class="text-blue-500"></span></p>
                    <div id="word-order-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 items-center justify-center">
                        <!-- Opciones generadas dinámicamente -->
                    </div>
                    <button id="check-word-order-answer" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Verificar Respuesta</button>
                    <p id="word-order-feedback" class="mt-2 text-gray-600"></p>
                </div>
            </section>

            <section class="mt-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Ejercicio: Encuentra la Letra</h2>
                <p class="text-gray-600 mb-6">Encuentra la letra correcta en la imagen:</p>
                <div id="find-letter-exercise-container" class="bg-white shadow-md rounded-lg p-6">
                    <p id="find-letter-exercise-question" class="text-lg font-semibold text-gray-800 mb-4">Letra: <span id="find-random-letter" class="text-blue-500"></span></p>
                    <div id="find-letter-options" class="grid grid-cols-2 sm:grid-cols-4 gap-6 items-center justify-center">
                        <!-- Opciones generadas dinámicamente -->
                    </div>
                    <button id="check-find-letter-answer" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">Verificar Respuesta</button>
                    <p id="find-letter-feedback" class="mt-2 text-gray-600"></p>
                </div>
            </section>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
