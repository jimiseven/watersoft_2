<?php
include 'conexion.php';

// Año y mes actual
$anio_actual = date('Y');
$mes_actual = date('m');

// Variables iniciales para los totales
$consumo_total_anual = 0;
$monto_recaudado = 0;

// Consultar el consumo total anual y el monto recaudado
$sql_anual = "
    SELECT 
        SUM(consumo.consumo) AS consumo_total_anual,
        SUM(deudas.monto) AS monto_recaudado
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE YEAR(deudas.fecha_pago) = ?
";
$stmt_anual = $conexion->prepare($sql_anual);
$stmt_anual->bind_param('i', $anio_actual);
$stmt_anual->execute();
$resultado_anual = $stmt_anual->get_result();
$data_anual = $resultado_anual->fetch_assoc();

if ($data_anual) {
    $consumo_total_anual = $data_anual['consumo_total_anual'] ?? 0;
    $monto_recaudado = $data_anual['monto_recaudado'] ?? 0;
}

// Inicializar array de meses
$ultimos_meses = [];

// Obtener los nombres y consumos de los últimos 3 meses
for ($i = 2; $i >= 0; $i--) {
    $mes = date('m', strtotime("-$i month"));
    $anio = date('Y', strtotime("-$i month"));
    $nombre_mes = date('F Y', strtotime("-$i month"));

    // Consulta para consumo del mes específico
    $sql_mes = "
        SELECT 
            SUM(consumo.consumo) AS consumo_mes
        FROM consumo
        INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
        WHERE YEAR(deudas.fecha_pago) = ? AND MONTH(deudas.fecha_pago) = ?
    ";
    $stmt_mes = $conexion->prepare($sql_mes);
    $stmt_mes->bind_param('ii', $anio, $mes);
    $stmt_mes->execute();
    $resultado_mes = $stmt_mes->get_result();
    $data_mes = $resultado_mes->fetch_assoc();

    // Agregar los datos al array, aunque no haya consumo
    $ultimos_meses[] = [
        'mes' => $nombre_mes,
        'consumo' => $data_mes['consumo_mes'] ?? 0,
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .reporte-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
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
                    <a class="nav-link text-white active" href="pagos.php">Reportes</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4 position-relative">
            <button class="btn btn-success reporte-btn" data-bs-toggle="modal" data-bs-target="#reporteModal">Reporte</button>
            <h1 class="mb-4">Cooperativa de agua Iquircollo SUD</h1>
            <p><strong>Consumo total anual:</strong> <?= htmlspecialchars($consumo_total_anual) ?> m³</p>
            <p><strong>Monto recaudado:</strong> <?= htmlspecialchars($monto_recaudado) ?> Bs</p>

            <h2 class="mb-4">Últimos 3 meses</h2>
            <div class="row">
                <?php foreach ($ultimos_meses as $mes): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase"><?= htmlspecialchars($mes['mes']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($mes['consumo']) ?> m³</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="mt-4">Estadísticas por Rango de Consumo</h2>
            <form method="GET" action="pagos.php" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= date('Y-m-01') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= date('Y-m-t') ?>" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de estadísticas -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rango</th>
                        <th>Costo Unitario</th>
                        <th>Medidores</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultar estadísticas por rango de consumo
                    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
                    $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

                    $sql_rangos = "
                        SELECT 
                            tarifas.rango_inicio,
                            tarifas.rango_fin,
                            tarifas.costo_unitario,
                            COUNT(consumo.id_consumo) AS medidores
                        FROM tarifas
                        LEFT JOIN consumo ON consumo.consumo BETWEEN tarifas.rango_inicio AND tarifas.rango_fin
                        INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
                        WHERE deudas.fecha_pago BETWEEN ? AND ?
                        GROUP BY tarifas.id_tarifa
                        ORDER BY tarifas.rango_inicio ASC
                    ";
                    $stmt_rangos = $conexion->prepare($sql_rangos);
                    $stmt_rangos->bind_param('ss', $fecha_inicio, $fecha_fin);
                    $stmt_rangos->execute();
                    $resultado_rangos = $stmt_rangos->get_result();

                    while ($fila = $resultado_rangos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['rango_inicio']) ?> - <?= htmlspecialchars($fila['rango_fin']) ?> m³</td>
                            <td>Bs <?= htmlspecialchars($fila['costo_unitario']) ?></td>
                            <td><?= htmlspecialchars($fila['medidores']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Reporte -->
    <div class="modal fade" id="reporteModal" tabindex="-1" aria-labelledby="reporteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="generar_reporte.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reporteModalLabel">Generar Reporte</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fecha_inicio_reporte" class="form-label">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio_reporte" name="fecha_inicio" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_fin_reporte" class="form-label">Fecha Fin:</label>
                            <input type="date" id="fecha_fin_reporte" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Generar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
