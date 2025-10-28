<?php
/**
 * ENVÍO MANUAL DE FACTURA POR EMAIL
 * Este archivo permite enviar la factura por email después de generarla
 */

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../libs/autoload.inc.php';
require_once __DIR__ . '/../../libs/EmailService.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('ID de pedido no válido');
}

echo "<h2>📧 Envío de Factura por Email</h2>";
echo "<p>Procesando pedido #$order_id...</p>";

try {
    // Obtener datos del cliente
    if (!isset($_SESSION['customer_id'])) {
        die('❌ Error: Cliente no logueado');
    }
    
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM customer WHERE customer_id = :id");
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $clienteData = $stmt->fetch();
    
    if (!$clienteData || !filter_var($clienteData['email'], FILTER_VALIDATE_EMAIL)) {
        die('❌ Error: Cliente sin email válido');
    }
    
    $clienteEmail = $clienteData['email'];
    $clienteNombre = trim($clienteData['first_name'] . ' ' . $clienteData['last_name']);
    
    echo "<p>📤 Enviando a: <strong>$clienteEmail</strong></p>";
    echo "<p>👤 Cliente: <strong>$clienteNombre</strong></p>";
    
    // Regenerar PDF para envío
    // (Código similar al de factura.php pero sin stream)
    
    // Para demo, usar datos simulados
    $pedido = [
        'order_id' => $order_id,
        'first_name' => $clienteData['first_name'],
        'last_name' => $clienteData['last_name'],
        'email' => $clienteEmail,
        'phone' => '123456789',
        'order_date' => date('Y-m-d H:i:s'),
        'metodo_pago' => 'Tarjeta de Crédito'
    ];
    
    $items = [
        ['product_id' => 1, 'product_name' => 'Heller Shagamaw Frame - 2017', 'quantity' => 1, 'price' => 1320.99, 'discount' => 0]
    ];
    
    $subtotalGeneral = 1320.99;
    $descuentoTotal = 0;
    $totalFinal = 1320.99;
    
    // Generar HTML
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
    
    echo "<p>📄 PDF generado: " . strlen($pdfContent) . " bytes</p>";
    
    // Enviar email
    $emailService = new EmailService();
    
    $datosOrden = [
        'total' => $totalFinal,
        'fecha' => $pedido['order_date'],
        'cantidad_productos' => count($items)
    ];
    
    echo "<p>📧 Enviando email...</p>";
    
    $resultado = $emailService->enviarFactura(
        $clienteEmail,
        $clienteNombre,
        $order_id,
        $pdfContent,
        $datosOrden
    );
    
    if ($resultado) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>✅ ¡Email enviado exitosamente!</h3>";
        echo "<p>La factura ha sido enviada a: <strong>$clienteEmail</strong></p>";
        echo "<p>Revisa tu bandeja de entrada (y spam si es necesario).</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ Error al enviar email</h3>";
        $errores = $emailService->getErrores();
        if (!empty($errores)) {
            echo "<ul>";
            foreach ($errores as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Excepción</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='factura.php?order_id=$order_id'>🔙 Volver a la factura</a></p>";
echo "<p><a href='mis_pedidos.php'>📋 Ver mis pedidos</a></p>";
?>