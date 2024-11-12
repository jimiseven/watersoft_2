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
        <h2 style='text-align: center;'>Cooperativa de agua Iquircollo SUD</h2>
        <p><strong>Socio:</strong> {$info['nombre_socio']} {$info['apellido_socio']}</p>
        <p><strong>CI:</strong> {$info['ci']}</p>
        <p><strong>Medidor:</strong> {$info['medidor']}</p>
        <p><strong>Zona:</strong> {$info['zona']}</p>
        <p><strong>Mes:</strong> {$info['mes']}</p>
        <p><strong>Consumo:</strong> {$info['consumo_total']} m³</p>
        <p><strong>Total:</strong> Bs {$info['monto_total']}</p>
        <p><strong>Fecha de pago:</strong> " . date('d-m-Y') . "</p>
    ";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("recibo_pago_{$id_consumo}.pdf", ["Attachment" => false]);

    exit; // Detener la ejecución después de generar el PDF
} else {
    die("Error al registrar el pago: " . $stmt_pago->error);
}
