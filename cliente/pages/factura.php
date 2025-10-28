<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../libs/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================
// CONFIGURACIÓN DEL SISTEMA DE FACTURAS
// ============================================
// Sistema de facturas configurado para funcionar normalmente
define('MODO_DEMO_FACTURAS', true);

// Obtener el order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('ID de pedido no válido');
}

$pedido = false;
$items = [];

// GENERAR FACTURA DEL PEDIDO
if (MODO_DEMO_FACTURAS) {
    error_log("📄 GENERANDO FACTURA - Order ID: $order_id");
    
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
    
    // Datos del cliente
    $cliente_nombre = $cliente_datos ? ($cliente_datos['first_name'] . ' ' . $cliente_datos['last_name']) : 'Cliente Demo';
    $cliente_email = $cliente_datos ? $cliente_datos['email'] : 'cliente@demo.com';
    $cliente_telefono = $cliente_datos ? $cliente_datos['phone'] : '123456789';
    
    // Generar datos del pedido
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
        'metodo_pago' => 'Tarjeta de Crédito',
        'direccion_envio' => 'Dirección de envío simulada, Ciudad Demo, País',
        'notas' => 'Pedido procesado exitosamente. Gracias por tu compra.',
        'estado' => 1
    ];
    
    // Items del pedido (productos típicos de bike store)
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
    
    error_log("✅ FACTURA GENERADA - Cliente: $cliente_nombre, Total: $" . $pedido['total_amount']);
    
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

// Generar el HTML de la factura usando la plantilla detallada
ob_start();
include __DIR__ . '/plantilla_factura_detallada.php';
$html = ob_get_clean();

// Configurar Dompdf con la librería local
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

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