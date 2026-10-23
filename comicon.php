<?php
/**
 * Comic-Con DAW 2026 - Script base de inscripción y gestión del evento.
 */

declare(strict_types=1);

// Mostrar todos los errores en fase de desarrollo
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Catálogo de tipos de entrada como array asociativo (valor => texto visible)
$tiposEntrada = [
    'General' => 'General (disponible)',
    'VIP' => 'VIP (disponible)',
    'Infantil' => 'Infantil (disponible)',
];

// Catálogo de talleres como array multidimensional
$catalogoTalleres = [
    'videojuegos' => ['nombre' => 'Torneo de Videojuegos', 'precio' => 10],
    'cosplay' => ['nombre' => 'Taller de Cosplay', 'precio' => 15],
    'rol' => ['nombre' => 'Zona de Rol en Vivo', 'precio' => 5],
];

// Variables de resultado inicializadas en ámbito global para que el HTML
// pueda usarlas de forma segura siempre, se haya enviado el formulario o no.
$nombre = '';
$edadTexto = '';
$tipo = 'General';
$talleresSeleccionados = [];
$precioBase = 0;
$precioTotal = 0;
$exito = false;
$error = null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Comic-Con DAW 2026</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
    </style>
</head>

<body>
    <h1>Comic-Con DAW 2026</h1>
</body>

</html>