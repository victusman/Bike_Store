<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></title>
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
            font-size: 11pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .notes-text {
            font-size: 10pt;
            color: #555;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
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
                    <div class="invoice-number">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></div>
                    <div class="invoice-date">
                        Fecha: <?php echo date('d/m/Y', strtotime($pedido['order_date'])); ?><br>
                        Hora: <?php echo date('H:i:s', strtotime($pedido['order_date'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cliente y Dirección -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-title">Cliente</div>
                        <div class="info-text">
                            <strong><?php echo htmlspecialchars($pedido['first_name'] . ' ' . $pedido['last_name']); ?></strong><br>
                            Email: <?php echo htmlspecialchars($pedido['email']); ?><br>
                            Teléfono: <?php echo htmlspecialchars($pedido['phone'] ?? 'No proporcionado'); ?>
                        </div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-title">Dirección de Envío</div>
                        <div class="info-text">
                            <?php echo nl2br(htmlspecialchars($pedido['direccion_envio'] ?? 'No especificada')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Productos -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Producto</th>
                    <th class="text-center" style="width: 10%;">Cantidad</th>
                    <th class="text-right" style="width: 20%;">Precio Unit.</th>
                    <th class="text-right" style="width: 20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $num = 1;
                foreach ($items as $item): 
                    $subtotal = $item['quantity'] * $item['list_price'];
                ?>
                <tr>
                    <td><?php echo $num++; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-right">$<?php echo number_format($item['list_price'], 2); ?></td>
                    <td class="text-right">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Totales -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="value">$<?php echo number_format($pedido['subtotal'], 2); ?></td>
                </tr>
                <?php if ($pedido['descuento'] > 0): ?>
                <tr>
                    <td class="label">Descuento:</td>
                    <td class="value discount">-$<?php echo number_format($pedido['descuento'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Costo de Envío:</td>
                    <td class="value <?php echo $pedido['costo_envio'] == 0 ? 'discount' : ''; ?>">
                        <?php echo $pedido['costo_envio'] == 0 ? 'GRATIS' : '$' . number_format($pedido['costo_envio'], 2); ?>
                    </td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td>$<?php echo number_format($pedido['total_amount'], 2); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Información de Pago -->
        <div class="payment-info">
            <div class="payment-info-title">Método de Pago</div>
            <div class="payment-info-text">
                <?php echo htmlspecialchars($pedido['metodo_pago'] ?? 'No especificado'); ?>
            </div>
        </div>
        
        <!-- Notas (si existen) -->
        <?php if (!empty($pedido['notas'])): ?>
        <div class="notes-section">
            <div class="notes-title">Notas del Pedido</div>
            <div class="notes-text">
                <?php echo nl2br(htmlspecialchars($pedido['notas'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p class="footer-bold">¡Gracias por tu compra!</p>
            <p style="margin-top: 10px;">
                Este es un documento electrónico y no requiere firma.<br>
                Para cualquier consulta, contáctanos en ventas@bikestore.com o al (555) 123-4567
            </p>
        </div>
    </div>
</body>
</html>
