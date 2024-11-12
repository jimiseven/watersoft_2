<?php
include 'conexion.php';

// Variables iniciales para los totales
$consumo_total_anual = 0;
$monto_recaudado = 0;

// Año actual
$anio_actual = date('Y');

// Consultar el consumo total anual y el monto recaudado
$sql = "
    SELECT 
        SUM(consumo.consumo) AS consumo_total_anual,
        SUM(deudas.monto) AS monto_recaudado
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE YEAR(deudas.fecha_pago) = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $anio_actual);
$stmt->execute();
$resultado = $stmt->get_result();
$data = $resultado->fetch_assoc();

if ($data) {
    $consumo_total_anual = $data['consumo_total_anual'] ?? 0;
    $monto_recaudado = $data['monto_recaudado'] ?? 0;
}

// Consultar el consumo de los últimos 3 meses
$sql_meses = "
    SELECT 
        DATE_FORMAT(deudas.fecha_pago, '%M') AS mes,
        SUM(consumo.consumo) AS consumo_mes
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE YEAR(deudas.fecha_pago) = ?
    GROUP BY MONTH(deudas.fecha_pago)
    ORDER BY MONTH(deudas.fecha_pago) DESC
    LIMIT 3
";
$stmt_meses = $conexion->prepare($sql_meses);
$stmt_meses->bind_param('i', $anio_actual);
$stmt_meses->execute();
$resultado_meses = $stmt_meses->get_result();
$meses = $resultado_meses->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
                    <a class="nav-link text-white active" href="pagos.php">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <h1 class="mb-4">Cooperativa de agua Iquircollo SUD</h1>
            <p><strong>Consumo total anual:</strong> <?= htmlspecialchars($consumo_total_anual) ?> m³</p>
            <p><strong>Monto recaudado:</strong> <?= htmlspecialchars($monto_recaudado) ?> Bs</p>

            <h2 class="mb-4">Últimos 3 meses</h2>
            <div class="row">
                <?php foreach ($meses as $mes): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-uppercase"><?= htmlspecialchars($mes['mes']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($mes['consumo_mes']) ?> m³</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="mt-4">Generar Reporte</h2>
            <form method="POST" action="generar_reporte.php" class="mt-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Generar Reporte</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
