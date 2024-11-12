<?php
include 'conexion.php';

// Obtener el ID del socio desde la URL
$id_socio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Inicializar mensaje
$mensaje = '';

// Cargar datos actuales del socio
$sql_socio = "SELECT * FROM socio WHERE id_socio = ?";
$stmt = $conexion->prepare($sql_socio);
$stmt->bind_param('i', $id_socio);
$stmt->execute();
$resultado = $stmt->get_result();
$socio = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar los datos del formulario
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];

    // Validar que no estén vacíos
    if (!empty($ci) && !empty($nombre) && !empty($apellido) && !empty($telefono)) {
        // Actualizar los datos en la base de datos
        $sql_update = "UPDATE socio SET ci = ?, nombre = ?, apellido = ?, telefono = ? WHERE id_socio = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param('ssssi', $ci, $nombre, $apellido, $telefono, $id_socio);

        if ($stmt_update->execute()) {
            // Redirigir a la vista de información del socio con datos actualizados
            header("Location: informacion_socio.php?id=$id_socio&success=1");
            exit;
        } else {
            $mensaje = 'Error al actualizar el socio: ' . $conexion->error;
        }
    } else {
        $mensaje = 'Todos los campos son obligatorios.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Socio</title>
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
                    <a class="nav-link text-white" href="pagos.php">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <h1 class="mb-4">Editar Socio</h1>
            <?php if ($mensaje): ?>
                <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($socio): ?>
                <form method="POST" action="editar_socio.php?id=<?= $id_socio ?>" class="p-4 border rounded">
                    <div class="mb-3">
                        <label for="ci" class="form-label">Cédula de Identidad</label>
                        <input type="text" class="form-control" id="ci" name="ci" value="<?= htmlspecialchars($socio['ci']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($socio['nombre']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?= htmlspecialchars($socio['apellido']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($socio['telefono']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="informacion_socio.php?id=<?= $id_socio ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            <?php else: ?>
                <p class="text-danger">Socio no encontrado.</p>
                <a href="socios.php" class="btn btn-primary">Volver</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
