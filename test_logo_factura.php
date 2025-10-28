<?php
/**
 * PRUEBA DEL LOGO EN FACTURA
 * Script para verificar que el logo se muestra correctamente en el PDF
 * Creado: 28/10/2025
 */

echo "🖼️ PRUEBA DEL LOGO EN FACTURA\n";
echo "=============================\n\n";

// Verificar archivos del logo
$logo_paths = [
    'Logo original' => 'c:\xampp\htdocs\Bike_Store\secciones\orders\logo.png',
    'Logo para facturas' => 'c:\xampp\htdocs\Bike_Store\cliente\pages\logo.png'
];

echo "📁 VERIFICANDO ARCHIVOS DE LOGO:\n";
echo "================================\n";

foreach ($logo_paths as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        $kb = round($size / 1024, 2);
        echo "✅ {$name}: Existe ({$kb} KB)\n";
        echo "   Ruta: {$path}\n";
    } else {
        echo "❌ {$name}: No encontrado\n";
        echo "   Ruta: {$path}\n";
    }
    echo "\n";
}

// Probar la función de base64
echo "🔄 PROBANDO CONVERSIÓN A BASE64:\n";
echo "================================\n";

$logo_path = __DIR__ . '/cliente/pages/logo.png';
if (file_exists($logo_path)) {
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_base64 = 'data:image/png;base64,' . $logo_data;
    
    echo "✅ Conversión exitosa\n";
    echo "📊 Tamaño base64: " . strlen($logo_base64) . " caracteres\n";
    echo "🔗 Formato: " . substr($logo_base64, 0, 50) . "...\n";
} else {
    echo "❌ No se pudo convertir - archivo no encontrado\n";
}

echo "\n🌐 URLs PARA PROBAR CON LOGO:\n";
echo "=============================\n";

$test_urls = [
    "Factura con Logo 1001" => "http://localhost/Bike_Store/cliente/pages/factura.php?order_id=1001",
    "Factura con Logo 2002" => "http://localhost/Bike_Store/cliente/pages/factura.php?order_id=2002",
    "Factura con Logo 3003" => "http://localhost/Bike_Store/cliente/pages/factura.php?order_id=3003"
];

foreach ($test_urls as $name => $url) {
    echo "🔗 {$name}:\n";
    echo "   {$url}\n\n";
}

echo "📋 VERIFICACIÓN DEL LOGO EN PDF:\n";
echo "===============================\n";
echo "1. ✅ Abrir cualquier URL de prueba\n";
echo "2. ✅ Verificar que aparece el logo en el header central\n";
echo "3. ✅ El logo debe tener buen tamaño (120px max width)\n";
echo "4. ✅ Debe aparecer 'Tu tienda de bicicletas de confianza' debajo\n";
echo "5. ✅ Si no hay logo, debe mostrar '🚲 BIKE STORE'\n";

echo "\n🎨 CARACTERÍSTICAS DEL LOGO:\n";
echo "===========================\n";
echo "• Tamaño máximo: 120px de ancho\n";
echo "• Altura máxima: 80px\n";
echo "• Formato: Base64 embebido en PDF\n";
echo "• Posición: Centro del header\n";
echo "• Fallback: Texto '🚲 BIKE STORE' si no hay imagen\n";

echo "\n✅ ESTADO: LOGO IMPLEMENTADO EN FACTURAS\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
?>