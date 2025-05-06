<?php include 'header.php'; ?>

<main>
    <h1>Aprender Lenguaje de Señas</h1>
    <p>Aquí puedes aprender el alfabeto en lenguaje de señas:</p>
    <div class="alphabet">
        <?php
        $letters = range('a', 'z');
        foreach ($letters as $letter) {
            echo "<div class='letter'>";
            echo "<img src='signs/$letter.png' alt='$letter'>";
            echo "<p>$letter</p>";
            echo "</div>";
        }
        ?>
    </div>

    <section class="interactive-exercises">
        <h2>Ejercicios Interactivos</h2>
        <p>Selecciona la imagen que corresponde a la letra mostrada:</p>
        <div id="exercise-container">
            <p id="exercise-question">Letra: <span id="random-letter"></span></p>
            <div id="exercise-options" class="alphabet">
                <!-- Opciones generadas dinámicamente -->
            </div>
            <button id="check-answer">Verificar Respuesta</button>
            <p id="exercise-feedback"></p>
        </div>
    </section>
    <section class="additional-exercises">
        <h2>Ejercicio: Completa la Palabra</h2>
        <p>Completa la palabra seleccionando las letras correctas en orden:</p>
        <div id="word-exercise-container">
            <p id="word-exercise-question">Palabra: <span id="random-word"></span></p>
            <div id="word-exercise-options" class="alphabet">
                <!-- Opciones generadas dinámicamente -->
            </div>
            <button id="check-word-answer">Verificar Respuesta</button>
            <p id="word-exercise-feedback"></p>
        </div>
    </section>
</main>

<script src="script.js"></script>
<link rel="stylesheet" href="styles.css">
