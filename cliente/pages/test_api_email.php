<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba API Email Factura</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; margin: 0 auto; }
        button { 
            background: #3498db; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px;
            margin: 10px 5px;
        }
        button:hover { background: #2980b9; }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .info { color: #3498db; }
        #resultado { 
            margin: 20px 0; 
            padding: 15px; 
            border-radius: 5px; 
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        pre { background: #f1f2f6; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba API Email Factura</h1>
        
        <p>Esta página prueba el envío de facturas por email usando AJAX.</p>
        
        <button onclick="probarAPI(1761657009)">📧 Enviar Factura #1761657009</button>
        <button onclick="probarAPI(12345)">❌ Probar ID Inválido</button>
        <button onclick="limpiarResultado()">🧹 Limpiar</button>
        
        <div id="resultado"></div>
    </div>

    <script>
        function probarAPI(orderID) {
            const resultadoDiv = document.getElementById('resultado');
            resultadoDiv.innerHTML = '<p class="info">⏳ Enviando petición...</p>';
            
            const data = {
                order_id: orderID
            };
            
            console.log('Enviando datos:', data);
            
            fetch('api_enviar_factura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(result => {
                console.log('Response data:', result);
                
                let html = '<h3>📨 Resultado de la API</h3>';
                
                if (result.success) {
                    html += '<p class="success">✅ <strong>Éxito:</strong> ' + result.message + '</p>';
                    if (result.data) {
                        html += '<p><strong>Email:</strong> ' + result.data.email + '</p>';
                        html += '<p><strong>Order ID:</strong> ' + result.data.order_id + '</p>';
                    }
                } else {
                    html += '<p class="error">❌ <strong>Error:</strong> ' + result.message + '</p>';
                }
                
                html += '<p><strong>Timestamp:</strong> ' + result.timestamp + '</p>';
                html += '<h4>📋 Respuesta completa:</h4>';
                html += '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
                resultadoDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                resultadoDiv.innerHTML = '<p class="error">❌ <strong>Error de conexión:</strong> ' + error.message + '</p>';
            });
        }
        
        function limpiarResultado() {
            document.getElementById('resultado').innerHTML = '';
        }
    </script>
</body>
</html>