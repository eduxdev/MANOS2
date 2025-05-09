<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk Hands</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="bg-white min-h-screen py-10 mt-6"> <!-- Added margin-top to separate header -->
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-bold text-center text-gray-900 mb-6">Comunicación en Lenguaje de Señas</h1>
            <textarea id="inputText" class="w-full p-4 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 mb-6" placeholder="Escribe aquí..." rows="4"></textarea>
            <button id="convertButton" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-500 transition">Convertir a Señas</button>
            <div id="output" class="mt-6 p-4 bg-gray-100 shadow-md rounded-lg text-gray-800 flex flex-wrap gap-4"></div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>