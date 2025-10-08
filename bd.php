<?php
// bd.php - Conexión a la base de datos (versión en español de db.php)
// Ajusta estos valores según tu instalación de XAMPP
$host = '127.0.0.1'; // host o IP del servidor MySQL
$port = 3307;        // puerto MySQL (XAMPP normalmente usa 3306)
$db   = 'Bike_Store';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Manejo por si accidentalmente se puso el puerto en la variable $host
if (is_numeric($host)) {
    $port = (int)$host;
    $host = '127.0.0.1';
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    exit;
}
?>
