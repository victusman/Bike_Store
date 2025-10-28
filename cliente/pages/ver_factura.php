<?php
/**
 * VISOR DE FACTURA CON ENVÍO AUTOMÁTICO DE EMAIL
 * Esta página muestra el PDF en un iframe y envía el email automáticamente
 */

session_start();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('ID de pedido no válido');
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['customer_id'])) {
    die('Usuario no autenticado');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Pedido #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            height: 100vh;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            position: relative;
        }
        
        .header h1 {
            font-size: 20px;
            margin: 0;
        }
        
        .header .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .pdf-container {
            height: calc(100vh - 70px);
            width: 100%;
            position: relative;
        }
        
        .pdf-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }
        
        .notification {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            font-size: 14px;
            max-width: 350px;
            word-wrap: break-word;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #27ae60;
            color: white;
        }
        
        .notification.error {
            background: #e74c3c;
            color: white;
        }
        
        .notification.warning {
            background: #f39c12;
            color: white;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 100;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚲 Factura - Pedido #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h1>
        <div class="actions">
            <button class="btn btn-primary" onclick="enviarEmailManual()">📧 Enviar por Email</button>
            <a href="mis_pedidos.php" class="btn btn-secondary">🔙 Mis Pedidos</a>
        </div>
    </div>
    
    <div class="pdf-container">
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Cargando factura...</p>
        </div>
        <iframe 
            id="pdfFrame" 
            class="pdf-iframe" 
            src="factura.php?order_id=<?php echo $order_id; ?>" 
            onload="pdfCargado()"
            style="display: none;">
        </iframe>
    </div>

    <script>
        let emailEnviado = false;
        
        function pdfCargado() {
            // Ocultar loading
            document.getElementById('loading').style.display = 'none';
            document.getElementById('pdfFrame').style.display = 'block';
            
            // Enviar email automáticamente después de 2 segundos
            setTimeout(() => {
                if (!emailEnviado) {
                    enviarEmailAutomatico();
                }
            }, 2000);
        }
        
        function enviarEmailAutomatico() {
            enviarEmail(true);
        }
        
        function enviarEmailManual() {
            enviarEmail(false);
        }
        
        function enviarEmail(esAutomatico = true) {
            if (emailEnviado && esAutomatico) {
                return; // No enviar múltiples emails automáticos
            }
            
            const orderID = <?php echo intval($order_id); ?>;
            
            console.log('🚀 Enviando factura por email...');
            
            const data = {
                order_id: orderID
            };
            
            fetch('api_enviar_factura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    console.log('✅ Email enviado exitosamente:', result.message);
                    mostrarNotificacion('✅ Factura enviada por email a: ' + result.data.email, 'success');
                    emailEnviado = true;
                } else {
                    console.log('❌ Error enviando email:', result.message);
                    mostrarNotificacion('⚠️ Error: ' + result.message, 'error');
                }
            })
            .catch(error => {
                console.log('❌ Error en petición:', error);
                mostrarNotificacion('⚠️ Error de conexión al enviar email', 'error');
            });
        }
        
        function mostrarNotificacion(mensaje, tipo = 'success') {
            // Remover notificación anterior si existe
            const notificacionAnterior = document.querySelector('.notification');
            if (notificacionAnterior) {
                notificacionAnterior.remove();
            }
            
            const notification = document.createElement('div');
            notification.className = `notification ${tipo}`;
            notification.innerHTML = mensaje;
            
            document.body.appendChild(notification);
            
            // Mostrar notificación
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>