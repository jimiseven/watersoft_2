<?php
include 'conexion.php';

// Obtener el ID del socio desde la URL
$id_socio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consulta para obtener la información del socio
$sql_socio = "SELECT nombre, apellido, ci FROM socio WHERE id_socio = ?";
$stmt_socio = $conexion->prepare($sql_socio);
$stmt_socio->bind_param('i', $id_socio);
$stmt_socio->execute();
$resultado_socio = $stmt_socio->get_result();
$socio = $resultado_socio->fetch_assoc();

// Consulta para obtener las zonas disponibles
$sql_zonas = "SELECT id_zona, nombre FROM zona";
$resultado_zonas = $conexion->query($sql_zonas);

// Consulta para obtener los medidores disponibles (no asignados)
$sql_medidores = "
    SELECT id_medidor, CONCAT('MED', LPAD(id_medidor, 4, '0')) AS medidor
    FROM medidor
    WHERE id_medidor NOT IN (SELECT id_medidor FROM asignacion_medidor)
";
$resultado_medidores = $conexion->query($sql_medidores);

// Procesar el formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_zona = $_POST['id_zona'];
    $id_medidor = $_POST['id_medidor'];
    $precio = $_POST['precio'];

    if (!empty($id_zona) && !empty($id_medidor) && !empty($precio)) {
        $sql_insert = "INSERT INTO asignacion_medidor (id_socio, id_medidor, id_zona, fecha, precio_accion)
                       VALUES (?, ?, ?, NOW(), ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->bind_param('iiid', $id_socio, $id_medidor, $id_zona, $precio);

        if ($stmt_insert->execute()) {
            // Redirigir con mensaje de éxito
            header("Location: informacion_socio.php?id=$id_socio&success=1");
            exit;
        } else {
            $mensaje = 'Error al asignar el medidor: ' . $conexion->error;
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
    <title>Asignar Medidor</title>
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
            <h1 class="mb-4">Asignación de Medidor</h1>
            <?php if ($mensaje): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <form method="POST" action="asignar_medidor.php?id=<?= $id_socio ?>" class="p-4 border rounded">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" value="<?= htmlspecialchars($socio['nombre']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label for="apellido" class="form-label">Apellido:</label>
                        <input type="text" class="form-control" id="apellido" value="<?= htmlspecialchars($socio['apellido']) ?>" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="ci" class="form-label">Cédula de Identidad:</label>
                        <input type="text" class="form-control" id="ci" value="<?= htmlspecialchars($socio['ci']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label for="fecha" class="form-label">Fecha Registro:</label>
                        <input type="text" class="form-control" value="<?= date('Y-m-d') ?>" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="id_zona" class="form-label">Zona:</label>
                        <select id="id_zona" name="id_zona" class="form-control" required>
                            <option value="">Seleccione una zona</option>
                            <?php while ($zona = $resultado_zonas->fetch_assoc()): ?>
                                <option value="<?= $zona['id_zona'] ?>"><?= htmlspecialchars($zona['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_medidor" class="form-label">Medidor:</label>
                        <select id="id_medidor" name="id_medidor" class="form-control" required>
                            <option value="">Seleccione un medidor</option>
                            <?php while ($medidor = $resultado_medidores->fetch_assoc()): ?>
                                <option value="<?= $medidor['id_medidor'] ?>"><?= htmlspecialchars($medidor['medidor']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio:</label>
                    <input type="number" step="0.01" class="form-control" id="precio" name="precio" placeholder="Ingrese el precio" required>
                </div>
                <button type="submit" class="btn btn-success">Asignar Medidor</button>
                <a href="informacion_socio.php?id=<?= $id_socio ?>" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>
