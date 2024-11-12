<?php
include 'conexion.php';

// Obtener el término de búsqueda si existe
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Consulta para obtener los socios con filtro de búsqueda
$sql = "SELECT id_socio, nombre, apellido, fecha_registro FROM socio";
if (!empty($busqueda)) {
    $sql .= " WHERE CONCAT(nombre, ' ', apellido) LIKE '%$busqueda%'";
}
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Socios</title>
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
            <h1 class="mb-4">Lista de Socios</h1>
            <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success" onclick="window.location.href='registrar_socio.php'">Nuevo Socio</button>
                <form class="d-flex" method="GET" action="socios.php">
                    <input class="form-control me-2" type="search" name="buscar" placeholder="Buscar" value="<?= htmlspecialchars($busqueda) ?>" aria-label="Buscar">
                    <button class="btn btn-outline-primary" type="submit">Buscar</button>
                </form>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Fecha de Registro</th>
                        <th scope="col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <!-- Nombre como texto sin enlace -->
                                <td><?= htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']) ?></td>
                                <td><?= date("d F Y", strtotime($fila['fecha_registro'])) ?></td>
                                <td>
                                    <!-- Botón Información -->
                                    <a href="informacion_socio.php?id=<?= $fila['id_socio'] ?>" class="btn btn-info btn-sm">Información</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No se encontraron socios</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
