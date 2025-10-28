<?php
/**
 * API AJAX PARA ENVÍO DE FACTURA POR EMAIL
 * Endpoint que recibe llamadas AJAX para enviar facturas por email
 */

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../libs/autoload.inc.php';
require_once __DIR__ . '/../../libs/EmailService.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Función para responder JSON
function jsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido');
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(false, 'Datos JSON inválidos');
}

$order_id = isset($input['order_id']) ? intval($input['order_id']) : 0;

if ($order_id === 0) {
    jsonResponse(false, 'ID de pedido no válido');
}

try {
    // Verificar sesión del cliente
    if (!isset($_SESSION['customer_id'])) {
        jsonResponse(false, 'Cliente no autenticado');
    }
    
    // Obtener datos del cliente
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM customer WHERE customer_id = :id");
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $clienteData = $stmt->fetch();
    
    if (!$clienteData || !filter_var($clienteData['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Cliente sin email válido');
    }
    
    $clienteEmail = $clienteData['email'];
    $clienteNombre = trim($clienteData['first_name'] . ' ' . $clienteData['last_name']);
    
    // Regenerar PDF para envío (misma lógica que factura.php)
    
    // DATOS DEL PEDIDO (DEMO)
    $pedido = [
        'order_id' => $order_id,
        'customer_id' => $_SESSION['customer_id'],
        'first_name' => $clienteData['first_name'],
        'last_name' => $clienteData['last_name'],
        'email' => $clienteEmail,
        'phone' => '123456',
        'order_date' => date('Y-m-d H:i:s'),
        'order_status' => 4,
        'metodo_pago' => 'Tarjeta de Crédito'
    ];
    
    // ITEMS DEL PEDIDO (DEMO)
    $items = [
        [
            'product_id' => 1, 
            'product_name' => 'Heller Shagamaw Frame - 2017', 
            'quantity' => 1, 
            'price' => 1320.99, 
            'discount' => 0
        ]
    ];
    
    $subtotalGeneral = 1320.99;
    $descuentoTotal = 0.0;
    $totalFinal = 1320.99;
    
    // Generar HTML del PDF
    ob_start();
    include __DIR__ . '/plantilla_factura_detallada.php';
    $html = ob_get_clean();
    
    // Generar PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();
    
    $pdfContent = $dompdf->output();
    
    // Enviar email
    $emailService = new EmailService();
    
    $datosOrden = [
        'total' => $totalFinal,
        'fecha' => $pedido['order_date'],
        'cantidad_productos' => count($items)
    ];
    
    $resultado = $emailService->enviarFactura(
        $clienteEmail,
        $clienteNombre,
        $order_id,
        $pdfContent,
        $datosOrden
    );
    
    if ($resultado) {
        error_log("✅ Factura enviada por email via AJAX a: $clienteEmail (Pedido #$order_id)");
        jsonResponse(true, "Factura enviada exitosamente a: $clienteEmail", [
            'email' => $clienteEmail,
            'order_id' => $order_id
        ]);
    } else {
        $errores = $emailService->getErrores();
        $errorMsg = !empty($errores) ? implode(', ', $errores) : 'Error desconocido';
        error_log("❌ Error enviando factura por email via AJAX a: $clienteEmail (Pedido #$order_id) - $errorMsg");
        jsonResponse(false, "Error al enviar email: $errorMsg");
    }
    
} catch (Exception $e) {
    error_log("❌ Excepción en API de email de factura: " . $e->getMessage() . " (Pedido #$order_id)");
    jsonResponse(false, "Error interno: " . $e->getMessage());
}
?>