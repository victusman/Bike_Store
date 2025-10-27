<?php
/**
 * Test de Envío de Email
 * Verifica la configuración de PHPMailer y envío de correos
 */

require_once __DIR__ . '/libs/EmailService.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/libs/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurar errores visibles
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 Test de Envío de Email - Bike Store</h1>";
echo "<hr>";

// 1. Verificar configuración .env
echo "<h3>1. Verificación de Configuración (.env)</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>";

$config_vars = [
    'MAIL_HOST' => MAIL_HOST,
    'MAIL_PORT' => MAIL_PORT,
    'MAIL_USERNAME' => MAIL_USERNAME,
    'MAIL_PASSWORD' => '***' . substr(MAIL_PASSWORD, -4), // Ocultar password
    'MAIL_FROM_ADDRESS' => MAIL_FROM_ADDRESS,
    'MAIL_FROM_NAME' => MAIL_FROM_NAME,
    'APP_URL' => APP_URL
];

foreach ($config_vars as $var => $value) {
    $status = !empty($value) ? '✅' : '❌';
    echo "<tr><td><strong>$var</strong></td><td>$value</td><td>$status</td></tr>";
}
echo "</table>";

// 2. Verificar PHPMailer instalado
echo "<h3>2. Verificación de PHPMailer</h3>";
$phpmailer_path = __DIR__ . '/gmailPHP/PHPMailer-master/src/PHPMailer.php';
if (file_exists($phpmailer_path)) {
    echo "✅ PHPMailer encontrado en: <code>$phpmailer_path</code><br>";
} else {
    echo "❌ PHPMailer NO encontrado. Ruta esperada: <code>$phpmailer_path</code><br>";
    die("Error: PHPMailer no está instalado.");
}

// 3. Test de conexión SMTP (sin enviar email)
echo "<h3>3. Test de Conexión SMTP</h3>";
try {
    $emailService = new EmailService();
    echo "✅ Servicio de email inicializado correctamente<br>";
    echo "📧 Configuración: " . MAIL_HOST . ":" . MAIL_PORT . "<br>";
} catch (Exception $e) {
    echo "❌ Error al inicializar servicio: " . $e->getMessage() . "<br>";
    die();
}

// 4. Obtener un pedido de prueba de la BD
echo "<h3>4. Obtener Pedido de Prueba</h3>";
try {
    $stmt = $pdo->query("SELECT o.*, c.email, c.first_name, c.last_name 
                         FROM orders o 
                         INNER JOIN customer c ON o.customer_id = c.customer_id 
                         ORDER BY o.order_id DESC 
                         LIMIT 1");
    $pedido_prueba = $stmt->fetch();
    
    if ($pedido_prueba) {
        echo "✅ Pedido encontrado: <strong>#" . str_pad($pedido_prueba['order_id'], 6, '0', STR_PAD_LEFT) . "</strong><br>";
        echo "📧 Email destino: <strong>" . $pedido_prueba['email'] . "</strong><br>";
        echo "👤 Cliente: " . $pedido_prueba['first_name'] . " " . $pedido_prueba['last_name'] . "<br>";
    } else {
        echo "⚠️ No hay pedidos en la base de datos. Crea un pedido primero.<br>";
        die();
    }
} catch (Exception $e) {
    echo "❌ Error al consultar BD: " . $e->getMessage() . "<br>";
    die();
}

// 5. Generar PDF de prueba
echo "<h3>5. Generación de PDF</h3>";
try {
    $order_id = $pedido_prueba['order_id'];
    $pedido = $pedido_prueba;
    
    // Obtener items del pedido
    $stmt = $pdo->prepare("
        SELECT oi.*, p.product_name 
        FROM order_items oi 
        INNER JOIN productos p ON oi.product_id = p.product_id
        WHERE oi.order_id = :id
    ");
    $stmt->execute(['id' => $order_id]);
    $items = $stmt->fetchAll();
    
    // Generar HTML
    ob_start();
    include __DIR__ . '/cliente/pages/plantilla_factura.php';
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
    $pdfSize = strlen($pdfContent);
    
    echo "✅ PDF generado exitosamente<br>";
    echo "📄 Tamaño del PDF: " . number_format($pdfSize / 1024, 2) . " KB<br>";
    
} catch (Exception $e) {
    echo "❌ Error al generar PDF: " . $e->getMessage() . "<br>";
    die();
}

// 6. Test de envío real (COMENTADO por defecto - descomentar para probar)
echo "<h3>6. Envío de Email de Prueba</h3>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> El envío está DESHABILITADO por defecto.</p>";
echo "<p>Para enviar un email de prueba, descomenta la sección en el código.</p>";

/*
// DESCOMENTAR ESTA SECCIÓN PARA ENVIAR EMAIL DE PRUEBA
echo "<p>🚀 Enviando email de prueba...</p>";

$datosOrden = [
    'total' => $pedido['total_amount'],
    'fecha' => $pedido['order_date'],
    'cantidad_productos' => count($items)
];

$resultado = $emailService->enviarFactura(
    $pedido['email'],
    $pedido['first_name'] . ' ' . $pedido['last_name'],
    $order_id,
    $pdfContent,
    $datosOrden
);

if ($resultado) {
    echo "✅ <strong style='color: green;'>EMAIL ENVIADO EXITOSAMENTE!</strong><br>";
    echo "📧 Revisa la bandeja de entrada de: " . $pedido['email'] . "<br>";
} else {
    echo "❌ <strong style='color: red;'>ERROR AL ENVIAR EMAIL</strong><br>";
    $errores = $emailService->getErrores();
    if (!empty($errores)) {
        echo "<pre>" . print_r($errores, true) . "</pre>";
    }
}
*/

echo "<hr>";
echo "<h3>✅ Test Completado</h3>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ol>";
echo "<li>Si todas las verificaciones pasaron, descomenta la sección de envío en este archivo</li>";
echo "<li>Recarga la página para enviar un email de prueba real</li>";
echo "<li>Revisa la bandeja de entrada (y spam) del email: <strong>" . $pedido['email'] . "</strong></li>";
echo "<li>Si el email llega correctamente, el sistema está listo para producción</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='test_email.php'>🔄 Recargar Test</a> | ";
echo "<a href='../cliente/pages/index.php'>🏠 Volver al Inicio</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h1 {
    color: #2c3e50;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
}

h3 {
    color: #2c3e50;
    margin-top: 30px;
    background-color: #ecf0f1;
    padding: 10px;
    border-left: 4px solid #3498db;
}

table {
    width: 100%;
    background-color: white;
    margin: 10px 0;
}

code {
    background-color: #e8e8e8;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

pre {
    background-color: #f8f8f8;
    padding: 15px;
    border-left: 4px solid #e74c3c;
    overflow-x: auto;
}
</style>
