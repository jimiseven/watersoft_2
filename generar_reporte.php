<?php
require 'vendor/autoload.php'; // Asegúrate de tener instalada la librería dompdf

use Dompdf\Dompdf;

// Conexión a la base de datos
include 'conexion.php';

// Validar las fechas
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';

if (empty($fecha_inicio) || empty($fecha_fin)) {
    die("Debe proporcionar ambas fechas para generar el reporte.");
}

// Consultar el consumo y los montos en el rango de fechas
$sql_reporte = "
    SELECT 
        COALESCE(SUM(consumo.consumo), 0) AS consumo_total,
        COALESCE(SUM(deudas.monto), 0) AS monto_total
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    WHERE deudas.fecha_pago BETWEEN ? AND ?
";
$stmt_reporte = $conexion->prepare($sql_reporte);
$stmt_reporte->bind_param('ss', $fecha_inicio, $fecha_fin);
$stmt_reporte->execute();
$resultado_reporte = $stmt_reporte->get_result();
$data_reporte = $resultado_reporte->fetch_assoc();

$consumo_total = $data_reporte['consumo_total'];
$monto_total = $data_reporte['monto_total'];

// Preparar el contenido del PDF
$html = "
    <h1 style='text-align: center;'>Cooperativa de Agua Iquircollo SUD</h1>
    <p style='text-align: center;'>Reporte de Consumo y Monto Recaudado</p>
    <hr>
    <p><strong>Fecha de Inicio:</strong> $fecha_inicio</p>
    <p><strong>Fecha de Fin:</strong> $fecha_fin</p>
    <p><strong>Consumo Total:</strong> $consumo_total m³</p>
    <p><strong>Monto Recaudado:</strong> Bs $monto_total</p>
    <hr>
    <p style='text-align: center;'>Gracias por confiar en nosotros.</p>
";

// Generar el PDF usando Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Enviar el PDF al navegador
$dompdf->stream("reporte_consumo.pdf", ["Attachment" => false]);
exit;
