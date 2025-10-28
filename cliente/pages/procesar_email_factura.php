<?php
/**
 * PROCESADOR DE EMAIL DE FACTURA
 * Maneja el envío de email sin interferir con el PDF
 */

session_start();

// Headers para imagen transparente
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Imagen PNG transparente de 1x1 pixel
$transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
echo $transparentPng;

// Obtener order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    error_log("❌ Procesador email: Order ID inválido");
    exit;
}

// Verificar datos en sesión
$sessionKey = 'factura_email_' . $order_id;
if (!isset($_SESSION[$sessionKey])) {
    error_log("⚠️ Procesador email: No hay datos en sesión para pedido #$order_id");
    exit;
}

$emailData = $_SESSION[$sessionKey];

// Verificar expiración (máximo 10 minutos)
if ((time() - $emailData['timestamp']) > 600) {
    error_log("⚠️ Procesador email: Datos expirados para pedido #$order_id");
    unset($_SESSION[$sessionKey]);
    exit;
}

// Verificar email válido
if (empty($emailData['cliente_email']) || !filter_var($emailData['cliente_email'], FILTER_VALIDATE_EMAIL)) {
    error_log("⚠️ Procesador email: Email inválido para pedido #$order_id - " . $emailData['cliente_email']);
    unset($_SESSION[$sessionKey]);
    exit;
}

try {
    require_once __DIR__ . '/../../libs/EmailService.php';
    
    $emailService = new EmailService();
    
    // Decodificar PDF
    $pdfContent = base64_decode($emailData['pdf_content']);
    
    if ($pdfContent === false) {
        error_log("❌ Procesador email: Error decodificando PDF para pedido #$order_id");
        unset($_SESSION[$sessionKey]);
        exit;
    }
    
    // Preparar datos del pedido
    $datosOrden = [
        'total' => $emailData['total'],
        'fecha' => $emailData['fecha'],
        'cantidad_productos' => $emailData['cantidad_productos']
    ];
    
    // Enviar email
    $resultado = $emailService->enviarFactura(
        $emailData['cliente_email'],
        $emailData['cliente_nombre'],
        $emailData['order_id'],
        $pdfContent,
        $datosOrden
    );
    
    if ($resultado) {
        error_log("✅ Factura enviada por email a: " . $emailData['cliente_email'] . " (Pedido #$order_id)");
    } else {
        error_log("❌ Error enviando factura por email a: " . $emailData['cliente_email'] . " (Pedido #$order_id)");
        $errores = $emailService->getErrores();
        foreach ($errores as $error) {
            error_log("Email Error: $error");
        }
    }
    
} catch (Exception $e) {
    error_log("❌ Excepción en procesador de email: " . $e->getMessage() . " (Pedido #$order_id)");
} finally {
    // Limpiar datos de sesión
    unset($_SESSION[$sessionKey]);
}
?>