<?php
include 'conexion.php';

// Obtener el ID del medidor desde la URL
$id_asignacion = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consulta para obtener la información del socio, medidor y zona
$sql_info = "
    SELECT 
        socio.nombre AS nombre_socio, 
        socio.apellido AS apellido_socio,
        socio.ci,
        socio.telefono,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona
    FROM asignacion_medidor
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
    WHERE asignacion_medidor.id_asignacion = ?
";
$stmt_info = $conexion->prepare($sql_info);
$stmt_info->bind_param('i', $id_asignacion);
$stmt_info->execute();
$resultado_info = $stmt_info->get_result();
$info = $resultado_info->fetch_assoc();

// Consulta para obtener el historial de consumo
$sql_historial = "
    SELECT 
        consumo.id_consumo,
        consumo.periodo AS mes,
        DATE_FORMAT(consumo.lectura_anterior, '%d %M') AS fecha_lectura,
        DATE_FORMAT(deudas.fecha_pago, '%d %M') AS fecha_pago,
        consumo.consumo AS consumo_total,
        deudas.monto,
        IF(deudas.fecha_pago IS NULL, 'Por pagar', 'Cancelado') AS estado
    FROM consumo
    LEFT JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE consumo.id_asignacion = ?
    ORDER BY consumo.periodo ASC
";
$stmt_historial = $conexion->prepare($sql_historial);
$stmt_historial->bind_param('i', $id_asignacion);
$stmt_historial->execute();
$resultado_historial = $stmt_historial->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Medidor</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-primary text-white p-3">
            <h2 class="sidebar-title">WATEREG</h2>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php">Medidores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="socios.php">Socios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="lecturador.php">Lecturador</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="pagos.php">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <?php if ($info): ?>
                <h1 class="mb-4">Información del Medidor</h1>
                <div class="mb-4">
                    <p><strong>Socio:</strong> <?= htmlspecialchars($info['nombre_socio'] . ' ' . $info['apellido_socio']) ?></p>
                    <p><strong>CI:</strong> <?= htmlspecialchars($info['ci']) ?></p>
                    <p><strong>Celular:</strong> <?= htmlspecialchars($info['telefono']) ?></p>
                    <p><strong>Medidor:</strong> <?= htmlspecialchars($info['medidor']) ?></p>
                    <p><strong>Zona:</strong> <?= htmlspecialchars($info['zona']) ?></p>
                </div>
                <h2 class="mb-3">Historial de Consumo</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Mes</th>
                            <th scope="col">Fecha Lectura</th>
                            <th scope="col">Fecha Pago</th>
                            <th scope="col">Consumo</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_historial->num_rows > 0): ?>
                            <?php while ($fila = $resultado_historial->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['mes']) ?></td>
                                    <td><?= htmlspecialchars($fila['fecha_lectura']) ?></td>
                                    <td><?= htmlspecialchars($fila['fecha_pago'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($fila['consumo_total']) ?> M³</td>
                                    <td>Bs <?= htmlspecialchars($fila['monto'] ?? '0.00') ?></td>
                                    <td>
                                        <?php if ($fila['estado'] === 'Por pagar'): ?>
                                            <button 
                                                class="btn btn-success registrar-pago-btn" 
                                                data-id="<?= htmlspecialchars($fila['id_consumo']) ?>">
                                                Registrar Pago
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted"><?= htmlspecialchars($fila['estado']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay historial de consumo disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-danger">Información del medidor no encontrada.</p>
                <a href="socios.php" class="btn btn-primary">Volver</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para registrar pago -->
    <div class="modal fade" id="modalPago" tabindex="-1" aria-labelledby="modalPagoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPagoLabel">Registrar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de registrar este pago?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a id="confirmarPago" href="#" class="btn btn-primary" target="_blank">Confirmar y Generar PDF</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Mostrar modal y establecer enlace para confirmar el pago
            $('.registrar-pago-btn').on('click', function () {
                const consumoId = $(this).data('id');
                $('#confirmarPago').attr('href', `registrar_pago.php?id=${consumoId}`);
                $('#modalPago').modal('show');
            });

            // Actualizar la tabla al cerrar el modal
            $('#modalPago').on('hidden.bs.modal', function () {
                location.reload();
            });
        });
    </script>
</body>
</html>
