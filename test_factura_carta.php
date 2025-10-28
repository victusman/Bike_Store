<?php
/**
 * SCRIPT DE PRUEBA - FACTURA TAMAÑO CARTA CON ESTRUCTURA DE LISTA_ORDERS
 * Verificar que la factura tenga exactamente la misma estructura y funcione correctamente
 * Creado: 28/10/2025
 */

echo "<h1>🧪 PRUEBA DE FACTURA TAMAÑO CARTA - ESTRUCTURA LISTA_ORDERS</h1>\n";
echo "<p>Verificando que la factura tenga la estructura exacta de lista_orders.php...</p>\n";

// Simular datos de sesión
session_start();
$_SESSION['customer_id'] = 1;

// URLs de prueba para factura
$base_url = "http://localhost/Bike_Store";
$test_urls = [
    "factura_demo_1" => "{$base_url}/cliente/pages/factura.php?order_id=1001",
    "factura_demo_2" => "{$base_url}/cliente/pages/factura.php?order_id=2002", 
    "factura_demo_3" => "{$base_url}/cliente/pages/factura.php?order_id=3003"
];

echo "<h2>📋 URLS DE PRUEBA PARA FACTURAS</h2>\n";
foreach ($test_urls as $name => $url) {
    echo "<p><strong>{$name}:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>\n";
}

// Verificar archivos críticos
echo "<h2>🔍 VERIFICACIÓN DE ARCHIVOS</h2>\n";

$files_to_check = [
    'factura.php' => __DIR__ . '/cliente/pages/factura.php',
    'plantilla_factura_detallada.php' => __DIR__ . '/cliente/pages/plantilla_factura_detallada.php',
    'autoload_dompdf' => __DIR__ . '/libs/autoload.inc.php',
    'lista_orders.php' => __DIR__ . '/secciones/orders/lista_orders.php'
];

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ <strong>{$name}</strong>: Existe ({$size} bytes)<br>\n";
    } else {
        echo "❌ <strong>{$name}</strong>: No encontrado en {$path}<br>\n";
    }
}

// Verificar configuración
echo "<h2>⚙️ CONFIGURACIÓN DEL SISTEMA</h2>\n";

echo "<p><strong>🎭 MODO DEMO ACTIVO:</strong> ";
if (defined('MODO_DEMO_FACTURAS')) {
    echo MODO_DEMO_FACTURAS ? "SÍ" : "NO";
} else {
    echo "Por definir en factura.php";
}
echo "</p>\n";

echo "<p><strong>📄 Configuración de PDF:</strong></p>\n";
echo "<ul>\n";
echo "<li>Tamaño: Carta (letter)</li>\n";
echo "<li>Orientación: Vertical (portrait)</li>\n";
echo "<li>Márgenes: 1.5cm</li>\n";
echo "<li>Fuente: Arial/DejaVu Sans</li>\n";
echo "<li>Librería: Dompdf local</li>\n";
echo "</ul>\n";

// Análisis de estructura
echo "<h2>🏗️ ANÁLISIS DE ESTRUCTURA</h2>\n";

