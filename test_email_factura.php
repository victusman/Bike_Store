<?php
/**
 * PRUEBA DE ENVÍO DE FACTURA POR EMAIL
 * Archivo de prueba para verificar el funcionamiento del sistema
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/libs/EmailService.php';

echo "<h2>🧪 Prueba de Envío de Factura por Email</h2>";

try {
    // Simular datos de prueba
    $order_id = 1761657009;
    $clienteEmail = "test@example.com"; // Cambia por un email real para pruebas
    $clienteNombre = "Cliente Prueba";
    
    // Crear un PDF de prueba simple
    $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Factura de Prueba) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000207 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n296\n%%EOF";
    
    echo "<p>🔍 <strong>Configuración:</strong></p>";
    echo "<ul>";
    echo "<li>Order ID: $order_id</li>";
    echo "<li>Email destino: $clienteEmail</li>";
    echo "<li>Cliente: $clienteNombre</li>";
    echo "<li>Tamaño PDF: " . strlen($pdfContent) . " bytes</li>";
    echo "</ul>";
    
    // Verificar configuración de email
    require_once __DIR__ . '/config/email_config.php';
    
    echo "<p>📧 <strong>Configuración de Email:</strong></p>";
    echo "<ul>";
    echo "<li>MAIL_HOST: " . MAIL_HOST . "</li>";
    echo "<li>MAIL_PORT: " . MAIL_PORT . "</li>";
    echo "<li>MAIL_USERNAME: " . MAIL_USERNAME . "</li>";
    echo "<li>MAIL_FROM_ADDRESS: " . MAIL_FROM_ADDRESS . "</li>";
    echo "<li>MAIL_FROM_NAME: " . MAIL_FROM_NAME . "</li>";
    echo "</ul>";
    
    if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
        echo "<p style='color: red;'>❌ <strong>Error:</strong> Configuración de email incompleta</p>";
        exit;
    }
    
    // Crear instancia del servicio de email
    $emailService = new EmailService();
    
    // Datos del pedido
    $datosOrden = [
        'total' => 1320.99,
        'fecha' => date('Y-m-d H:i:s'),
        'cantidad_productos' => 1
    ];
    
    echo "<p>📤 <strong>Enviando email...</strong></p>";
    
    // Enviar email
    $resultado = $emailService->enviarFactura(
        $clienteEmail,
        $clienteNombre,
        $order_id,
        $pdfContent,
        $datosOrden
    );
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ <strong>¡Email enviado exitosamente!</strong></p>";
        echo "<p>El email con la factura ha sido enviado a: <strong>$clienteEmail</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Error al enviar email</strong></p>";
        
        $errores = $emailService->getErrores();
        if (!empty($errores)) {
            echo "<p><strong>Errores encontrados:</strong></p>";
            echo "<ul>";
            foreach ($errores as $error) {
                echo "<li style='color: red;'>$error</li>";
            }
            echo "</ul>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Excepción:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong><br><pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<hr>";
echo "<p><a href='cliente/pages/factura.php?order_id=1761657009'>🔗 Probar factura real</a></p>";
echo "<p><a href='test_email.php'>🔗 Probar email básico</a></p>";
?>