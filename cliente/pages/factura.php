<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../libs/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Obtener el order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('ID de pedido no válido');
}

// Obtener datos del pedido
$stmt = $pdo->prepare("SELECT o.*, c.first_name, c.last_name, c.email, c.phone 
                       FROM orders o
                       INNER JOIN customer c ON o.customer_id = c.customer_id
                       WHERE o.order_id = :id");
$stmt->execute(['id' => $order_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    die('Pedido no encontrado');
}

// Verificar que el pedido pertenece al cliente actual (si está logueado)
if (isset($_SESSION['customer_id']) && $pedido['customer_id'] != $_SESSION['customer_id']) {
    die('No tienes permiso para ver esta factura');
}

// Obtener items del pedido
$stmt = $pdo->prepare("SELECT oi.*, p.product_name 
                       FROM order_items oi
                       LEFT JOIN productos p ON oi.product_id = p.product_id
                       WHERE oi.order_id = :id");
$stmt->execute(['id' => $order_id]);
$items = $stmt->fetchAll();

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

// Generar el HTML de la factura
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.5;
        }
        
        .container {
            width: 100%;
            padding: 20px;
        }
        
        .header {
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .logo {
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .logo-icon {
            color: #3498db;
        }
        
        .company-info {
            font-size: 9pt;
            color: #666;
            line-height: 1.4;
        }
        
        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 14pt;
            color: #666;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 10pt;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .info-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .info-text {
            font-size: 10pt;
            color: #555;
            line-height: 1.6;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table thead {
            background-color: #2c3e50;
            color: white;
        }
        
        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .items-table th.text-center {
            text-align: center;
        }
        
        .items-table th.text-right {
            text-align: right;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10pt;
        }
        
        .items-table td.text-center {
            text-align: center;
        }
        
        .items-table td.text-right {
            text-align: right;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        
        .totals-table {
            width: 350px;
            float: right;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 12px;
            font-size: 10pt;
        }
        
        .totals-table .label {
            text-align: right;
            color: #666;
            border-bottom: 1px solid #eee;
        }
        
        .totals-table .value {
            text-align: right;
            font-weight: bold;
            border-bottom: 1px solid #eee;
            width: 120px;
        }
        
        .totals-table .discount {
            color: #27ae60;
        }
        
        .totals-table .total-row {
            background-color: #2c3e50;
            color: white;
            font-size: 12pt;
        }
        
        .totals-table .total-row td {
            padding: 12px;
            border-bottom: none;
        }
        
        .payment-info {
            clear: both;
            margin-top: 30px;
            padding: 15px;
            background-color: #e8f4f8;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .payment-info-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .payment-info-text {
            font-size: 10pt;
            color: #555;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            font-size: 9pt;
            color: #999;
        }
        
        .footer-bold {
            font-weight: bold;
            color: #666;
        }
        
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff9e6;
            border-left: 4px solid #f39c12;
            border-radius: 5px;
        }
        
        .notes-title {
            font-size: 10pt;
            font-weight: bold;
            color: #d68910;
            margin-bottom: 8px;
        }
        
        .notes-text {
            font-size: 9pt;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ENCABEZADO -->
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <div class="logo">
                        <span class="logo-icon">🚲</span> Bike Store
                    </div>
                    <div class="company-info">
                        Av. Principal #123, Ciudad<br>
                        Teléfono: (555) 123-4567<br>
                        Email: ventas@bikestore.com<br>
                        Web: www.bikestore.com
                    </div>
                </div>
                <div class="header-right">
                    <div class="invoice-title">FACTURA</div>
                    <div class="invoice-number">#' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '</div>
                    <div class="invoice-date">
                        Fecha: ' . date('d/m/Y', strtotime($pedido['order_date'])) . '<br>
                        Hora: ' . date('H:i:s', strtotime($pedido['order_date'])) . '
                    </div>
                </div>
            </div>
        </div>
        
        <!-- INFORMACIÓN DEL CLIENTE Y ENVÍO -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-title">Cliente</div>
                        <div class="info-text">
                            <strong>' . htmlspecialchars($pedido['first_name'] . ' ' . $pedido['last_name']) . '</strong><br>
                            Email: ' . htmlspecialchars($pedido['email']) . '<br>
                            ' . ($pedido['phone'] ? 'Teléfono: ' . htmlspecialchars($pedido['phone']) : '') . '
                        </div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-title">Dirección de Envío</div>
                        <div class="info-text">
                            ' . nl2br(htmlspecialchars($pedido['direccion_envio'] ?? 'No especificada')) . '
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TABLA DE PRODUCTOS -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%;">#</th>
                    <th style="width: 45%;">Producto</th>
                    <th class="text-center" style="width: 15%;">Cantidad</th>
                    <th class="text-right" style="width: 15%;">Precio Unit.</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>';

$item_number = 1;
foreach ($items as $item) {
    $subtotal = $item['quantity'] * $item['list_price'];
    $html .= '
                <tr>
                    <td>' . $item_number . '</td>
                    <td>' . htmlspecialchars($item['product_name'] ?? 'Producto') . '</td>
                    <td class="text-center">' . $item['quantity'] . '</td>
                    <td class="text-right">$' . number_format($item['list_price'], 2) . '</td>
                    <td class="text-right">$' . number_format($subtotal, 2) . '</td>
                </tr>';
    $item_number++;
}

$html .= '
            </tbody>
        </table>
        
        <!-- TOTALES -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">$' . number_format($pedido['subtotal'] ?? 0, 2) . '</td>
                </tr>';

if ($pedido['descuento'] > 0) {
    $html .= '
                <tr>
                    <td class="label discount">Descuento:</td>
                    <td class="value discount">-$' . number_format($pedido['descuento'], 2) . '</td>
                </tr>';
}

$html .= '
                <tr>
                    <td class="label">Costo de Envío:</td>
                    <td class="value">' . ($pedido['costo_envio'] == 0 ? '<span style="color: #27ae60;">GRATIS</span>' : '$' . number_format($pedido['costo_envio'], 2)) . '</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>$' . number_format($pedido['total_amount'] ?? 0, 2) . '</td>
                </tr>
            </table>
        </div>
        
        <!-- INFORMACIÓN DE PAGO -->
        <div class="payment-info">
            <div class="payment-info-title">Método de Pago</div>
            <div class="payment-info-text">
                ' . htmlspecialchars($pedido['metodo_pago'] ?? 'No especificado') . '
            </div>
        </div>';

// Notas del pedido (si existen)
if (!empty($pedido['notas'])) {
    $html .= '
        <div class="notes-section">
            <div class="notes-title">Notas del Pedido</div>
            <div class="notes-text">
                ' . nl2br(htmlspecialchars($pedido['notas'])) . '
            </div>
        </div>';
}

$html .= '
        <!-- PIE DE PÁGINA -->
        <div class="footer">
            <p class="footer-bold">¡Gracias por tu compra!</p>
            <p>Esta es una factura generada electrónicamente.</p>
            <p>Para cualquier consulta, contáctanos a: ventas@bikestore.com o al (555) 123-4567</p>
        </div>
    </div>
</body>
</html>';

// Cargar el HTML en Dompdf
$dompdf->loadHtml($html);

// Configurar el tamaño de página y la orientación
$dompdf->setPaper('letter', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Enviar el PDF al navegador
$filename = 'Factura_' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]); // false para mostrar en navegador, true para descargar
?>
