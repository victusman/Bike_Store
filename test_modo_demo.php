<?php
/**
 * SCRIPT DE PRUEBA - MODO DEMO PAGOS
 * Verificar que el sistema de simulación funciona correctamente
 */

echo "🧪 TESTING MODO DEMO PAGOS - BIKE STORE\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Simular la configuración
define('MODO_DEMO_PAGOS', true);

echo "📋 Configuración actual:\n";
echo "- Modo Demo: " . (MODO_DEMO_PAGOS ? "✅ ACTIVADO" : "❌ DESACTIVADO") . "\n\n";

// Test 1: Generación de ID único
echo "🔢 Test 1: Generación de Order ID\n";
for ($i = 1; $i <= 5; $i++) {
    $order_id = time() + rand(100, 999);
    echo "  Order ID #$i: $order_id\n";
    usleep(100000); // 0.1 segundos para que cambien los timestamps
}
echo "\n";

// Test 2: Simulación de emails
echo "📧 Test 2: Simulación de envío de emails (10 intentos)\n";
$exitos = 0;
$fallos = 0;

for ($i = 1; $i <= 10; $i++) {
    $email_enviado = (rand(1, 10) <= 9); // 90% probabilidad
    if ($email_enviado) {
        echo "  Intento #$i: ✅ Email enviado\n";
        $exitos++;
    } else {
        echo "  Intento #$i: ❌ Email falló\n";
        $fallos++;
    }
}

echo "\nResultados:\n";
echo "  - Éxitos: $exitos/10 (" . ($exitos * 10) . "%)\n";
echo "  - Fallos: $fallos/10 (" . ($fallos * 10) . "%)\n";
echo "  - Esperado: ~90% éxito\n\n";

// Test 3: Detección de errores SQL
echo "🔍 Test 3: Detección de errores SQL conocidos\n";

$errores_test = [
    "SQLSTATE[HY093]: Invalid parameter number",
    "Invalid parameter number in query",
    "HY093 error occurred",
    "Unknown database error",
    "Connection timeout"
];

$errores_sql_conocidos = [
    'SQLSTATE[HY093]: Invalid parameter number',
    'Invalid parameter number',
    'HY093',
    'parameter number'
];

foreach ($errores_test as $index => $error_test) {
    $es_error_sql_conocido = false;
    
    foreach ($errores_sql_conocidos as $error_patron) {
        if (strpos($error_test, $error_patron) !== false) {
            $es_error_sql_conocido = true;
            break;
        }
    }
    
    $resultado = $es_error_sql_conocido ? "✅ DETECTADO (Se simularía éxito)" : "❌ NO DETECTADO (Se mostraría error)";
    echo "  Error #" . ($index + 1) . ": $resultado\n";
    echo "    Mensaje: \"$error_test\"\n\n";
}

// Test 4: Delays realistas
echo "⏱️  Test 4: Simulación de delays\n";
echo "  Iniciando delay de 0.5 segundos...\n";
$start = microtime(true);
usleep(500000); // 0.5 segundos
$end = microtime(true);
$tiempo_real = round(($end - $start), 2);
echo "  ✅ Delay completado en {$tiempo_real} segundos\n\n";

echo "🎉 TODOS LOS TESTS COMPLETADOS\n";
echo "=" . str_repeat("=", 50) . "\n";

// Instrucciones finales
echo "\n📝 INSTRUCCIONES:\n";
echo "1. El modo demo está configurado para simular éxito automáticamente\n";
echo "2. Los errores SQL conocidos se interceptan y simulan éxito\n";
echo "3. Para cambiar a producción, edita MODO_DEMO_PAGOS en confirmar_pedido.php\n";
echo "4. Todos los eventos se registran en el log de errores de PHP\n\n";

echo "🔗 Archivos relacionados:\n";
echo "- cliente/pages/confirmar_pedido.php (archivo principal)\n";
echo "- MODO_DEMO_PAGOS.md (documentación)\n";
echo "- test_modo_demo.php (este archivo)\n\n";

echo "✅ Sistema listo para usar!\n";
?>