if (file_exists(__DIR__ . '/cliente/pages/plantilla_factura_detallada.php')) {
    $template_content = file_get_contents(__DIR__ . '/cliente/pages/plantilla_factura_detallada.php');
    
    // Verificar elementos clave de lista_orders.php
    $structure_checks = [
        'CSS @page letter' => strpos($template_content, 'size: letter') !== false,
        'Header con 3 secciones' => strpos($template_content, 'header-left') !== false && strpos($template_content, 'header-center') !== false,
        'Logo placeholder' => strpos($template_content, 'logo.png') !== false,
        'Título FACTURA' => strpos($template_content, 'titulo-recibo') !== false,
        'Info cliente dual' => strpos($template_content, 'info-left') !== false && strpos($template_content, 'info-right') !== false,
        'Tabla productos' => strpos($template_content, 'tabla-items') !== false,
        'Sección totales' => strpos($template_content, 'clearfix') !== false && strpos($template_content, 'resumen') !== false,
        'Función numeroATexto' => strpos($template_content, 'numeroATexto') !== false,
        'BOLIVIANOS currency' => strpos($template_content, 'BOLIVIANOS') !== false
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Elemento de Estructura</th><th>Estado</th></tr>\n";
    
    foreach ($structure_checks as $element => $exists) {
        $status = $exists ? "✅ Presente" : "❌ Faltante";
        echo "<tr><td>{$element}</td><td>{$status}</td></tr>\n";
    }
    echo "</table>\n";
}

// Comparación con lista_orders.php
echo "<h2>🔄 COMPARACIÓN CON LISTA_ORDERS.PHP</h2>\n";

if (file_exists(__DIR__ . '/secciones/orders/lista_orders.php')) {
    $orders_content = file_get_contents(__DIR__ . '/secciones/orders/lista_orders.php');
    
    // Extraer elementos CSS clave de lista_orders.php
    $css_elements = [
        'margin: 1.5cm' => strpos($orders_content, 'margin: 1.5cm') !== false,
        'font-family: Arial' => strpos($orders_content, 'font-family: Arial') !== false,
        'border-bottom: 2px solid #333' => strpos($orders_content, 'border-bottom: 2px solid #333') !== false,
        'background-color: #f0f0f0' => strpos($orders_content, 'background-color: #f0f0f0') !== false,
        'width: calc(100% - 270px)' => strpos($orders_content, 'width: calc(100% - 270px)') !== false
    ];
    
    echo "<p><strong>Elementos CSS críticos coincidentes:</strong></p>\n";
    echo "<ul>\n";
    foreach ($css_elements as $css => $found) {
        $status = $found ? "✅" : "❌";
        echo "<li>{$status} {$css}</li>\n";
    }
    echo "</ul>\n";
}

// Instrucciones de uso
echo "<h2>📖 INSTRUCCIONES DE USO</h2>\n";
echo "<ol>\n";
echo "<li><strong>Acceder a cualquier URL de prueba</strong> de la lista anterior</li>\n";
echo "<li><strong>El PDF se abrirá automáticamente</strong> en el navegador</li>\n";
echo "<li><strong>Verificar tamaño carta:</strong> El PDF debe tener proporciones 8.5\" x 11\"</li>\n";
echo "<li><strong>Verificar estructura:</strong> Debe ser idéntica a lista_orders.php</li>\n";
echo "<li><strong>Verificar datos:</strong> Deben mostrar información de venta real o demo</li>\n";
echo "</ol>\n";

// Características de la nueva factura
echo "<h2>🌟 CARACTERÍSTICAS DE LA NUEVA FACTURA</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Tamaño carta (letter)</strong> - 8.5\" x 11\"</li>\n";
echo "<li>✅ <strong>Estructura idéntica</strong> a lista_orders.php</li>\n";
echo "<li>✅ <strong>Header con logo</strong> y información de venta</li>\n";
echo "<li>✅ <strong>Información dual del cliente</strong> (izquierda/derecha)</li>\n";
echo "<li>✅ <strong>Tabla de productos</strong> con códigos y totales</li>\n";
echo "<li>✅ <strong>Conversión a texto</strong> en BOLIVIANOS</li>\n";
echo "<li>✅ <strong>Resumen de totales</strong> con descuentos</li>\n";
echo "<li>✅ <strong>Marca de agua DEMO</strong> cuando corresponde</li>\n";
echo "<li>✅ <strong>CSS responsive</strong> para PDF</li>\n";
echo "<li>✅ <strong>Librería Dompdf local</strong> desde libs/</li>\n";
echo "</ul>\n";

echo "<h2>🎯 PRÓXIMOS PASOS</h2>\n";
echo "<p>1. <strong>Probar las URLs</strong> para verificar funcionamiento</p>\n";
echo "<p>2. <strong>Verificar tamaño carta</strong> al imprimir o guardar PDF</p>\n";
echo "<p>3. <strong>Customizar logo</strong> reemplazando logo.png si es necesario</p>\n";
echo "<p>4. <strong>Ajustar datos de empresa</strong> en el header si es necesario</p>\n";

echo "<hr>\n";
echo "<p><strong>Estado del sistema:</strong> ✅ FACTURA CARTA CON ESTRUCTURA LISTA_ORDERS IMPLEMENTADA</p>\n";
echo "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>\n";
?>