<?php
/**
 * ENVÍO DE EMAIL DE FACTURA EN SEGUNDO PLANO
 * Este archivo se ejecuta de forma asíncrona para enviar el email
 * sin interferir con la visualización del PDF
 */

session_start();
require_once __DIR__ . '/../../db.php';

// Configurar headers para imagen invisible (evita mostrar contenido)
header('Content-Type: image/gif');
header('Content-Length: 43');

// Imagen GIF transparente de 1x1 pixel
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

// Obtener el order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    error_log("❌ Email factura: Order ID no válido");
    exit;
}

// Verificar si tenemos datos del email en la sesión
$emailDataKey = 'email_data_' . $order_id;
if (!isset($_SESSION[$emailDataKey])) {
    error_log("❌ Email factura: No hay datos en sesión para pedido #$order_id");
    exit;
}

$emailData = $_SESSION[$emailDataKey];

// Verificar que el archivo temporal existe
if (!file_exists($emailData['pdf_path'])) {
    error_log("❌ Email factura: Archivo PDF temporal no encontrado para pedido #$order_id");
    // Limpiar datos de sesión
    unset($_SESSION[$emailDataKey]);
    exit;
}

// Verificar que los datos no sean muy antiguos (máximo 5 minutos)
if ((time() - $emailData['timestamp']) > 300) {
    error_log("⚠️ Email factura: Datos expirados para pedido #$order_id");
    // Limpiar archivo temporal y datos de sesión
    unlink($emailData['pdf_path']);
    unset($_SESSION[$emailDataKey]);
    exit;
}

try {
    // Obtener datos del cliente
    if (!isset($_SESSION['customer_id'])) {
        error_log("⚠️ Email factura: Cliente no logueado para pedido #$order_id");
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM customer WHERE customer_id = :id");
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $clienteData = $stmt->fetch();
    
    if (!$clienteData || !filter_var($clienteData['email'], FILTER_VALIDATE_EMAIL)) {
        error_log("⚠️ Email factura: Cliente sin email válido para pedido #$order_id");
        exit;
    }
    
    $clienteEmail = $clienteData['email'];
    $clienteNombre = trim($clienteData['first_name'] . ' ' . $clienteData['last_name']);
    
    // Leer el contenido del PDF
    $pdfContent = file_get_contents($emailData['pdf_path']);
    
    if ($pdfContent === false) {
        error_log("❌ Email factura: No se pudo leer el PDF para pedido #$order_id");
        exit;
    }
    
    // Enviar email
    require_once __DIR__ . '/../../libs/EmailService.php';
    
    $emailService = new EmailService();
    
    // Preparar datos del pedido
    $datosOrden = [
        'total' => $emailData['total'],
        'fecha' => $emailData['fecha'],
        'cantidad_productos' => $emailData['cantidad_productos']
    ];
    
    // Enviar email con factura adjunta
    $emailEnviado = $emailService->enviarFactura(
        $clienteEmail,
        $clienteNombre,
        $order_id,
        $pdfContent,
        $datosOrden
    );
    
    if ($emailEnviado) {
        error_log("✅ Factura enviada por email a: $clienteEmail (Pedido #$order_id) [Segundo plano]");
    } else {
        error_log("❌ Error al enviar factura por email a: $clienteEmail (Pedido #$order_id) [Segundo plano]");
        $errores = $emailService->getErrores();
        foreach ($errores as $error) {
            error_log("Email Error: $error");
        }
    }
    
} catch (Exception $e) {
    error_log("❌ Excepción en envío de email de factura: " . $e->getMessage() . " (Pedido #$order_id)");
} finally {
    // Limpiar archivo temporal y datos de sesión
    if (file_exists($emailData['pdf_path'])) {
        unlink($emailData['pdf_path']);
    }
    unset($_SESSION[$emailDataKey]);
}
?>