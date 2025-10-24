<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

// Configurar zona horaria de Bolivia
date_default_timezone_set('America/La_Paz');

// Verificar si se solicita generar PDF
$generarPDF = isset($_GET['pdf']) && $_GET['pdf'] == '1';
$id = $_GET['id'] ?? null;

if (!$generarPDF || !$id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del pedido
$order = false;
$customerTables = ['customers', 'customer'];
foreach ($customerTables as $ct) {
    $sql = "SELECT o.*, c.first_name, c.last_name, c.phone, c.street, c.city, c.state 
            FROM orders o 
            LEFT JOIN {$ct} c ON o.customer_id = c.customer_id 
            WHERE o.order_id = :id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$order) {
    header('Location: index.php');
    exit;
}

// Obtener items del pedido
$items = [];
$productTables = ['products', 'productos', 'producto', 'product'];
$itemsFound = false;
foreach ($productTables as $pt) {
    $sql = "SELECT oi.*, p.product_name 
            FROM order_items oi 
            JOIN {$pt} p ON oi.product_id = p.product_id 
            WHERE oi.order_id = :id";
    try {
        $itStmt = $pdo->prepare($sql);
        $itStmt->execute(['id' => $id]);
        $items = $itStmt->fetchAll();
        $itemsFound = true;
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$itemsFound) {
    $itStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :id');
    $itStmt->execute(['id' => $id]);
    $items = $itStmt->fetchAll();
}

// Calcular totales
$subtotalGeneral = 0.0;
$descuentoTotal = 0.0;
foreach ($items as $it) {
    $qty = (int)($it['quantity'] ?? 0);
    $price = (float)($it['price'] ?? 0);
    $discount = (isset($it['discount']) ? (float)$it['discount'] : 0.0);
    
    $isPercent = ($discount > 0 && $discount <= 1);
    if ($isPercent) {
        $descuentoItem = $qty * $price * $discount;
    } else {
        $descuentoItem = $discount;
    }
    
    $subtotalGeneral += $qty * $price;
    $descuentoTotal += $descuentoItem;
}

$totalFinal = $subtotalGeneral - $descuentoTotal;

// Calcular descuento en porcentaje si es aplicable
$descuentoPorcentaje = $subtotalGeneral > 0 ? ($descuentoTotal / $subtotalGeneral * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo - Pedido #<?php echo htmlspecialchars($order['order_id']); ?></title>
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
            max-width: 150px;
            height: auto;
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
        .btn-print { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 12px 24px; 
            background: #4CAF50; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .btn-print:hover { background: #45a049; }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    
    <div class="header">
        <div class="header-left">
            <strong>N.º VNT-<?php echo date('Ymd'); ?>-<?php echo str_pad($order['order_id'], 3, '0', STR_PAD_LEFT); ?></strong><br>
            <strong>Venta De Productos</strong><br>
            <?php echo date('d/m/Y'); ?>
        </div>
        <div class="header-center">
            <img src="logo.png" alt="Bike Store" class="logo">
        </div>
        <div class="header-right">
         
        </div>
    </div>
    
    <div class="titulo-recibo">FACTURA</div>
    
    <div class="info-cliente">
        <div class="info-left">
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'No especificado'); ?></p>
        </div>
        <div class="info-right">
            <p><strong>Tipo:</strong> Venta de Productos</p>
            <p><strong>Método Pago:</strong> Efectivo</p>
            <p><strong>Hora:</strong> <?php 
                // Intentar extraer hora de order_date, si no tiene hora usar la actual
                $orderDateTime = strtotime($order['order_date']);
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
                $totalEntero = floor($totalFinal);
                $centavos = round(($totalFinal - $totalEntero) * 100);
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

<?php
/**
 * Convertir número a texto
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
?>
