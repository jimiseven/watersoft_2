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

// Consultar estadísticas de consumo por rango
$sql_estadisticas = "
    SELECT 
        tarifas.rango_inicio, 
        tarifas.rango_fin, 
        COUNT(consumo.id_consumo) AS cantidad
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    INNER JOIN tarifas ON consumo.consumo BETWEEN tarifas.rango_inicio AND tarifas.rango_fin
    WHERE deudas.fecha_pago BETWEEN ? AND ?
    GROUP BY tarifas.id_tarifa
    ORDER BY tarifas.rango_inicio
";
$stmt_estadisticas = $conexion->prepare($sql_estadisticas);
$stmt_estadisticas->bind_param('ss', $fecha_inicio, $fecha_fin);
$stmt_estadisticas->execute();
$resultado_estadisticas = $stmt_estadisticas->get_result();
$estadisticas = $resultado_estadisticas->fetch_all(MYSQLI_ASSOC);

// Consultar listado de consumos detallados
$sql_detalles = "
    SELECT 
        CONCAT('MED', LPAD(medidor.id_medidor, 4, '0')) AS medidor,
        CONCAT(socio.nombre, ' ', socio.apellido) AS socio,
        consumo.consumo
    FROM consumo
    INNER JOIN deudas ON consumo.id_consumo = deudas.id_consumo
    INNER JOIN asignacion_medidor ON consumo.id_asignacion = asignacion_medidor.id_asignacion
    INNER JOIN medidor ON asignacion_medidor.id_medidor = medidor.id_medidor
    INNER JOIN socio ON asignacion_medidor.id_socio = socio.id_socio
    WHERE deudas.fecha_pago BETWEEN ? AND ?
    ORDER BY consumo.consumo DESC
";
$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->bind_param('ss', $fecha_inicio, $fecha_fin);
$stmt_detalles->execute();
$resultado_detalles = $stmt_detalles->get_result();
$detalles = $resultado_detalles->fetch_all(MYSQLI_ASSOC);

// Preparar el contenido del PDF con diseño mejorado
$html = "
    <style>
        body { font-family: Arial, sans-serif; }
        h1, h2, h3 { text-align: center; }
        .header { background-color: #004085; color: #fff; padding: 10px; text-align: center; }
        .section { margin-bottom: 20px; }
        .section p { margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .table th { background-color: #f8f9fa; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
    <div class='header'>
        <h1>Cooperativa de Agua Iquircollo SUD</h1>
        <h3>Reporte de Consumo y Monto Recaudado</h3>
    </div>
    <div class='section'>
        <h3>Información del Rango de Fechas</h3>
        <p><strong>Fecha de Inicio:</strong> $fecha_inicio</p>
        <p><strong>Fecha de Fin:</strong> $fecha_fin</p>
    </div>
    <div class='section'>
        <h3>Totales Generales</h3>
        <p><strong>Consumo Total:</strong> $consumo_total m³</p>
        <p><strong>Monto Recaudado:</strong> Bs $monto_total</p>
    </div>
    <div class='section'>
        <h3>Estadísticas de Consumo por Rango</h3>
        <table class='table'>
            <thead>
                <tr>
                    <th>Rango de Consumo (m³)</th>
                    <th>Cantidad de Medidores</th>
                </tr>
            </thead>
            <tbody>";
            
foreach ($estadisticas as $fila) {
    $html .= "
                <tr>
                    <td>{$fila['rango_inicio']} - {$fila['rango_fin']}</td>
                    <td>{$fila['cantidad']}</td>
                </tr>";
}

$html .= "
            </tbody>
        </table>
    </div>
    <div class='section'>
        <h3>Listado de Consumos Detallados</h3>
        <table class='table'>
            <thead>
                <tr>
                    <th>Medidor</th>
                    <th>Socio</th>
                    <th>Consumo (m³)</th>
                </tr>
            </thead>
            <tbody>";
            
foreach ($detalles as $detalle) {
    $html .= "
                <tr>
                    <td>{$detalle['medidor']}</td>
                    <td>{$detalle['socio']}</td>
                    <td>{$detalle['consumo']}</td>
                </tr>";
}

$html .= "
            </tbody>
        </table>
    </div>
    <div class='footer'>
        <p>Gracias por confiar en nosotros.</p>
        <p>&copy; Cooperativa de Agua Iquircollo SUD</p>
    </div>
";

// Generar el PDF usando Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Enviar el PDF al navegador
$dompdf->stream("reporte_consumo.pdf", ["Attachment" => false]);
exit;
