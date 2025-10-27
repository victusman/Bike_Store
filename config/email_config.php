<?php
/**
 * Configuración de Email - Bike Store
 * Lee las variables del archivo .env
 */

function getEnvVar($key, $default = '') {
    $envFile = dirname(__DIR__) . '/.env';
    
    if (!file_exists($envFile)) {
        return $default;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remover comillas si existen
        $value = trim($value, '"\'');
        
        if ($name === $key) {
            return $value;
        }
    }
    
    return $default;
}

// Configuración de email desde .env
define('MAIL_HOST', getEnvVar('MAIL_HOST', 'smtp.hostinger.com'));
define('MAIL_PORT', getEnvVar('MAIL_PORT', '465'));
define('MAIL_USERNAME', getEnvVar('MAIL_USERNAME'));
define('MAIL_PASSWORD', getEnvVar('MAIL_PASSWORD'));
define('MAIL_FROM_ADDRESS', getEnvVar('MAIL_FROM_ADDRESS'));
define('MAIL_FROM_NAME', getEnvVar('MAIL_FROM_NAME', 'Bike Store'));
define('APP_URL', getEnvVar('APP_URL', 'http://localhost/Bike_Store'));

// Validar configuración
if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
    error_log('ERROR: Configuración de email incompleta en .env');
}
