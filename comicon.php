<?php
/**
 * Comic-Con DAW 2026 - Inscripción en un único archivo:
 * muestra el formulario, valida los datos enviados por POST,
 * calcula el precio final y muestra el resguardo.
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

/**
 * Calcula el precio base asociado a un tipo de entrada.
 * Lanza Exception si el tipo recibido no es válido.
 */
function calcularPrecioBase(string $tipo): int
{
    switch ($tipo) {
        case 'General':
            return 30;
        case 'VIP':
            return 60;
        case 'Infantil':
            return 10;
        default:
            throw new Exception("El tipo de entrada seleccionado no es válido");
    }
}

/**
 * Procesa la inscripción sumando el precio base y los talleres elegidos.
 * Lanza Exception si el tipo de entrada o algún taller seleccionado no existe.
 */
function procesarInscripcion(string $tipo, array $talleresSeleccionados, array $catalogoTalleres): int
{
    // Todos los datos llegan por parámetro sin depender de 'global'.
    // $total es una variable local que se destruye al salir de la función.
    $total = calcularPrecioBase($tipo);

    foreach ($talleresSeleccionados as $clave) {
        if (!isset($catalogoTalleres[$clave])) {
            throw new Exception("Se ha seleccionado un taller que no existe");
        }
        $total += $catalogoTalleres[$clave]['precio'];
    }

    return $total;
}

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

// Procesamiento del formulario al enviar por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // El operador ?? evita warnings si alguna clave no está definida (ej. ningún taller marcado)
        // Saneamiento con htmlspecialchars contra XSS
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $edadTexto = trim($_POST['edad'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $talleresSeleccionados = $_POST['talleres'] ?? [];

        // Los checkboxes marcados llegan como array; si no lo es, se inicializa como array vacío
        if (!is_array($talleresSeleccionados)) {
            $talleresSeleccionados = [];
        }

        // Devuelve false si no es un número entero positivo
        $edad = filter_var($edadTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Validación con if / elseif / else
        if ($nombre === '') {
            throw new Exception("El nombre completo es obligatorio");
        } elseif ($edad === false) {
            throw new Exception("La edad debe ser un número entero positivo");
        } elseif ($edad < 18 && $tipo === 'VIP') {
            throw new Exception("La entrada VIP está restringida a mayores de edad");
        } elseif ($edad > 16 && $tipo === 'Infantil') {
            throw new Exception("La entrada Infantil está restringida a menores de 17 años");
        } else {
            $precioBase = calcularPrecioBase($tipo);
            $precioTotal = procesarInscripcion($tipo, $talleresSeleccionados, $catalogoTalleres);
            $exito = true;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    } finally {
        // Se ejecuta siempre y deja registro interno en el servidor
        error_log("[" . date('Y-m-d H:i:s') . "] Inscripción Comic-Con procesada: " . ($exito ? "ÉXITO" : "FALLO"));
    }
}
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

        .campo {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        .error {
            background-color: #fde8e8;
            color: #9b1c1c;
            padding: 10px 15px;
            border-radius: 4px;
        }

        .resguardo {
            border: 2px dashed #333;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Comic-Con DAW 2026</h1>

    <?php if ($error !== null): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="comicon.php" method="post">
        <!-- Campo: Nombre completo -->
        <div class="campo">
            <label for="nombre">Nombre completo:</label>
            <!-- $nombre se pinta directamente sin volver a escapar porque ya llegará saneado con htmlspecialchars -->
            <input type="text" id="nombre" name="nombre" required value="<?= $nombre ?>">
        </div>

        <!-- Campo: Edad -->
        <div class="campo">
            <label for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" min="1" step="1" required
                value="<?= htmlspecialchars($edadTexto, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- Campo: Tipo de entrada -->
        <div class="campo">
            <label for="tipo">Tipo de entrada:</label>
            <select id="tipo" name="tipo">
                <?php foreach ($tiposEntrada as $clave => $texto): ?>
                    <option value="<?= $clave ?>" <?= $tipo === $clave ? 'selected' : '' ?>>
                        <?= $texto ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Talleres especiales (name="talleres[]" hace que PHP reciba los marcados como array) -->
        <fieldset class="campo">
            <legend>Talleres especiales</legend>
            <?php foreach ($catalogoTalleres as $clave => $taller): ?>
                <label>
                    <input type="checkbox" name="talleres[]" value="<?= $clave ?>" <?= in_array($clave, $talleresSeleccionados, true) ? 'checked' : '' ?>>
                    <?= $taller['nombre'] ?> (<?= $taller['precio'] ?> €)
                </label>
            <?php endforeach; ?>
        </fieldset>

        <button type="submit">Inscribirme</button>
    </form>

    <?php if ($exito): ?>
        <!-- Resguardo generado tras procesar la inscripción con éxito -->
        <div class="resguardo">
            <h2>Resguardo de inscripción</h2>
            <p>Asistente: <?= $nombre ?></p>
            <p>Entrada: <?= $tipo ?> (<?= $precioBase ?> €)</p>
            <p>Talleres:</p>
            <?php if (count($talleresSeleccionados) > 0): ?>
                <ul>
                    <?php foreach ($talleresSeleccionados as $clave): ?>
                        <li><?= $catalogoTalleres[$clave]['nombre'] ?>: <?= $catalogoTalleres[$clave]['precio'] ?> €</li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Ningún taller seleccionado.</p>
            <?php endif; ?>
            <p>Total a pagar: <strong><?= $precioTotal ?> €</strong></p>
        </div>
    <?php endif; ?>
</body>

</html>