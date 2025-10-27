<?php
/**
 * Servicio de Email usando PHPMailer
 * Bike Store - Sistema de envío de correos electrónicos
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private $mail;
    private $errorLog = [];
    
    /**
     * Constructor - Inicializa PHPMailer con configuración del .env
     */
    public function __construct() {
        // Cargar configuración
        require_once __DIR__ . '/../config/email_config.php';
        
        // Cargar PHPMailer
        require_once __DIR__ . '/../gmailPHP/PHPMailer-master/src/Exception.php';
        require_once __DIR__ . '/../gmailPHP/PHPMailer-master/src/PHPMailer.php';
        require_once __DIR__ . '/../gmailPHP/PHPMailer-master/src/SMTP.php';
        
        $this->mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host       = MAIL_HOST;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = MAIL_USERNAME;
            $this->mail->Password   = MAIL_PASSWORD;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port       = MAIL_PORT;
            $this->mail->CharSet    = 'UTF-8';
            
            // Configuración del remitente
            $this->mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            
            // Desactivar verificación SSL (solo para desarrollo local)
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
        } catch (Exception $e) {
            $this->errorLog[] = "Error de configuración: {$this->mail->ErrorInfo}";
            error_log("EmailService Error: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar email con factura adjunta
     * 
     * @param string $destinatario Email del cliente
     * @param string $nombreCliente Nombre del cliente
     * @param int $orderId ID del pedido
     * @param string $pdfContent Contenido del PDF en string
     * @param array $datosOrden Datos del pedido (total, fecha, productos)
     * @return bool True si se envió exitosamente
     */
    public function enviarFactura($destinatario, $nombreCliente, $orderId, $pdfContent, $datosOrden = []) {
        try {
            // Limpiar destinatarios previos
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Destinatario
            $this->mail->addAddress($destinatario, $nombreCliente);
            
            // Adjuntar PDF
            $this->mail->addStringAttachment(
                $pdfContent, 
                'Factura_' . str_pad($orderId, 6, '0', STR_PAD_LEFT) . '.pdf',
                'base64',
                'application/pdf'
            );
            
            // Asunto
            $this->mail->Subject = '🚲 Tu factura de Bike Store - Pedido #' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
            
            // Cuerpo del email en HTML
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getPlantillaFactura($nombreCliente, $orderId, $datosOrden);
            
            // Texto alternativo (para clientes de email sin HTML)
            $this->mail->AltBody = $this->getTextoAlternativo($nombreCliente, $orderId, $datosOrden);
            
            // Enviar
            $resultado = $this->mail->send();
            
            if ($resultado) {
                error_log("Email enviado exitosamente a: $destinatario (Pedido #$orderId)");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            $this->errorLog[] = "Error al enviar email: {$this->mail->ErrorInfo}";
            error_log("Error EmailService::enviarFactura - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Plantilla HTML profesional para el email de factura
     */
    private function getPlantillaFactura($nombreCliente, $orderId, $datos) {
        $orderNumber = str_pad($orderId, 6, '0', STR_PAD_LEFT);
        $total = isset($datos['total']) ? '$' . number_format($datos['total'], 2) : '';
        $fecha = isset($datos['fecha']) ? date('d/m/Y', strtotime($datos['fecha'])) : date('d/m/Y');
        $cantidadProductos = isset($datos['cantidad_productos']) ? $datos['cantidad_productos'] : 0;
        
        $html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Bike Store</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Container principal -->
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 32px;">
                                🚲 Bike Store
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ecf0f1; font-size: 16px;">
                                Tu tienda de bicicletas de confianza
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Contenido principal -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <!-- Saludo -->
                            <h2 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 24px;">
                                ¡Hola ' . htmlspecialchars($nombreCliente) . '!
                            </h2>
                            
                            <p style="margin: 0 0 20px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                ¡Gracias por tu compra! Tu pedido ha sido confirmado exitosamente.
                            </p>
                            
                            <!-- Box de información del pedido -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ecf0f1; border-radius: 6px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="5" cellspacing="0">
                                            <tr>
                                                <td style="color: #7f8c8d; font-size: 14px;">
                                                    <strong>Número de Pedido:</strong>
                                                </td>
                                                <td align="right" style="color: #2c3e50; font-size: 16px; font-weight: bold;">
                                                    #' . $orderNumber . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #7f8c8d; font-size: 14px;">
                                                    <strong>Fecha:</strong>
                                                </td>
                                                <td align="right" style="color: #2c3e50; font-size: 14px;">
                                                    ' . $fecha . '
                                                </td>
                                            </tr>
                                            ' . ($cantidadProductos > 0 ? '
                                            <tr>
                                                <td style="color: #7f8c8d; font-size: 14px;">
                                                    <strong>Productos:</strong>
                                                </td>
                                                <td align="right" style="color: #2c3e50; font-size: 14px;">
                                                    ' . $cantidadProductos . ' artículo(s)
                                                </td>
                                            </tr>
                                            ' : '') . '
                                            ' . (!empty($total) ? '
                                            <tr>
                                                <td style="color: #7f8c8d; font-size: 14px;">
                                                    <strong>Total:</strong>
                                                </td>
                                                <td align="right" style="color: #27ae60; font-size: 20px; font-weight: bold;">
                                                    ' . $total . '
                                                </td>
                                            </tr>
                                            ' : '') . '
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Mensaje sobre el PDF -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px;">
                                    📎 <strong>Factura adjunta:</strong> Hemos adjuntado tu factura en formato PDF a este correo.
                                </p>
                            </div>
                            
                            <!-- Próximos pasos -->
                            <h3 style="margin: 30px 0 15px 0; color: #2c3e50; font-size: 18px;">
                                📦 Próximos pasos:
                            </h3>
                            
                            <ul style="color: #555555; font-size: 14px; line-height: 1.8; padding-left: 20px;">
                                <li>Procesaremos tu pedido en las próximas 24 horas</li>
                                <li>Recibirás un email cuando tu pedido sea enviado</li>
                                <li>El tiempo de entrega estimado es de 3-5 días hábiles</li>
                            </ul>
                            
                            <!-- Botón CTA -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="' . APP_URL . '/cliente/pages/mis_pedidos.php" 
                                   style="display: inline-block; background-color: #3498db; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 5px; font-size: 16px; font-weight: bold;">
                                    Ver Mi Pedido
                                </a>
                            </div>
                            
                            <!-- Mensaje de agradecimiento -->
                            <p style="margin: 30px 0 0 0; color: #555555; font-size: 14px; line-height: 1.6;">
                                Si tienes alguna pregunta sobre tu pedido, no dudes en contactarnos. 
                                ¡Estamos aquí para ayudarte!
                            </p>
                            
                            <p style="margin: 20px 0 0 0; color: #555555; font-size: 14px;">
                                Saludos,<br>
                                <strong>El equipo de Bike Store</strong> 🚲
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #2c3e50; padding: 20px 30px; text-align: center;">
                            <p style="margin: 0 0 10px 0; color: #ecf0f1; font-size: 12px;">
                                <strong>Bike Store</strong> - Tu tienda de bicicletas de confianza
                            </p>
                            <p style="margin: 0 0 10px 0; color: #95a5a6; font-size: 11px;">
                                📍 Av. Principal #123, Ciudad | 📞 (555) 123-4567
                            </p>
                            <p style="margin: 0; color: #95a5a6; font-size: 11px;">
                                📧 <a href="mailto:' . MAIL_FROM_ADDRESS . '" style="color: #3498db; text-decoration: none;">' . MAIL_FROM_ADDRESS . '</a>
                            </p>
                            <p style="margin: 15px 0 0 0; color: #7f8c8d; font-size: 10px;">
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Texto alternativo para clientes sin soporte HTML
     */
    private function getTextoAlternativo($nombreCliente, $orderId, $datos) {
        $orderNumber = str_pad($orderId, 6, '0', STR_PAD_LEFT);
        $total = isset($datos['total']) ? '$' . number_format($datos['total'], 2) : '';
        $fecha = isset($datos['fecha']) ? date('d/m/Y', strtotime($datos['fecha'])) : date('d/m/Y');
        
        $texto = "BIKE STORE - CONFIRMACIÓN DE PEDIDO\n\n";
        $texto .= "Hola $nombreCliente,\n\n";
        $texto .= "¡Gracias por tu compra! Tu pedido ha sido confirmado.\n\n";
        $texto .= "DETALLES DEL PEDIDO:\n";
        $texto .= "- Número de Pedido: #$orderNumber\n";
        $texto .= "- Fecha: $fecha\n";
        if (!empty($total)) {
            $texto .= "- Total: $total\n";
        }
        $texto .= "\n";
        $texto .= "Tu factura está adjunta a este correo en formato PDF.\n\n";
        $texto .= "PRÓXIMOS PASOS:\n";
        $texto .= "- Procesaremos tu pedido en las próximas 24 horas\n";
        $texto .= "- Recibirás un email cuando sea enviado\n";
        $texto .= "- Tiempo de entrega: 3-5 días hábiles\n\n";
        $texto .= "Ver tu pedido: " . APP_URL . "/cliente/pages/mis_pedidos.php\n\n";
        $texto .= "Saludos,\n";
        $texto .= "El equipo de Bike Store 🚲\n\n";
        $texto .= "---\n";
        $texto .= "Bike Store | Av. Principal #123 | (555) 123-4567\n";
        $texto .= MAIL_FROM_ADDRESS;
        
        return $texto;
    }
    
    /**
     * Obtener log de errores
     */
    public function getErrores() {
        return $this->errorLog;
    }
    
    /**
     * Modo debug - Habilitar salida detallada
     */
    public function enableDebug() {
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->Debugoutput = 'html';
    }
}
