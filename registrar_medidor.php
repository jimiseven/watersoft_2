<?php
include 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $fecha = $_POST['fecha'];

    // Validar que los campos no estén vacíos
    if (!empty($marca) && !empty($modelo) && !empty($fecha)) {
        // Insertar datos en la tabla medidor
        $sql = "INSERT INTO medidor (marca, modelo, fecha) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('sss', $marca, $modelo, $fecha);

        if ($stmt->execute()) {
            $mensaje = 'Medidor registrado con éxito.';
        } else {
            $mensaje = 'Error al registrar el medidor: ' . $conexion->error;
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
    <title>Registrar Medidor</title>
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
                    <a class="nav-link text-white" href="#">Socios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Lecturador</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Pagos</a>
                </li>
            </ul>
        </div>

        <!-- Contenido Principal -->
        <div class="content flex-grow-1 p-4">
            <h1 class="mb-4">Registrar Medidor</h1>
            <?php if ($mensaje): ?>
                <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <form method="POST" action="registrar_medidor.php" class="p-4 border rounded">
                <div class="mb-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input type="text" class="form-control" id="marca" name="marca" required>
                </div>
                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo" name="modelo" required>
                </div>
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha de registro</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </form>
        </div>
    </div>
</body>
</html>
