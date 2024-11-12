<?php
include 'conexion.php';

// Obtener el término de búsqueda si existe
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta para obtener los medidores con filtro de búsqueda
$sql = "SELECT id_medidor, marca, modelo, fecha FROM medidor";
if (!empty($busqueda)) {
    $sql .= " WHERE CONCAT('MED', LPAD(id_medidor, 4, '0')) LIKE '%$busqueda%' 
              OR marca LIKE '%$busqueda%' 
              OR modelo LIKE '%$busqueda%'";
}
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Medidores</title>
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
            <h1 class="mb-4">Lista de Medidores</h1>
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success" onclick="window.location.href='registrar_medidor.php'">Nuevo Medidor</button>
                <form class="d-flex" method="GET" action="index.php">
                    <input class="form-control me-2" type="search" name="buscar" placeholder="Buscar" value="<?= htmlspecialchars($busqueda) ?>" aria-label="Buscar">
                    <button class="btn btn-outline-primary" type="submit">Buscar</button>
                </form>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">N° de medidor</th>
                        <th scope="col">Fecha registro</th>
                        <th scope="col">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>MED<?= str_pad($fila['id_medidor'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date("d F Y", strtotime($fila['fecha'])) ?></td>
                                <td><?= rand(0, 1) ? 'Asignado' : 'Libre' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No se encontraron resultados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>