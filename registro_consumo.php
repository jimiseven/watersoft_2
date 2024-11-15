<?php
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
$stmt_medidor->bind_param('i', $id_asignacion);
$stmt_medidor->execute();
$resultado_medidor = $stmt_medidor->get_result();
$medidor = $resultado_medidor->fetch_assoc();

if (!$medidor) {
    echo '<div class="alert alert-danger">Información del medidor no encontrada.</div>';
    echo '<a href="lecturador.php" class="btn btn-primary">Volver</a>';
    exit;
}

// Procesar el formulario si se envió
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$mostrar_modal) {
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
        $stmt_insert->bind_param('iddsds', $id_asignacion, $lectura_anterior, $lectura_actual, $periodo, $consumo, $observaciones);

        if ($stmt_insert->execute()) {
            header("Location: lecturador.php?success=1");
            exit;
        } else {
            $mensaje = 'Error al registrar el consumo: ' . $stmt_insert->error;
        }
    } else {
        $mensaje = 'La lectura actual no puede ser menor a la lectura anterior.';
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
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Registro de Consumo</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

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
