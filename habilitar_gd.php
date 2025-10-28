<?php
/**
 * SCRIPT PARA HABILITAR EXTENSIÓN GD
 * Ayuda a habilitar automáticamente la extensión GD en XAMPP
 * Creado: 28/10/2025
 */

echo "🔧 HABILITADOR DE EXTENSIÓN GD PARA XAMPP\n";
echo "========================================\n\n";

// Detectar ruta del php.ini
$php_ini_path = php_ini_loaded_file();
echo "📁 Archivo php.ini detectado: {$php_ini_path}\n\n";

if (!$php_ini_path) {
    echo "❌ No se pudo detectar el archivo php.ini\n";
    echo "   Ruta manual típica: C:\\xampp\\php\\php.ini\n";
    exit;
}

// Verificar si el archivo existe y es escribible
if (!is_writable($php_ini_path)) {
    echo "⚠️  ATENCIÓN: El archivo php.ini no es escribible\n";
    echo "   Ejecuta como administrador o modifica manualmente\n\n";
}

// Leer el contenido del php.ini
$ini_content = file_get_contents($php_ini_path);

if ($ini_content === false) {
    echo "❌ Error al leer el archivo php.ini\n";
    exit;
}

echo "🔍 ANALIZANDO CONFIGURACIÓN GD:\n";
echo "==============================\n";

// Verificar estado actual de GD
if (extension_loaded('gd')) {
    echo "✅ GD ya está habilitada\n";
    $gd_info = gd_info();
    echo "   Versión: " . $gd_info['GD Version'] . "\n";
    
    // Reactivar el logo si GD está disponible
    echo "\n🖼️ REACTIVANDO LOGO AUTOMÁTICAMENTE:\n";
    echo "===================================\n";
    
    $template_path = __DIR__ . '/cliente/pages/plantilla_factura_detallada.php';
    if (file_exists($template_path)) {
        $template_content = file_get_contents($template_path);
        
        // Reactivar la función del logo
        $new_content = str_replace(
            'return null;',
            '// return null; // GD habilitada - logo reactivado',
            $template_content
        );
        
        $new_content = str_replace(
            '/* Código original (habilitar cuando GD esté disponible):',
            '// Código original reactivado:',
            $new_content
        );
        
        $new_content = str_replace(
            'return null;
    */
}',
            '$logo_path = __DIR__ . \'/logo.png\';
    if (file_exists($logo_path)) {
        $logo_data = base64_encode(file_get_contents($logo_path));
        return \'data:image/png;base64,\' . $logo_data;
    }
    return null;
}',
            $new_content
        );
        
        if (file_put_contents($template_path, $new_content)) {
            echo "✅ Logo reactivado automáticamente\n";
        } else {
            echo "⚠️  No se pudo reactivar el logo automáticamente\n";
        }
    }
    
} else {
    echo "❌ GD no está habilitada\n";
    
    // Buscar la línea de extensión GD
    if (strpos($ini_content, ';extension=gd') !== false) {
        echo "✅ Línea ;extension=gd encontrada (comentada)\n";
        echo "🔧 HABILITANDO GD AUTOMÁTICAMENTE...\n";
        
        if (is_writable($php_ini_path)) {
            // Descomentar la extensión GD
            $new_ini_content = str_replace(';extension=gd', 'extension=gd', $ini_content);
            
            if (file_put_contents($php_ini_path, $new_ini_content)) {
                echo "✅ Extensión GD habilitada en php.ini\n";
                echo "🔄 NECESITAS REINICIAR APACHE EN XAMPP\n";
            } else {
                echo "❌ Error al escribir en php.ini\n";
            }
        } else {
            echo "⚠️  php.ini no es escribible - habilitar manualmente\n";
        }
        
    } else if (strpos($ini_content, 'extension=gd') !== false) {
        echo "✅ extension=gd ya está descomentada\n";
        echo "🔄 Reinicia Apache en XAMPP para activar\n";
        
    } else {
        echo "❌ Línea extension=gd no encontrada\n";
        echo "📝 Agregar manualmente: extension=gd\n";
    }
}

echo "\n📋 INSTRUCCIONES MANUALES (si es necesario):\n";
echo "===========================================\n";
echo "1. Abrir XAMPP Control Panel\n";
echo "2. Hacer clic en 'Config' junto a Apache\n";
echo "3. Seleccionar 'PHP (php.ini)'\n";
echo "4. Buscar: ;extension=gd\n";
echo "5. Cambiar a: extension=gd\n";
echo "6. Guardar archivo\n";
echo "7. Reiniciar Apache\n";

echo "\n🌐 PROBAR FACTURAS DESPUÉS DE REINICIAR:\n";
echo "=======================================\n";
echo "http://localhost/Bike_Store/cliente/pages/factura.php?order_id=1001\n";

echo "\n✅ SCRIPT COMPLETADO\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
?>