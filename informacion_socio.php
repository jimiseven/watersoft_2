<?php
include 'conexion.php';

// Obtener el ID del socio desde la URL
$id_socio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consulta para obtener la información del socio
$sql_socio = "SELECT * FROM socio WHERE id_socio = ?";
$stmt_socio = $conexion->prepare($sql_socio);
$stmt_socio->bind_param('i', $id_socio);
$stmt_socio->execute();
$resultado_socio = $stmt_socio->get_result();
$socio = $resultado_socio->fetch_assoc();

// Consulta para obtener los medidores y consumos asignados al socio
$sql_medidores = "
    SELECT 
        asignacion_medidor.id_asignacion,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona,
        IFNULL(SUM(consumo.consumo), 0) AS consumo_total
    FROM asignacion_medidor
    LEFT JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    LEFT JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
    LEFT JOIN consumo ON asignacion_medidor.id_asignacion = consumo.id_asignacion
    WHERE asignacion_medidor.id_socio = ?
    GROUP BY asignacion_medidor.id_asignacion, medidor.id_medidor, zona.nombre
";
$stmt_medidores = $conexion->prepare($sql_medidores);
$stmt_medidores->bind_param('i', $id_socio);
$stmt_medidores->execute();
$resultado_medidores = $stmt_medidores->get_result();

// Mensaje de éxito
$mensaje = isset($_GET['success']) && $_GET['success'] == 1 ? 'Los datos del socio se actualizaron correctamente.' : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Socio</title>
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
                    <a class="nav-link text-white" href="#">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($socio): ?>
                <h1 class="mb-4">Información del Socio</h1>
                <div class="mb-4">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido']) ?></p>
                    <p><strong>CI:</strong> <?= htmlspecialchars($socio['ci']) ?></p>
                    <p><strong>Celular:</strong> <?= htmlspecialchars($socio['telefono']) ?></p>
                </div>
                <div class="mb-4 d-flex justify-content-end">
                    <a href="asignar_medidor.php?id=<?= $socio['id_socio'] ?>" class="btn btn-success me-2">Asignar Medidor</a>
                    <a href="editar_socio.php?id=<?= $socio['id_socio'] ?>" class="btn btn-primary">Editar Socio</a>
                </div>
                <h2 class="mb-3">Medidores Asignados</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Medidor</th>
                            <th scope="col">Zona</th>
                            <th scope="col">Consumo Total (M³)</th>
                            <th scope="col">Información</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_medidores->num_rows > 0): ?>
                            <?php while ($fila = $resultado_medidores->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['medidor']) ?></td>
                                    <td><?= htmlspecialchars($fila['zona']) ?></td>
                                    <td><?= htmlspecialchars($fila['consumo_total']) ?> M³</td>
                                    <td>
                                        <a href="informacion_medidor.php?id=<?= $fila['id_asignacion'] ?>" class="btn btn-info btn-sm">Información</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay medidores asignados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-danger">Socio no encontrado.</p>
                <a href="socios.php" class="btn btn-primary">Volver</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
