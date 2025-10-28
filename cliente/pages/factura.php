<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../libs/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================
// CONFIGURACIÓN DEL MODO DEMO PARA FACTURAS
// ============================================
// Debe coincidir con la configuración de confirmar_pedido.php
define('MODO_DEMO_FACTURAS', true);

// Obtener el order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('ID de pedido no válido');
}

$pedido = false;
$items = [];

// MODO DEMO - GENERAR FACTURA SIMULADA
if (MODO_DEMO_FACTURAS) {
    error_log("🎭 MODO DEMO FACTURAS - Generando factura simulada para Order ID: $order_id");
    
    // Intentar obtener datos del cliente actual
    $cliente_datos = null;
    if (isset($_SESSION['customer_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
            $stmt->execute(['id' => $_SESSION['customer_id']]);
            $cliente_datos = $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error obteniendo datos del cliente: " . $e->getMessage());
        }
    }
    
    // Datos del cliente simulados o reales
    $cliente_nombre = $cliente_datos ? ($cliente_datos['first_name'] . ' ' . $cliente_datos['last_name']) : 'Cliente Demo';
    $cliente_email = $cliente_datos ? $cliente_datos['email'] : 'cliente@demo.com';
    $cliente_telefono = $cliente_datos ? $cliente_datos['phone'] : '123456789';
    
    // Simular datos del pedido realistas
    $pedido = [
        'order_id' => $order_id,
        'customer_id' => $_SESSION['customer_id'] ?? 1,
        'first_name' => explode(' ', $cliente_nombre)[0],
        'last_name' => explode(' ', $cliente_nombre . ' ')[1] ?? '',
        'email' => $cliente_email,
        'phone' => $cliente_telefono,
        'order_date' => date('Y-m-d H:i:s'),
        'subtotal' => 1320.99,
        'descuento' => 0.00,
        'costo_envio' => 0.00,
        'total_amount' => 1320.99,
        'metodo_pago' => 'Pago Demo',
        'direccion_envio' => 'Dirección de envío simulada, Ciudad Demo, País',
        'notas' => 'Este es un pedido generado en modo demo para pruebas.',
        'estado' => 1
    ];
    
    // Simular items del pedido (productos típicos de bike store)
    $items = [
        [
            'product_id' => 1,
            'product_name' => 'Heller Shagamaw Frame - 2017',
            'quantity' => 1,
            'price' => 1320.99,
            'list_price' => 1320.99,
            'discount' => 0.00
        ]
    ];
    
    error_log("✅ FACTURA DEMO GENERADA - Cliente: $cliente_nombre, Total: $" . $pedido['total_amount']);
    
} else {
    // MODO PRODUCCIÓN - BUSCAR EN BASE DE DATOS
    try {
        // Obtener datos del pedido
        $stmt = $pdo->prepare("SELECT o.*, c.first_name, c.last_name, c.email, c.phone 
                               FROM orders o
                               INNER JOIN customer c ON o.customer_id = c.customer_id
                               WHERE o.order_id = :id");
        $stmt->execute(['id' => $order_id]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            die('Pedido no encontrado en la base de datos');
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
        
    } catch (Exception $e) {
        error_log("Error en modo producción: " . $e->getMessage());
        die('Error al obtener datos del pedido');
    }
}

// Verificar que tenemos datos válidos
if (!$pedido) {
    die('No se pudieron obtener los datos del pedido');
}

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

// Calcular totales para la factura
$subtotalGeneral = 0.0;
$descuentoTotal = 0.0;
foreach ($items as $item) {
    $qty = (int)($item['quantity'] ?? 0);
    $price = (float)($item['price'] ?? 0);
    $discount = (isset($item['discount']) ? (float)$item['discount'] : 0.0);
    
    $subtotalGeneral += $qty * $price;
    $descuentoTotal += $discount;
}

$totalFinal = max(0, $subtotalGeneral - $descuentoTotal);

// Generar el HTML de la factura (estilo mejorado similar a lista_orders.php)
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '</title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm;
        }
        
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
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        
        .header-left {
            flex: 1;
            font-size: 10pt;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
        }
        
        .header-right {
            flex: 1;
            text-align: right;
            font-size: 10pt;
        }
        
        .logo {
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
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
            font-size: 28pt;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 20px 0;
        }
        
        .invoice-number {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 10pt;
            color: #666;
        }
        
        .info-cliente {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .info-left, .info-right {
            flex: 1;
        }
        
        .info-left p, .info-right p {
            margin: 5px 0;
        }
        
        .info-left strong, .info-right strong {
            display: inline-block;
            width: 120px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .items-table thead {
            background-color: #2c3e50;
            color: white;
            border-top: 2px solid #2c3e50;
            border-bottom: 2px solid #2c3e50;
        }
        
        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table th:last-child,
        .items-table td:last-child,
        .items-table th:nth-child(4),
        .items-table td:nth-child(4),
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) {
            text-align: right;
        }
        
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            text-align: center;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .nota-son {
            font-size: 10pt;
            font-style: italic;
            margin: 10px 0;
            float: left;
            width: calc(100% - 270px);
            padding-top: 10px;
        }
        
        .resumen {
            float: right;
            width: 250px;
            margin-top: 10px;
        }
        
        .resumen table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
        }
        
        .resumen td {
            padding: 8px;
            border: 1px solid #2c3e50;
        }
        
        .resumen td:first-child {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 60%;
        }
        
        .resumen td:last-child {
            text-align: right;
        }
        
        .resumen .total-row {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }
        
        .demo-badge {
            background-color: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 9pt;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            font-size: 9pt;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ENCABEZADO -->
        <div class="header">
            <div class="header-left">
                <strong>N.º VNT-' . date('Ymd') . '-' . str_pad($order_id, 3, '0', STR_PAD_LEFT) . '</strong><br>
                <strong>Venta De Productos</strong><br>
                ' . date('d/m/Y', strtotime($pedido['order_date'])) . '
            </div>
            <div class="header-center">
                <div class="logo">
                    <span class="logo-icon">🚲</span> Bike Store
                </div>
                ' . (MODO_DEMO_FACTURAS ? '<div class="demo-badge">🎭 MODO DEMO</div>' : '') . '
            </div>
            <div class="header-right">
                <strong>Factura</strong><br>
                #' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '<br>
                ' . date('H:i', strtotime($pedido['order_date'])) . '
            </div>
        </div>
        
        <div class="invoice-title">FACTURA</div>
        
        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="info-cliente">
            <div class="info-left">
                <p><strong>Cliente:</strong> ' . htmlspecialchars($pedido['first_name'] . ' ' . $pedido['last_name']) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($pedido['email']) . '</p>
                <p><strong>Teléfono:</strong> ' . htmlspecialchars($pedido['phone'] ?? 'No especificado') . '</p>
            </div>
            <div class="info-right">
                <p><strong>Tipo:</strong> Venta de Productos</p>
                <p><strong>Método Pago:</strong> ' . htmlspecialchars($pedido['metodo_pago']) . '</p>
                <p><strong>Estado:</strong> ' . (MODO_DEMO_FACTURAS ? 'Procesado (Demo)' : 'Procesado') . '</p>
            </div>
        </div>
        
        <!-- TABLA DE PRODUCTOS -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Descuento</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>';

foreach ($items as $item) {
    $qty = (int)($item['quantity'] ?? 0);
    $price = (float)($item['price'] ?? 0);
    $discount = (isset($item['discount']) ? (float)$item['discount'] : 0.0);
    
    // Calcular subtotal
    $subtotal = max(0, $qty * $price - $discount);
    
    $html .= '
                <tr>
                    <td>PROD-' . str_pad($item['product_id'], 3, '0', STR_PAD_LEFT) . '</td>
                    <td>' . htmlspecialchars($item['product_name'] ?? 'Producto ID ' . ($item['product_id'] ?? 'N/A')) . '</td>
                    <td style="text-align: center;">' . $qty . '</td>
                    <td style="text-align: right;">$' . number_format($price, 2) . '</td>
                    <td style="text-align: right;">' . ($discount > 0 ? '$' . number_format($discount, 2) : '0.00') . '</td>
                    <td style="text-align: right;">$' . number_format($subtotal, 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
        
        <!-- TOTALES Y RESUMEN -->
        <div class="clearfix">
            <div class="nota-son">
                ' . (MODO_DEMO_FACTURAS ? '<br><strong>FACTURA DEMO</strong><br>Esta es una factura generada en modo de demostración.<br><br>' : '<br><br><br>') . '
                Son: ' . numeroATexto($totalFinal) . ' ' . str_pad(round(($totalFinal - floor($totalFinal)) * 100), 2, '0', STR_PAD_LEFT) . '/100
            </div>
            <div class="resumen">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td>$' . number_format($subtotalGeneral, 2) . '</td>
                    </tr>';

if ($descuentoTotal > 0) {
    $descuentoPorcentaje = $subtotalGeneral > 0 ? ($descuentoTotal / $subtotalGeneral * 100) : 0;
    $html .= '
                    <tr>
                        <td>Descuento</td>
                        <td>' . number_format($descuentoPorcentaje, 2) . '%</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>$' . number_format($descuentoTotal, 2) . '</td>
                    </tr>';
}

$html .= '
                    <tr>
                        <td>Envío</td>
                        <td>' . ($pedido['costo_envio'] == 0 ? 'GRATIS' : '$' . number_format($pedido['costo_envio'], 2)) . '</td>
                    </tr>
                    <tr>
                        <td>Total Bs.</td>
                        <td>$' . number_format($totalFinal, 2) . '</td>
                    </tr>
                    <tr class="total-row">
                        <td>A cuenta</td>
                        <td>$' . number_format($totalFinal, 2) . '</td>
                    </tr>
                    <tr>
                        <td>Saldo</td>
                        <td>$0.00</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- PIE DE PÁGINA -->
        <div class="footer">
            <p><strong>🚲 Bike Store - Tu tienda de confianza</strong></p>
            <p>Av. Principal #123, Ciudad | Tel: (555) 123-4567 | Email: ventas@bikestore.com</p>
            ' . (MODO_DEMO_FACTURAS ? '<p style="color: #ff6b6b;"><strong>🎭 Esta es una factura generada en modo demo para pruebas</strong></p>' : '') . '
            <p>¡Gracias por tu compra! - Factura generada el ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

/**
 * Convertir número a texto (función reutilizada de lista_orders.php)
 */
function numeroATexto($numero)
{
    $numero = intval($numero);
    
    $unidades = array('', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE');
    $decenas = array('', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA');
    $especiales = array(11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 
                       16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE');
    $centenas = array('', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS');
    
    if ($numero == 0) return 'CERO';
    if ($numero == 100) return 'CIEN';
    
    $resultado = '';
    
    // Miles
    if ($numero >= 1000) {
        $miles = intval($numero / 1000);
        if ($miles == 1) {
            $resultado .= 'MIL ';
        } else {
            $resultado .= $unidades[$miles] . ' MIL ';
        }
        $numero = $numero % 1000;
    }
    
    // Centenas
    if ($numero >= 100) {
        $resultado .= $centenas[intval($numero / 100)] . ' ';
        $numero = $numero % 100;
    }
    
    // Números especiales del 11 al 19
    if ($numero >= 11 && $numero <= 19) {
        $resultado .= $especiales[$numero] . ' ';
        return trim($resultado) . ' DÓLARES';
    }
    
    // Decenas y unidades
    if ($numero >= 10) {
        $resultado .= $decenas[intval($numero / 10)] . ' ';
        $numero = $numero % 10;
    }
    
    if ($numero > 0) {
        $resultado .= $unidades[$numero] . ' ';
    }
    
    return trim($resultado) . ' DÓLARES';
}

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
