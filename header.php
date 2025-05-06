<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lenguaje de Señas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        header {
            height: 150px; /* Aumentar la altura del header */
            background: url('https://i.pinimg.com/736x/70/69/da/7069da9f4487be1caffbeeee72370ce0.jpg') no-repeat center center; /* Imagen de fondo */
            background-size: cover;
            position: relative;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fondo translúcido */
            z-index: 1;
        }

        header nav {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 2;
        }

        header h1 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 2rem;
            z-index: 2;
        }
    </style>
</head>
<body>
    <header>
        <h1>Talk Hands</h1>
        <nav>
            <ul>
                <li><a href="inicio.php">Inicio</a></li>
                <li><a href="index.php">Traducción</a></li>
                <li><a href="aprender.php">Aprender</a></li>
            </ul>
        </nav>
    </header>