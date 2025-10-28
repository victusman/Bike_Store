<?php
/**
 * PLANTILLA DE FACTURA CON ESTRUCTURA DE LISTA_ORDERS.PHP
 * Diseño idéntico a lista_orders.php pero para facturas PDF
 * Creado: 28/10/2025
 */

// Esta variable debe venir del archivo que incluye esta plantilla
// $pedido, $items, $subtotalGeneral, $descuentoTotal, $totalFinal, $order_id

if (!isset($pedido) || !isset($items)) {
    die('Error: Datos de factura no proporcionados');
}

// Logo configurado con ruta simple para evitar problemas con extensión GD
// El archivo logo.png debe estar en la misma carpeta que esta plantilla

// Función para convertir número a texto (idéntica a lista_orders.php)
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
        return trim($resultado) . ' BOLIVIANOS';
    }
    
    // Decenas y unidades
    if ($numero >= 10) {
        $resultado .= $decenas[intval($numero / 10)] . ' ';
        $numero = $numero % 10;
    }
    
    if ($numero > 0) {
        $resultado .= $unidades[$numero] . ' ';
    }
    
    return trim($resultado) . ' BOLIVIANOS';
}

// Calcular valores para la plantilla (idéntico a lista_orders.php)
$descuentoPorcentaje = $subtotalGeneral > 0 ? ($descuentoTotal / $subtotalGeneral * 100) : 0;
$totalEntero = floor($totalFinal);
$centavos = round(($totalFinal - $totalEntero) * 100);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo - Pedido #<?php echo htmlspecialchars($order_id); ?></title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px;
            color: #333;
            font-size: 11pt;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header-left, .header-right {
            flex: 1;
        }
        .header-center {
            flex: 1;
            text-align: center;
        }
        .header-left { font-size: 9pt; }
        .header-right { text-align: right; font-size: 9pt; }
        .logo {
            max-width: 250px;
            max-height: 350px;
            height: auto;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
            margin: 5px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .company-tagline {
            font-size: 10pt;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .titulo-recibo {
            font-size: 24pt;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
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
        .tabla-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
        }
        .tabla-items thead {
            background-color: #f0f0f0;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }
        .tabla-items th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .tabla-items td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .tabla-items th:last-child,
        .tabla-items td:last-child {
            text-align: right;
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
        .nota-son {
            font-size: 9pt;
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
            font-size: 10pt;
        }
        .resumen td {
            padding: 5px;
            border: 1px solid #333;
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
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

    </style>
</head>
<body>

    
    <div class="header">
        <div class="header-left">
            <strong>N.º VNT-<?php echo date('Ymd', strtotime($pedido['order_date'])); ?>-<?php echo str_pad($order_id, 3, '0', STR_PAD_LEFT); ?></strong><br>
            <strong>Venta De Productos</strong><br>
            <?php echo date('d/m/Y', strtotime($pedido['order_date'])); ?>
        </div>
        <div class="header-center">
            <?php 
            $logoPath = __DIR__ . '/logoClien.png';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoMime = mime_content_type($logoPath);
                echo '<img src="data:' . $logoMime . ';base64,' . $logoData . '" alt="Bike Store" class="logo">';
            } else {
                echo '<div class="company-name">Bike Store</div>';
            }
            ?>
        </div>
        <div class="header-right">
         
        </div>
    </div>
    
    <div class="titulo-recibo">FACTURA</div>
    
    <div class="info-cliente">
        <div class="info-left">
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['first_name'] . ' ' . $pedido['last_name']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['phone'] ?? 'No especificado'); ?></p>
        </div>
        <div class="info-right">
            <p><strong>Tipo:</strong> Venta de Productos</p>
            <p><strong>Método Pago:</strong> <?php echo htmlspecialchars($pedido['metodo_pago'] ?? 'Efectivo'); ?></p>
            <p><strong>Hora:</strong> <?php 
                // Intentar extraer hora de order_date, si no tiene hora usar la actual
                $orderDateTime = strtotime($pedido['order_date']);
                $orderHour = date('H:i', $orderDateTime);
                // Si la hora es 00:00, usar la hora actual
                echo ($orderHour == '00:00') ? date('H:i') : $orderHour;
            ?></p>
        </div>
    </div>
    
    <table class="tabla-items">
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
        <tbody>
            <?php foreach ($items as $it): 
                $qty = (int)($it['quantity'] ?? 0);
                $price = (float)($it['price'] ?? 0);
                $discount = (isset($it['discount']) ? (float)$it['discount'] : 0.0);
                
                // Interpretar descuento: si está entre 0 y 1 es porcentaje, sino es monto fijo
                $isPercent = ($discount > 0 && $discount <= 1);
                if ($isPercent) {
                    $subtotal = max(0, $qty * $price * (1.0 - $discount));
                } else {
                    $subtotal = max(0, $qty * $price - $discount);
                }
            ?>
            <tr>
                <td>PROD-<?php echo str_pad($it['product_id'], 3, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($it['product_name'] ?? 'Producto ID ' . ($it['product_id'] ?? 'N/A')); ?></td>
                <td style="text-align: center;"><?php echo $qty; ?></td>
                <td style="text-align: right;"><?php echo number_format($price, 2); ?></td>
                <td style="text-align: right;"><?php echo $isPercent ? (rtrim(rtrim(number_format($discount*100, 2), '0'), '.') . '%') : number_format($discount, 2); ?></td>
                <td style="text-align: right;"><?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="clearfix">
        <div class="nota-son">
            <br>
            <br>
            <br>
            <?php 
                // Convertir número a texto
                echo 'Son: ' . numeroATexto($totalEntero) . ' ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100';
            ?>
        </div>
        <div class="resumen">
            <table>
                <tr>
                    <td>Descuento.</td>
                    <td><?php echo number_format($descuentoPorcentaje, 2); ?>%</td>
                </tr>
                <tr>
                    <td></td>
                    <td><?php echo number_format($descuentoTotal, 2); ?></td>
                </tr>
                <tr>
                    <td>Total Bs.</td>
                    <td><?php echo number_format($totalFinal, 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td>A cuenta.</td>
                    <td><?php echo number_format($totalFinal, 2); ?></td>
                </tr>
                <tr>
                    <td>Saldo.</td>
                    <td>0.00</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>