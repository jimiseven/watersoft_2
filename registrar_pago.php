<?php
require 'conexion.php';
require 'vendor/autoload.php'; // Cargar Dompdf

use Dompdf\Dompdf;

// Obtener el ID del consumo desde la URL
$id_consumo = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_consumo <= 0) {
    die("ID de consumo inválido.");
}

// Consulta para obtener información de la deuda y consumo
$sql_info = "
    SELECT 
        socio.nombre AS nombre_socio,
        socio.apellido AS apellido_socio,
        socio.ci,
        zona.nombre AS zona,
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        consumo.periodo AS mes,
        consumo.consumo AS consumo_total,
        deudas.monto AS monto_total
    FROM deudas
    INNER JOIN consumo ON deudas.id_consumo = consumo.id_consumo
    INNER JOIN asignacion_medidor ON consumo.id_asignacion = asignacion_medidor.id_asignacion
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN zona ON asignacion_medidor.id_zona = zona.id_zona
    WHERE deudas.id_consumo = ?
";
$stmt_info = $conexion->prepare($sql_info);
$stmt_info->bind_param('i', $id_consumo);
$stmt_info->execute();
$resultado_info = $stmt_info->get_result();
$info = $resultado_info->fetch_assoc();

if (!$info) {
    die("Información no encontrada para este consumo.");
}

// Registrar el pago
$sql_pago = "
    UPDATE deudas
    SET fecha_pago = NOW()
    WHERE id_consumo = ?
";
$stmt_pago = $conexion->prepare($sql_pago);
$stmt_pago->bind_param('i', $id_consumo);

if ($stmt_pago->execute()) {
    // Generar PDF de recibo
    $dompdf = new Dompdf();
    $html = "
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            .header {
                text-align: center;
                background-color: #004085;
                color: #fff;
                padding: 10px;
                margin-bottom: 20px;
            }
            .header h2 {
                margin: 0;
            }
            .content {
                padding: 20px;
            }
            .section {
                margin-bottom: 20px;
            }
            .section p {
                margin: 5px 0;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #555;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .table th {
                background-color: #f4f4f4;
                font-weight: bold;
            }
        </style>
        <div class='header'>
            <h2>Cooperativa de Agua Iquircollo SUD</h2>
            <p>Recibo de Pago</p>
        </div>
        <div class='content'>
            <div class='section'>
                <h3>Información del Socio</h3>
                <p><strong>Nombre:</strong> {$info['nombre_socio']} {$info['apellido_socio']}</p>
                <p><strong>CI:</strong> {$info['ci']}</p>
                <p><strong>Zona:</strong> {$info['zona']}</p>
            </div>
            <div class='section'>
                <h3>Información del Consumo</h3>
                <p><strong>Medidor:</strong> {$info['medidor']}</p>
                <p><strong>Mes:</strong> {$info['mes']}</p>
                <p><strong>Consumo:</strong> {$info['consumo_total']} m³</p>
                <p><strong>Total a Pagar:</strong> Bs {$info['monto_total']}</p>
                <p><strong>Fecha de Pago:</strong> " . date('d-m-Y') . "</p>
            </div>
            <div class='footer'>
                <p>Gracias por confiar en nosotros.</p>
                <p>&copy; Cooperativa de Agua Iquircollo SUD</p>
            </div>
        </div>
    ";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("recibo_pago_{$id_consumo}.pdf", ["Attachment" => false]);

    exit; // Detener la ejecución después de generar el PDF
} else {
    die("Error al registrar el pago: " . $stmt_pago->error);
}
