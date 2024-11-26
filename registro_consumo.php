<?php
require 'vendor/autoload.php'; // Cargar Dompdf
use Dompdf\Dompdf;

// Habilitar mensajes de error para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtener el ID de asignación desde la URL
$id_asignacion = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validar el ID de asignación
if ($id_asignacion <= 0) {
    die("ID de asignación inválido.");
}

// Comprobar si ya existe un registro de consumo para el mes actual
$mes_actual = date('F');
$sql_verificar = "
    SELECT id_consumo
    FROM consumo
    WHERE id_asignacion = ? AND periodo = ?
    LIMIT 1
";
$stmt_verificar = $conexion->prepare($sql_verificar);
$stmt_verificar->bind_param('is', $id_asignacion, $mes_actual);
$stmt_verificar->execute();
$resultado_verificar = $stmt_verificar->get_result();

if ($resultado_verificar->num_rows > 0) {
    $mensaje_modal = "El consumo de este mes ya ha sido registrado.";
    $mostrar_modal = true;
} else {
    $mostrar_modal = false;
}

// Consulta para cargar la información del medidor
$sql_medidor = "
    SELECT 
        socio.nombre AS nombre_socio,
        socio.apellido AS apellido_socio,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona,
        IFNULL(MAX(consumo.lectura_actual), 0) AS lectura_anterior
    FROM asignacion_medidor
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
    LEFT JOIN consumo ON asignacion_medidor.id_asignacion = consumo.id_asignacion
    WHERE asignacion_medidor.id_asignacion = ?
";
$stmt_medidor = $conexion->prepare($sql_medidor);
if (!$stmt_medidor) {
    die("Error en la consulta de medidor: " . $conexion->error);
}
$stmt_medidor->bind_param('i', $id_asignacion);
$stmt_medidor->execute();
$resultado_medidor = $stmt_medidor->get_result();
$medidor = $resultado_medidor->fetch_assoc();

if (!$medidor) {
    die("Información del medidor no encontrada.");
}

// Procesar el formulario si se envió
<<<<<<< HEAD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
=======
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$mostrar_modal) {
>>>>>>> cbe22471d23d941c7f3b4c8a0b7815ac42730af6
    $lectura_actual = isset($_POST['lectura_actual']) ? (float)$_POST['lectura_actual'] : null;
    $lectura_anterior = isset($_POST['lectura_anterior']) ? (float)$_POST['lectura_anterior'] : null;
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    $periodo = isset($_POST['periodo']) ? $_POST['periodo'] : '';

    if ($lectura_actual !== null && $lectura_actual >= $lectura_anterior) {
        $consumo = $lectura_actual - $lectura_anterior;

        // Insertar el consumo en la base de datos
        $sql_insert = "
            INSERT INTO consumo (id_asignacion, lectura_anterior, lectura_actual, periodo, consumo, observaciones)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt_insert = $conexion->prepare($sql_insert);
        if (!$stmt_insert) {
            die("Error al preparar la consulta de inserción: " . $conexion->error);
        }
        $stmt_insert->bind_param('iddsds', $id_asignacion, $lectura_anterior, $lectura_actual, $periodo, $consumo, $observaciones);

        if ($stmt_insert->execute()) {
<<<<<<< HEAD
            // Calcular el monto a pagar según la tabla de tarifas
            $sql_tarifa = "
                SELECT costo_unitario
                FROM tarifas
                WHERE ? BETWEEN rango_inicio AND rango_fin
            ";
            $stmt_tarifa = $conexion->prepare($sql_tarifa);
            $stmt_tarifa->bind_param('d', $consumo);
            $stmt_tarifa->execute();
            $resultado_tarifa = $stmt_tarifa->get_result();
            $tarifa = $resultado_tarifa->fetch_assoc();

            if ($tarifa) {
                $monto = $consumo * $tarifa['costo_unitario'];

                // Generar el PDF
                $html = "
                <!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Boleta de Consumo</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <h1>Boleta de Consumo de Agua</h1>
                    <p><strong>Fecha:</strong> " . date('Y-m-d') . "</p>
                    <p><strong>Socio:</strong> {$medidor['nombre_socio']} {$medidor['apellido_socio']}</p>
                    <p><strong>Zona:</strong> {$medidor['zona']}</p>
                    <p><strong>Medidor:</strong> {$medidor['medidor']}</p>
                    <table>
                        <tr>
                            <th>Lectura Anterior (m³)</th>
                            <td>$lectura_anterior</td>
                        </tr>
                        <tr>
                            <th>Lectura Actual (m³)</th>
                            <td>$lectura_actual</td>
                        </tr>
                        <tr>
                            <th>Consumo Total (m³)</th>
                            <td>$consumo</td>
                        </tr>
                        <tr>
                            <th>Monto a Pagar (Bs.)</th>
                            <td>" . number_format($monto, 2) . "</td>
                        </tr>
                        <tr>
                            <th>Observaciones</th>
                            <td>$observaciones</td>
                        </tr>
                    </table>
                </body>
                </html>";

                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream("Boleta_Consumo.pdf", ["Attachment" => false]);
                exit;
            } else {
                die("No se encontró una tarifa para el consumo registrado.");
            }
=======
            header("Location: lecturador.php?success=1");
            exit;
>>>>>>> cbe22471d23d941c7f3b4c8a0b7815ac42730af6
        } else {
            die("Error al registrar el consumo: " . $stmt_insert->error);
        }
    } else {
        die("La lectura actual no puede ser menor a la lectura anterior.");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Consumo</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Registro de Consumo</h1>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($medidor['nombre_socio'] . ' ' . $medidor['apellido_socio']) ?></h5>
                <p class="card-text">
                    Medidor: <strong><?= htmlspecialchars($medidor['medidor']) ?></strong><br>
                    Zona: <strong><?= htmlspecialchars($medidor['zona']) ?></strong><br>
                    Lectura anterior: <strong><?= htmlspecialchars($medidor['lectura_anterior']) ?> m³</strong>
                </p>
            </div>
        </div>

<<<<<<< HEAD
        <form method="POST">
            <div class="mb-3">
                <label for="periodo" class="form-label">Mes:</label>
                <input type="text" id="periodo" name="periodo" class="form-control" value="<?= date('F') ?>" readonly>
            </div>
=======
        <?php if ($mostrar_modal): ?>
            <!-- Modal -->
            <div class="modal show" tabindex="-1" style="display: block; background: rgba(0, 0, 0, 0.5);">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Atención</h5>
                        </div>
                        <div class="modal-body">
                            <p><?= htmlspecialchars($mensaje_modal) ?></p>
                        </div>
                        <div class="modal-footer">
                            <a href="lecturador.php" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="fecha_actual" class="form-label">Fecha Actual:</label>
                    <input type="text" id="fecha_actual" class="form-control" value="<?= date('Y-m-d') ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="periodo" class="form-label">Mes:</label>
                    <input type="text" id="periodo" name="periodo" class="form-control" value="<?= date('F') ?>" readonly>
                </div>
>>>>>>> cbe22471d23d941c7f3b4c8a0b7815ac42730af6

                <div class="mb-3">
                    <label for="lectura_anterior" class="form-label">Lectura Anterior:</label>
                    <input type="text" id="lectura_anterior" name="lectura_anterior" class="form-control" value="<?= htmlspecialchars($medidor['lectura_anterior']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="lectura_actual" class="form-label">Lectura Actual:</label>
                    <input type="number" id="lectura_actual" name="lectura_actual" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Registrar</button>
                <a href="lecturador.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
