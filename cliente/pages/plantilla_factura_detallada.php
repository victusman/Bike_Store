<?php
/**
 * PLANTILLA DE FACTURA DETALLADA PARA DOMPDF
 * Diseño profesional inspirado en lista_orders.php
 * Creado: 28/10/2025
 */

// Esta variable debe venir del archivo que incluye esta plantilla
// $pedido, $items, $subtotalGeneral, $descuentoTotal, $totalFinal, $order_id

if (!isset($pedido) || !isset($items)) {
    die('Error: Datos de factura no proporcionados');
}

// Función para convertir número a texto (reutilizada)
function numeroATextoPDF($numero)
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

// Calcular valores para la plantilla
$descuentoPorcentaje = $subtotalGeneral > 0 ? ($descuentoTotal / $subtotalGeneral * 100) : 0;
$totalEntero = floor($totalFinal);
$centavos = round(($totalFinal - $totalEntero) * 100);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> - Bike Store</title>
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
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        
        .container {
            width: 100%;
            padding: 15px;
        }
        
        /* HEADER STYLE - Similar a lista_orders.php */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }
        
        .header-left, .header-right {
            flex: 1;
            font-size: 10pt;
        }
        
        .header-center {
            flex: 1;
            text-align: center;
        }
        
        .header-right {
            text-align: right;
        }
        
        .logo {
            font-size: 24pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .logo-icon {
            color: #3498db;
            font-size: 28pt;
        }
        
        .company-info {
            font-size: 9pt;
            color: #666;
            line-height: 1.3;
        }
        
        .invoice-title {
            font-size: 32pt;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
        }
        
        .demo-badge {
            background-color: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 9pt;
            font-weight: bold;
            display: inline-block;
            margin-top: 5px;
        }
        
        /* INFORMACIÓN DEL CLIENTE */
        .info-cliente {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .info-left, .info-right {
            flex: 1;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .info-right {
            margin-left: 15px;
            border-left-color: #27ae60;
        }
        
        .info-cliente p {
            margin: 6px 0;
        }
        
        .info-cliente strong {
            display: inline-block;
            width: 100px;
            color: #2c3e50;
        }
        
        .info-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        
        /* TABLA DE PRODUCTOS - Estilo mejorado */
        .tabla-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .tabla-items thead {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .tabla-items th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .tabla-items td {
            padding: 10px 8px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .tabla-items tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .tabla-items tbody tr:hover {
            background-color: #e8f4f8;
        }
        
        .tabla-items th:nth-child(3),
        .tabla-items td:nth-child(3) {
            text-align: center;
        }
        
        .tabla-items th:nth-child(4),
        .tabla-items th:nth-child(5),
        .tabla-items th:nth-child(6),
        .tabla-items td:nth-child(4),
        .tabla-items td:nth-child(5),
        .tabla-items td:nth-child(6) {
            text-align: right;
        }
        
        .product-code {
            font-family: 'Courier New', monospace;
            background-color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9pt;
        }
        
        /* SECCIÓN DE TOTALES */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .nota-son {
            float: left;
            width: calc(100% - 280px);
            font-size: 10pt;
            padding-top: 15px;
            border-top: 1px solid #bdc3c7;
            margin-top: 15px;
        }
        
        .nota-son-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .nota-son-text {
            font-style: italic;
            color: #7f8c8d;
        }
        
        .demo-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            color: #856404;
            font-weight: bold;
        }
        
        .resumen {
            float: right;
            width: 260px;
            margin-top: 15px;
        }
        
        .resumen table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .resumen td {
            padding: 10px 12px;
            border: 1px solid #bdc3c7;
        }
        
        .resumen td:first-child {
            font-weight: bold;
            background: linear-gradient(135deg, #ecf0f1 0%, #d5dbdb 100%);
            color: #2c3e50;
            width: 60%;
        }
        
        .resumen td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .resumen .total-row {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            font-size: 12pt;
        }
        
        .resumen .discount-row {
            background-color: #d5f4e6;
            color: #27ae60;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #bdc3c7;
            text-align: center;
            font-size: 9pt;
            color: #7f8c8d;
        }
        
        .footer-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .footer-info {
            margin: 5px 0;
        }
        
        .footer-demo {
            background-color: #ffebee;
            border: 1px solid #f8bbd9;
            border-radius: 5px;
            padding: 8px;
            margin-top: 10px;
            color: #c62828;
            font-weight: bold;
        }
        
        /* WATERMARK PARA DEMO */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(231, 76, 60, 0.1);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <?php if (defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS): ?>
    <div class="watermark">DEMO</div>
    <?php endif; ?>
    
    <div class="container">
        <!-- ENCABEZADO -->
        <div class="header">
            <div class="header-left">
                <strong>N.º VNT-<?php echo date('Ymd', strtotime($pedido['order_date'])); ?>-<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></strong><br>
                <strong>Venta De Productos</strong><br>
                <?php echo date('d/m/Y', strtotime($pedido['order_date'])); ?>
            </div>
            <div class="header-center">
                <div class="logo">
                    <span class="logo-icon">🚲</span> Bike Store
                </div>
                <?php if (defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS): ?>
                <div class="demo-badge">🎭 MODO DEMO</div>
                <?php endif; ?>
                <div class="company-info">
                    Av. Principal #123, Ciudad<br>
                    Tel: (555) 123-4567<br>
                    Email: ventas@bikestore.com
                </div>
            </div>
            <div class="header-right">
                <strong>Factura</strong><br>
                #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?><br>
                <?php echo date('H:i', strtotime($pedido['order_date'])); ?><br>
                <small>Estado: <?php echo defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS ? 'Demo' : 'Procesado'; ?></small>
            </div>
        </div>
        
        <div class="invoice-title">FACTURA</div>
        
        <!-- INFORMACIÓN DEL CLIENTE -->
        <div class="info-cliente">
            <div class="info-left">
                <div class="info-title">📋 Datos del Cliente</div>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['first_name'] . ' ' . $pedido['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['email']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['phone'] ?? 'No especificado'); ?></p>
                <p><strong>ID Cliente:</strong> #<?php echo str_pad($pedido['customer_id'], 4, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="info-right">
                <div class="info-title">🚚 Información de Entrega</div>
                <p><strong>Tipo:</strong> Venta de Productos</p>
                <p><strong>Método Pago:</strong> <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion_envio'] ?? 'Retiro en tienda'); ?></p>
                <p><strong>Estado:</strong> <span style="color: #27ae60;">✓ Confirmado</span></p>
            </div>
        </div>
        
        <!-- TABLA DE PRODUCTOS -->
        <table class="tabla-items">
            <thead>
                <tr>
                    <th style="width: 15%;">Código</th>
                    <th style="width: 40%;">Producto</th>
                    <th style="width: 10%;">Cantidad</th>
                    <th style="width: 15%;">Precio</th>
                    <th style="width: 10%;">Descuento</th>
                    <th style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): 
                    $qty = (int)($item['quantity'] ?? 0);
                    $price = (float)($item['price'] ?? 0);
                    $discount = (isset($item['discount']) ? (float)$item['discount'] : 0.0);
                    
                    // Interpretar descuento: si está entre 0 y 1 es porcentaje, sino es monto fijo
                    $isPercent = ($discount > 0 && $discount <= 1);
                    if ($isPercent) {
                        $subtotal = max(0, $qty * $price * (1.0 - $discount));
                        $discountDisplay = rtrim(rtrim(number_format($discount*100, 2), '0'), '.') . '%';
                    } else {
                        $subtotal = max(0, $qty * $price - $discount);
                        $discountDisplay = $discount > 0 ? '$' . number_format($discount, 2) : '0.00';
                    }
                ?>
                <tr>
                    <td><span class="product-code">PROD-<?php echo str_pad($item['product_id'], 3, '0', STR_PAD_LEFT); ?></span></td>
                    <td>
                        <strong><?php echo htmlspecialchars($item['product_name'] ?? 'Producto ID ' . ($item['product_id'] ?? 'N/A')); ?></strong>
                        <?php if (defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS): ?>
                        <br><small style="color: #7f8c8d;">Producto de demostración</small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><strong><?php echo $qty; ?></strong></td>
                    <td style="text-align: right;">$<?php echo number_format($price, 2); ?></td>
                    <td style="text-align: right;"><?php echo $discountDisplay; ?></td>
                    <td style="text-align: right;"><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- TOTALES Y RESUMEN -->
        <div class="clearfix">
            <div class="nota-son">
                <?php if (defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS): ?>
                <div class="demo-note">
                    🎭 <strong>FACTURA DE DEMOSTRACIÓN</strong><br>
                    Esta es una factura generada en modo demo para pruebas del sistema.
                </div>
                <?php endif; ?>
                
                <div class="nota-son-title">Monto en Letras:</div>
                <div class="nota-son-text">
                    Son: <?php echo numeroATextoPDF($totalEntero); ?> <?php echo str_pad($centavos, 2, '0', STR_PAD_LEFT); ?>/100
                </div>
                
                <?php if (!empty($pedido['notas'])): ?>
                <div style="margin-top: 15px;">
                    <div class="nota-son-title">Notas del Pedido:</div>
                    <div class="nota-son-text">
                        <?php echo nl2br(htmlspecialchars($pedido['notas'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="resumen">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td>$<?php echo number_format($subtotalGeneral, 2); ?></td>
                    </tr>
                    <?php if ($descuentoTotal > 0): ?>
                    <tr class="discount-row">
                        <td>Descuento</td>
                        <td><?php echo number_format($descuentoPorcentaje, 1); ?>%</td>
                    </tr>
                    <tr class="discount-row">
                        <td></td>
                        <td>-$<?php echo number_format($descuentoTotal, 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Envío</td>
                        <td><?php echo ($pedido['costo_envio'] == 0) ? 'GRATIS' : '$' . number_format($pedido['costo_envio'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>$<?php echo number_format($totalFinal, 2); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td>A Cuenta</td>
                        <td>$<?php echo number_format($totalFinal, 2); ?></td>
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
            <div class="footer-title">🚲 Bike Store - Tu tienda de confianza</div>
            <div class="footer-info">Av. Principal #123, Ciudad | Tel: (555) 123-4567 | Email: ventas@bikestore.com</div>
            <div class="footer-info">Web: www.bikestore.com | Síguenos en redes sociales</div>
            
            <?php if (defined('MODO_DEMO_FACTURAS') && MODO_DEMO_FACTURAS): ?>
            <div class="footer-demo">
                🎭 Esta es una factura generada en modo demostración para pruebas del sistema
            </div>
            <?php endif; ?>
            
            <div class="footer-info" style="margin-top: 10px;">
                <strong>¡Gracias por tu compra!</strong> - Factura generada el <?php echo date('d/m/Y H:i:s'); ?>
            </div>
            <div class="footer-info">
                Esta factura es válida como comprobante de compra | Consultas: ventas@bikestore.com
            </div>
        </div>
    </div>
</body>
</html>