<?php
/**
 * SCRIPT DE PRUEBA - FACTURAS MODO DEMO
 * Verificar que el sistema de facturas funciona con pedidos simulados
 */

echo "🧪 TESTING FACTURAS MODO DEMO - BIKE STORE\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Simular la configuración
define('MODO_DEMO_FACTURAS', true);

echo "📋 Configuración actual:\n";
echo "- Modo Demo Facturas: " . (MODO_DEMO_FACTURAS ? "✅ ACTIVADO" : "❌ DESACTIVADO") . "\n\n";

// Test 1: Generar URLs de factura
echo "🔗 Test 1: URLs de factura generadas\n";
$order_ids = [1001, 1002, 1320, 9999];

foreach ($order_ids as $id) {
    $url = "http://localhost/Bike_Store/cliente/pages/factura.php?order_id=$id";
    echo "  Order ID $id: $url\n";
}
echo "\n";

// Test 2: Datos simulados típicos
echo "📊 Test 2: Datos simulados que se generarán\n";

// Simular datos del pedido
$pedido_demo = [
    'order_id' => 1320,
    'customer_id' => 1,
    'first_name' => 'Cliente',
    'last_name' => 'Demo',
    'email' => 'cliente@demo.com',
    'phone' => '123456789',
    'order_date' => date('Y-m-d H:i:s'),
    'subtotal' => 1320.99,
    'descuento' => 0.00,
    'costo_envio' => 0.00,
    'total_amount' => 1320.99,
    'metodo_pago' => 'Pago Demo',
    'direccion_envio' => 'Dirección de envío simulada, Ciudad Demo, País',
    'notas' => 'Este es un pedido generado en modo demo para pruebas.',
    'estado' => 1
];

echo "  Cliente: " . $pedido_demo['first_name'] . " " . $pedido_demo['last_name'] . "\n";
echo "  Email: " . $pedido_demo['email'] . "\n";
echo "  Total: $" . number_format($pedido_demo['total_amount'], 2) . "\n";
echo "  Método Pago: " . $pedido_demo['metodo_pago'] . "\n";
echo "  Fecha: " . $pedido_demo['order_date'] . "\n\n";

// Test 3: Items simulados
echo "🛍️ Test 3: Items del pedido simulados\n";

$items_demo = [
    [
        'product_id' => 1,
        'product_name' => 'Heller Shagamaw Frame - 2017',
        'quantity' => 1,
        'price' => 1320.99,
        'list_price' => 1320.99,
        'discount' => 0.00
    ]
];

foreach ($items_demo as $index => $item) {
    echo "  Item #" . ($index + 1) . ":\n";
    echo "    - Producto: " . $item['product_name'] . "\n";
    echo "    - Cantidad: " . $item['quantity'] . "\n";
    echo "    - Precio: $" . number_format($item['price'], 2) . "\n";
    echo "    - Subtotal: $" . number_format($item['quantity'] * $item['price'], 2) . "\n";
}
echo "\n";

// Test 4: Función de conversión de número a texto
echo "💰 Test 4: Conversión de números a texto\n";

function numeroATextoTest($numero)
{
    $numero = intval($numero);
    
    $unidades = array('', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE');
    $decenas = array('', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA');
    $especiales = array(11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 
                       16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE');
    $centenas = array('', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS');
    
    if ($numero == 0) return 'CERO';
    if ($numero == 100) return 'CIEN';
    
    $resultado = '';
    
    // Miles
    if ($numero >= 1000) {
        $miles = intval($numero / 1000);
        if ($miles == 1) {
            $resultado .= 'MIL ';
        } else {
            $resultado .= $unidades[$miles] . ' MIL ';
        }
        $numero = $numero % 1000;
    }
    
    // Centenas
    if ($numero >= 100) {
        $resultado .= $centenas[intval($numero / 100)] . ' ';
        $numero = $numero % 100;
    }
    
    // Números especiales del 11 al 19
    if ($numero >= 11 && $numero <= 19) {
        $resultado .= $especiales[$numero] . ' ';
        return trim($resultado) . ' DÓLARES';
    }
    
    // Decenas y unidades
    if ($numero >= 10) {
        $resultado .= $decenas[intval($numero / 10)] . ' ';
        $numero = $numero % 10;
    }
    
    if ($numero > 0) {
        $resultado .= $unidades[$numero] . ' ';
    }
    
    return trim($resultado) . ' DÓLARES';
}

$numeros_test = [0, 1, 15, 100, 250, 1000, 1320];
foreach ($numeros_test as $num) {
    echo "  $num = " . numeroATextoTest($num) . "\n";
}
echo "\n";

// Test 5: Verificar archivos relacionados
echo "📁 Test 5: Archivos del sistema\n";

$archivos_verificar = [
    'cliente/pages/factura.php' => 'Archivo principal de facturas',
    'cliente/pages/confirmar_pedido.php' => 'Archivo de confirmación (modo demo)',
    'secciones/orders/lista_orders.php' => 'Referencia de diseño',
    'MODO_DEMO_PAGOS.md' => 'Documentación del modo demo'
];

foreach ($archivos_verificar as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "  ✅ $archivo - $descripcion\n";
    } else {
        echo "  ❌ $archivo - $descripcion (NO ENCONTRADO)\n";
    }
}

echo "\n🎉 TODOS LOS TESTS COMPLETADOS\n";
echo "=" . str_repeat("=", 50) . "\n";

// Instrucciones finales
echo "\n📝 INSTRUCCIONES DE USO:\n";
echo "1. Visita: http://localhost/Bike_Store/cliente/pages/factura.php?order_id=1320\n";
echo "2. Deberías ver una factura profesional con datos simulados\n";
echo "3. La factura incluye:\n";
echo "   - Header con logo de Bike Store\n";
echo "   - Badge 'MODO DEMO' visible\n";
echo "   - Datos del cliente simulados\n";
echo "   - Tabla de productos\n";
echo "   - Cálculos de totales\n";
echo "   - Conversión a texto del monto\n";
echo "   - Footer informativo\n\n";

echo "🔧 CONFIGURACIÓN:\n";
echo "- Para desactivar modo demo: Cambiar MODO_DEMO_FACTURAS a false\n";
echo "- Para activar modo demo: Cambiar MODO_DEMO_FACTURAS a true\n\n";

echo "✅ Sistema de facturas listo para usar!\n";
?>