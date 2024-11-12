<?php
require 'vendor/autoload.inc.php';
use Dompdf\Dompdf;

// Incluir la conexión a la base de datos
include 'conexion.php';

// Obtener el ID del consumo desde la URL
$id_consumo = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consulta para obtener los datos de la boleta
$sql = "
    SELECT 
        CONCAT(socio.nombre, ' ', socio.apellido) AS socio,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        zona.nombre AS zona,
        consumo.consumo AS consumo_total,
        deudas.monto AS total,
        deudas.fecha_pago
    FROM consumo
    INNER JOIN asignacion_medidor ON consumo.id_asignacion = asignacion_medidor.id_asignacion
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE consumo.id_consumo = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $id_consumo);
$stmt->execute();
$resultado = $stmt->get_result();
$boleta = $resultado->fetch_assoc();

if (!$boleta) {
    die("No se encontraron datos para la boleta.");
}

// Crear el contenido HTML para el PDF
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            border: 1px solid #000;
            padding: 20px;
            text-align: center;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info {
            margin-top: 20px;
            font-size: 16px;
        }
        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Cooperativa de agua Iquircollo SUD</h1>
    <div class="info">
        <p><strong>Socio:</strong> ' . htmlspecialchars($boleta['socio']) . '</p>
        <p><strong>Medidor:</strong> ' . htmlspecialchars($boleta['medidor']) . '</p>
        <p><strong>Zona:</strong> ' . htmlspecialchars($boleta['zona']) . '</p>
        <p><strong>Fecha pago:</strong> ' . htmlspecialchars($boleta['fecha_pago']) . '</p>
        <p><strong>Consumo:</strong> ' . htmlspecialchars($boleta['consumo_total']) . ' m³</p>
        <p class="total"><strong>Total:</strong> Bs ' . htmlspecialchars($boleta['total']) . '</p>
    </div>
</body>
</html>
';

// Instanciar y usar dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Forzar descarga del archivo PDF
$dompdf->stream('boleta_pago.pdf', ['Attachment' => true]);
?>
