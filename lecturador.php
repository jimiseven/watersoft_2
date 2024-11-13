<?php
include 'conexion.php';

// Inicializar variables para contadores
$lecturados = 0;
$sin_lectura = 0;

// Consultar el número de medidores lecturados y sin lectura
$sql_contadores = "
    SELECT 
        SUM(CASE WHEN consumo.id_consumo IS NOT NULL THEN 1 ELSE 0 END) AS lecturados,
        SUM(CASE WHEN consumo.id_consumo IS NULL THEN 1 ELSE 0 END) AS sin_lectura
    FROM asignacion_medidor
    LEFT JOIN consumo ON asignacion_medidor.id_asignacion = consumo.id_asignacion
";
$result_contadores = $conexion->query($sql_contadores);
if ($result_contadores) {
    $data_contadores = $result_contadores->fetch_assoc();
    $lecturados = $data_contadores['lecturados'] ?? 0;
    $sin_lectura = $data_contadores['sin_lectura'] ?? 0;
}

// Obtener el término de búsqueda y filtro si existen
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

// Consulta para obtener los medidores y su estado de lectura
$sql = "
    SELECT 
        asignacion_medidor.id_asignacion, 
        CONCAT(socio.nombre, ' ', socio.apellido) AS socio,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona,
        IFNULL((SELECT 'Lecturado' FROM consumo WHERE consumo.id_asignacion = asignacion_medidor.id_asignacion LIMIT 1), 'Sin lectura') AS estado
    FROM asignacion_medidor
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
";

// Aplicar filtro según la selección
if ($filtro === 'lecturados') {
    $sql .= " WHERE EXISTS (SELECT 1 FROM consumo WHERE consumo.id_asignacion = asignacion_medidor.id_asignacion)";
} elseif ($filtro === 'sin_lectura') {
    $sql .= " WHERE NOT EXISTS (SELECT 1 FROM consumo WHERE consumo.id_asignacion = asignacion_medidor.id_asignacion)";
}

// Aplicar búsqueda si existe
if (!empty($busqueda)) {
    $sql .= (strpos($sql, 'WHERE') !== false ? ' AND' : ' WHERE') . " (CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) LIKE ? OR socio.nombre LIKE ? OR socio.apellido LIKE ?)";
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
    <link rel="stylesheet" href="css/styles_lecturado.css">
</head>

<body>
    <!-- Botón para mostrar el sidebar en móviles -->
    <button class="toggle-sidebar-btn d-md-none">☰ Menú</button>

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
            <h1 class="mb-4 text-center">Lecturación</h1>

            <!-- Contadores -->
            <div class="d-flex flex-column flex-md-row justify-content-around mb-4">
                <div class="contador">
                    <h2><?= htmlspecialchars($lecturados) ?></h2>
                    <p>Lecturados</p>
                </div>
                <div class="contador">
                    <h2><?= htmlspecialchars($sin_lectura) ?></h2>
                    <p>Sin Lectura</p>
                </div>
            </div>

            <!-- Formulario de búsqueda y filtros -->
            <form method="GET" action="lecturador.php" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="buscar" placeholder="Buscar por medidor o socio" value="<?= htmlspecialchars($busqueda) ?>">
                    <select class="form-select" name="filtro">
                        <option value="todos" <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="lecturados" <?= $filtro === 'lecturados' ? 'selected' : '' ?>>Lecturados</option>
                        <option value="sin_lectura" <?= $filtro === 'sin_lectura' ? 'selected' : '' ?>>Sin Lectura</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>

            <!-- Listado de medidores -->
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

    <script>
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');

        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    </script>
</body>

</html>
