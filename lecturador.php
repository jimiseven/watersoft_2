<?php
include 'conexion.php';

// Obtener el término de búsqueda si existe
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta para obtener los medidores y su estado de lectura
$sql = "
    SELECT 
        asignacion_medidor.id_asignacion, -- Aseguramos que id_asignacion esté disponible
        CONCAT(socio.nombre, ' ', socio.apellido) AS socio,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona,
        IFNULL((SELECT 'Lecturado' FROM consumo WHERE consumo.id_asignacion = asignacion_medidor.id_asignacion LIMIT 1), 'Sin lectura') AS estado
    FROM asignacion_medidor
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
";
if (!empty($busqueda)) {
    $sql .= " WHERE CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) LIKE ? OR socio.nombre LIKE ? OR socio.apellido LIKE ?";
    $stmt = $conexion->prepare($sql);
    $like_busqueda = '%' . $busqueda . '%';
    $stmt->bind_param('sss', $like_busqueda, $like_busqueda, $like_busqueda);
} else {
    $stmt = $conexion->prepare($sql);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturador</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .medidor-card {
            border: 1px solid #000;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .medidor-card:hover {
            background-color: #f1f1f1;
        }
        .medidor-card a {
            text-decoration: none;
            color: inherit;
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
                    <a class="nav-link text-white active" href="lecturador.php">Lecturador</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="pagos.php">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <h1 class="mb-4">Lecturación</h1>
            <form method="GET" action="lecturador.php" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="buscar" placeholder="Buscar por medidor o socio" value="<?= htmlspecialchars($busqueda) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <div class="medidor-card">
                        <a href="registro_consumo.php?id=<?= htmlspecialchars($fila['id_asignacion']) ?>">
                            <strong><?= htmlspecialchars($fila['socio']) ?></strong><br>
                            <?= htmlspecialchars($fila['medidor']) ?> - <?= htmlspecialchars($fila['zona']) ?><br>
                            <span class="text-muted"><?= htmlspecialchars($fila['estado']) ?></span>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-danger">No se encontraron medidores.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
