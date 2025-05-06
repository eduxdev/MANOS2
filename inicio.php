<?php include 'header.php'; ?>

<style>
    main {
        font-family: Arial, sans-serif;
        color: #4B0082; /* Tonos morados */
        background-color: #F3E5F5; /* Fondo claro en tonos morados */
        padding: 20px;
        margin: 0 auto;
        max-width: 1200px; /* Margen a los lados */
    }

    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }

    .card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 300px;
        text-align: center;
    }

    .card img {
        width: 100%;
        height: auto;
    }

    .card h2 {
        color: #6A0DAD;
        font-size: 1.5rem;
        margin: 15px 0;
    }

    .card p {
        padding: 0 15px 15px;
        line-height: 1.6;
    }
</style>

<main>
    <h1 style="text-align: center;">Bienvenido a la página de Lenguaje de Señas</h1>
    <p style="text-align: center;">Esta página está diseñada para facilitar la comunicación a través del lenguaje de señas. Aquí puedes traducir texto a señas, aprender el alfabeto y más.</p>

    <div class="card-container">
        <div class="card">
            <img src="imagenes/talk.jpg" alt="Persona usando lenguaje de señas">
            <h2>El lenguaje de señas</h2>
            <p>El lenguaje de señas es un sistema de comunicación visual y gestual utilizado por las personas sordas o con dificultades auditivas para expresar pensamientos, emociones e información.</p>
        </div>

        <div class="card">
            <img src="imagenes/a.jpg" alt="Grupo comunicándose en lenguaje de señas">
            <h2>Importancia e impacto</h2>
            <p>El acceso al lenguaje de señas promueve una mayor inclusión de las personas con discapacidad auditiva, permitiéndoles participar plenamente en la sociedad y mejorar su calidad de vida.</p>
        </div>

        <div class="card">
            <img src="imagenes/b.jpg" alt="Diversidad cultural en lenguaje de señas">
            <h2>Diversidad cultural</h2>
            <p>El reconocimiento y uso del lenguaje de señas contribuye al respeto y aprecio por la diversidad cultural y lingüística, promoviendo la empatía y la integración social.</p>
        </div>

        <div class="card">
            <img src="images/sign-language-4.jpg" alt="Educación inclusiva con lenguaje de señas">
            <h2>Beneficios</h2>
            <p>Facilita la comunicación efectiva, fortalece la autonomía y promueve la equidad educativa, permitiendo a los estudiantes sordos acceder a contenidos académicos de manera igualitaria.</p>
        </div>
    </div>
</main>
